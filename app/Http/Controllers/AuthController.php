<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for user authentication"
 * )
 */
class AuthController extends Controller
{
      /**
     * @OA\Post(
     *     path="/login",
     *     summary="Login with employee credentials",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","employee_id"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="employee_id", type="string", example="EMP123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|laravel_sanctum_token..."),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="employee_id", type="string", example="EMP123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    
    public function login(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'employee_id' => 'required|string'
        ]);

        $user = User::where('employee_id', $request->employee_id)->first();

        if (!$user) {
            // Create new user if not exists
            $user = User::create([
                'name' => $request->name,
                'employee_id' => $request->employee_id
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
