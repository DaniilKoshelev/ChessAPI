<?php

namespace App\Http;

use App\Exceptions\Http\HttpRequestException;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Exception\ValidationException;
use JsonSchema\Validator;

class JsonRequest
{
    /**
     * JSON data
     * @var Object
     */
    protected Object $json;

    /**
     * JsonRequest constructor. Get JSON data
     */
    public function __construct()
    {
        $this->json = json_decode(file_get_contents('php://input'));
    }

    /**
     * Validate JSON data according to given JSON schema
     * @param $schema
     * @throws ValidationException
     */
    public function tryValidate($schema)
    {
        $validator = new Validator();

        $validator->validate(
            $this->json,
            (object)['$ref' => 'file://' . realpath($schema)],
            Constraint::CHECK_MODE_EXCEPTIONS
        );
    }

    /**
     * Get JSON field from the request
     * @param string $field
     * @return string
     * @throws HttpRequestException
     */
    public function get(string $field): string
    {
        if (!property_exists($this->json, $field))
            throw new HttpRequestException('The request does not contain the field [' . $field . ']');

        return $this->json->$field;
    }
}