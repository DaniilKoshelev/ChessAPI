<?php

namespace App\Http;

class JsonResponse
{
    protected string $json;

    public function __construct($data)
    {
        $this->json = json_encode($data);
    }

    public function __toString(): string
    {
        return $this->json;
    }
}