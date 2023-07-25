<?php

namespace App\Services;

use App\Helper\Helper;
use App\Models\ContactUs;
use Illuminate\Support\Facades\DB;
use Exception;

class ContactUsService extends BaseService
{
    public function contactUs($request)
    {
        try {
            DB::beginTransaction();
            $contact_us = new ContactUs();
            $contact_us->full_name = $request->full_name;
            $contact_us->email = $request->email;
            $contact_us->mobile = $request->mobile;
            $contact_us->message = $request->message;
            $contact_us->save();

            DB::commit();
            return $contact_us;

        } catch (Exception $e) {

            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("ContactUsService: contactUs", $error);
            return false;
        }
    }
}