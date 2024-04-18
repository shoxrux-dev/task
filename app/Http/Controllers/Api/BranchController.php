<?php

namespace App\Http\Controllers\Api;

use App\Const\ImageFolderPath;
use App\Http\Controllers\Controller;
use App\Http\Util\FileUtil;
use App\Models\Branch;
use App\Models\BranchImage;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class BranchController extends Controller
{
    /**
     * Create a new branch.
     * @OA\Post(
     *     path="/api/branch",
     *     summary="Create a new branch",
     *     tags={"Branch"},
     *     security={{"bearer":{}}},
     *     @OA\RequestBody(
     *          required=true,
     *          description="Branch data",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"name", "brand_id", "region_id", "district_id"},
     *                  @OA\Property(property="name", type="string", example="branch 1"),
     *                  @OA\Property(property="images[]", type="array", @OA\Items(type="string", format="binary")),
     *                  @OA\Property(property="brand_id", type="integer", example="1"),
     *                  @OA\Property(property="region_id", type="integer", example="1"),
     *                  @OA\Property(property="district_id", type="integer", example="1")
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *         response=201,
     *         description="Branch created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Branch created successfully"),
     *             @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(
     *                          property="name",
     *                          type="object",
     *                          @OA\Property(property="name", type="string"),
     *                          @OA\Property(property="brand_id", type="integer"),
     *                          @OA\Property(property="region_id", type="integer"),
     *                          @OA\Property(property="district_id", type="integer"),
     *                  ),
     *              )
     *          )
     *     ),
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createBranch(Request $request): JsonResponse
    {

        $validatedData = Validator::make($request->all(),
            [
                'name' => 'required|string',
                'brand_id' => 'required|integer|exists:brands,id',
                'region_id' => 'required|integer|exists:regions,id',
                'district_id' => 'required|integer|exists:districts,id',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

        if ($validatedData->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validatedData->errors()
            ], 422);
        }

        $branch = Branch::create([
            'name' => $request->name,
            'region_id' => $request->region_id,
            'district_id' => $request->district_id,
            'brand_id' => $request->brand_id
        ]);

        $savedImages = [];
        $failedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image->isValid()) {
                    $branchImage = new BranchImage();
                    $storedImagePath = FileUtil::store($image, ImageFolderPath::BRANCH);
                    $branchImage->image = $storedImagePath;
                    $branchImage->branch_id = $branch->id;
                    $branchImage->save();
                    $savedImages[] = $storedImagePath;
                } else {
                    $failedImages[] = $image->getClientOriginalName();
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Branch created successfully',
            'data' => [
                'branch' => $branch,
                'saved_images' => $savedImages,
                'failed_images' => $failedImages
            ]
        ], 201);

    }

    /**
     * Get a list of all branches.
     *
     * @OA\Get(
     *     path="/api/branches",
     *     summary="Get all branches",
     *     tags={"Branch"},
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of branches",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="brand_id", type="integer"),
     *                 @OA\Property(property="region_id", type="integer"),
     *                 @OA\Property(property="district_id", type="integer"),
     *             )
     *         )
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function getAllBranches(): JsonResponse
    {
        $branches = Branch::all();

        return response()->json($branches);
    }

    /**
     * Delete a branch by ID.
     *
     * @param int $id The ID of the branch to delete
     *
     * @OA\Delete(
     *     path="/api/branch/{id}",
     *     summary="Delete branch by ID",
     *     tags={"Branch"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the branch to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     security={{"bearer":{}}},
     *     @OA\Response(
     *         response=204,
     *         description="Branch deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Branch not found"
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function deleteBranch(int $id): JsonResponse
    {
        $branch = Branch::find($id);

        if (!$branch) {
            return response()->json([
                'status' => false,
                'message' => 'Branch not found'
            ], 404);
        }

        foreach ($branch->images as $branch_image) {
            FileUtil::delete($branch_image->image, ImageFolderPath::BRANCH);
            $branch_image->delete();
        }

        $branch->delete();

        return response()->json([
            'status' => true,
            'message' => 'Branch deleted successfully'
        ], 204);
    }

    /**
     * Update a branch's details.
     *
     * @OA\Post(
     *     path="/api/branch/{id}",
     *     summary="Update branch details",
     *     tags={"Branch"},
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Branch ID",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *           required=false,
     *           description="Branch data",
     *           @OA\MediaType(
     *               mediaType="multipart/form-data",
     *               @OA\Schema(
     *                   @OA\Property(property="name", type="string", example="branch 1"),
     *                   @OA\Property(property="images[]", type="array", @OA\Items(type="string", format="binary")),
     *                   @OA\Property(property="brand_id", type="integer", example="1"),
     *                   @OA\Property(property="region_id", type="integer", example="1"),
     *                   @OA\Property(property="district_id", type="integer", example="1")
     *               )
     *           )
     *       ),
     *     @OA\Response(
     *          response=201,
     *          description="Branch created successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Branch created successfully"),
     *              @OA\Property(
     *                   property="data",
     *                   type="object",
     *                   @OA\Property(
     *                           property="name",
     *                           type="object",
     *                          @OA\Property(property="name", type="string"),
     *                           @OA\Property(property="brand_id", type="integer"),
     *                           @OA\Property(property="region_id", type="integer"),
     *                           @OA\Property(property="district_id", type="integer"),
     *                   ),
     *               )
     *           )
     *      ),
     *     @OA\Response(
     *         response=404,
     *         description="Branch not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Branch not found")
     *         )
     *     )
     * )
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function updateBranch(Request $request, int $id): JsonResponse
    {
        $validatedData = Validator::make($request->all(),
            [
                'name' => 'nullable|string',
                'brand_id' => 'nullable|integer|exists:brands,id',
                'region_id' => 'nullable|integer|exists:regions,id',
                'district_id' => 'nullable|integer|exists:districts,id',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
            ]);

        if ($validatedData->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validatedData->errors()
            ], 422);
        }

        $branch = Branch::find($id);

        if (!$branch) {
            return response()->json([
                'status' => false,
                'message' => 'Branch not found'
            ], 404);
        }

        if(isset($request->name)) {
            $branch->name = $request->name;
        } elseif (isset($request->brand_id)) {
            $branch->brand_id = $request->brand_id;
        } elseif (isset($request->region_id)) {
            $branch->region_id = $request->region_id;
        } elseif (isset($request->district_id)) {
            $branch->district_id = $request->district_id;
        }

        $savedImages = [];
        $failedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image->isValid()) {
                    $branchImage = new BranchImage();
                    $storedImagePath = FileUtil::store($image, ImageFolderPath::BRANCH);
                    $branchImage->image = $storedImagePath;
                    $branchImage->branch_id = $branch->id;
                    $branchImage->save();
                    $savedImages[] = $storedImagePath;
                } else {
                    $failedImages[] = $image->getClientOriginalName();
                }
            }
        }

        $branch->update();

        return response()->json([
            'status' => true,
            'message' => 'Branch updated successfully',
            'data' => [
                'branch' => $branch,
                'saved_images' => $savedImages,
                'failed_images' => $failedImages
            ]
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/branch/{region_id}",
     *     summary="Get branch counts by region",
     *     tags={"Branch"},
     *     security={{"bearer":{}}},
     *     @OA\Parameter(
     *         name="region_id",
     *         in="path",
     *         description="ID of the region",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="region",
     *                 type="string",
     *                 description="Name of the region"
     *             ),
     *             @OA\Property(
     *                 property="districts",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         description="Name of the district"
     *                     ),
     *                     @OA\Property(
     *                         property="brands",
     *                         type="array",
     *                         @OA\Items(
     *                             @OA\Property(
     *                                 property="name",
     *                                 type="string",
     *                                 description="Name of the brand"
     *                             ),
     *                             @OA\Property(
     *                                 property="countOfBranches",
     *                                 type="integer",
     *                                 description="Number of branches for the brand in the district"
     *                             ),
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Region not found"
     *     )
     * )
     */

    public function getBranchByRegion(int $region_id): JsonResponse
    {
        $region = Region::find($region_id);

        if (!$region) {
            return response()->json([
                'status' => false,
                'message' => 'Region not found'
            ], 404);
        }

        $branchCounts = [
            'region' => $region->name,
            'districts' => [],
        ];

        $hasBranches = false;

        foreach ($region->districts as $district) {
            if ($district->branches->isNotEmpty()) {
                $hasBranches = true;

                $districtBrands = $district->branches->groupBy('brand.name')->map(function ($branches, $name) {
                    return [
                        'name' => $name,
                        'countOfBranches' => $branches->count()
                    ];
                })->values()->toArray();

                $branchCounts['districts'][] = [
                    'name' => $district->name,
                    'brands' => $districtBrands
                ];
            }
        }

        if (!$hasBranches) {
            return response()->json([
                'status' => false,
                'message' => 'No branches found in the region'
            ]);
        }

        return response()->json($branchCounts);
    }
}

