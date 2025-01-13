<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="HackR API",
 *     version="1.0.0",
 *     description="Voici la documentation swagger de l'API HackR réalisé par Haitam El Qassimi",
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
