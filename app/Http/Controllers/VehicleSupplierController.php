<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleSupplier;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Models\AdminActivity;
use Tymon\JWTAuth\Facades\JWTAuth;

class VehicleSupplierController extends Controller
{
    public function read(Request $request)
    {
        try {

            $search = $request->search;
            $status = $request->status;
            $perPage = $request->per_page ?? 20;

            $query = VehicleSupplier::select(
                'vehicle_supplier.id',
                'vehicle_supplier.name',
                'vehicle_supplier.slug',
                'vehicle_supplier.description',
                'vehicle_supplier.status',
                'vehicle_supplier.created_at',
            );

            $query->where('vehicle_supplier.id', '!=', env('UUID_SUPER'));

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('vehicle_supplier.name', 'ilike', '%' . $search . '%');
                });
            }

            if ($status !== null) {
                $query->where('vehicle_supplier.status', $status);
            }

            $data = $query->orderBy('vehicle_supplier.created_at', 'desc')
                ->paginate($perPage)
                ->withPath(env('DASHBOARD_URL') . '/vehicle-supplier')
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

    public function view($id)
    {
        try {
            $data = VehicleSupplier::select(
                'vehicle_supplier.id',
                'vehicle_supplier.name',
                'vehicle_supplier.slug',
                'vehicle_supplier.description',
                'vehicle_supplier.status',
                'vehicle_supplier.created_at',
                'vehicle_supplier.updated_at',
            )
                ->where('vehicle_supplier.id', $id)
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