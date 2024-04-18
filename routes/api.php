<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // user
    Route::get('/user/{phone}', [UserController::class, 'getUserByPhone']);
    Route::delete('/user/{id}', [UserController::class, 'deleteUser']);
    Route::put('/user/{id}', [UserController::class, 'updateUser']);
    Route::get('/users', [UserController::class, 'getAllUsers']);

    // brand
    Route::get('/brands', [BrandController::class, 'getAllBrands']);
    Route::post('/brand', [BrandController::class, 'createBrand']);
    Route::delete('/brand/{id}', [BrandController::class, 'deleteBrand']);
    Route::post('/brand/{id}', [BrandController::class, 'updateBrand']);

    // branches
    Route::get('/branches', [BranchController::class, 'getAllBranches']);
    Route::post('/branch', [BranchController::class, 'createBranch']);
    Route::delete('/branch/{id}', [BranchController::class, 'deleteBranch']);
    Route::post('/branch/{id}', [BranchController::class, 'updateBranch']);
    Route::get('/branch/{region_id}', [BranchController::class, 'getBranchByRegion']);

    // region
    Route::get('/regions', [RegionController::class, 'getAllRegions']);

    // district
    Route::get('/districts', [DistrictController::class, 'getAllDistricts']);

    // currency
    Route::get('/currencies', [CurrencyController::class, 'getAllCurrencies']);
});
