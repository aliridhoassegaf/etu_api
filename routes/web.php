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

Route::post('admin-login',[AdminController::class,'login']);
Route::post('user-login',[UserController::class,'login']);

Route::middleware(['jwt.auth'])->group(function () {
    Route::get('/admin',[AdminController::class,'read']);
    Route::get('/admin-role',[AdminRoleController::class,'read']);
    Route::get('/admin-access',[AdminAccessController::class,'read']);
    Route::get('/admin-activity',[AdminActivityController::class,'read']);

    Route::get('/user',[UserController::class,'read']);
    Route::get('/user-role',[UserRoleController::class,'read']);
    Route::get('/user-status',[UserStatusController::class,'read']);
    Route::get('/user-access',[UserAccessController::class,'read']);
    Route::get('/user-activity',[UserActivityController::class,'read']);
});


