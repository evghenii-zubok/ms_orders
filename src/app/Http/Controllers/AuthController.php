<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{    
    /**
    * @OA\Post(
    *     path="/api/login",
    *     operationId="login",
    *     tags={"Authentication"},
    *     summary="Authentication",
    *     description="Authenticate to receive access token to be used with other Api calls. Use ""Authorize"" button to get authenticated. Remeber to add ""Bearer "" suffix to token before using it.",
    *
    *     @OA\RequestBody(
    *         @OA\MediaType(
    *             mediaType="application/json",
    *             @OA\Schema(
    *                 @OA\Property(
    *                     property="client_id",
    *                     type="string",
    *                     default="Bob Lee",
    *                 ),
    *                 @OA\Property(
    *                     property="client_secret",
    *                     type="string",
    *                     default="MySuperSecretPassword",
    *                 ),
    *             ),
    *         ),
    *     ),
    *
    *     @OA\Response(
    *         response=200,
    *         description="Authenticated",
    *         @OA\JsonContent(
    *             type="object",
    *             @OA\Property(
    *                 type="boolean",
    *                 default="true",
    *                 description="status",
    *                 property="status",
    *             ),
    *             @OA\Property(
    *                 type="string",
    *                 default="25|iPUe4XPd6zfuNMSzRJQxnKnFh8YsYvK6LF9Xei39",
    *                 description="token",
    *                 property="token",
    *             ),
    *         ),
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Bad request",
    *     ),
    *     @OA\Response(
    *         response=401,
    *         description="Missing Authentication"
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="Internal server error"
    *     ),
    * )
    */
    public function login(Request $request) : JsonResponse
    {
        $request->validate([
            'client_id' => 'required',
            'client_secret' => 'required',
        ]);

        $user = User::where('client_id', $request->input('client_id'))->first();

        if (!$user || !Hash::check($request->input('client_secret'), $user->client_secret)) {

            return response()->json([
                'status' => false,
                'data' => 'Missing Authentication'
            ], 401);
        }

        // Generate a Sanctum token for the user
        $token = $user->createToken($user->client_id . '_' . Carbon::now()->format('yyyymmddhhiiss'))->plainTextToken;

        return response()->json([
            'status' => true,
            'token' => $token,
        ]);
    }
}
