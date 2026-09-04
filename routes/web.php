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
use App\Http\Controllers\VehicleColorController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleStatusController;
use App\Http\Controllers\CompanyPoolController;
use App\Http\Controllers\WebsiteHomeController;
use App\Http\Controllers\WebsiteHomeSliderController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormVersionController;
use App\Http\Controllers\VehicleCatalogController;
use App\Http\Controllers\VehicleTypeController;
use App\Http\Controllers\VehicleFuelController;
use App\Http\Controllers\UserEducationController;
use App\Http\Controllers\UserSimTypeController;
use App\Http\Controllers\UserLeadSourceController;
use App\Http\Controllers\UserWorkExperienceController;
use App\Http\Controllers\UserOnlineApplicationController;
use App\Http\Controllers\UserLengthOfStayController;
use App\Http\Controllers\CompanyVehicleRentalPeriodController;
use App\Http\Controllers\AssignmentStatusController;
use App\Http\Controllers\AssignmentController;

Route::post('admin-login',[AdminController::class,'login']);
Route::post('user-login',[UserController::class,'login']);

Route::get('/website-home/{id}', [WebsiteHomeController::class,'view']);
Route::get('/website-home-slider', [WebsiteHomeSliderController::class,'read']);
Route::get('/website-home-slider/{id}', [WebsiteHomeSliderController::class,'view']);

Route::middleware(['jwt.auth'])->group(function () {

    Route::get('/assignment',[AssignmentController::class,'read']);

    Route::get('/admin',[AdminController::class,'read']);
    Route::get('/admin/{id}', [AdminController::class,'view']);
    Route::post('/admin-logout',[AdminController::class,'logout']);

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

    Route::get('/form',[FormController::class,'read']);
    Route::get('/form/{id}', [FormController::class,'view']);

    Route::get('/form-version',[FormVersionController::class,'read']);
    Route::get('/form-version/{id}', [FormVersionController::class,'view']);

    Route::get('/vehicle-model',[VehicleModelController::class,'read']);
    Route::get('/vehicle-model/{id}', [VehicleModelController::class,'view']);

    Route::get('/vehicle-color',[VehicleColorController::class,'read']);
    
    Route::get('/vehicle-status',[VehicleStatusController::class,'read']);

    Route::get('/company-vehicle-rental-period',[CompanyVehicleRentalPeriodController::class,'read']);

    Route::get('/company-pool',[CompanyPoolController::class,'read']);
    Route::get('/company-pool/{id}', [CompanyPoolController::class,'view']);

    Route::get('/vehicle-catalog',[VehicleCatalogController::class,'read']);
    Route::get('/vehicle-catalog/{id}', [VehicleCatalogController::class,'view']);

    Route::get('/vehicle-type',[VehicleTypeController::class,'read']);

    Route::get('/vehicle-fuel',[VehicleFuelController::class,'read']);

    Route::get('/user-education',[UserEducationController::class,'read']);

    Route::get('/user-sim-type',[UserSimTypeController::class,'read']);

    Route::get('/user-lead-source',[UserLeadSourceController::class,'read']);

    Route::get('/user-work-experience',[UserWorkExperienceController::class,'read']);

    Route::get('/user-online-application',[UserOnlineApplicationController::class,'read']);

    Route::get('/user-length-of-stay',[UserLengthOfStayController::class,'read']);

    Route::get('/assignment-status',[AssignmentStatusController::class,'read']);
});


