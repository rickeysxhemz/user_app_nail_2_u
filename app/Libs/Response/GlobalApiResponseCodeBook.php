<?php

namespace App\Libs\Response;

class GlobalApiResponseCodeBook
{

    /**
     * A list of elements for SUCCESS API response
     *
     * @var array
     */
    const SUCCESS = [
        "outcome" => "SUCCESS",
        "outcomeCode" => 0,
        "httpResponseCode" => 200
    ];

    /**
     * A list of elements for RECORD_CREATED API response
     *
     * @var array
     */
    const RECORD_CREATED = [
        "outcome" => "RECORD_CREATED",
        "outcomeCode" => 14,
        "httpResponseCode" => 204
    ];

    const RECORD_UPDATED = [
        "outcome" => "RECORD_UPDATED",
        "outcomeCode" => 14,
        "httpResponseCode" => 200
    ];

    /**
     * A list of elements for INVALID_FORM_INPUTS API response
     *
     * @var array
     */
    const INVALID_FORM_INPUTS = [
        "outcome" => "INVALID_FORM_INPUTS",
        "outcomeCode" => 2,
        "httpResponseCode" => 400
    ];

    /**
     * A list of elements for NOT_AUTHORIZED API response
     *
     * @var array
     */
    const NOT_AUTHORIZED = [
        "outcome" => "NOT_AUTHORIZED",
        "outcomeCode" => 3,
        "httpResponseCode" => 401
    ];

    /**
     * A list of elements for INVALID_CREDENTIALS API response
     *
     * @var array
     */
    const INVALID_CREDENTIALS = [
        "outcome" => "INVALID_CREDENTIALS",
        "outcomeCode" => 4,
        "httpResponseCode" => 401
    ];

    /**
     * A list of elements for NOT_LOGGED_IN API response
     *
     * @var array
     */
    const NOT_LOGGED_IN = [
        "outcome" => "NOT_LOGGED_IN",
        "outcomeCode" => 5,
        "httpResponseCode" => 401
    ];

    /**
     * A list of elements for ACCESS_DENIED API response
     *
     * @var array
     */
    const ACCESS_DENIED = [
        "outcome" => "ACCESS_DENIED",
        "outcomeCode" => 6,
        "httpResponseCode" => 403
    ];

    /**
     * A list of elements for RECORD_NOT_EXISTS API response
     *
     * @var array
     */
    const RECORD_NOT_EXISTS = [
        "outcome" => "RECORD_NOT_EXISTS",
        "outcomeCode" => 7,
        "httpResponseCode" => 404
    ];

    /**
     * A list of elements for FILE_NOT_EXISTS API response
     *
     * @var array
     */
    const FILE_NOT_EXISTS = [
        "outcome" => "FILE_NOT_EXISTS",
        "outcomeCode" => 8,
        "httpResponseCode" => 404
    ];

    /**
     * A list of elements for RECORD_ALREADY_EXISTS API response
     *
     * @var array
     */
    const RECORD_ALREADY_EXISTS = [
        "outcome" => "RECORD_ALREADY_EXISTS",
        "outcomeCode" => 9,
        "httpResponseCode" => 409
    ];

    /**
     * A list of elements for INTERNAL_SERVER_ERROR API response
     *
     * @var array
     */
    const INTERNAL_SERVER_ERROR = [
        "outcome" => "INTERNAL_SERVER_ERROR",
        "outcomeCode" => 10,
        "httpResponseCode" => 500
    ];

    /**
     * A list of elements for EMAIL_NOT_VERIFIED API response
     *
     * @var array
     */
    const EMAIL_NOT_VERIFIED = [
        "outcome" => "EMAIL_NOT_VERIFIED",
        "outcomeCode" => 11,
        "httpResponseCode" => 401
    ];

    /**
     * A list of elements for LINK_EXPIRED API response
     *
     * @var array
     */
    const LINK_EXPIRED = [
        "outcome" => "LINK_EXPIRED",
        "outcomeCode" => 12,
        "httpResponseCode" => 410
    ];

    /**
     * A list of elements for EMAIL_DISPATCHED API response
     *
     * @var array
     */
    const EMAIL_DISPATCHED = [
        "outcome" => "EMAIL_DISPATCHED",
        "outcomeCode" => 13,
        "httpResponseCode" => 200
    ];

    const RECORDS_FOUND = [
        "outcome" => "RECORDS_FOUND",
        "outcomeCode" => 14,
        "httpResponseCode" => 404
    ];

    const COMPLETE_YOUR_PROFILE = [
        "outcome" => "COMPLETE_YOUR_PROFILE",
        "outcomeCode" => 15,
        "httpResponseCode" => 200
    ];
}
