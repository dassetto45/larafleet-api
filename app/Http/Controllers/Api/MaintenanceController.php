<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(): JsonResponse
    {
        $maintenances = Maintenance::with(['vehicle', 'user'])
            ->latest()
            ->paginate(15);

        return response()->json($maintenances);
    }

    public function store(Request $request): JsonResponse
    {
        abort_if(!$request->user()->isAdmin(), 403, 'Unauthorized');

        $data = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'description' => 'required|string',
            'scheduled_at' => 'required|date',
        ]);

        $vehicle = Vehicle::findOrFail($data['vehicle_id']);
        $vehicle->update(['status' => 'maintenance']);

        $maintenance = $request->user()->maintenances()->create($data);

        return response()->json($maintenance->load('vehicle'), 201);
    }

    public function complete(Request $request, Maintenance $maintenance): JsonResponse
    {
        abort_if(!$request->user()->isAdmin(), 403, 'Unauthorized');

        $maintenance->update(['completed_at' => now()]);
        $maintenance->vehicle->update(['status' => 'available']);

        return response()->json($maintenance->load('vehicle'));
    }
}
