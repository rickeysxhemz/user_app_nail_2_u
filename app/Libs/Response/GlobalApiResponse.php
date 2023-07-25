<?php

namespace App\Libs\Response;

use App\libs\ErrorBook;

/**
 * Class ApiResponse
 *
 * Generate standard response for REST Api
 *
 * @package App\libs\Response
 */
class GlobalApiResponse implements \JsonSerializable
{
    /**
     * @var string
     */
    private $outcome = "SUCCESS";

    /**
     * @var int
     */
    private $outcomeCode = 0;

    private $httpResponseCode = 0;

    /**
     * @var string
     */
    private $message = "";

    /**
     * @var int
     */
    private $numOfRecords = 0;

    /**
     * @var \stdClass
     */
    private $records;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $outcome
     */
    public function setOutcome($outcome)
    {
        $this->outcome = $outcome;
    }

    /**
     * @return string
     */
    public function getOutcome()
    {
        return $this->outcome;
    }

    /**
     * @param int $outcomeCode
     */
    public function setOutcomeCode($outcomeCode)
    {
        $this->outcomeCode = $outcomeCode;
    }

    /**
     * @return int
     */
    public function getOutcomeCode()
    {
        return $this->outcomeCode;
    }

    /**
     * @param int $numOfRecords
     */
    public function setNumOfRecords($numOfRecords)
    {
        $this->numOfRecords = $numOfRecords;
    }

    /**
     * @return int
     */
    public function getNumOfRecords()
    {
        return $this->numOfRecords;
    }

    /**
     * @param array|\stdClass $records
     */
    public function setRecords($records)
    {
        $this->records = $records;
    }

    /**
     * @return array|\stdClass
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * @param array $errors
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param int $outcome
     * @param int $outcomeCode
     * @param int $numOfRecords
     * @param string $message
     * @param array $records
     * @param array $errors
     * @return $this
     */
    public function setResponse($outcome, $httpResponseCode, $outcomeCode, $numOfRecords, $message, $records, $errors)
    {
        $this->outcome = $outcome;
        $this->outcomeCode = $outcomeCode;
        $this->httpResponseCode = $httpResponseCode;
        $this->message = $message;
        $this->numOfRecords = $numOfRecords;
        $this->records = $records ?: new \stdClass();
        $this->errors = $errors;

        return $this;
    }

    /**
     * @param string $message
     * @param int $numOfRecords
     * @param array $records
     * @return $this
     */
    public function success($message, $numOfRecords, $records)
    {
        $this->setResponse(
            GlobalApiResponseCodeBook::SUCCESS['outcome'],
            GlobalApiResponseCodeBook::SUCCESS['httpResponseCode'],
            GlobalApiResponseCodeBook::SUCCESS['outcomeCode'],
            $message,
            $numOfRecords,
            $records ?: new \stdClass(),
            []
        );

        return $this;
    }

    /**
     * @param array $outcomeArray example ['outcome' => '', 'outcomeCode' => '']
     * @param string $message
     * @param array $errors
     * @return $this
     */
    public function error(array $outcomeArray, $message, $errors)
    {
        $this->setResponse(
            $outcomeArray['outcome'],
            $outcomeArray['outcomeCode'],
            $outcomeArray['httpResponseCode'],
            0,
            $message,
            new \stdClass(),
            $errors
        );

        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            "_metadata" => [
                "outcome" => $this->outcome,
                "outcomeCode" => $this->outcomeCode,
                "httpResponseCode" => $this->httpResponseCode,
                "numOfRecords" => $this->numOfRecords,
                "message" => $this->message
            ],
            "records" => $this->records ?: new \stdClass(),
            "errors" => $this->errors
        ];
    }

    public function isSuccess()
    {
        return $this->outcomeCode == ErrorBook::API_SUCCESS;
    }
}
