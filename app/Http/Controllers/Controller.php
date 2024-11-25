<?php

namespace App\Http\Controllers;
/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="Toilet Finder API",
 *         description="Office Toilet Finder API documentation"
 *     ),
 *     @OA\Server(
 *         url="/api",
 *         description="API Server"
 *     ),
 *     @OA\Components(
 *         @OA\SecurityScheme(
 *             securityScheme="bearerAuth",
 *             type="http",
 *             scheme="bearer"
 *         )
 *     )
 * )
 */
abstract class Controller
{
}
