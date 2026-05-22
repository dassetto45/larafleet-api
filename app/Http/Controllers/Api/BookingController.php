<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendBookingConfirmationJob;
use App\Models\Booking;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BookingController extends Controller
{
    #[OA\Get(
        path: '/bookings',
        tags: ['Bookings'],
        summary: 'Lista prenotazioni',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista paginata di prenotazioni'),
            new OA\Response(response: 401, description: 'Non autenticato')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $bookings = $request->user()->isAdmin()
            ? Booking::with(['user', 'vehicle'])->latest()->paginate(15)
            : $request->user()->bookings()->with('vehicle')->latest()->paginate(15);

        return response()->json($bookings);
    }

    #[OA\Post(
        path: '/bookings',
        tags: ['Bookings'],
        summary: 'Crea prenotazione',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['vehicle_id', 'start_at', 'end_at'],
                properties: [
                    new OA\Property(property: 'vehicle_id', type: 'integer', example: 1),
                    new OA\Property(property: 'start_at', type: 'string', format: 'date-time', example: '2026-06-01 09:00:00'),
                    new OA\Property(property: 'end_at', type: 'string', format: 'date-time', example: '2026-06-03 18:00:00'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Prenotazione creata'),
            new OA\Response(response: 401, description: 'Non autenticato'),
            new OA\Response(response: 422, description: 'Errore di validazione')
        ]
    )]

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


    #[OA\Delete(
        path: '/bookings/{id}',
        tags: ['Bookings'],
        summary: 'Cancella prenotazione',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 204, description: 'Prenotazione cancellata'),
            new OA\Response(response: 403, description: 'Non autorizzato'),
            new OA\Response(response: 404, description: 'Prenotazione non trovata')
        ]
    )]

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
