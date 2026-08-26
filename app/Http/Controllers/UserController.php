<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Helpers\ApiResponse;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    public function read(Request $request)
    {
        try {

            $search = $request->search;
            $status = $request->status;
            $user_role_id = $request->user_role_id;
            $perPage = $request->per_page ?? 20;

            $query = User::select(
                'user.id',
                'user.fullname',
                'user.email',
                'user.phone',
                'user.user_role_id',
                'user.status',
                'user.created_at',
                'user_role.name as user_role_name',
                'user_status.name as status_name',
            )
                ->join('user_role', 'user.user_role_id', '=', 'user_role.id')
                ->join('user_status', 'user.status', '=', 'user_status.id');

            $query->where('user.id', '!=', env('UUID_SUPER'));

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('user.fullname', 'ilike', '%' . $search . '%')
                        ->orWhere('user.email', 'ilike', '%' . $search . '%');
                });
            }

            if ($status !== null) {
                $query->where('user.status', $status);
            }

            if ($user_role_id !== null) {
                $query->where('user.user_role_id', $user_role_id);
            }

            $data = $query->orderBy('user.created_at', 'desc')
                ->paginate($perPage)
                ->withPath(env('DASHBOARD_URL') . '/user')
                ->appends($request->query());

            $hasFilter =
                $request->filled('search') ||
                $request->filled('user_role_id') ||
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

            $user = User::select(
                'user.id',
                'user.fullname',
                'user.email',
                'user.password',
                'user.status',
                'user.user_role_id',
                'user_role.name as role_name',
                'user_role.user_permission as access'
            )
                ->join('user_role', 'user.user_role_id', '=', 'user_role.id')
                ->where('user.email', $request->email)
                ->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid email or password'
                ], 401);
            }

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid email or password'
                ], 401);
            }

            if ($user->status == 2) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account is inactive'
                ], 403);
            }

            if ($user->status == 3) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your account has not been confirmed'
                ], 403);
            }

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'fullname' => $user->fullname,
                        'email' => $user->email,
                        'role_id' => $user->user_role_id,
                        'role' => $user->role_name,
                        'access' => json_decode($user->access, true)
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