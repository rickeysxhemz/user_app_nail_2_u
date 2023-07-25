<?php

namespace App\Services;

use App\Helper\Helper;
use App\Libs\Response\GlobalApiResponseCodeBook;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;

class ServiceDiscountService extends BaseService
{
    public function all()
    {
        try {
            $services_all = Service::where('artist_id', Auth::id())->orderBy('created_at', 'desc')->paginate(10);

            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_CREATED['outcomeCode'], $services_all->toArray());

        } catch (\Exception $e) {

            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("ServicesService: add", $error);
            return false;
        }
    }

    public function add($request)
    {
        try {
            $services = new Service();
            $services->artist_id = Auth::id();
            $services->name = $request->name;
            $services->price = $request->price;
            $services->save();

            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_CREATED['outcomeCode'], $services->toArray());

        } catch (\Exception $e) {

            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("ServicesService: add", $error);
            return false;
        }
    }

    public function edit($request)
    {
        try {

            $update_services = Service::
            where('artist_id', Auth::id())
                ->where('id', $request['services_id'])->first();

            if ($update_services) {
                $update_services->discount_percentage = $request['discount_percentage'];
                $update_services->start_date = $request['start_date'];
                $update_services->end_date = $request['end_date'];
                $update_services->save();

                return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_UPDATED, $update_services->toArray());
            }

            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode']);

        } catch (\Exception $e) {

            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("ServicesService: add", $error);
            return false;
        }
    }

    public function removeDiscount($id)
    {
        try {

            $update_services = Service::
            where('artist_id', Auth::id())
                ->where('id', $id)->first();

            if ($update_services) {
                $update_services->discount_percentage = null;
                $update_services->start_date = null;
                $update_services->end_date = null;

                return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_UPDATED, $update_services->toArray());
            }

            return Helper::returnRecord(GlobalApiResponseCodeBook::RECORD_NOT_EXISTS['outcomeCode']);

        } catch (\Exception $e) {

            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("ServicesService: add", $error);
            return false;
        }
    }
}
