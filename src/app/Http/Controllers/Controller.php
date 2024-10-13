<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="0.0.1",
 *      x={
 *          "logo": {
 *              "url": "https://picsum.photos/190/90?text=Orders Management API Docs"
 *          }
 *      },
 *      title="Orders Management API Docs",
 *      description="",
 *      @OA\Contact(
 *          email="evghenii.zubok@gmail.com"
 *      )
 * )
 *
 * @OAS\SecurityScheme(
 *      securityScheme="sanctum",
 *      type="https",
 *      scheme="bearer"
 * )
 */

abstract class Controller {}
