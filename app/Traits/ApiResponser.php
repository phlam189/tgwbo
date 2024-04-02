<?php

namespace App\Traits;

use App\Enums\HttpStatus;

trait ApiResponser
{
    protected function successResponse(
        $data,
        $message = null,
        $code = HttpStatus::OK
    ) {
        return response()->json(
            [
                "status" => "Success",
                "message" => $message,
                "data" => $data,
            ],
            $code->value
        );
    }

    protected function errorResponse(
        $message = null,
        $err_code = null,
        $code = HttpStatus::BAD_REQUEST,
        $data = null
    ) {
        return response()->json(
            [
                "status" => "Error",
                "message" => $message,
                "code" => $err_code,
                "data" => $data,
            ],
            $code->value
        );
    }
}
