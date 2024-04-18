<?php

namespace App\Http\Controllers\Api;

use App\Const\ImageFolderPath;
use App\Http\Controllers\Controller;
use App\Http\Util\FileUtil;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/brands",
     *     summary="Get all brands",
     *     tags={"Brand"},
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of brands",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="image", type="string")
     *             )
     *         )
     *     )
     * )
     *
     * @return JsonResponse
     */

    public function getAllBrands(): JsonResponse
    {
        $brands = Brand::all();

        return response()->json($brands, 200);
    }

    /**
     * Create a new brand.
     * @OA\Post(
     *     path="/api/brand",
     *     summary="Create a new brand",
     *     tags={"Brand"},
     *     security={{"bearer":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Brand data",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Brand created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Brand created successfully"),
     *             @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="id", type="integer"),
     *                  @OA\Property(property="name", type="string"),
     *                  @OA\Property(property="image", type="string")
     *              )
     *          )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The given data was invalid"),
     *             @OA\Property(property="errors", type="object", example={"name": {"The name field is required."}})
     *         )
     *     )
     * )
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function createBrand(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        if($request->hasFile('image')) {
            $brand->image = FileUtil::store($request->file('image'), ImageFolderPath::BRAND);
        }

        $brand->save();

        return response()->json([
            'status' => true,
            'message' => 'Brand created successfully',
            'data' => $brand
        ], 201);
    }

    /**
     * Delete a brand by ID.
     *
     * @param int $id The ID of the brand to delete
     *
     * @OA\Delete(
     *     path="/api/brand/{id}",
     *     summary="Delete brand by ID",
     *     tags={"Brand"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Brand ID",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=204,
     *         description="Brand deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Brand not found"
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function deleteBrand(int $id): JsonResponse
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json([
                'status' => false,
                'message' => 'Brand not found'
            ], 404);
        }
        FileUtil::delete($brand['image'], ImageFolderPath::BRAND);
        $brand->delete();

        return response()->json([
            'status' => true,
            'message' => 'Brand deleted successfully'
        ], 204);
    }

    /**
     * Update a brand's details.
     *
     * @OA\Post(
     *     path="/api/brand/{id}",
     *     summary="Update brand details",
     *     tags={"Brand"},
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Brand ID",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         description="Brand data",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="image", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brand updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Brand updated successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="image", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Brand not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Brand not found")
     *         )
     *     )
     * )
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function updateBrand(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json([
                'status' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        if(isset($request->name)) {
            $brand->name = $request->name;
        }

        if ($request->hasFile('image')) {
            FileUtil::delete($brand->image, ImageFolderPath::BRAND);
            $brand->image = FileUtil::store($request->file('image'), ImageFolderPath::BRAND);
        }

        $brand->update();

        return response()->json([
            'status' => true,
            'message' => 'Brand updated successfully',
            'data' => $brand
        ], 200);
    }

}
