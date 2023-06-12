<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponder
{
    /**
     * @param string $message
     * @param $data
     * @param int $code
     * @return JsonResponse
     */
    protected function success(string $message, $data = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            "message" => $message,
            "data" => $data
        ], $code);
    }

    /**
     * @param string $message
     * @param $data
     * @param int $code
     * @return JsonResponse
     */
    protected function error(string $message, $data = null, int $code = 400): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            "message" => $message,
            "data" => $data
        ], $code);
    }
}
