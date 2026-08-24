<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminRoleController;
use App\Http\Controllers\AdminAccessController;
use App\Http\Controllers\AdminActivityController;

Route::post('admin-login',[AdminController::class,'login']);

Route::middleware(['jwt.auth'])->group(function () {
    Route::get('/admin',[AdminController::class,'read']);
    Route::get('/admin-role',[AdminRoleController::class,'read']);
    Route::get('/admin-access',[AdminAccessController::class,'read']);
    Route::get('/admin-activity',[AdminActivityController::class,'read']);
});


