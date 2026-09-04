<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WebsiteHomeSlider;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Models\AdminActivity;
use Tymon\JWTAuth\Facades\JWTAuth;

class WebsiteHomeSliderController extends Controller
{
    public function read(Request $request)
    {
        $token = $request->bearerToken();

        try {
            JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            if (!$token || $token !== env('TOKEN_STATIC')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token tidak valid'
                ], 401);
            }
        }

        try {

            $search = $request->search;
            $status = $request->status;
            $perPage = $request->per_page ?? 20;
            $with_sort = $request->with_sort;

            $query = WebsiteHomeSlider::select(
                'website_home_slider.id',
                'website_home_slider.name',
                'website_home_slider.slug',
                'website_home_slider.title',
                DB::raw("
                    CONCAT(
                        '" . env('STORAGE_URL') . "/',
                        'storage/images/slider/',
                        website_home_slider.image
                    ) AS image
                "),
                DB::raw("
                    CONCAT(
                        '" . env('WEBSITE_URL') . "/',
                        'p/',
                        website_home_slider.url
                    ) AS url
                "),
                'website_home_slider.sort',
                'website_home_slider.description',
                'website_home_slider.status',
                'website_home_slider.created_at',
            );

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('website_home_slider.name', 'ilike', '%' . $search . '%')
                        ->orWhere('website_home_slider.title', 'ilike', '%' . $search . '%')
                        ->orWhere('website_home_slider.url', 'ilike', '%' . $search . '%');
                });
            }

            if ($status !== null) {
                $query->where('website_home_slider.status', $status);
            }

            $sortField = $with_sort == 1 ? 'website_home_slider.sort' : 'website_home_slider.created_at';
            $sortDirection = $with_sort == 1 ? 'asc' : 'desc';

            $data = $query
                ->orderBy($sortField, $sortDirection)
                ->paginate($perPage)
                ->withPath(env('DASHBOARD_URL') . '/website-home-slider')
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

    public function view(Request $request, $id)
    {
        $token = $request->bearerToken();

        try {
            JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            if (!$token || $token !== env('TOKEN_STATIC')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token tidak valid'
                ], 401);
            }
        }

        try {
            $data = WebsiteHomeSlider::select(
                'website_home_slider.id',
                'website_home_slider.name',
                'website_home_slider.slug',
                'website_home_slider.title',
                DB::raw("
                    CONCAT(
                        '" . env('STORAGE_URL') . "/',
                        'storage/images/slider/',
                        website_home_slider.image
                    ) AS image
                "),
                DB::raw("
                    CONCAT(
                        '" . env('WEBSITE_URL') . "/',
                        'p/',
                        website_home_slider.url
                    ) AS url
                "),
                'website_home_slider.sort',
                'website_home_slider.description',
                'website_home_slider.status',
                'website_home_slider.created_at',
            )
                ->where('website_home_slider.id', $id)
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