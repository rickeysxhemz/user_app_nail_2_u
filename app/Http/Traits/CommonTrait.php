<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use Exception;
use Pusher;
use App\Helper\Helper;

trait CommonTrait {

    function all($fields = '*', $from_table = '', $where = [], $order_by = '', $dir = 'asc', $limit = '') {

        $query = DB::table($from_table);
        $query->select($fields);

        if ($where) {
            $result = $query->where($where);
        }
        if(!empty($order_by)){
            $query->orderByRaw($order_by . ' ' . $dir)->get();
        }

        if(!empty($limit)){
            $query->limit($limit)->get();
        }

        return $query->get();
    }

    function single($fields = '*', $from_table = '', $where = []) {
        $query = DB::table($from_table);
        $query->select($fields);

        if ($where) {
            $result = $query->where($where);
        }
        return $query->first();
    }

    function makeDropDown($params, $fistOption = 'Select Any Option', $id = '', $firstParam = '', $secondParam = '') {

        $dropDown = ['' => $fistOption];

        if ($fistOption == 'null') {
            array_pop($dropDown);
        }

        if (!empty($params)) {
            foreach ($params as $param) {
                if (!empty($id) && !empty($firstParam)) {
                    $dropDown[$param->id] = $param->$firstParam . ' ' . (!empty($param->$secondParam) ? ' - ' . $param->$secondParam : '');
                } else {
                    $dropDown[$param->id] = $param->name;
                }
            }
        }
        return $dropDown;
    }

    function form_dd($data = '', $options = array(), $selected = array(), $extra = '')
    {
        $defaults = array();

        if (is_array($data))
        {
            if (isset($data['selected']))
            {
                $selected = $data['selected'];
                unset($data['selected']); // select tags don't have a selected attribute
            }

            if (isset($data['options']))
            {
                $options = $data['options'];
                unset($data['options']); // select tags don't use an options attribute
            }
        }
        else
        {
            $defaults = array('name' => $data);
        }

        is_array($selected) OR $selected = array($selected);
        is_array($options) OR $options = array($options);

        // If no selected state was submitted we will attempt to set it automatically
        if (empty($selected))
        {
            if (is_array($data))
            {
                if (isset($data['name'], $_POST[$data['name']]))
                {
                    $selected = array($_POST[$data['name']]);
                }
            }
            elseif (isset($_POST[$data]))
            {
                $selected = array($_POST[$data]);
            }
        }

        $extra = $this->_attributes_to_string($extra);

        $multiple = (count($selected) > 1 && stripos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';

        $form = '<select '.rtrim($this->_parse_form_attributes($data, $defaults)).$extra.$multiple.">\n";

        foreach ($options as $key => $val)
        {
            $key = (string) $key;

            if (is_array($val))
            {
                if (empty($val))
                {
                    continue;
                }

                $form .= '<optgroup label="'.$key."\">\n";

                foreach ($val as $optgroup_key => $optgroup_val)
                {
                    $sel = in_array($optgroup_key, $selected) ? ' selected="selected"' : '';
                    $form .= '<option value="'.$this->html_escape($optgroup_key).'"'.$sel.'>'
                        .(string) $optgroup_val."</option>\n";
                }

                $form .= "</optgroup>\n";
            }
            else
            {
                $form .= '<option value="'.$this->html_escape($key).'"'
                    .(in_array($key, $selected) ? ' selected="selected"' : '').'>'
                    .(string) $val."</option>\n";
            }
        }

        return $form."</select>\n";
    }

    function _attributes_to_string($attributes)
    {
        if (empty($attributes))
        {
            return '';
        }

        if (is_object($attributes))
        {
            $attributes = (array) $attributes;
        }

        if (is_array($attributes))
        {
            $atts = '';

            foreach ($attributes as $key => $val)
            {
                $atts .= ' '.$key.'="'.$val.'"';
            }

            return $atts;
        }

        if (is_string($attributes))
        {
            return ' '.$attributes;
        }

        return FALSE;
    }

    function _parse_form_attributes($attributes, $default)
    {
        if (is_array($attributes))
        {
            foreach ($default as $key => $val)
            {
                if (isset($attributes[$key]))
                {
                    $default[$key] = $attributes[$key];
                    unset($attributes[$key]);
                }
            }

            if (count($attributes) > 0)
            {
                $default = array_merge($default, $attributes);
            }
        }

        $att = '';

        foreach ($default as $key => $val)
        {
            if ($key === 'value')
            {
                $val = $this->html_escape($val);
            }
            elseif ($key === 'name' && ! strlen($default['name']))
            {
                continue;
            }

            $att .= $key.'="'.$val.'" ';
        }

        return $att;
    }

    function html_escape($var, $double_encode = TRUE)
    {
        if (empty($var))
        {
            return $var;
        }

        if (is_array($var))
        {
            foreach (array_keys($var) as $key)
            {
                $var[$key] = $this->html_escape($var[$key], $double_encode);
            }

            return $var;
        }

        return htmlspecialchars($var, ENT_QUOTES, 'UTF-8', $double_encode);
    }

    function config_item($item)
    {
        static $_config;

        if (empty($_config))
        {
            // references cannot be directly assigned to static variables, so we use an array
            $_config[0] =& get_config();
        }

        return isset($_config[0][$item]) ? $_config[0][$item] : NULL;
    }
    
    function notifications($id, $title = '', $body = '', $status = '', $user) 
    {
        try {
            $url = 'https://fcm.googleapis.com/fcm/send';
            
            if($user == 'user'){
                $FcmToken = User::where('id', $id)->pluck('device_token')->all();
                $serverKey = 'AAAAJcOrpN0:APA91bEtslMX_50vBaw_f7QEcIIMYaqiYJA7o2s3D7q5GGZED6vAKC0XjrjjPzTXDC4my-2gmuqWSrffqfHXyeUKRL8eR8--ZOyrGHXUxFIT2Vd2pfJSfMqyVmiym8vrdZuigq7cuCbQ';
                
            } else {
                $FcmToken = User::where('id', $id)->pluck('device_token')->all();
                $serverKey = 'AAAA4w8dmVo:APA91bF-pLd04HYx0NK6exUBQL5EkspAWqwAGlJdTkWf1ZSqL6xx3xS2BJAOGpLE_AN1c-WqWcf5guUFxHaX9QBzFH9MsAgj2DtMwu2RJk9p3FRYm8QPsbeV3na1caY9L4TG4BQEV8XV';
            }
            $data = [
                "registration_ids" => $FcmToken,
                "data" => [
                    "status" => $status,
                ],
                "notification" => [
                    "title" => $title,
                    "body" => $body
                ]
            ];
            $encodedData = json_encode($data);
        
            $headers = [
                'Authorization:key=' . $serverKey,
                'Content-Type: application/json',
            ];
        
            $ch = curl_init();
        
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            // Disabling SSL Certificate support temporarly
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);

            // Execute post
            $result = curl_exec($ch);

            if ($result === FALSE) {
                die('Curl failed: ' . curl_error($ch));
            }        

            // Close connection
            curl_close($ch);
            
        }catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("CommonTrait: Notification", $error);
            return Helper::returnRecord(false, []);
        }
        
    }
    
    function pusher($id, $title, $body, $data) 
    {
        try {
            
            $url = 'https://fcm.googleapis.com/fcm/send';
            
            
            $FcmToken = User::where('id', $id)->pluck('device_token')->all();
            $serverKey = 'AAAA4w8dmVo:APA91bF-pLd04HYx0NK6exUBQL5EkspAWqwAGlJdTkWf1ZSqL6xx3xS2BJAOGpLE_AN1c-WqWcf5guUFxHaX9QBzFH9MsAgj2DtMwu2RJk9p3FRYm8QPsbeV3na1caY9L4TG4BQEV8XV';
            $data = [
                "registration_ids" => $FcmToken,
                "data" => $data,
                "notification" => [
                    "title" => $title,
                    "body" => $body
                ]
            ];
            $encodedData = json_encode($data);
        
            $headers = [
                'Authorization:key=' . $serverKey,
                'Content-Type: application/json',
            ];
        
            $ch = curl_init();
        
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            // Disabling SSL Certificate support temporarly
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);        
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);

            // Execute post
            $result = curl_exec($ch);

            if ($result === FALSE) {
                die('Curl failed: ' . curl_error($ch));
            }        

            // Close connection
            curl_close($ch);
            
        }catch (Exception $e) {
            $error = "Error: Message: " . $e->getMessage() . " File: " . $e->getFile() . " Line #: " . $e->getLine();
            Helper::errorLogs("CommonTrait: Pusher", $error);
            return Helper::returnRecord(false, []);
        }
        
    }
}
