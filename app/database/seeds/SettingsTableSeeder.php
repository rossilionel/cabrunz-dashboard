<?php
class SettingsTableSeeder extends Seeder {

    public function run()
    {
        Settings::create(array('id' => 1,'tool_tip' => 'This is the default unit of distance','page'=>'1','key' => 'default_distance_unit' , 'value' => '0'));
        Settings::create(array('id' => 2,'tool_tip' => 'Default Changing method for users','page'=>'1','key' => 'default_charging_method_for_users' , 'value' => '1'));
        Settings::create(array('id' => 3,'tool_tip' => 'Incase of Fixed price payment, Base price is the total amount thats charged to users','page'=>'1','key' => 'base_price' , 'value' => '50'));
        Settings::create(array('id' => 4,'tool_tip' => 'Needed only incase of time and distance based payment','page'=>'1','key' => 'price_per_unit_distance' , 'value' => '10'));
        Settings::create(array('id' => 5,'tool_tip' => 'Needed only incase of time and distance based payment','page'=>'1','key' => 'price_per_unit_time' , 'value' => '8'));
        Settings::create(array('id' => 6,'tool_tip' => 'Maximum time for provider to respond for a request','page'=>'1','key' => 'provider_timeout' , 'value' => '60'));
        Settings::create(array('id' => 7,'tool_tip' => 'Send SMS Notifications','page'=>'1','key' => 'sms_notification' , 'value' => '0'));
        Settings::create(array('id' => 8,'tool_tip' => 'Send Email Notifications','page'=>'1','key' => 'email_notification' , 'value' => '1'));
        Settings::create(array('id' => 9,'tool_tip' => 'Send Push Notifications','page'=>'1','key' => 'push_notification' , 'value' => '1'));
        Settings::create(array('id' => 10,'tool_tip' => 'Bonus credit that should be added incase if user refers another','page'=>'1','key' => 'default_referral_bonus' , 'value' => '10'));
        Settings::create(array('id' => 11,'tool_tip' => 'This mobile number will get SMS notifications about requests','page'=>'1','key' => 'admin_phone_number' , 'value' => '+917708288018'));
        Settings::create(array('id' => 12,'tool_tip' => 'This address will get Email notifications about requests','page'=>'1','key' => 'admin_email_address' , 'value' => 'prabakaranbs@gmail.com'));
        Settings::create(array('id' => 13,'tool_tip' => 'This Template will be used to notify user by SMS when a provider the accepts request','page'=>'2','key' => 'sms_when_provider_accepts' , 'value' => 'Hi %user%, Your request is accepted by %driver%. You can reach him by %driver_mobile%'));
        Settings::create(array('id' => 14,'tool_tip' => 'This Template will be used to notify user by SMS when a provider the arrives','page'=>'2','key' => 'sms_when_provider_arrives' , 'value' => 'Hi %user%, The %driver% has arrived at your location.You can reach user by %driver_mobile%'));
        Settings::create(array('id' => 15,'tool_tip' => 'This Template will be used to notify user by SMS when a provider the completes the service','page'=>'2','key' => 'sms_when_provider_completes_job' , 'value' => 'Hi %user%, Your request is successfully completed by %driver%. Your Bill amount id %amount%'));
        Settings::create(array('id' => 16,'tool_tip' => 'This Template will be used to notify admin by SMS when a new request is created','page'=>'2','key' => 'sms_request_created' , 'value' => 'Request id %id% is created by %user%, You can reach him by %user_mobile%'));
        Settings::create(array('id' => 17,'tool_tip' => 'This Template will be used to notify admin by SMS when a request remains unanswered by all providers','page'=>'2','key' => 'sms_request_unanswered' , 'value' => 'Request id %id% created by %user% is left unanswered, You can reach user by %user_mobile%'));
        Settings::create(array('id' => 18,'tool_tip' => 'This Template will be used to notify admin by SMS when a request is completed','page'=>'2','key' => 'sms_request_completed' , 'value' => 'Request id %id% created by %user% is completed, You can reach user by %user_mobile%'));
        Settings::create(array('id' => 19,'tool_tip' => 'This Template will be used to notify admin by SMS when payment is generated for a request','page'=>'2','key' => 'sms_payment_generated' , 'value' => 'Payment for Request id %id% is generated.'));
        
        Settings::create(array('id' => 20,'tool_tip' => 'This Template will be used to notify users and providers by email when they reset their password','page'=>'3','key' => 'email_forgot_password' , 'value' => 'Your New Password is %password%. Please dont forget to change the password once you log in next time.'));
        Settings::create(array('id' => 21,'tool_tip' => 'This Template will be used for welcome mail to provider','page'=>'3','key' => 'email_walker_new_registration' , 'value' => 'Welcome on Board %name% , After Logged in to your account Upload your documents to get approve from the admin side , Please Activation your Email here %link% . Upload your documents and someone will look into your application and get back.'));
        Settings::create(array('id' => 22,'tool_tip' => 'This Template will be used for welcome mail to user','page'=>'3','key' => 'email_owner_new_registration' , 'value' => 'Welcome on Board %name%'));
        Settings::create(array('id' => 23,'tool_tip' => 'This Template will be used notify admin by email when a new request is created','page'=>'3','key' => 'email_new_request' , 'value' => 'New Request %id% is created. Follow the request through %url%'));
        Settings::create(array('id' => 24,'tool_tip' => 'This Template will be used notify admin by email when a request remains unanswerd by all providers','page'=>'3','key' => 'email_request_unanswered' , 'value' => 'Request %id% has beed declined by all providers. Follow the request through %url%'));
        Settings::create(array('id' => 25,'tool_tip' => 'This Template will be used notify admin by email when a request is completed','page'=>'3','key' => 'email_request_finished' , 'value' => 'Request %id% is finished. Follow the request through %url%'));
        Settings::create(array('id' => 26,'tool_tip' => 'This Template will be used notify admin by email when a client is charged for a request','page'=>'3','key' => 'email_payment_charged' , 'value' => 'Request %id% is finished. Follow the request through %url%'));
        Settings::create(array('id' => 27,'tool_tip' => 'This Template will be used notify user by email when invoice is generated','page'=>'3','key' => 'email_invoice_generated_user' , 'value' => 'invoice for Request id %id% is generated. Total amount is %amount%'));
        Settings::create(array('id' => 28,'tool_tip' => 'This Template will be used notify provider by email when invoice is generated','page'=>'3','key' => 'email_invoice_generated_provider' , 'value' => 'invoice for Request id %id% is generated. Total amount is %amount%'));
        Settings::create(array('id' => 29,'tool_tip' => 'This is latitude for the map center','page'=>'1','key' => 'map_center_latitude' , 'value' => '0'));
        Settings::create(array('id' => 30,'tool_tip' => 'This is longitude for the map center','page'=>'1','key' => 'map_center_longitude' , 'value' => '0'));
        Settings::create(array('id' => 31,'tool_tip' => 'Defalt search radius to look for providers','page'=>'1','key' => 'default_search_radius' , 'value' => '5'));
        Settings::create(array('id' => 32,'tool_tip' => 'Automatically assign provider or manually select from a displayed list of all providers','page'=>'4','key' => 'provider_selection' , 'value' => '1')); 
        Settings::create(array('id' => 33,'tool_tip' => 'Service Fee Amount','page'=>'4','key' => 'service_fee' , 'value' => '10'));
        Settings::create(array('id' => 34,'tool_tip' => 'This Template will be used notify provider by email when payment has been made','page'=>'3','key' => 'payment_made_client' , 'value' => 'Payment has been made for Request id %id%. Total amount is %amount%'));

        Settings::create(array('id' => 35,'tool_tip' => 'Transfer','page'=>'7','key' => 'transfer' , 'value' => '0'));
        Settings::create(array('id' => 36,'tool_tip' => 'Allow Calendar','page'=>'7','key' => 'allow_calendar' , 'value' => '1'));

        Settings::create(array('id' => 37,'tool_tip' => 'Pay by Cash','page'=>'8','key' => 'cod' , 'value' => '1'));
        Settings::create(array('id' => 38,'tool_tip' => 'Pay by Paypal','page'=>'8','key' => 'paypal' , 'value' => '0'));
        Settings::create(array('id' => 39,'tool_tip' => 'Promo Code Allowed','page'=>'8','key' => 'promo_code' , 'value' => '0'));
        Settings::create(array('id' => 40,'tool_tip' => 'Allow or not to get Destination','page'=>'3','key' => 'get_destination' , 'value' => '1')); 
        Settings::create(array('id' => 41,'tool_tip' => 'Enable/Disable multiple service select','page'=>'3','key' => 'allow_multiple_service' , 'value' => '0')); 
	   Settings::create(array('id' => 42,'tool_tip' => 'Default deduct amount for user request cancellation','page'=>'3','key' => 'cancel_deduct_amount' , 'value' => '0'));  
	   Settings::create(array('id' => 43,'tool_tip' => 'Default deduct time for user request cancellation','page'=>'3','key' => 'cancel_deduct_time' , 'value' => '0'));        
         Settings::create(array('id' => 45,'tool_tip' => 'Scheduling time','page'=>'1','key' => 'scheduling_time' , 'value' => '30'));
         Settings::create(array('id' => 46,'tool_tip' => 'Defalt inactive timeout for providers in minutes','page'=>'1','key' => 'default_inactive_timeout' , 'value' => '5'));
         Settings::create(array('id' => 47,'tool_tip' => 'walk started by the driver','page'=>'1','key' => 'walk_started' , 'value' => 'Your Trip Started')); 
         Settings::create(array('id' => 48,'tool_tip' => 'walk completed','page'=>'1','key' => 'walk_completed' , 'value' => 'Trip completed')); 
         Settings::create(array('id' => 49,'tool_tip' => 'walk arrived','page'=>'1','key' => 'walk_arrived' , 'value' => 'Provider has arrived')); 
         Settings::create(array('id' => 50,'tool_tip' => 'walking started','page'=>'1','key' => 'walking_started' , 'value' => 'Provider is on the way')); 
         Settings::create(array('id' => 51,'tool_tip' => 'user cancelled the Request','page'=>'1','key' => 'user_request_cancelled' , 'value' => 'User cancelled the Request')); 
         Settings::create(array('id' => 52,'tool_tip' => 'accepted_friend','page'=>'1','key' => 'accepted_friend' , 'value' => 'Accepted By the friend')); 
         Settings::create(array('id' => 53,'tool_tip' => 'Rejected By the friend','page'=>'1','key' => 'rejected_friend' , 'value' => 'Rejected By the friend')); 
         Settings::create(array('id' => 54,'tool_tip' => 'Pay for your friend','page'=>'1','key' => 'pay_for_friend' , 'value' => 'Pay for your friend')); 
         Settings::create(array('id' => 55,'tool_tip' => 'New Request','page'=>'1','key' => 'create_request' , 'value' => 'New Request')); 
         Settings::create(array('id' => 56,'tool_tip' => 'sub admin user email','page'=>'1','key' => 'sub_admin_register' , 'value' => 'hi admin welcome you')); 
         Settings::create(array('id' => 57,'tool_tip' => 'user register email','page'=>'1','key' => 'user_register' , 'value' => 'hi registered user'));      

    }

}
