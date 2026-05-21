<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $vehicles = Vehicle::query()
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->type, fn($q, $v) => $q->where('type', $v))
            ->latest()
            ->paginate(15);

        return response()->json($vehicles);
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        return response()->json($vehicle);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'plate' => 'required|string|unique:vehicles',
            'brand' => 'required|string',
            'model' => 'required|string',
            'year' => 'required|integer|min:2000|max:2030',
            'km' => 'integer|min:0',
            'type' => 'required|in:car,truck,scooter,van,bus',
            'status' => 'in:available,in_use,maintenance',
        ]);

        $vehicle = Vehicle::create($data);

        return response()->json($vehicle, 201);
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'plate' => 'string|unique:vehicles,plate,' . $vehicle->id,
            'brand' => 'string',
            'model' => 'string',
            'year' => 'integer|min:2000|max:2030',
            'km' => 'integer|min:0',
            'type' => 'in:car,truck,scooter,van,bus',
            'status' => 'in:available,in_use,maintenance',
        ]);

        $vehicle->update($data);

        return response()->json($vehicle);
    }

    public function destroy(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorizeAdmin($request);
        $vehicle->delete();

        return response()->json(null, 204);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_if(!$request->user()->isAdmin(), 403, 'Unauthorized');
    }
}
