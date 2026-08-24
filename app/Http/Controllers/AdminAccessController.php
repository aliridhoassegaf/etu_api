<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdminAccess;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Models\AdminActivity;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminAccessController extends Controller
{
    public function read(Request $request)
    {
        try {

            $search = $request->search;
            $perPage = $request->per_page ?? 20;

            $query = AdminAccess::select(
                'admin_access.id',
                'admin_access.name',
                'admin_access.initial',
                'admin_access.super',
                'admin_access.description',
                'admin_access.created_at',
                'admin_access.updated_at',
            );

            $query->where(function ($q) {
                $q->where('admin_access.super', '!=', 1)
                    ->orWhereNull('admin_access.super');
            });

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('admin_access.name', 'ilike', '%' . $search . '%')
                        ->orWhere('admin_access.initial', 'ilike', '%' . $search . '%');
                });
            }

            $data = $query->orderBy('admin_access.initial', 'asc')
                ->orderBy('admin_access.initial', 'asc')
                ->paginate($perPage)
                ->withPath(env('DASHBOARD_URL') . '/admin-access')
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