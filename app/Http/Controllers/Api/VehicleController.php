<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class VehicleController extends Controller
{
    #[OA\Get(
        path: '/vehicles',
        tags: ['Vehicles'],
        summary: 'Lista veicoli',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['available', 'in_use', 'maintenance'])
            ),
            new OA\Parameter(
                name: 'type',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['car', 'truck', 'scooter', 'van', 'bus'])
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lista paginata di veicoli'),
            new OA\Response(response: 401, description: 'Non autenticato')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $vehicles = Vehicle::query()
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->type, fn($q, $v) => $q->where('type', $v))
            ->latest()
            ->paginate(15);

        return response()->json($vehicles);
    }

    #[OA\Get(
        path: '/vehicles/{id}',
        tags: ['Vehicles'],
        summary: 'Dettaglio veicolo',
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
            new OA\Response(response: 200, description: 'Dettaglio veicolo'),
            new OA\Response(response: 404, description: 'Veicolo non trovato')
        ]
    )]
    public function show(Vehicle $vehicle): JsonResponse
    {
        return response()->json($vehicle);
    }

    #[OA\Post(
        path: '/vehicles',
        tags: ['Vehicles'],
        summary: 'Crea veicolo (admin)',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['plate', 'brand', 'model', 'year', 'type'],
                properties: [
                    new OA\Property(property: 'plate', type: 'string', example: 'AB123CD'),
                    new OA\Property(property: 'brand', type: 'string', example: 'Fiat'),
                    new OA\Property(property: 'model', type: 'string', example: 'Panda'),
                    new OA\Property(property: 'year', type: 'integer', example: 2022),
                    new OA\Property(property: 'km', type: 'integer', example: 0),
                    new OA\Property(property: 'type', type: 'string', enum: ['car', 'truck', 'scooter', 'van', 'bus']),
                    new OA\Property(property: 'status', type: 'string', enum: ['available', 'in_use', 'maintenance'])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Veicolo creato'),
            new OA\Response(response: 403, description: 'Non autorizzato'),
            new OA\Response(response: 422, description: 'Errore di validazione')
        ]
    )]
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

    #[OA\Put(
        path: '/vehicles/{id}',
        tags: ['Vehicles'],
        summary: 'Aggiorna veicolo (admin)',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'plate', type: 'string'),
                    new OA\Property(property: 'brand', type: 'string'),
                    new OA\Property(property: 'model', type: 'string'),
                    new OA\Property(property: 'year', type: 'integer'),
                    new OA\Property(property: 'km', type: 'integer'),
                    new OA\Property(property: 'type', type: 'string', enum: ['car', 'truck', 'scooter', 'van', 'bus']),
                    new OA\Property(property: 'status', type: 'string', enum: ['available', 'in_use', 'maintenance'])
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Veicolo aggiornato'),
            new OA\Response(response: 403, description: 'Non autorizzato'),
            new OA\Response(response: 404, description: 'Veicolo non trovato')
        ]
    )]
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

    #[OA\Delete(
        path: '/vehicles/{id}',
        tags: ['Vehicles'],
        summary: 'Elimina veicolo (admin)',
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
            new OA\Response(response: 204, description: 'Veicolo eliminato'),
            new OA\Response(response: 403, description: 'Non autorizzato'),
            new OA\Response(response: 404, description: 'Veicolo non trovato')
        ]
    )]
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
