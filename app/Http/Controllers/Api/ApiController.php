<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'LaraFleet API',
    version: '1.0.0',
    description: 'Fleet management system API',
    contact: new OA\Contact(email: 'admin@larafleet.test')
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
#[OA\Server(
    url: '/api/v1',
    description: 'API v1'
)]
class ApiController extends Controller {}
