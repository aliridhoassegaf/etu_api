<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($message = 'Success', $data = [], $status = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public static function error($message = 'Error', $status = 500, $error = null)
    {
        $response = [
            'status' => false,
            'message' => $message
        ];

        if ($error) {
            $response['error'] = $error;
        }

        return response()->json($response, $status);
    }

    public static function tokenInvalid()
    {
        return response()->json([
            'status' => false,
            'message' => 'Token not valid'
        ], 401);
    }

    public static function tokenExpired()
    {
        return response()->json([
            'status' => false,
            'message' => 'Token expired'
        ], 401);
    }

    public static function serverError($error = null)
    {
        return response()->json([
            'status' => false,
            'message' => 'Server error',
            'error' => $error
        ], 500);
    }
}