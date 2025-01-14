<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="HackR API",
 *     version="1.0.0",
 *     description="Here is my Swagger documentation for my HackR API",
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
