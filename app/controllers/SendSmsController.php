<?php


class SendSmsController extends BaseController
{
	 public function fire($job, $data)
    {
    	
       $settings = Settings::where('key', 'sms_request_created')->first();
	   $pattern = $settings->value;
	   $pattern = str_replace('%user%', $data["first_name"] . " " . $data["last_name"], $pattern);
	   $pattern = str_replace('%id%', $data["request_id"], $pattern);
	   $pattern = str_replace('%user_mobile%', $data["phone"], $pattern);
					
	   sms_notification(1, 'admin', $pattern);
    }
}