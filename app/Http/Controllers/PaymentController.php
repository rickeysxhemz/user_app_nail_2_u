<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Libs\Response\GlobalApiResponse;
use App\Libs\Response\GlobalApiResponseCodeBook;

class PaymentController extends Controller
{
    public function __construct(PaymentService $PaymentService, GlobalApiResponse $GlobalApiResponse)
    {
        $this->payment_service = $PaymentService;
        $this->global_api_response = $GlobalApiResponse;
    }

    public function getDetails()
    {
        $payment_details = $this->payment_service->getDetails();

        if ($payment_details === GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "Payment record not found!", $payment_details));

        if (!$payment_details)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Payment details did not fetched!", $payment_details));

        return ($this->global_api_response->success(count($payment_details), "Payment details fetched successfully!", $payment_details));
    }

    public function getTotalEarning()
    {
        $total_earning = $this->payment_service->getTotalEarning();

        if (!$total_earning)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Total earning did not fetched!", $total_earning));

        return ($this->global_api_response->success(1, "Total earning fetched successfully!", $total_earning));
    }
    
    public function sendPayments(Request $request)
    {
        $send_Payments= $this->payment_service->sendPayments($request);

        if (!$send_Payments)
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Payment did not send!", $send_Payments));
        
        if ($send_Payments['outcomeCode'] === GlobalApiResponseCodeBook::INVALID_FORM_INPUTS['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INVALID_FORM_INPUTS, 'invalid card', $send_Payments));

        if(isset($request->booking_id)){
            return ($this->global_api_response->success(1, "Payment send successfully!", ['status' => '0'], $send_Payments));
        } else {
            return ($this->global_api_response->success(1, "Payment send successfully!", ['status' => '1'], $send_Payments));
        }
    }
}
