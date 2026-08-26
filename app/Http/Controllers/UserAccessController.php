<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAccess;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Models\AdminActivity;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserAccessController extends Controller
{
    public function read(Request $request)
    {
        try {

            $search = $request->search;
            $perPage = $request->per_page ?? 20;

            $query = UserAccess::select(
                'user_access.id',
                'user_access.name',
                'user_access.initial',
                'user_access.super',
                'user_access.description',
                'user_access.created_at',
                'user_access.updated_at',
            );

            $query->where(function ($q) {
                $q->where('user_access.super', '!=', 1)
                    ->orWhereNull('user_access.super');
            });

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('user_access.name', 'ilike', '%' . $search . '%')
                        ->orWhere('user_access.initial', 'ilike', '%' . $search . '%');
                });
            }

            $data = $query->orderBy('user_access.initial', 'asc')
                ->orderBy('user_access.initial', 'asc')
                ->paginate($perPage)
                ->withPath(env('DASHBOARD_URL') . '/user-access')
                ->appends($request->query());

            return response()->json([
                'status' => true,
                'message' => 'Data retrieved successfully',
                'data' => $data->items(),
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