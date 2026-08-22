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
                'admin.fullname',
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
                        'fullname' => $admin->fullname,
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