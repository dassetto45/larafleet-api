<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendBookingConfirmationJob;
use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bookings = $request->user()->isAdmin()
            ? Booking::with(['user', 'vehicle'])->latest()->paginate(15)
            : $request->user()->bookings()->with('vehicle')->latest()->paginate(15);

        return response()->json($bookings);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'start_at' => 'required|date|after:now',
            'end_at' => 'required|date|after:start_at',
            'notes' => 'nullable|string',
        ]);

        $vehicle = Vehicle::findOrFail($data['vehicle_id']);

        abort_if(!$vehicle->isAvailable(), 422, 'Vehicle is not available');

        $booking = $request->user()->bookings()->create([
            ...$data,
            'status' => 'active',
        ]);

        $vehicle->update(['status' => 'in_use']);

        SendBookingConfirmationJob::dispatch($booking);

        return response()->json($booking->load('vehicle'), 201);
    }

    public function destroy(Request $request, Booking $booking): JsonResponse
    {
        abort_if(
            !$request->user()->isAdmin() && $booking->user_id !== $request->user()->id,
            403,
            'Unauthorized'
        );

        $booking->vehicle->update(['status' => 'available']);
        $booking->update(['status' => 'cancelled']);

        return response()->json(null, 204);
    }
}
