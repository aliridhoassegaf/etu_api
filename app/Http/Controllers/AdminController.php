<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Helpers\ApiResponse;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function logout(Request $request)
    {
        try {

            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'status' => true,
                'message' => 'Your session has expired. Please login again to continue'
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
            if ($id == env('UUID_SUPER')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data not found'
                ], 404);
            }
            $data = Admin::select(
                'admin.id',
                'admin.full_name',
                'admin.email',
                'admin.phone',
                'admin.admin_role_id',
                'admin.status',
                'admin.created_at',
                'admin.updated_at',
                'admin_role.name as admin_role_name'
            )
                ->join('admin_role', 'admin.admin_role_id', '=', 'admin_role.id')
                ->where('admin.id', $id)
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
            $admin_role_id = $request->admin_role_id;
            $perPage = $request->per_page ?? 20;

            $query = Admin::select(
                'admin.id',
                'admin.full_name',
                'admin.slug',
                'admin.email',
                'admin.phone',
                'admin.admin_role_id',
                'admin.status',
                'admin.created_at',
                'admin_role.name as admin_role_name'
            )
                ->join('admin_role', 'admin.admin_role_id', '=', 'admin_role.id');

            $query->where('admin.id', '!=', env('UUID_SUPER'));

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('admin.full_name', 'ilike', '%' . $search . '%')
                        ->orWhere('admin.email', 'ilike', '%' . $search . '%');
                });
            }

            if ($status !== null) {
                $query->where('admin.status', $status);
            }

            if ($admin_role_id !== null) {
                $query->where('admin.admin_role_id', $admin_role_id);
            }

            $data = $query->orderBy('admin.created_at', 'desc')
                ->paginate($perPage)
                ->withPath(env('DASHBOARD_URL') . '/admin')
                ->appends($request->query());

            $hasFilter =
                $request->filled('search') ||
                $request->filled('admin_role_id') ||
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
    public function login(Request $request)
    {
        try {

            $token = $request->bearerToken();

            if (!$token || $token !== env('TOKEN_STATIC')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid token'
                ], 401);
            }

            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $admin = Admin::select(
                'admin.id',
                'admin.full_name',
                'admin.email',
                'admin.password',
                'admin.status',
                'admin.admin_role_id',
                'admin_role.name as role_name',
                'admin_role.admin_permission as access'
            )
                ->join('admin_role', 'admin.admin_role_id', '=', 'admin_role.id')
                ->where('admin.email', $request->email)
                ->first();

            if (!$admin) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid email or password'
                ], 401);
            }

            if (!Hash::check($request->password, $admin->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid email or password'
                ], 401);
            }

            if ($admin->status == 2) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account is inactive'
                ], 403);
            }

            if ($admin->status == 3) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account has not been confirmed'
                ], 403);
            }

            $token = JWTAuth::fromUser($admin);

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'admin' => [
                        'id' => $admin->id,
                        'full_name' => $admin->full_name,
                        'email' => $admin->email,
                        'role_id' => $admin->admin_role_id,
                        'role' => $admin->role_name,
                        'access' => json_decode($admin->access, true)
                    ]
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return ApiResponse::serverError($e->getMessage());

        }
    }
}