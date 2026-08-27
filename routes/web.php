<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminRoleController;
use App\Http\Controllers\AdminAccessController;
use App\Http\Controllers\AdminActivityController;

use App\Http\Controllers\UserStatusController;
use App\Http\Controllers\UserAccessController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\UserActivityController;

use App\Http\Controllers\VehicleSupplierController;
use App\Http\Controllers\VehicleBrandController;
use App\Http\Controllers\VehicleModelController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleStatusController;
use App\Http\Controllers\CompanyPoolController;

Route::post('admin-login',[AdminController::class,'login']);
Route::post('user-login',[UserController::class,'login']);

Route::middleware(['jwt.auth'])->group(function () {
    Route::get('/admin',[AdminController::class,'read']);
    Route::get('/admin/{id}', [AdminController::class,'view']);

    Route::get('/admin-role',[AdminRoleController::class,'read']);
    Route::get('/admin-role/{id}', [AdminRoleController::class,'view']);

    Route::get('/admin-access',[AdminAccessController::class,'read']);

    Route::get('/admin-activity',[AdminActivityController::class,'read']);
    Route::get('/admin-activity/{id}', [AdminActivityController::class,'view']);

    Route::get('/user',[UserController::class,'read']);
    Route::get('/user/{id}', [UserController::class,'view']);

    Route::get('/user-role',[UserRoleController::class,'read']);

    Route::get('/user-status',[UserStatusController::class,'read']);

    Route::get('/user-access',[UserAccessController::class,'read']);
    
    Route::get('/user-activity',[UserActivityController::class,'read']);

    Route::get('/vehicle',[VehicleController::class,'read']);
    Route::get('/vehicle/{id}', [VehicleController::class,'view']);

    Route::get('/vehicle-supplier',[VehicleSupplierController::class,'read']);
    Route::get('/vehicle-supplier/{id}', [VehicleSupplierController::class,'view']);

    Route::get('/vehicle-brand',[VehicleBrandController::class,'read']);
    Route::get('/vehicle-brand/{id}', [VehicleBrandController::class,'view']);

    Route::get('/vehicle-model',[VehicleModelController::class,'read']);
    Route::get('/vehicle-model/{id}', [VehicleModelController::class,'view']);
    
    Route::get('/vehicle-status',[VehicleStatusController::class,'read']);

    Route::get('/company-pool',[CompanyPoolController::class,'read']);
    Route::get('/company-pool/{id}', [CompanyPoolController::class,'view']);
});


