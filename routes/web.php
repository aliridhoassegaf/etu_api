<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminRoleController;
use App\Http\Controllers\AdminAccessController;

Route::post('admin-login',[AdminController::class,'login']);

Route::middleware(['jwt.auth'])->group(function () {
    Route::get('/admin-role',[AdminRoleController::class,'read']);
    Route::get('/admin-access',[AdminAccessController::class,'read']);
});


