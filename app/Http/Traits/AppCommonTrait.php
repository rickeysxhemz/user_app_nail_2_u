<?php

namespace App\Http\Traits;

use App\libs\ErrorBook;
use App\libs\Response\GlobalApiResponse;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Support\Facades\Request;
use Session;

trait AppCommonTrait
{
    public function helpReturn($records, $message = '')
    {
        if ($records) {
            $numOfRecords = 0;
            if (is_array($records)) {
                $numOfRecords = count($records);
            } elseif (preg_match('/App/i', get_class($records))) {
                $numOfRecords = 1;
            } elseif (preg_match('/Collection/i', get_class($records))) {
                $numOfRecords = $records->count();
            } elseif (preg_match('/Pagination/i', get_class($records))) {
                $data = ($records->toArray());
                $numOfRecords = count($data['data']);
            }
            /*
            if (is_array($records)) {
                $numOfRecords = count($records);
            }*/
            $response['_metadata'] = [
                'outcome' => "SUCCESS",
                'outcomeCode' => 0,
                'numOfRecords' => $numOfRecords,
                'message' => $message
            ];
            $response['records'] = $records;
        } else {
            $response = $this->helpError(40);
        }

        $response['errors'] = [];
        return $response;
    }

    public function makeError($reason, $message)
    {
        return ['map' => $reason, 'message' => $message];
    }

    public function makeInputErrors($messageBagArray)
    {
        $errors = [];
        foreach ($messageBagArray as $key => $message) {
            $errors[] = $this->makeError($key, $message[0]);
        }
        return $errors;
    }

    public function generateUniqueString()
    {
        return time() . md5(time()) . uniqid();
    }

    public function helpError($code, $codeInfo = false, $errorMessages = [])
    {
        $errors = [
            0 => 'SUCCESS',
            1 => 'SCRIPT_ERROR',
            2 => 'INVALID_PARAMS',
            3 => 'NO_ACCESS',
            4 => 'ALREADY_EXISTS',
            5 => 'INVALID_OR_AUTH_TOKEN_NOT_FOUND',
            6 => 'ALREADY_LOGGED_IN',
            7 => 'TOKEN_EXPIRED',
            8 => "NOT_LOGGED_IN",
            34 => 'DATABASE_ERROR',
            36 => 'LOGIN_FAILED',
            38 => 'USER_DOES_NOT_EXISTS',
            40 => 'DOES_NOT_EXISTS',
            56 => 'WRONG_VERIFICATION_CODE',
            70 => 'NO_ACCESS_FOR_THIS_ACTION',
            1000 => 'UNKOWN_ERROR',
            12 => 'ALREADY_LOGGED_IN',
            155 => 'CAPTCHA_ERROR'
        ];
        if (!isset($errors[$code])) {
            $code = 1000;
        }
        $response['_metadata'] = [
            'outcome' => $errors[$code],
            'outcomeCode' => $code,
            'numOfRecords' => 0,
            'message' => $codeInfo,
        ];
        $response['records'] = [];
        $response['errors'] = $errorMessages;
        return ($response);
    }


    public function serialize($data)
    {
        return base64_encode(serialize($data));
    }

    public function unserialize($data)
    {
        return unserialize(base64_decode($data));
    }

    public function getAllCookiesAsString()
    {
        $cookies = '';
        foreach ($_COOKIE as $key => $val) {
            $cookies .= ' ' . $key . '=' . $val . "; ";
        }
        return $cookies;
    }

    public function getUrlResponse($type, $url, $queryParams = [], $formParams = [], $isJSON = false, $headers = [], $multipart = [])
    {
        $httpClient = new GuzzleHttpClient();
        $optionalParams = [];

        $optionalParams['headers'] = [
            'Cookie' => $this->getAllCookiesAsString(),
        ];

        if(Request::has('loginToken')){
            $optionalParams['headers']['Authorization'] = 'Bearer '.Request::get('loginToken');
        }elseif(Session::has('token')){
            $optionalParams['headers']['Authorization'] = 'Bearer '.Session::get('token');
        }


        if (is_array($headers) && !empty($headers)) {
            $optionalParams['headers'] = array_merge($optionalParams['headers'], $headers);
        }

        if (is_array($formParams) && !empty($formParams)) {
            $optionalParams['form_params'] = $formParams;
        }

        if (is_array($queryParams) && !empty($queryParams)) {
            $optionalParams['query'] = $queryParams;
        }
        if (is_array($multipart) && !empty($multipart)) {
            $optionalParams['multipart'] = $multipart;
        }
        if ($isJSON) {
            $optionalParams['json'] = $formParams;
        }



//        if (Request::has('loginToken')) {
//            $optionalParams['headers'] = array_merge($optionalParams['headers'], [
//                'Authorization' => 'Bearer ' . Request::get('loginToken')
//            ]);
//        }

        $response = $httpClient->request($type, $url , $optionalParams);
        $response = $response->getBody()->getContents();
        return $response;
    }

    public function globalHelpResponse($return)
    {
        if ($return instanceof GlobalApiResponse) {
            if (!Request::ajax()) {
                if ($return->getOutcomeCode() == ErrorBook::API_USER_NOT_LOGGED_IN) {
                    return redirect('/login');
                }

            }
        }
        return $return;
    }
}