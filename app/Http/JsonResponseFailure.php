<?php

namespace App\Http;

class JsonResponseFailure extends JsonResponse
{
    public function __construct(string $error)
    {
        $data['success'] = 'false';
        $data['error'] = $error;
        parent::__construct($data);
    }
}