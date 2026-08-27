<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleModel;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Models\AdminActivity;
use Tymon\JWTAuth\Facades\JWTAuth;

class VehicleModelController extends Controller
{
    public function view($id)
    {
        try {
            $data = VehicleModel::select(
                'vehicle_model.id',
                'vehicle_model.name',
                'vehicle_model.slug',
                'vehicle_model.description',
                'vehicle_model.vehicle_brand_id',
                'vehicle_model.status',
                'vehicle_model.created_at',
                'vehicle_model.updated_at',
                'vehicle_brand.name as vehicle_brand_name'
            )
                ->join('vehicle_brand', 'vehicle_model.vehicle_brand_id', '=', 'vehicle_brand.id')
                ->where('vehicle_model.id', $id)
                ->first();

            if (!$data) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data not found'
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Data retrieved successfully',
                'data' => $data
            ]);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return ApiResponse::tokenInvalid();

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return ApiResponse::tokenExpired();

        } catch (\Exception $e) {

            return ApiResponse::serverError($e->getMessage());

        }
    }
    public function read(Request $request)
    {
        try {

            $search = $request->search;
            $status = $request->status;
            $perPage = $request->per_page ?? 20;

            $query = VehicleModel::select(
                'vehicle_model.id',
                'vehicle_model.name',
                'vehicle_model.slug',
                'vehicle_model.description',
                'vehicle_model.status',
                'vehicle_model.created_at',
                'vehicle_brand.name as vehicle_brand_name'
            )
            ->join('vehicle_brand', 'vehicle_model.vehicle_brand_id', '=', 'vehicle_brand.id');
    
            $query->where('vehicle_model.id', '!=', env('UUID_SUPER'));

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('vehicle_model.name', 'ilike', '%' . $search . '%');
                });
            }

            if ($status !== null) {
                $query->where('vehicle_model.status', $status);
            }

            $data = $query->orderBy('vehicle_model.created_at', 'desc')
                ->paginate($perPage)
                ->withPath(env('DASHBOARD_URL') . '/vehicle-model')
                ->appends($request->query());

            $hasFilter =
                $request->filled('search') ||
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