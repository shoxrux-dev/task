<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    /**
     * Get a list of all users.
     *
     * @OA\Get(
     *     path="/api/districts",
     *     summary="Get all districts",
     *     tags={"District"},
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of districts",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(
     *                     property="id",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                  @OA\Property(
     *                      property="region_id",
     *                      type="integer"
     *                  ),
     *             )
     *         )
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function getAllDistricts(): JsonResponse
    {
        $districts = District::all();

        return response()->json($districts);
    }
}
