<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MaintenanceController extends Controller
{
    #[OA\Get(
        path: '/maintenances',
        tags: ['Maintenances'],
        summary: 'Lista manutenzioni',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Lista paginata di manutenzioni'),
            new OA\Response(response: 401, description: 'Non autenticato')
        ]
    )]
    public function index(): JsonResponse
    {
        $maintenances = Maintenance::with(['vehicle', 'user'])
            ->latest()
            ->paginate(15);

        return response()->json($maintenances);
    }

    #[OA\Post(
        path: '/maintenances',
        tags: ['Maintenances'],
        summary: 'Registra manutenzione (admin)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['vehicle_id', 'description', 'scheduled_at'],
                properties: [
                    new OA\Property(property: 'vehicle_id', type: 'integer', example: 1),
                    new OA\Property(property: 'description', type: 'string', example: 'Tagliando annuale'),
                    new OA\Property(property: 'scheduled_at', type: 'string', format: 'date', example: '2026-06-15')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Manutenzione registrata'),
            new OA\Response(response: 403, description: 'Non autorizzato')
        ]
    )]
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

    #[OA\Patch(
        path: '/maintenances/{id}/complete',
        tags: ['Maintenances'],
        summary: 'Completa manutenzione (admin)',
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
            new OA\Response(response: 200, description: 'Manutenzione completata'),
            new OA\Response(response: 403, description: 'Non autorizzato'),
            new OA\Response(response: 404, description: 'Manutenzione non trovata')
        ]
    )]
    public function complete(Request $request, Maintenance $maintenance): JsonResponse
    {
        abort_if(!$request->user()->isAdmin(), 403, 'Unauthorized');

        $maintenance->update(['completed_at' => now()]);
        $maintenance->vehicle->update(['status' => 'available']);

        return response()->json($maintenance->load('vehicle'));
    }
}
