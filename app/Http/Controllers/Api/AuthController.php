<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Register user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone", "password"},
     *             @OA\Property(property="phone", type="string", example="1234567890"),
     *             @OA\Property(property="password", type="string", example="secret")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User Registered Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User Registered Successfully"),
     *             @OA\Property(property="token", type="string", example="API_TOKEN")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation Error"),
     *             @OA\Property(property="errors", type="object", example={"phone": {"The phone field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error")
     *         )
     *     )
     * )
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validateUser = Validator::make($request->all(),
                [
                    'phone' => 'required|unique:users,phone',
                    'password' => 'required'
                ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::create([
                'phone' => $request->phone,
                'status' => 'active',
                'password' => Hash::make($request->password)
            ]);
            unset($user->password);
            return response()->json([
                'status' => true,
                'message' => 'User Registered Successfully',
                'data' => $user,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Authenticate and log in a user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phone", "password"},
     *             @OA\Property(property="phone", type="string", example="1234567890"),
     *             @OA\Property(property="password", type="string", example="secret")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User Logged In Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User Logged In Successfully"),
     *             @OA\Property(property="token", type="string", example="API_TOKEN")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Phone & Password does not match with our record.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Server Error")
     *         )
     *     )
     * )
     */
    public function login(Request $request): JsonResponse
    {
        // cron
        try {
            $validateUser = Validator::make($request->all(),
                [
                    'phone' => 'required',
                    'password' => 'required'
                ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if(!Auth::attempt($request->only(['phone', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Phone & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('phone', $request->phone)->first();

            if($user->status == 'block') {
                return response()->json([
                    'status' => true,
                    'message' => 'User is blocked',
                ], 403);
            }

            $user->last_login_at = now();
            $user->update();

            unset($user->password);

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'data' => $user,
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
