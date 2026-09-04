<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleCatalog;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Models\AdminActivity;
use Tymon\JWTAuth\Facades\JWTAuth;

class VehicleCatalogController extends Controller
{
    public function read(Request $request)
    {
        try {

            $status = $request->status;
            $vehicle_model_id = $request->vehicle_model_id;
            $perPage = $request->per_page ?? 20;
            $with_sort = $request->with_sort;

            $query = VehicleCatalog::select(
                'vehicle_catalog.id',
                'vehicle_catalog.vehicle_model_id',
                'vehicle_catalog.description',
                'vehicle_catalog.sort',
                'vehicle_catalog.status',
                'vehicle_catalog.created_at',
                'vehicle_model.name as vehicle_model_name',
                'vehicle_brand.name as vehicle_brand_name'
            )
            ->join('vehicle_model', 'vehicle_catalog.vehicle_model_id', '=', 'vehicle_model.id')
            ->join('vehicle_brand', 'vehicle_model.vehicle_brand_id', '=', 'vehicle_brand.id');

            if ($status !== null) {
                $query->where('vehicle_catalog.status', $status);
            }

            if ($vehicle_model_id !== null) {
                $query->where('vehicle_catalog.vehicle_model_id', $vehicle_model_id);
            }

            $sortField = $with_sort == 1 ? 'vehicle_catalog.sort' : 'vehicle_catalog.created_at';
            $sortDirection = $with_sort == 1 ? 'asc' : 'desc';

            $data = $query
                ->orderBy($sortField, $sortDirection)
                ->paginate($perPage)
                ->withPath(env('DASHBOARD_URL') . '/vehicle-catalog')
                ->appends($request->query());

            $hasFilter =
                $request->filled('vehicle_model_id') ||
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

    public function view($id)
    {
        try {
            $data = VehicleCatalog::select(
                'vehicle_catalog.id',
                'vehicle_catalog.vehicle_model_id',
                'vehicle_catalog.description',
                'vehicle_catalog.sort',
                'vehicle_catalog.status',
                'vehicle_catalog.created_at',
                'vehicle_model.name as vehicle_model_name',
                'vehicle_brand.name as vehicle_brand_name'
            )
                ->join('vehicle_model', 'vehicle_catalog.vehicle_model_id', '=', 'vehicle_model.id')
                ->join('vehicle_brand', 'vehicle_model.vehicle_brand_id', '=', 'vehicle_brand.id')
                ->where('vehicle_catalog.id', $id)
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
}