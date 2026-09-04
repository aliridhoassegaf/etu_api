<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WebsiteHome;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use App\Models\AdminActivity;
use Tymon\JWTAuth\Facades\JWTAuth;

class WebsiteHomeController extends Controller
{
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
            $data = WebsiteHome::select(
                'website_home.id',
                'website_home.about_title',
                'website_home.about_title',
                'website_home.about_image',
                'website_home.annual_report_title',
                'website_home.annual_report_short_description',
                'website_home.created_at',
                'website_home.updated_at',
            )
                ->where('website_home.id', $id)
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