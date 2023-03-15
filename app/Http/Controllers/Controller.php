<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function errors(string $message, int $statusCode = 400, $errors = null): JsonResponse
    {
        return response()->json([
            "message" => $message,
            "statusCode" => $statusCode,
            "errors" => $errors,
        ], $statusCode);
    }

    protected function success(string $message, $data = null, int $statusCode = 200){
        return response()->json([
            "message" => $message,
            "statusCode" => $statusCode,
            "data" => $data
        ]);
    }
}
