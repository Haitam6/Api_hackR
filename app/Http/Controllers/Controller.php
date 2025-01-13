<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="HackR API",
 *     version="1.0.0",
 *     description="HackR API Documentation",
 * )
 * 
 * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * type="http",
 * scheme="bearer",
 * bearerFormat="JWT"
 * )
 */

abstract class Controller
{
    //
}
