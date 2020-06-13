<?php

namespace App\Http;

class JsonResponseSuccess extends JsonResponse
{
    public function __construct($data = [])
    {
        $data['success'] = 'true';
        parent::__construct($data);
    }
}