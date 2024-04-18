<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    /**
     * Get a list of all users.
     *
     * @OA\Get(
     *     path="/api/regions",
     *     summary="Get all regions",
     *     tags={"Region"},
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of regions",
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
     *             )
     *         )
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function getAllRegions(): JsonResponse
    {
        $regions = Region::all();

        return response()->json($regions);
    }
}
