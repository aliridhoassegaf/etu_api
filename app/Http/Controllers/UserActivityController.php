<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserActivity;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserActivityController extends Controller
{
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
            $user_id = $request->user_id;
            $perPage = $request->per_page ?? 20;

            $query = UserActivity::select(
                'user_activity.id',
                'user_activity.target_id',
                'user_activity.target_name',
                'user_activity.description',
                'user_activity.ip',
                'user_activity.user_id',
                'user_activity.action',
                'user_activity.created_at',
                'user.fullname as user_fullname'
            )
            ->join('user', 'user_activity.user_id', '=', 'user.id');

            if ($createdStart && $createdEnd) {
                $query->whereBetween('user_activity.created_at', [
                    $createdStart . ' 00:00:00',
                    $createdEnd . ' 23:59:59'
                ]);
            } elseif ($createdStart) {
                $query->whereDate('user_activity.created_at', '>=', $createdStart);
            } elseif ($createdEnd) {
                $query->whereDate('user_activity.created_at', '<=', $createdEnd);
            }

            $query->where('user_activity.user_id', '!=', env('UUID_SUPER'));

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('user_activity.target_name', 'ilike', '%' . $search . '%')
                        ->orWhere('user_activity.description', 'ilike', '%' . $search . '%')
                        ->orWhere('user_activity.ip', 'ilike', '%' . $search . '%')
                        ->orWhere('user_activity.action', 'ilike', '%' . $search . '%');
                });
            }

            if ($user_id !== null) {
                $query->where('user_activity.user_id', $user_id);
            }

            $data = $query->orderBy('user_activity.created_at', 'desc')
                ->paginate($perPage)
                ->withPath(env('DASHBOARD_URL') . '/user-activity')
                ->appends($request->query());

            $hasFilter =
                $request->filled('search') ||
                $request->filled('user_id');

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