<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminActivity;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminActivityController extends Controller
{
    public function view($id)
    {
        try {
            if ($id == env('UUID_SUPER')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data not found'
                ], 404);
            }
            $data = AdminActivity::select(
                'admin_activity.id',
                'admin_activity.target_id',
                'admin_activity.target_name',
                'admin_activity.description',
                'admin_activity.ip',
                'admin_activity.changes',
                'admin_activity.admin_id',
                'admin_activity.created_at',
                'admin_activity.action',
                'admin_activity.message',
                'admin.fullname as admin_fullname'
            )
                ->join('admin', 'admin_activity.admin_id', '=', 'admin.id')
                ->where('admin_activity.id', $id)
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

            $request->validate([
                'created_start' => 'nullable|date',
                'created_end' => 'nullable|date',
            ]);

            $search = $request->search;
            $createdStart = $request->created_start;
            $createdEnd = $request->created_end;
            $admin_id = $request->admin_id;
            $perPage = $request->per_page ?? 20;

            $query = AdminActivity::select(
                'admin_activity.id',
                'admin_activity.target_id',
                'admin_activity.target_name',
                'admin_activity.description',
                'admin_activity.ip',
                'admin_activity.admin_id',
                'admin_activity.action',
                'admin_activity.created_at',
                'admin.fullname as admin_fullname'
            )
            ->join('admin', 'admin_activity.admin_id', '=', 'admin.id');

            if ($createdStart && $createdEnd) {
                $query->whereBetween('admin_activity.created_at', [
                    $createdStart . ' 00:00:00',
                    $createdEnd . ' 23:59:59'
                ]);
            } elseif ($createdStart) {
                $query->whereDate('admin_activity.created_at', '>=', $createdStart);
            } elseif ($createdEnd) {
                $query->whereDate('admin_activity.created_at', '<=', $createdEnd);
            }

            $query->where('admin_activity.admin_id', '!=', env('UUID_SUPER'));

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('admin_activity.target_name', 'ilike', '%' . $search . '%')
                        ->orWhere('admin_activity.description', 'ilike', '%' . $search . '%')
                        ->orWhere('admin_activity.ip', 'ilike', '%' . $search . '%')
                        ->orWhere('admin_activity.action', 'ilike', '%' . $search . '%');
                });
            }

            if ($admin_id !== null) {
                $query->where('admin_activity.admin_id', $admin_id);
            }

            $data = $query->orderBy('admin_activity.created_at', 'desc')
                ->paginate($perPage)
                ->withPath(env('DASHBOARD_URL') . '/admin-activity')
                ->appends($request->query());

            $hasFilter =
                $request->filled('search') ||
                $request->filled('admin_id');

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