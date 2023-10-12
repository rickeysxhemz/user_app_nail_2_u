<?php

namespace App\Http\Controllers;

use App\Http\Requests\Booking\CreateBookingRequest;
use App\Libs\Response\GlobalApiResponse;
use App\Libs\Response\GlobalApiResponseCodeBook;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\returnArgument;

class BookingController extends Controller
{
    public function __construct(BookingService $BookingService, GlobalApiResponse $GlobalApiResponse)
    {
        $this->booking_service = $BookingService;
        $this->global_api_response = $GlobalApiResponse;
    }

    public function all(Request $request)
    {
        $bookings = $this->booking_service->all($request);

        if (!$bookings['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Booking history details did not fetched!", $bookings['record']));

        return ($this->global_api_response->success(count($bookings), "Booking history details fetched successfully!", $bookings['record']));
    }
    
    
    public function bookingDetail(Request $request)
    {
        $bookings = $this->booking_service->bookingDetail($request);
        if (!$bookings['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Booking history details did not fetched!", $bookings['record']));

        return ($this->global_api_response->success(count($bookings), "Booking history details fetched successfully!", $bookings['record']));
    }

    public function create(CreateBookingRequest $request)
    {

        $create_booking = $this->booking_service->create($request);

        if ($create_booking['outcomeCode'] === GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS, "Ids not found!", $create_booking['record']));

        if (!$create_booking['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Booking failed!", $create_booking['record']));

        return ($this->global_api_response->success(count($create_booking), "Booking created successfully!", $create_booking['record']));
    }

    public function getAvailableArtistTime(Request $request)
    {

        $available_time = $this->booking_service->getAvailableArtistTime($request);

        if (!$available_time['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Artist available details did not fetched!", $available_time['record']));

        return ($this->global_api_response->success(count($available_time), "Artist available details fetched successfully!", $available_time['record']));
    }
    public function cancelBooking($id)
    {
        $cancel_booking = $this->booking_service->cancelBooking($id);

        if (!$cancel_booking['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Booking cancel failed!", $cancel_booking['record']));

        return ($this->global_api_response->success(count($cancel_booking), "Booking cancel successfully!", $cancel_booking['record']));
    }
   public function getAvailableSlots(Request $request)
   {
        $available_slots = $this->booking_service->getAvailableSlots($request);

        if (!$available_slots['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Artist available slots details did not fetched!", $available_slots['record']));

        return ($this->global_api_response->success(count($available_slots), "Artist available slots details fetched successfully!", $available_slots['record']));
   }
   public function updateSchedular(Request $request)
   {
        $update_schedular = $this->booking_service->updateSchedular($request);

        if (!$update_schedular['outcomeCode'])
            return ($this->global_api_response->error(GlobalApiResponseCodeBook::INTERNAL_SERVER_ERROR, "Schedular update failed!", $update_schedular['record']));

        return ($this->global_api_response->success(count($update_schedular), "Schedular update successfully!", $update_schedular['record']));
   }
}
