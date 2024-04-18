<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Get a user by phone number.
     *
     * @param string $phone
     * @return JsonResponse
     *
     * @OA\Get(
     *      path="/api/user/{phone}",
     *      operationId="getUserByPhone",
     *      tags={"User"},
     *      summary="Get user by phone number",
     *      description="Returns a user by phone.",
     *      @OA\Parameter(name="phone",in="path",required=true,description="Phone number of the user",
     *          @OA\Schema(type="string",)
     *      ),
     *     security={{"bearer":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="User found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status",type="boolean",example=true),
     *              @OA\Property(property="message",type="string",example="User found"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="User not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status",type="boolean",example=false),
     *              @OA\Property(property="message",type="string",example="User not found")
     *          )
     *      )
     * )
     */
    public function getUserByPhone(string $phone): JsonResponse
    {
        $user = User::where('phone', $phone)->first();

        if($user) {
            return response()->json([
                'status' => true,
                'message' => 'User found',
                'user' => $user
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }
    }

    /**
     * Delete a user by ID.
     *
     * @param int $id The ID of the user to delete
     *
     * @OA\Delete(
     *     path="/api/user/{id}",
     *     summary="Delete user by ID",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=204,
     *         description="User deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function deleteUser(int $id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully'
        ], 204);
    }

    /**
     * Get a list of all users.
     *
     * @OA\Get(
     *     path="/api/users",
     *     summary="Get all users",
     *     tags={"User"},
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="phone",
     *                     type="string"
     *                 ),
     *             )
     *         )
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function getAllUsers(): JsonResponse
    {
        $users = User::all();

        return response()->json($users);
    }

    /**
     * Update a user's details.
     *
     * @OA\Put(
     *     path="/api/user/{id}",
     *     summary="Update user details",
     *     tags={"User"},
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="User ID",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="phone", type="string", example=""),
     *             @OA\Property(property="password", type="string", example="")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User updated successfully"),
     *             @OA\Property(
     *                  property="id",
     *                  type="integer"
     *              ),
     *             @OA\Property(
     *                  property="phone",
     *                  type="string"
     *              ),
     *          )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not found"),
     *         )
     *     )
     * )
     *
     * @param  int  $id
     * @param  Request  $request
     * @return JsonResponse
     */
    public function updateUser(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        if(isset($request->phone)) {
            $user->phone = $request->phone;
        } elseif (isset($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->update();

        unset($user->password);

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ], 200);
    }

}
