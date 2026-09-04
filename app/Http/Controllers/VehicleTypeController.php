<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VehicleType;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Models\AdminActivity;
use Tymon\JWTAuth\Facades\JWTAuth;

class VehicleTypeController extends Controller
{
    public function read(Request $request)
    {
        try {

            $search = $request->search;
            $perPage = $request->per_page ?? 20;
            $with_sort = $request->with_sort;

            $query = VehicleType::select(
                'vehicle_type.id',
                'vehicle_type.name',
                'vehicle_type.status',
                'vehicle_type.created_at',
                'vehicle_type.updated_at',
            );

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('vehicle_type.name', 'ilike', '%' . $search . '%');
                });
            }

            $sortField = $with_sort == 1 ? 'vehicle_type.name' : 'vehicle_type.created_at';
            $sortDirection = $with_sort == 1 ? 'asc' : 'desc';

            $data = $query
                ->orderBy($sortField, $sortDirection)
                ->paginate($perPage)
                ->withPath(env('DASHBOARD_URL') . '/vehicle-type')
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