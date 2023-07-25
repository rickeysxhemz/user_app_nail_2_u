<?php

namespace App\Http\Traits;

trait GlobalErrorHandlingTrait
{
    private $errors = [];

    /**
     * Checking if there is any error occurs
     * @return bool
     */
    public function hasError()
    {
        return count($this->errors) ? true : false;
    }

    /**
     * clears the errors array
     */
    public function resetErrors()
    {
        $this->errors = [];
    }

    /**
     * append current errors array with new error
     * @param $name
     * @param $message
     */
    public function addError($name, $message)
    {
        $this->errors[] = [
            'map' => $name,
            'message' => $message
        ];
    }

    /**
     * append current errors array with new error
     * @param array $errors
     */
    public function addErrors($errors)
    {
        $this->errors = array_merge($this->errors, $errors);
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

}