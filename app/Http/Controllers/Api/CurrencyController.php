<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * Get a list of all currencies.
     *
     * @OA\Get(
     *     path="/api/currencies",
     *     summary="Get all currencies",
     *     tags={"Currency"},
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of currencies",
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
     *                 @OA\Property(
     *                     property="code",
     *                     type="string"
     *                 ),
     *             )
     *         )
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function getAllCurrencies(): JsonResponse
    {
        $currencies = Currency::all();

        return response()->json($currencies);
    }
}
