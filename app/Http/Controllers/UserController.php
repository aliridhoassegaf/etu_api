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
use Carbon\Carbon;
use App\Models\UserOnlineApplication;
use App\Models\UserLeadSource;

class UserController extends Controller
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
            $data = User::select(
                'user.id',
                'user.full_name',
                'user.email',
                'user.phone',
                'user.user_role_id',
                'user.status',
                'user.nik',
                'user.dob',
                'user.sim_number',
                DB::raw("
                    CONCAT(
                        '" . env('DASHBOARD_URL') . "/',
                        'assets/img/temp/',
                        \"user\".\"selfie_photo\"
                    ) AS selfie_photo
                "),
                DB::raw("
                    CONCAT(
                        '" . env('DASHBOARD_URL') . "/',
                        'assets/img/temp/',
                        \"user\".\"id_card_photo\"
                    ) AS id_card_photo
                "),
                DB::raw("
                    CONCAT(
                        '" . env('DASHBOARD_URL') . "/',
                        'assets/img/temp/',
                        \"user\".\"sim_photo\"
                    ) AS sim_photo
                "),
                DB::raw("
                    CONCAT(
                        '" . env('DASHBOARD_URL') . "/',
                        'assets/img/temp/',
                        \"user\".\"family_card_photo\"
                    ) AS family_card_photo
                "),
                'user.created_at',
                'user.updated_at',
                'user.user_online_application_id',
                'user.user_lead_source_id',
                'user_role.name as user_role_name',
                'user_status.name as status_name',
                'user_education.name as user_education_name',
                'user_sim_type.name as user_sim_type_name',
                'user_length_of_stay.name as user_length_of_stay_name',
                'user_work_experience.name as user_work_experience_name',
                'user_role.name as user_role_name',
                'kp.name as province_name',
                'kc.name as city_name',
                'kd.name as district_name',
                'kv.name as village_name',
                'user.full_address',
            )
                ->leftJoin('user_role', 'user.user_role_id', '=', 'user_role.id')
                ->leftJoin('user_status', 'user.status', '=', 'user_status.id')
                ->leftJoin('user_education', 'user.user_education_id', '=', 'user_education.id')
                ->leftJoin('user_sim_type', 'user.user_sim_type_id', '=', 'user_sim_type.id')
                ->leftJoin('user_length_of_stay', 'user.user_length_of_stay_id', '=', 'user_length_of_stay.id')
                ->leftJoin('user_work_experience', 'user.user_work_experience_id', '=', 'user_work_experience.id')
                ->leftJoin('location_province as kp', 'user.location_province_id', '=', 'kp.id')
                ->leftJoin('location_regency as kc', 'user.location_city_id', '=', 'kc.id')
                ->leftJoin('location_district as kd', 'user.location_district_id', '=', 'kd.id')
                ->leftJoin('location_village as kv', 'user.location_village_id', '=', 'kv.id')
                ->where('user.id', $id)
                ->first();

            if ($data && $data->dob) {
                $data->dob_format = Carbon::parse($data->dob)
                    ->locale('id')
                    ->translatedFormat('d F Y');
            }

            if ($data) {

                // Online Application
                $applicationIds = json_decode(
                    $data->user_online_application_id,
                    true
                ) ?? [];

                $applicationIds = array_map('intval', $applicationIds);

                $applicationNames = UserOnlineApplication::whereIn('id', $applicationIds)
                    ->pluck('name')
                    ->implode(', ');

                $data->user_online_application_id = $applicationIds;
                $data->user_online_application_name = $applicationNames;


                // Lead Source
                $leadSourceIds = json_decode(
                    $data->user_lead_source_id,
                    true
                ) ?? [];

                $leadSourceIds = array_map('intval', $leadSourceIds);

                $leadSourceNames = UserLeadSource::whereIn('id', $leadSourceIds)
                    ->pluck('name')
                    ->implode(', ');

                $data->user_lead_source_id = $leadSourceIds;
                $data->user_lead_source_name = $leadSourceNames;
            }

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
            $user_role_id = $request->user_role_id;
            $perPage = $request->per_page ?? 20;

            $query = User::select(
                'user.id',
                'user.full_name',
                'user.email',
                'user.phone',
                'user.user_role_id',
                'user.status',
                'user.nik',
                'user.created_at',
                'user_status.name as status_name',
            )
                ->join('user_status', 'user.status', '=', 'user_status.id');

            $query->where('user.id', '!=', env('UUID_SUPER'));

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('user.full_name', 'ilike', '%' . $search . '%')
                        ->orWhere('user.nik', 'ilike', '%' . $search . '%')
                        ->orWhere('user.phone', 'ilike', '%' . $search . '%')
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
                'user.full_name',
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
                        'full_name' => $user->full_name,
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