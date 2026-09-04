<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Models\AdminActivity;
use Tymon\JWTAuth\Facades\JWTAuth;

class AssignmentController extends Controller
{
    public function read(Request $request)
    {
        try {

            $status = $request->status;
            $vehicle_id = $request->vehicle_id;
            $user_id = $request->user_id;
            $company_vehicle_rental_period_id = $request->company_vehicle_rental_period_id;
            $perPage = $request->per_page ?? 20;

            $query = Assignment::select(
                'assignment.id',
                'assignment.vehicle_id',
                'assignment.status',
                'assignment.created_at',
                'assignment.user_id',
                'assignment.company_vehicle_rental_period_id',
                'assignment.start_date',
                'assignment.end_date',
                'vehicle.plat_number',
                'vehicle.year',
                'user.full_name as user_full_name',
                'user.nik as user_nik',
                'company_vehicle_rental_period.name as company_vehicle_rental_period_name',
                'vehicle_model.name as vehicle_model_name',
                'vehicle_brand.name as vehicle_brand_name',
                'assignment_status.name as assignment_status_name',
            )
            ->join('vehicle', 'assignment.vehicle_id', '=', 'vehicle.id')
            ->join('vehicle_model', 'vehicle.vehicle_model_id', '=', 'vehicle_model.id')
            ->join('vehicle_brand', 'vehicle.vehicle_brand_id', '=', 'vehicle_brand.id')
            ->join('user', 'assignment.user_id', '=', 'user.id')
            ->join('company_vehicle_rental_period', 'assignment.company_vehicle_rental_period_id', '=', 'company_vehicle_rental_period.id')
            ->join('assignment_status', 'assignment.status', '=', 'assignment_status.id');

            if ($status !== null) {
                $query->where('assignment.status', $status);
            }
            
            if ($vehicle_id !== null) {
                $query->where('assignment.vehicle_id', $vehicle_id);
            }

            if ($user_id !== null) {
                $query->where('assignment.user_id', $user_id);
            }

            if ($company_vehicle_rental_period_id !== null) {
                $query->where('assignment.company_vehicle_rental_period_id', $company_vehicle_rental_period_id);
            }
            
            $data = $query->orderBy('assignment.created_at', 'desc')
                ->paginate($perPage)
                ->withPath(env('DASHBOARD_URL') . '/assignment')
                ->appends($request->query());

            $hasFilter =
                $request->filled('vehicle_id') ||
                $request->filled('user_id') ||
                $request->filled('company_vehicle_rental_period_id') ||
                $request->filled('status');

            if ($data->total() === 0) {

                $dataState = $hasFilter
                    ? 'filtered_empty'
                    : 'empty';

            } else {

                $dataState = 'has_data';

            }

            return response()->json([
                'status' => true,
                'message' => 'Data retrieved successfully',
                'data' => $data->items(),
                'data_state' => $dataState,
                'pagination' => [
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'from' => $data->firstItem(),
                    'to' => $data->lastItem(),

                    'first_page_url' => $data->url(1),
                    'last_page_url' => $data->url($data->lastPage()),
                    'next_page_url' => $data->nextPageUrl(),
                    'prev_page_url' => $data->previousPageUrl(),
                    'path' => $data->path()
                ]
            ]);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return ApiResponse::tokenInvalid();

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return ApiResponse::tokenExpired();

        } catch (\Exception $e) {

            return ApiResponse::serverError($e->getMessage());

        }
    }
}