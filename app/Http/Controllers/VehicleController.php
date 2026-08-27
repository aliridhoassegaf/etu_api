<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Models\AdminActivity;
use Tymon\JWTAuth\Facades\JWTAuth;

class VehicleController extends Controller
{
    public function view($id)
    {
        try {
            $data = Vehicle::select(
                'vehicle.id',
                'vehicle.description',
                'vehicle.status',
                'vehicle.created_at',
                'vehicle_model.name as vehicle_model_name',
                'vehicle_brand.name as vehicle_brand_name',
                'vehicle_supplier.name as vehicle_supplier_name',
                'vehicle_status.name as status_name',
            )
                ->join('vehicle_model', 'vehicle.vehicle_model_id', '=', 'vehicle_model.id')
                ->join('vehicle_brand', 'vehicle.vehicle_brand_id', '=', 'vehicle_brand.id')
                ->join('vehicle_supplier', 'vehicle.vehicle_supplier_id', '=', 'vehicle_supplier.id')
                ->join('vehicle_status', 'vehicle.status', '=', 'vehicle_status.id')
                ->where('vehicle.id', $id)
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

            $status = $request->status;
            $vehicle_model_id = $request->vehicle_model_id;
            $vehicle_brand_id = $request->vehicle_brand_id;
            $vehicle_supplier_id = $request->vehicle_supplier_id;
            $perPage = $request->per_page ?? 20;

            $query = Vehicle::select(
                'vehicle.id',
                'vehicle.description',
                'vehicle.status',
                'vehicle.created_at',
                'vehicle_model.name as vehicle_model_name',
                'vehicle_brand.name as vehicle_brand_name',
                'vehicle_supplier.name as vehicle_supplier_name',
                'vehicle_status.name as status_name',
            )
            ->join('vehicle_model', 'vehicle.vehicle_model_id', '=', 'vehicle_model.id')
            ->join('vehicle_brand', 'vehicle.vehicle_brand_id', '=', 'vehicle_brand.id')
            ->join('vehicle_supplier', 'vehicle.vehicle_supplier_id', '=', 'vehicle_supplier.id')
            ->join('vehicle_status', 'vehicle.status', '=', 'vehicle_status.id');

            $query->where('vehicle.id', '!=', env('UUID_SUPER'));

            if ($status !== null) {
                $query->where('vehicle.status', $status);
            }
            
            if ($vehicle_model_id !== null) {
                $query->where('vehicle.vehicle_model_id', $vehicle_model_id);
            }

            if ($vehicle_brand_id !== null) {
                $query->where('vehicle.vehicle_brand_id', $vehicle_brand_id);
            }

            if ($vehicle_supplier_id !== null) {
                $query->where('vehicle.vehicle_supplier_id', $vehicle_supplier_id);
            }

            $data = $query->orderBy('vehicle.created_at', 'desc')
                ->paginate($perPage)
                ->withPath(env('DASHBOARD_URL') . '/vehicle')
                ->appends($request->query());

            $hasFilter =
                $request->filled('vehicle_model_id') ||
                $request->filled('vehicle_brand_id') ||
                $request->filled('vehicle_supplier_id') ||
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