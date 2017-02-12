<?php

class OwnerController extends BaseController
{

    public function isAdmin($token)
    {
        return false;
    }

    public function getOwnerData($owner_id, $token, $is_admin)
    {

        if ($owner_data = Owner::where('token', '=', $token)->where('id', '=', $owner_id)->first()) {
            return $owner_data;
        } elseif ($is_admin) {
            $owner_data = Owner::where('id', '=', $owner_id)->first();
            if (!$owner_data) {
                return false;
            }
            return $owner_data;
        } else {
            return false;
        }

    }


    public function get_braintree_token()
    {

        $token = Input::get('token');
        $owner_id = Input::get('id');
        $validator = Validator::make(
            array(
                'token' => $token,
                'owner_id' => $owner_id,
            ),
            array(
                'token' => 'required',
                'owner_id' => 'required|integer'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    if (Config::get('app.default_payment') == 'braintree') {

                        Braintree_Configuration::environment(Config::get('app.braintree_environment'));
                        Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
                        Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
                        Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
                        $clientToken = Braintree_ClientToken::generate();
                        $response_array = array('success' => true, 'token' => $clientToken);
                        $response_code = 200;

                    } else {
                        $response_array = array('success' => false, 'error' => 'Please change braintree as default gateway', 'error_code' => 440);
                        $response_code = 200;
                    }


                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID is not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;
    }


    public function apply_referral_code()
    {
        $referral_code = Input::get('referral_code');
        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
            array(
                'referral_code' => $referral_code,
                'token' => $token,
                'owner_id' => $owner_id,
            ),
            array(
                'referral_code' => 'required',
                'token' => 'required',
                'owner_id' => 'required|integer'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401);
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {

                    if ($ledger = Ledger::where('referral_code', $referral_code)->where('owner_id', '!=', $owner_id)->first()) {
                        $referred_by = $ledger->owner_id;
                        $settings = Settings::where('key', 'default_referral_bonus')->first();
                        $referral_bonus = $settings->value;

                        $ledger = Ledger::find($ledger->id);
                        if ($ledger->referral_code != NULL) {
                            $ledger->referral_code = $referral_code;
                            $ledger->total_referrals = $ledger->total_referrals + 1;
                            $ledger->amount_earned = $ledger->amount_earned + $referral_bonus;
                            $ledger->save();

                            $owner = Owner::find($owner_id);
                            $owner->referred_by = $ledger->owner_id;
                            $owner->save();

                            $response_array = array('success' => true);
                        } else {
                            $response_array = array('success' => false, 'error' => 'Already applied referral code', 'error_code' => 482);
                        }
                    } elseif ($ledger = Ledger::where('referral_code', $referral_code)->where('owner_id', $owner_id)->first()) {

                        $response_array = array('success' => false, 'error' => 'Can not add your own Referral code', 'error_code' => 483);

                    } elseif ($pcode = PromoCodes::where('coupon_code', $referral_code)->where('type', 2)->where('state', 1)->where('uses', '>', 0)->first()) {
                        
                        $promohistory = PromoHistory::where('user_id', $owner_id)->where('promo_code', $referral_code)->first();
                        if (!$promohistory) {
                            $promo_code = $pcode->id;
                            $pcode->uses = $pcode->uses - 1;
                            $pcode->save();
                            $phist = new PromoHistory();
                            $phist->user_id = $owner_id;
                            $phist->promo_code = $referral_code;
                            // Assuming all are absolute discount
                            $phist->amount_earned = $pcode->value;
                            $phist->save();
                            // Add to ledger amount
                            $led = Ledger::where('owner_id', $owner_id)->first();
                            if ($led) {
                                $led->amount_earned = $led->amount_earned + $pcode->value;
                                $led->save();
                            } else {
                                $ledger = new Ledger();
                                $ledger->owner_id = $owner_id;
                                $ledger->referral_code = "0";
                                $ledger->total_referrals = 0;
                                $ledger->amount_earned = $pcode->value;
                                $ledger->amount_spent = 0;
                                $ledger->save();
                            }
                            $response_array = array('success' => true);
                        } else {
                            $response_array = array('success' => false, 'error' => 'Promo Code Already Applied.', 'error_code' => 495);
                        }
                    } elseif ($pcode = PromoCodes::where('coupon_code', $referral_code)->where('uses', 0)->first()) {
                        $response_array = array('success' => false, 'error' => 'Invalid promo code', 'error_code' => 496);
                    } elseif ($pcode = PromoCodes::where('coupon_code', $referral_code)->where('type', 1)->first()) {
                        $response_array = array('success' => false, 'error' => 'Percentage discount can not be applied here.', 'error_code' => 465);
                    } elseif ($pcode = PromoCodes::where('coupon_code', '!=', $referral_code)->first()) {
                        $response_array = array('success' => false, 'error' => 'Invalid promo code', 'error_code' => 475);
                    } elseif ($pcode = PromoCodes::where('coupon_code', $referral_code, 'state', '!=', 1)->first()) {
                        $response_array = array('success' => false, 'error' => 'Invalid promo code', 'error_code' => 485);
                    } else {
                        $response_array = array('success' => false, 'error' => 'Invalid referral code', 'error_code' => 455);
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID is not Found', 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);
                }
            }
        }
        $response_code = 200;
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    // test
    public function register()
    {
        $first_name = Input::get('first_name');
        $last_name = Input::get('last_name');
        $email = Input::get('email');
        $phone = Input::get('phone');
        $password = Input::get('password');
        $picture = Input::file('picture');
        $device_token = Input::get('device_token');
        $device_type = Input::get('device_type');
        $bio = Input::get('bio');
        $address = Input::get('address');
        $state = Input::get('state');
        $country = Input::get('country');
        $zipcode = Input::get('zipcode');
        $login_by = Input::get('login_by');
        $social_unique_id = Input::get('social_unique_id');

        if ($password != "" and $social_unique_id == "") {
            $validator = Validator::make(
                array(
                    'password' => $password,
                    'email' => $email,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'device_token' => $device_token,
                    'device_type' => $device_type,
                    'bio' => $bio,
                    'address' => $address,
                    'state' => $state,
                    'country' => $country,
                    'zipcode' => $zipcode,
                    'login_by' => $login_by
                ),
                array(
                    'password' => 'required',
                    'email' => 'required|email',
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'device_token' => 'required',
                    'device_type' => 'required|in:android,ios',
                    'bio' => '',
                    'address' => '',
                    'state' => '',
                    'country' => '',
                    'zipcode' => 'integer',
                    'login_by' => 'required|in:manual,facebook,google',
                )
            );

            $validatorPhone = Validator::make(
                array(
                    'phone' => $phone,
                ),
                array(
                    'phone' => 'phone'
                )
            );
        } elseif ($social_unique_id != "" and $password == "") {
            $validator = Validator::make(
                array(
                    'email' => $email,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'device_token' => $device_token,
                    'device_type' => $device_type,
                    'bio' => $bio,
                    'address' => $address,
                    'state' => $state,
                    'country' => $country,
                    'zipcode' => $zipcode,
                    'login_by' => $login_by,
                    'social_unique_id' => $social_unique_id
                ),
                array(
                    'email' => 'required|email',
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'device_token' => 'required',
                    'device_type' => 'required|in:android,ios',
                    'bio' => '',
                    'address' => '',
                    'state' => '',
                    'country' => '',
                    'zipcode' => 'integer',
                    'login_by' => 'required|in:manual,facebook,google',
                    'social_unique_id' => 'required|unique:owner'
                )
            );

            $validatorPhone = Validator::make(
                array(
                    'phone' => $phone,
                ),
                array(
                    'phone' => 'phone'
                )
            );
        } elseif ($social_unique_id != "" and $password != "") {
            $response_array = array('success' => false, 'error' => 'Invalid Input - either social_unique_id or password should be passed', 'error_code' => 401);
            $response_code = 200;
            goto response;
        }

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();

            Log::info('Error while during owner registration = ' . print_r($error_messages, true));
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else if ($validatorPhone->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Phone Number', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {

            if (Owner::where('email', '=', $email)->first()) {
                $response_array = array('success' => false, 'error' => 'Email ID already Registred', 'error_code' => 402);
                $response_code = 200;
            } else {
                $generate_code = mt_rand(100000, 999999);
                $owner = new Owner;
                $owner->first_name = $first_name;
                $owner->last_name = $last_name;
                $owner->email = $email;
                $owner->phone = $phone;
                $owner->otp_code = $generate_code;
                $owner->otp_status = 0;
                if ($password != "") {
                    $owner->password = Hash::make($password);
                }
                $owner->token = generate_token();
                $owner->token_expiry = generate_expiry();

                // upload image
                $file_name = time();
                $file_name .= rand();
                $file_name = sha1($file_name);
                if ($picture) {
                    $local_url = $file_name . "." . $picture_ext;

                    
                    $binary=base64_decode($picture);
                    header('Content-Type: bitmap; charset=utf-8');
                    // Images will be saved under 'www/imgupload/uplodedimages' folder
                    $file = fopen(public_path() . "/uploads/".$local_url, 'wb');
                    // Create File
                    fwrite($file, $binary);
                    fclose($file);




                    // $ext = Input::file('picture')->getClientOriginalExtension();
                    // Input::file('picture')->move(public_path() . "/uploads", $file_name . "." . $ext);
                    // $local_url = $file_name . "." . $ext;

                    // Upload to S3
                    if (Config::get('app.s3_bucket') != "") {
                        $s3 = App::make('aws')->get('s3');
                        $pic = $s3->putObject(array(
                            'Bucket' => Config::get('app.s3_bucket'),
                            'Key' => $file_name,
                            'SourceFile' => public_path() . "/uploads/" . $local_url,
                        ));

                        $s3->putObjectAcl(array(
                            'Bucket' => Config::get('app.s3_bucket'),
                            'Key' => $file_name,
                            'ACL' => 'public-read'
                        ));

                        $s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name);
                    } else {
                        $s3_url = asset_url() . '/uploads/' . $local_url;
                    }
                    $owner->picture = $s3_url;
                }
                $owner->device_token = $device_token;
                $owner->device_type = $device_type;
                if (Input::has('bio'))
                    $owner->bio = $bio;
                if (Input::has('address'))
                    $owner->address = $address;
                if (Input::has('state'))
                    $owner->state = $state;
                $owner->login_by = $login_by;
                if (Input::has('country'))
                    $owner->country = $country;
                if (Input::has('zipcode'))
                    $owner->zipcode = $zipcode;
                if ($social_unique_id != "") {
                    $owner->social_unique_id = $social_unique_id;
                }

                If (Input::has('timezone')) {
                    $owner->timezone = Input::get('timezone');
                }

                $owner->save();

                if ($owner->id) {

                    $led = Ledger::where('owner_id', $owner->id)->first();
                    $referral_code = mt_rand(0000000, 9999999);

                    if ($led) {
                        $ledger = $led;
                    } else {
                        $ledger = new Ledger;
                        $ledger->owner_id = $owner->id;
                    }
                    $ledger->referral_code = $referral_code;
                    $ledger->save();
                }

                // Send SMS
                $pattern = $generate_code;
                //$pattern ="hai";
                sms_notification($owner->id, 'owner', $pattern);

                // send email
                $subject = "Welcome On Board";
                $email_data['name'] = $owner->first_name;

                send_email($owner->id, 'owner', $email_data, $subject, 'userregister');

                if ($owner->picture == NULL) {
                    $owner_picture = "";
                } else {
                    $owner_picture = $owner->picture;
                }
                if ($owner->bio == NULL) {
                    $owner_bio = "";
                } else {
                    $owner_bio = $owner->bio;
                }
                if ($owner->address == NULL) {
                    $owner_address = "";
                } else {
                    $owner_address = $owner->address;
                }
                if ($owner->state == NULL) {
                    $owner_state = "";
                } else {
                    $owner_state = $owner->state;
                }
                if ($owner->country == NULL) {
                    $owner_country = "";
                } else {
                    $owner_country = $owner->country;
                }
                if ($owner->zipcode == NULL) {
                    $owner_zipcode = "";
                } else {
                    $owner_zipcode = $owner->zipcode;
                }
                if ($owner->timezone == NULL) {
                    $owner_time = Config::get('app.timezone');
                } else {
                    $owner_time = $owner->timezone;
                }
                $response_array = array(
                    'success' => true,
                    'id' => $owner->id,
                    'first_name' => $owner->first_name,
                    'last_name' => $owner->last_name,
                    'phone' => $owner->phone,
                    'email' => $owner->email,
                    'picture' => $owner_picture,
                    'bio' => $owner_bio,
                    'address' => $owner_address,
                    'state' => $owner_state,
                    'country' => $owner_country,
                    'zipcode' => $owner_zipcode,
                    'login_by' => $owner->login_by,
                    'social_unique_id' => $owner->social_unique_id ? $owner->social_unique_id : "",
                    'device_token' => $owner->device_token,
                    'device_type' => $owner->device_type,
                    'timezone' => $owner_time,
                    'token' => $owner->token,
                    'otp_code' => $owner->otp_code,
                );

                $response_code = 200;

            }
        }

        response:
        $response = Response::json($response_array, $response_code);
        return $response;

    }


    public function login()
    {
        $login_by = Input::get('login_by');
        $device_token = Input::get('device_token');
        $device_type = Input::get('device_type');

        if (Input::has('email') && Input::has('password')) {
            $email = Input::get('email');
            $password = Input::get('password');
            $validator = Validator::make(
                array(
                    'password' => $password,
                    'email' => $email,
                    'device_token' => $device_token,
                    'device_type' => $device_type,
                    'login_by' => $login_by
                ),
                array(
                    'password' => 'required',
                    'email' => 'required|email',
                    'device_token' => 'required',
                    'device_type' => 'required|in:android,ios',
                    'login_by' => 'required|in:manual,facebook,google'
                )
            );

            if ($validator->fails()) {
                $error_messages = $validator->messages()->all();
                $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
                $response_code = 200;
                Log::error('Validation error during manual login for owner = ' . print_r($error_messages, true));
            } else {
                if ($owner = Owner::where('email', '=', $email)->first()) {
                    $otp_code = $owner->otp_code;
                    $token = $owner->token;
                    // $device_token_user_check = Owner::where('device_token',$device_token)->get();
                    // foreach ($device_token_user_check as $dc) {
                    //     $change_device_token = Owner::where('id',$dc->id)->first();
                    //     $change_device_token->device_token = "";
                    //     $change_device_token->save();
                    // }

                    $device_token_user_check = Owner::where('device_token',$device_token)->get();
                   foreach ($device_token_user_check as $dc) {
                       $change_device_token = Owner::where('id',$dc->id)->where('id','!=',$owner->id)->first();
                        if($change_device_token)
                           { 
                       $change_device_token->device_token = "";
                       $change_device_token->save();
                    }
                   }

                    
                    if($owner->is_otp_verified == 1) {
                        if (Hash::check($password, $owner->password)) {
                            if ($login_by !== "manual") {
                                $response_array = array('success' => false, 'error' => 'Login by mismatch', 'error_code' => 417);
                                $response_code = 200;
                            } else {
                                if ($owner->device_type != $device_type) {
                                    $owner->device_type = $device_type;
                                }
                                if ($owner->device_token != $device_token) {
                                    $owner->device_token = $device_token;
                                }
                                $owner->token = generate_token();
                                $owner->token_expiry = generate_expiry();
                                $owner->save();

                                $response_array = array(
                                    'success' => true,
                                    'id' => $owner->id,
                                    'first_name' => $owner->first_name,
                                    'last_name' => $owner->last_name,
                                    'phone' => $owner->phone,
                                    'email' => $owner->email,
                                    'picture' => $owner->picture,
                                    'bio' => $owner->bio,
                                    'address' => $owner->address,
                                    'state' => $owner->state,
                                    'country' => $owner->country,
                                    'zipcode' => $owner->zipcode,
                                    'login_by' => $owner->login_by,
                                    'social_unique_id' => $owner->social_unique_id,
                                    'device_token' => $owner->device_token,
                                    'device_type' => $owner->device_type,
                                    'timezone' => $owner->timezone,
                                    'token' => $owner->token,
                                );

                                $dog = Dog::find($owner->dog_id);
                                if ($dog !== NULL) {
                                    $response_array = array_merge($response_array, array(
                                        'dog_id' => $dog->id,
                                        'age' => $dog->age,
                                        'name' => $dog->name,
                                        'breed' => $dog->breed,
                                        'likes' => $dog->likes,
                                        'image_url' => $dog->image_url,
                                    ));
                                }

                                $response_code = 200;
                            }
                        } else {
                            $response_array = array('success' => false, 'error' => 'Invalid Username and Password', 'error_code' => 403);
                            $response_code = 200;
                        }
                    } else {
                        $response_array = ['success' => false , 'error' => 'Verify the OTP Code' , 'error_code' => 210 , 'owner_id' => $owner->id , 'token' => $token , 'otp_code' => $otp_code];
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 'Not a Registered User', 'error_code' => 404);
                    $response_code = 200;
                }
            }
        } elseif (Input::has('social_unique_id')) {
            $social_unique_id = Input::get('social_unique_id');
            $socialValidator = Validator::make(
                array(
                    'social_unique_id' => $social_unique_id,
                    'device_token' => $device_token,
                    'device_type' => $device_type,
                    'login_by' => $login_by
                ),
                array(
                    'social_unique_id' => 'required|exists:owner,social_unique_id',
                    'device_token' => 'required',
                    'device_type' => 'required|in:android,ios',
                    'login_by' => 'required|in:manual,facebook,google'
                )
            );

            if ($socialValidator->fails()) {
                $error_messages = $socialValidator->messages();
                Log::error('Validation error during social login for owner = ' . print_r($error_messages, true));
                $error_messages = $socialValidator->messages()->all();
                $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
                $response_code = 200;
            } else {
                if ($owner = Owner::where('social_unique_id', '=', $social_unique_id)->first()) {
                    $otp_code = $owner->otp_code;
                    $token = $owner->token;
                    if($owner->is_otp_verified == 1) {
                        if (!in_array($login_by, array('facebook', 'google'))) {
                            $response_array = array('success' => false, 'error' => 'Login by mismatch', 'error_code' => 417);
                            $response_code = 200;
                        } else {
                            if ($owner->device_type != $device_type) {
                                $owner->device_type = $device_type;
                            }
                            if ($owner->device_token != $device_token) {
                                $owner->device_token = $device_token;
                            }
                            $owner->token_expiry = generate_expiry();
                            $owner->save();

                            $response_array = array(
                                'success' => true,
                                'id' => $owner->id,
                                'first_name' => $owner->first_name,
                                'last_name' => $owner->last_name,
                                'phone' => $owner->phone,
                                'email' => $owner->email,
                                'picture' => $owner->picture,
                                'bio' => $owner->bio,
                                'address' => $owner->address,
                                'state' => $owner->state,
                                'country' => $owner->country,
                                'zipcode' => $owner->zipcode,
                                'login_by' => $owner->login_by,
                                'social_unique_id' => $owner->social_unique_id,
                                'device_token' => $owner->device_token,
                                'device_type' => $owner->device_type,
                                'timezone' => $owner->timezone,
                                'token' => $owner->token,
                            );

                            $dog = Dog::find($owner->dog_id);
                            if ($dog !== NULL) {
                                $response_array = array_merge($response_array, array(
                                    'dog_id' => $dog->id,
                                    'age' => $dog->age,
                                    'name' => $dog->name,
                                    'breed' => $dog->breed,
                                    'likes' => $dog->likes,
                                    'image_url' => $dog->image_url,
                                ));
                            }

                            $response_code = 200;
                        }
                    } else {
                        $response_array = ['success' => false , 'error' => 'Verify the OTP Code' , 'error_code' => 210 , 'owner_id' => $owner->id , 'token' => $token , 'otp_code' => $otp_code];
                        $response_code = 200;
                    }

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid social registration User', 'error_code' => 404);
                    $response_code = 200;
                }
            }
        } else {
            $response_array = array('success' => false, 'error' => 'Invalid input', 'error_code' => 404);
            $response_code = 200;
        }

        $response = Response::json($response_array, $response_code);
        return $response;

    }

    public function details()
    {
        if (Request::isMethod('post')) {
            $address = Input::get('address');
            $state = Input::get('state');
            $zipcode = Input::get('zipcode');
            $token = Input::get('token');
            $owner_id = Input::get('id');

            $validator = Validator::make(
                array(
                    'address' => $address,
                    'state' => $state,
                    'zipcode' => $zipcode,
                    'token' => $token,
                    'owner_id' => $owner_id,
                ),
                array(
                    'address' => 'required',
                    'state' => 'required',
                    'zipcode' => 'required|integer',
                    'token' => 'required',
                    'owner_id' => 'required|integer'
                )
            );

            if ($validator->fails()) {
                $error_messages = $validator->messages()->all();
                $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
                $response_code = 200;
            } else {
                $is_admin = $this->isAdmin($token);
                if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                    // check for token validity
                    if (is_token_active($owner_data->token_expiry) || $is_admin) {
                        // Do necessary operations

                        $owner = Owner::find($owner_data->id);
                        $owner->address = $address;
                        $owner->state = $state;
                        $owner->zipcode = $zipcode;
                        $owner->save();

                        $response_array = array('success' => true);
                        $response_code = 200;
                    } else {
                        $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                        $response_code = 200;
                    }
                } else {
                    if ($is_admin) {
                        $var = Keywords::where('id', 2)->first();
                        $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                    } else {
                        $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                    }
                    $response_code = 200;
                }
            }
        } else {
            //handles get request
            $token = Input::get('token');
            $owner_id = Input::get('id');
            $validator = Validator::make(
                array(
                    'token' => $token,
                    'owner_id' => $owner_id,
                ),
                array(
                    'token' => 'required',
                    'owner_id' => 'required|integer'
                )
            );

            if ($validator->fails()) {
                $error_messages = $validator->messages()->all();
                $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
                $response_code = 200;
            } else {

                $is_admin = $this->isAdmin($token);
                if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                    // check for token validity
                    if (is_token_active($owner_data->token_expiry) || $is_admin) {

                        $response_array = array(
                            'success' => true,
                            'address' => $owner_data->address,
                            'state' => $owner_data->state,
                            'zipcode' => $owner_data->zipcode,

                        );
                        $response_code = 200;
                    } else {
                        $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                        $response_code = 200;
                    }
                } else {
                    if ($is_admin) {
                        $var = Keywords::where('id', 2)->first();
                        $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                    } else {
                        $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                    }
                    $response_code = 200;

                }

            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;

    }


    public function addcardtoken()
    {
        $payment_token = Input::get('payment_token');
        $last_four = Input::get('last_four');
        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
            array(
                'last_four' => $last_four,
                'payment_token' => $payment_token,
                'token' => $token,
                'owner_id' => $owner_id,
            ),
            array(
                'last_four' => 'required',
                'payment_token' => 'required',
                'token' => 'required',
                'owner_id' => 'required|integer'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {

                    try {

                        if (Config::get('app.default_payment') == 'stripe') {
                            Stripe::setApiKey(Config::get('app.stripe_secret_key'));

                            $customer = Stripe_Customer::create(array(
                                    "card" => $payment_token,
                                    "description" => $owner_data->email)
                            );
                            Log::info('customer = ' . print_r($customer, true));
                            if ($customer) {
                                $customer_id = $customer->id;
                                $payment = new Payment;
                                $payment->owner_id = $owner_id;
                                $payment->customer_id = $customer_id;
                                $payment->last_four = $last_four;
                                $payment->card_token = $customer->sources->data[0]->id;
                                $paymnt = Payment::where('owner_id', $owner_id)->first();
                                if ($paymnt)
                                    $payment->is_default = 0;
                                else
                                    $payment->is_default = 1;
                                $payment->save();
                                $response_array = array('success' => true);
                                $response_code = 200;
                            } else {
                                $response_array = array('success' => false, 'error' => 'Could not create client ID', 'error_code' => 450);
                                $response_code = 200;
                            }
                        } else {
                            Braintree_Configuration::environment(Config::get('app.braintree_environment'));
                            Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
                            Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
                            Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
                            $result = Braintree_Customer::create(array(
                                'paymentMethodNonce' => $payment_token
                            ));
                            Log::info('result = ' . print_r($result, true));
                            if ($result->success) {

                                $customer_id = $result->customer->id;
                                $payment = new Payment;
                                $payment->owner_id = $owner_id;
                                $payment->customer_id = $customer_id;
                                $payment->last_four = $last_four;
                                $payment->card_token = $result->customer->creditCards[0]->token;
                                $payment->save();

                                $response_array = array('success' => true);
                                $response_code = 200;
                            } else {
                                $response_array = array('success' => false, 'error' => 'Could not create client ID', 'error_code' => 450);
                                $response_code = 200;
                            }
                        }


                    } catch (Exception $e) {
                        $response_array = array('success' => false, 'error' => $e, 'error_code' => 405);
                        $response_code = 200;
                    }


                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;

    }

    public function deletecardtoken()
    {
        $card_id = Input::get('card_id');
        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
            array(
                'card_id' => $card_id,
                'token' => $token,
                'owner_id' => $owner_id,
            ),
            array(
                'card_id' => 'required',
                'token' => 'required',
                'owner_id' => 'required|integer'
            )
        );

        $var = Keywords::where('id', 2)->first();

        if ($validator->fails()) {
            $error_messages = $validator->messages();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    if ($payment = Payment::find($card_id)) {
                        if ($payment->owner_id == $owner_id) {
                            Payment::find($card_id)->delete();
                            $response_array = array('success' => true);
                            $response_code = 200;
                        } else {
                            $response_array = array('success' => false, 'error' => 'Card ID and ' . $var->keyword . ' ID Doesnot matches', 'error_code' => 440);
                            $response_code = 200;
                        }
                    } else {
                        $response_array = array('success' => false, 'error' => 'Card not found', 'error_code' => 441);
                        $response_code = 200;
                    }

                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;

    }

    public function set_referral_code()
    {
        $code = Input::get('referral_code');
        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
            array(
                'code' => $code,
                'token' => $token,
                'owner_id' => $owner_id,
            ),
            array(
                'code' => 'required',
                'token' => 'required',
                'owner_id' => 'required|integer'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations

                    $ledger_count = Ledger::where('referral_code', $code)->count();
                    if ($ledger_count > 0) {
                        $response_array = array('success' => false, 'error' => 'This Code already is taken by another user', 'error_code' => 484);
                    } else {
                        $led = Ledger::where('owner_id', $owner_id)->first();
                        if ($led) {
                            $ledger = Ledger::where('owner_id', $owner_id)->first();
                        } else {
                            $ledger = new Ledger;
                            $ledger->owner_id = $owner_id;
                        }
                        $ledger->referral_code = $code;
                        $ledger->save();

                        $response_array = array('success' => true);
                    }

                    $response_code = 200;
                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;

    }

    public function get_referral_code()
    {

        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
            array(
                'token' => $token,
                'owner_id' => $owner_id,
            ),
            array(
                'token' => 'required',
                'owner_id' => 'required|integer'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations

                    $ledger = Ledger::where('owner_id', $owner_id)->first();
                    if ($ledger) {
                        $response_array = array(
                            'success' => true,
                            'referral_code' => $ledger->referral_code,
                            'total_referrals' => $ledger->total_referrals,
                            'amount_earned' => $ledger->amount_earned,
                            'amount_spent' => $ledger->amount_spent,
                            'balance_amount' => $ledger->amount_earned - $ledger->amount_spent,
                        );
                    } else {
                        $response_array = array('success' => false, 'error' => 'This user does not have a referral code');
                    }


                    $response_code = 200;
                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;

    }


    public function get_cards()
    {

        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
            array(
                'token' => $token,
                'owner_id' => $owner_id,
            ),
            array(
                'token' => 'required',
                'owner_id' => 'required|integer'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations
                    $payments = array();
                    $paymnt = Payment::where('owner_id', $owner_id)->first();
                    if ($paymnt != NULL) {
                        $paymnt_count = Payment::where('owner_id', $owner_id)->where('is_default', 1)->count();
                        Log::info('paymnt_count = ' . print_r($paymnt_count, true));
                        if ($paymnt_count > 1) {
                            $paymn = Payment::where('owner_id', $owner_id)->where('is_default', 1)->get();
                            foreach ($paymn as $pd) {
                                $pdn = Payment::where('id', $pd->id)->first();
                                $pdn->is_default = 0;
                                $pdn->save();
                            }
                        }

                        $paymnt_count2 = Payment::where('owner_id', $owner_id)->where('is_default', 1)->count();
                        Log::info('paymnt_count2 = ' . print_r($paymnt_count2, true));

                        if ($paymnt_count2 == 0) {
                            $paymnt = Payment::where('owner_id', $owner_id)->first();
                            $paymnt->is_default = 1;
                            $paymnt->save();
                        } else {
                            Log::info('default should be 1 card');
                        }
                        $payment_data = Payment::where('owner_id', $owner_id)->get();
                        foreach ($payment_data as $data) {
                            $data['id'] = $data->id;
                            $data['customer_id'] = $data->customer_id;
                            $data['card_id'] = $data->card_token;
                            $data['last_four'] = $data->last_four;
                            $data['is_default'] = $data->is_default;
                            array_push($payments, $data);
                        }
                        $response_array = array(
                            'success' => true,
                            'payments' => $payments
                        );
                    } else {
                        $response_array = array(
                            'success' => false,
                            'error' => 'No Card Found'
                        );
                    }


                    $response_code = 200;
                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;

    }

    public function get_completed_requests()
    {

        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
            array(
                'token' => $token,
                'owner_id' => $owner_id,
            ),
            array(
                'token' => 'required',
                'owner_id' => 'required|integer'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations

                    $request_data = DB::table('request')
                        ->where('request.owner_id', $owner_id)
                        ->where('is_completed', 1)
                        ->where('is_cancelled', 0)
                        ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                        ->leftJoin('walker_services', 'walker.id', '=', 'walker_services.provider_id')
                        ->leftJoin('walker_type', 'walker_type.id', '=', 'walker_services.type')
                        ->leftJoin('request_services', 'request_services.request_id', '=', 'request.id')
                        ->select('request.*', 'request.request_start_time', 'request.promo_code', 'walker.first_name', 'walker.id as walker_id',
                            'walker.last_name', 'walker.phone', 'walker.email', 'walker.picture', 'walker.bio',
                            'walker_type.name as type', 'walker_type.icon',
                            'request.distance','request.confirmed_walker' ,'request.time', 'request_services.base_price as req_base_price', 'request_services.distance_cost as req_dis_cost', 'request_services.time_cost as req_time_cost', 'request.total')
                        ->distinct('request.id')
                        ->get();

                    $requests = array();
                    $settings = Settings::where('key', 'default_distance_unit')->first();
                    $setbase_price = Settings::where('key', 'base_price')->first();
                    $setdistance_price = Settings::where('key', 'price_per_unit_distance')->first();
                    $settime_price = Settings::where('key', 'price_per_unit_time')->first();
                    $unit = $settings->value;
                    if ($unit == 0) {
                        $unit_set = 'kms';
                    } elseif ($unit == 1) {
                        $unit_set = 'miles';
                    }

                    $currency_selected = Keywords::find(5);

                    foreach ($request_data as $data) {

                        $walker = Walker::where('id', $data->walker_id)->first();

                        if ($walker != NULL) {
                            $user_timezone = $walker->timezone;
                        } else {
                            $user_timezone = 'UTC';
                        }

                        $default_timezone = Config::get('app.timezone');

                        $date_time = get_user_time($default_timezone, $user_timezone, $data->request_start_time);

                        $dist = number_format($data->distance, 2, '.', '');

                        $request['id'] = $data->id;
                        $request['date'] = $date_time;
                        $request['distance'] = (string)$dist;
                        $request['unit'] = $unit_set;
                        $request['time'] = $data->time;
                        $discount = 0;
                        if ($data->promo_code !== NULL) {
                            if ($data->promo_code !== NULL) {
                                $promo_code = PromoCodes::where('id', $data->promo_code)->first();
                                if ($promo_code) {
                                    $promo_value = $promo_code->value;
                                    $promo_type = $promo_code->type;
                                    if ($promo_type == 1) {
                                        // Percent Discount
                                        $discount = $data->total * $promo_value / 100;
                                    } elseif ($promo_type == 2) {
                                        // Absolute Discount
                                        $discount = $promo_value;
                                    }
                                }
                            }
                        }

                        $request['promo_discount'] = currency_converted($discount);

                        $distance_time_cost = ProviderServices::where('provider_id', $data->confirmed_walker)->first();
                        $request['distance_cost_only']=$distance_time_cost->price_per_unit_distance;
                        $request['time_cost_only']=$distance_time_cost->price_per_unit_time;
                          
                        $is_multiple_service = Settings::where('key', 'allow_multiple_service')->first();
                        if ($is_multiple_service->value == 0) {

                            if ($data->req_base_price) {
                                $request['base_price'] = currency_converted($data->req_base_price);
                            } else {
                                $request['base_price'] = currency_converted($setbase_price->value);
                            }

                            if ($data->req_dis_cost) {
                                $request['distance_cost'] = currency_converted($data->req_dis_cost);
                            } else {
                                $request['distance_cost'] = currency_converted($setdistance_price->value * $data->distance);
                            }

                            if ($data->req_time_cost) {
                                $request['time_cost'] = currency_converted($data->req_time_cost);
                            } else {
                                $request['time_cost'] = currency_converted($settime_price->value * $data->time);
                            }
                             
                            $request['total'] = currency_converted($data->total);
                            $request['actual_total'] = currency_converted($data->total + $data->ledger_payment + $discount);
                            $request['type'] = $data->type;
                            $request['type_icon'] = $data->icon;
                        } else {
                            $rserv = RequestServices::where('request_id', $data->id)->get();
                            $typs = array();
                            $typi = array();
                            $typp = array();
                            $total_price = 0;

                            foreach ($rserv as $typ) {
                                $typ1 = ProviderType::where('id', $typ->type)->first();
                                $typ_price = ProviderServices::where('provider_id', $data->confirmed_walker)->where('type', $typ->type)->first();

                                if ($typ_price->base_price > 0) {
                                    $typp1 = 0.00;
                                    $typp1 = $typ_price->base_price;
                                } elseif ($typ_price->price_per_unit_distance > 0) {
                                    $typp1 = 0.00;
                                    foreach ($rserv as $key) {
                                        $typp1 = $typp1 + $key->distance_cost;
                                    }
                                } else {
                                    $typp1 = 0.00;
                                }
                                $typs['name'] = $typ1->name;
                                $typs['price'] = currency_converted($typp1);
                                $total_price = $total_price + $typp1;
                                array_push($typi, $typs);

                            }
                            $request['type'] = $typi;
                            $base_price = 0;
                            $distance_cost = 0;
                            $time_cost = 0;
                            foreach ($rserv as $key) {
                                $base_price = $base_price + $key->base_price;
                                $distance_cost = $distance_cost + $key->distance_cost;
                                $time_cost = $time_cost + $key->time_cost;
                            }
                            $request['base_price'] = currency_converted($base_price);
                            $request['distance_cost'] = currency_converted($distance_cost);
                            $request['time_cost'] = currency_converted($time_cost);
                            $request['total'] = currency_converted($total_price);

                        }

                        $rate = WalkerReview::where('request_id', $data->id)->where('walker_id', $data->confirmed_walker)->first();
                        if ($rate != NULL) {
                            $request['walker']['rating'] = $rate->rating;
                        } else {
                            $request['walker']['rating'] = '0.0';
                        }

                        $request['currency'] = $currency_selected->keyword;
                        $request['walker']['first_name'] = $data->first_name;
                        $request['walker']['last_name'] = $data->last_name;
                        $request['walker']['phone'] = $data->phone;
                        $request['walker']['email'] = $data->email;
                        $request['walker']['picture'] = $data->picture;
                        $request['walker']['bio'] = $data->bio;
                        $request['walker']['type'] = $data->type;
                        array_push($requests, $request);
                    }

                    $response_array = array(
                        'success' => true,
                        'requests' => $requests
                    );

                    $response_code = 200;
                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;

    }


    public function update_profile()
    {

        $token = Input::get('token');
        $owner_id = Input::get('id');
        $first_name = Input::get('first_name');
        $last_name = Input::get('last_name');
        $phone = Input::get('phone');
        $password = Input::get('password');
        $picture = Input::file('picture');
        $bio = Input::get('bio');
        $address = Input::get('address');
        $state = Input::get('state');
        $country = Input::get('country');
        $zipcode = Input::get('zipcode');

        $validator = Validator::make(
            array(
                'token' => $token,
                'owner_id' => $owner_id,
                'picture' => $picture,
                'zipcode' => $zipcode
            ),
            array(
                'token' => 'required',
                'owner_id' => 'required|integer',
                'picture' => 'mimes:jpeg,bmp,png',
                'zipcode' => 'integer'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations
                    $owner = Owner::find($owner_id);
                    if ($first_name) {
                        $owner->first_name = $first_name;
                    }
                    if ($last_name) {
                        $owner->last_name = $last_name;
                    }
                    if ($phone) {
                        $owner->phone = $phone;
                    }
                    if ($bio) {
                        $owner->bio = $bio;
                    }
                    if ($address) {
                        $owner->address = $address;
                    }
                    if ($state) {
                        $owner->state = $state;
                    }
                    if ($country) {
                        $owner->country = $country;
                    }
                    if ($zipcode) {
                        $owner->zipcode = $zipcode;
                    }
                    if ($password) {
                        $owner->password = Hash::make($password);
                    }

                    if (Input::hasFile('picture')) {
                        if ($owner->picture != "") {
                            $path = $owner->picture;
                            Log::info($path);
                            $filename = basename($path);
                            Log::info($filename);
                            if (file_exists($path)) {
                                unlink(public_path() . "/uploads/" . $filename);
                            }
                        }
                        // upload image
                        $file_name = time();
                        $file_name .= rand();
                        $file_name = sha1($file_name);

                        $ext = Input::file('picture')->getClientOriginalExtension();
                        Input::file('picture')->move(public_path() . "/uploads", $file_name . "." . $ext);
                        $local_url = $file_name . "." . $ext;

                        // Upload to S3
                        if (Config::get('app.s3_bucket') != "") {
                            $s3 = App::make('aws')->get('s3');
                            $pic = $s3->putObject(array(
                                'Bucket' => Config::get('app.s3_bucket'),
                                'Key' => $file_name,
                                'SourceFile' => public_path() . "/uploads/" . $local_url,
                            ));

                            $s3->putObjectAcl(array(
                                'Bucket' => Config::get('app.s3_bucket'),
                                'Key' => $file_name,
                                'ACL' => 'public-read'
                            ));

                            $s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name);

                        } else {
                            $s3_url = asset_url() . '/uploads/' . $local_url;
                        }


                        $owner->picture = $s3_url;
                    }
                    If (Input::has('timezone')) {
                        $owner->timezone = Input::get('timezone');
                    }

                    $owner->save();

                    $response_array = array(
                        'success' => true,
                        'id' => $owner->id,
                        'first_name' => $owner->first_name,
                        'last_name' => $owner->last_name,
                        'phone' => $owner->phone,
                        'email' => $owner->email,
                        'picture' => $owner->picture,
                        'bio' => $owner->bio,
                        'address' => $owner->address,
                        'state' => $owner->state,
                        'country' => $owner->country,
                        'zipcode' => $owner->zipcode,
                        'login_by' => $owner->login_by,
                        'social_unique_id' => $owner->social_unique_id,
                        'device_token' => $owner->device_token,
                        'device_type' => $owner->device_type,
                        'timezone' => $owner->timezone,
                        'token' => $owner->token,
                    );


                    $response_code = 200;
                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;

    }

    public function select_card()
    {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $card_token = Input::get('card_id');

        $validator = Validator::make(
            array(
                'token' => $token,
                'owner_id' => $owner_id,
                'card' => $card_token
            ),
            array(
                'token' => 'required',
                'owner_id' => 'required|integer',
                'card' => 'required|integer'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {

                    $payments = Payment::where('owner_id')->get();
                    foreach ($payments as $data) {
                        $data->is_default = 0;
                        $data->save();
                    }
                    $payment = Payment::where('card_token', $card_token)->where('owner_id', $owner_id)->first();
                    $payment->is_default = 1;
                    $payment->save();
                    $response_array = array('success' => true);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function pay_debt()
    {
        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
            array(
                'token' => $token,
                'owner_id' => $owner_id
            ),
            array(
                'token' => 'required',
                'owner_id' => 'required|integer'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    $total = $owner_data->debt;
                    if ($total == 0) {
                        $response_array = array('success' => true);
                        $response_code = 200;
                        $response = Response::json($response_array, $response_code);
                        return $response;
                    }
                    $payment_data = Payment::where('owner_id', $owner_id)->where('is_default', 1)->first();
                    if (!$payment_data)
                        $payment_data = Payment::where('owner_id', $request->owner_id)->first();

                    if ($payment_data) {
                        $customer_id = $payment_data->customer_id;

                        if (Config::get('app.default_payment') == 'stripe') {
                            Stripe::setApiKey(Config::get('app.stripe_secret_key'));

                            try {
                                Stripe_Charge::create(array(

                                        "amount" => $total * 100,

                                        "currency" => "usd",
                                        "customer" => $customer_id)
                                );
                            } catch (Stripe_InvalidRequestError $e) {
                                // Invalid parameters were supplied to Stripe's API
                                $ownr = Owner::find($owner_id);
                                $ownr->debt = $total;
                                $ownr->save();
                                $response_array = array('error' => $e->getMessage());
                                $response_code = 200;
                                $response = Response::json($response_array, $response_code);
                                return $response;
                            }
                            $owner_data->debt = 0;
                            $owner_data->save();
                        } else {
                            $amount = $total;
                            Braintree_Configuration::environment(Config::get('app.braintree_environment'));
                            Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
                            Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
                            Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
                            $card_id = $payment_data->card_token;
                            $result = Braintree_Transaction::sale(array(
                                'amount' => $amount,
                                'paymentMethodToken' => $card_id
                            ));

                            Log::info('result = ' . print_r($result, true));
                            if ($result->success) {
                                $owner_data->debt = $total;
                            } else {
                                $owner_data->debt = 0;
                            }
                            $owner_data->save();
                        }

                    }
                    $response_array = array('success' => true);
                    $response_code = 200;
                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);
                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);
                }
                $response_code = 200;
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function paybypaypal()
    {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $request_id = Input::get('request_id');
        $paypal_id = Input::get('paypal_id');

        $validator = Validator::make(
            array(
                'token' => $token,
                'owner_id' => $owner_id,
                'paypal_id' => $paypal_id
            ),
            array(
                'token' => 'required',
                'owner_id' => 'required|integer',
                'paypal_id' => 'required'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    Log::info('paypal_id = ' . print_r($paypal_id, true));
                    $req = Requests::find($request_id);
                    Log::info('req = ' . print_r($req, true));
                    $req->is_paid = 1;
                    $req->payment_id = $paypal_id;
                    $req->save();
                    $response_array = array('success' => true);
                    $response_code = 200;
                }
            }
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function paybybitcoin()
    {
        // $token = Input::get('token');
        // $owner_id = Input::get('id');
        // $request_id = Input::get('request_id');

        // $validator = Validator::make(
        // 	array(
        // 		'token' => $token,
        // 		'owner_id' => $owner_id,
        // 	),
        // 	array(
        // 		'token' => 'required',
        // 		'owner_id' => 'required|integer',
        // 	)
        // );

        // if ($validator->fails()) {
        // 	$error_messages = $validator->messages()->all();
        // 		$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages );
        // 		$response_code = 200;
        // } else {
        // 	$is_admin = $this->isAdmin($token);
        // 	if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
        // 		// check for token validity
        // 		if (is_token_active($owner_data->token_expiry) || $is_admin) {
        $coinbaseAPIKey = Config::get('app.coinbaseAPIKey');
        $coinbaseAPISecret = Config::get('app.coinbaseAPISecret');
        // coinbase
        $coinbase = Coinbase::withApiKey($coinbaseAPIKey, $coinbaseAPISecret);
        // $balance = $coinbase->getBalance() . " BTC";
        $user = $coinbase->getUser();
        // $contacts = $coinbase->getContacts("user");
        // $currencies = $coinbase->getCurrencies();
        // $rates = $coinbase->getExchangeRate();

        // $paymentButton = $coinbase->createButton(
        //     "Request ID",
        //     "19.99", 
        //     "USD", 
        //     "TRACKING_CODE_1", 
        //     array(
        //            "description" => "My 19.99 USD donation to PL",
        //            "cancel_url" => "http://localhost:8000/user/acceptbitcoin",
        //            "success_url" => "http://localhost:8000/user/acceptbitcoin"
        //        )
        // );

        Log::info('user = ' . print_r($user, true));

        $response_array = array('success' => true);
        // 		}else{
        // 			$response_array = array('success' => false);
        // 			Log::error('1');
        // 		}
        // 	}else{
        // 		$response_array = array('success' => false);
        // 		Log::error('2');
        // 	}
        // }
        $response_code = 200;
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function acceptbitcoin()
    {
        $response = Input::get('response');
        /*
		Sample Response
		{
		  "order": {
		    "id": "5RTQNACF",
		    "created_at": "2012-12-09T21:23:41-08:00",
		    "status": "completed",
		    "event": {
		      "type": "completed"
		    },
		    "total_btc": {
		      "cents": 100000000,
		      "currency_iso": "BTC"
		    },
		    "total_native": {
		      "cents": 1253,
		      "currency_iso": "USD"
		    },
		    "total_payout": {
		      "cents": 2345,
		      "currency_iso": "USD"
		    },
		    "custom": "order1234",
		    "receive_address": "1NhwPYPgoPwr5hynRAsto5ZgEcw1LzM3My",
		    "button": {
		      "type": "buy_now",
		      "name": "Alpaca Socks",
		      "description": "The ultimate in lightweight footwear",
		      "id": "5d37a3b61914d6d0ad15b5135d80c19f"
		    },
		    "transaction": {
		      "id": "514f18b7a5ea3d630a00000f",
		      "hash": "4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b",
		      "confirmations": 0
		    },
		    "refund_address": "1HcmQZarSgNuGYz4r7ZkjYumiU4PujrNYk"
		  },
		  "customer": {
		    "email": "coinbase@example.com",
		    "shipping_address": [
		      "John Smith",
		      "123 Main St.",
		      "Springfield, OR 97477",
		      "United States"
		    ]
		  }
		}
		*/
        Log::info('response = ' . print_r($response, true));
        return Response::json(200, $response);
    }

    public function send_eta()
    {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $phones = Input::get('phone');
        $request_id = Input::get('request_id');
        $eta = Input::get('eta');

        $validator = Validator::make(
            array(
                'token' => $token,
                'owner_id' => $owner_id,
                'phones' => $phones,
                'eta' => $eta,
            ),
            array(
                'token' => 'required',
                'phones' => 'required',
                'owner_id' => 'required|integer',
                'eta' => 'required'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // If phones is not an array
                    if (!is_array($phones)) {
                        $phones = explode(',', $phones);
                    }

                    Log::info('phones = ' . print_r($phones, true));

                    foreach ($phones as $key) {

                        $owner = Owner::where('id', $owner_id)->first();
                        $secret = str_random(6);

                        $request = Requests::where('id', $request_id)->first();
                        $request->security_key = $secret;
                        $request->save();
                        $msg = $owner->first_name . ' ' . $owner->last_name . ' ETA : ' . $eta;
                        send_eta($key, $msg);
                        Log::info('Send ETA MSG  = ' . print_r($msg, true));
                    }

                    $response_array = array('success' => true);
                    $response_code = 200;
                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function payment_options_allowed()
    {
        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
            array(
                'token' => $token,
                'owner_id' => $owner_id,
            ),
            array(
                'token' => 'required',
                'owner_id' => 'required|integer'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry)) {
                    // Payment options allowed
                    $payment_options = array();

                    $payments = Payment::where('owner_id', $owner_id)->count();

                    if ($payments) {
                        $payment_options['stored_cards'] = 1;
                    } else {
                        $payment_options['stored_cards'] = 0;
                    }

                    $codsett = Settings::where('key', 'cod')->first();
                    
                    if ($codsett->value == 1) {
                        $payment_options['cod'] = 1;
                    } else {
                        $payment_options['cod'] = 0;
                    }

                    $paypalsett = Settings::where('key', 'paypal')->first();

                    if ($paypalsett->value == 1) {
                        $payment_options['paypal'] = 1;
                    } else {
                        $payment_options['paypal'] = 0;
                    }

                    Log::info('payment_options = ' . print_r($payment_options, true));

                    // Promo code allowed
                    $promosett = Settings::where('key', 'promo_code')->first();
                    if ($promosett->value == 1) {
                        $promo_allow = 1;
                    } else {
                        $promo_allow = 0;
                    }

                    $response_array = array('success' => true, 'payment_options' => $payment_options, 'promo_allow' =>
                        $promo_allow);
                } else {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);
                }
            } else {
                $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);
            }
            $response_code = 200;
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function get_credits()
    {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $validator = Validator::make(
            array(
                'token' => $token,
                'owner_id' => $owner_id,
            ),
            array(
                'token' => 'required',
                'owner_id' => 'required|integer'
            )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry)) {
                    $currency_selected = Keywords::find(5);
                    $ledger = Ledger::where('owner_id', $owner_id)->first();
                    if ($ledger) {
                        $credits['balance'] = currency_converted($ledger->amount_earned - $ledger->amount_spent);
                        $credits['currency'] = $currency_selected->keyword;
                        $response_array = array('success' => true, 'credits' => $credits);
                    } else {
                        $response_array = array('success' => false, 'error' => 'No Credit Found', 'error_code' => 475);
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);
                }
            } else {
                $response_array = array('success' => false, 'error' => 'User Not Found', 'error_code' => 402);
            }
            $response_code = 200;
        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function fav_address()
    {

        $token = Input::get('token');
        $owner_id = Input::get('id');
        $address = Input::get('address');
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');
        $name = Input::get('name');

        $validator = Validator::make(
            array(
                'token' => $token,
                'owner_id' => $owner_id,
                'address' => $address,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'name' => $name
            ),
            array(
                'token' => 'required',
                'owner_id' => 'required|integer',
                'address' => 'required',
                'latitude' => 'required',
                'longitude' => 'required',
                'name' => 'required'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    // Do necessary operations
                    $fav_list = FavAddress::where('owner_id', $owner_id)->get();
                    $limit = 8;

                    if (count($fav_list) < $limit) {
                        $fav = new FavAddress;
                        $fav->owner_id = $owner_id;
                        $fav->address = $address;
                        $fav->latitude = $latitude;
                        $fav->longitude = $longitude;
                        $fav->name = $name;
                        $fav->save();

                        $response_array = array('success' => true);
                        $response_code = 200;
                    } else {
                        $response_array = array('success' => false, 'error' => "You already saved 8 favourite address..");
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;
    }

    /***
     * Function Brief : This function is used to load the user favourite address from the fav_address table
     * Input          : owner token , id
     * Output         : user_fav_address
     ***/

    public function list_fav_address()
    {

        $token = Input::get('token');
        $owner_id = Input::get('id');

        $validator = Validator::make(
            array(
                'owner_id' => $owner_id,
                'token' => $token
            ),
            array(
                'owner_id' => 'required|integer',
                'token' => 'required',
            )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {

            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {

                    $user_fav_address = FavAddress::where('owner_id', $owner_id)->get();

                    if (!empty($user_fav_address)) {

                        $response_array = array('success' => true, 'user_fav_address' => $user_fav_address);
                        $response_code = 200;
                    } else {
                        $response_array = array('success' => false, 'error' => "No favourite address found");
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);
                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;
    }

    /***
     * Function Brief : This function is used to load the user favourite address from the fav_address table
     * Input          : owner_id, token
     * Output         : user_fav_address
     ***/

    public function delete_fav_address()
    {

        $token = Input::get('token');
        $owner_id = Input::get('id');
        $fav_address_id = Input::get('fav_address_id');

        $validator = Validator::make(
            array(
                'owner_id' => $owner_id,
                'token' => $token,
                'fav_address_id' => $fav_address_id
            ),
            array(
                'owner_id' => 'required|integer',
                'token' => 'required',
                'fav_address_id' => 'required|integer'
            )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {

            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {

                    $user_fav_address = FavAddress::find($fav_address_id)->delete();

                    if ($user_fav_address) {

                        $response_array = array('success' => true, 'message' => "Fav address deleted successfully");
                        $response_code = 200;
                    } else {
                        $response_array = array('success' => false, 'error' => "Error Occured during deletion");
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);
                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;
    }

    /**
     * Function Brief : This function is used for load the total and sub total details
     *                    after walk completed
     *
     **/
    public function get_walk_complete_amount()
    {
        $owner_id = Input::get('id');
        $token = Input::get('token');
        $request_id = Input::get('request_id');

        $validator = Validator::make(
            array(
                'owner_id' => $owner_id,
                'token' => $token,
                'request_id' => $request_id,
            ),
            array(
                'owner_id' => 'required|integer',
                'token' => 'required',
                'request_id' => 'required|integer'
            )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    if ($request = Requests::find($request_id)) {
                        if ($request->owner_id == $owner_data->id) {
                            if ($request->is_started == 1) {
                                $request_total = $request->total;
                                $request_sub_total = $request->sub_total;

                                $response_array = array('success' => true, 'request_total' => $request_total, 'request_sub_total' => $request_sub_total);
                                $response_code = 200;

                            } else {
                                $response_array = array('success' => false, 'error' => 'Service not yet started', 'error_code' => 413);
                                $response_code = 200;
                            }
                        } else {

                            $response_array = array('success' => false, 'error' => 'User ID Doesnot match ...', 'error_code' => 407);
                            $response_code = 200;
                        }
                    } else {
                        $response_array = array('success' => false, 'error' => 'Service ID not Found', 'error_code' => 407);
                        $response_code = 200;
                    }

                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }

            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);
                }
                $response_code = 200;
            }

        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function walk_complete_pay()
    {

        $token = Input::get('token');
        $owner_id = Input::get('id');
        $request_id = Input::get('request_id');
        $amount_status = Input::get('amount_status');

        $validator = Validator::make(
            array(
                'owner_id' => $owner_id,
                'token' => $token,
                'request_id' => $request_id,
                'amount_status' => $amount_status,
            ),
            array(
                'owner_id' => 'required|integer',
                'token' => 'required',
                'request_id' => 'required|integer',
                'amount_status' => 'required'
            )
        );

        $var = Keywords::where('id', 2)->first();

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {

            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    if ($request = Requests::find($request_id)) {
                        if ($request->owner_id == $owner_data->id) {
                            $total = 0;
                            $walker_id = $request->confirmed_walker;
                            $distance = $request->distance;
                            $time = $request->time;
                            $requestserv = RequestServices::where('request_id', $request_id)->first();

                            if ($amount_status == 1) //User agree the admin allocated amount
                            {
                                $total = $request->total;
                                $request->amount_by = 1;

                            } elseif ($amount_status == 2) { // user agree the Provider created amount
                                $total = $request->sub_total;
                                $sub_total = $request->total;

                                $request->total = $total;
                                $request->sub_total = $sub_total;
                                $request->amount_by = 2;
                            }

                            $requestserv->total = $total;
                            $requestserv->save();

                            $actual_total = $requestserv->total;

                            // charge client
                            $ledger = Ledger::where('owner_id', $request->owner_id)->first();

                            if ($ledger) {
                                $balance = $ledger->amount_earned - $ledger->amount_spent;
                                Log::info('ledger balance = ' . print_r($balance, true));
                                if ($balance > 0) {
                                    if ($total > $balance) {
                                        $request->ledger_payment = $balance;
                                        $ledger_temp = Ledger::find($ledger->id);
                                        $ledger_temp->amount_spent = $ledger_temp->amount_spent + $balance;
                                        $ledger_temp->save();
                                        $total = $total - $balance;
                                    } else {
                                        $request->ledger_payment = $total;
                                        $ledger_temp = Ledger::find($ledger->id);
                                        $ledger_temp->amount_spent = $ledger_temp->amount_spent + $total;
                                        $ledger_temp->save();
                                        $total = 0;
                                    }

                                }
                            }
                            $promo_discount = 0;

                            if ($pcode = PromoCodes::where('id', $request->promo_code)->where('type', 1)->first()) {
                                $discount = ($pcode->value) / 100;
                                $promo_discount = $total * $discount;
                                $total = $total - $promo_discount;
                                if ($total < 0) {
                                    $total = 0;
                                }
                            }

                            $request->total = $total;

                            Log::info('final total = ' . print_r($total, true));

                            $cod_sett = Settings::where('key', 'cod')->first();
                            $allow_cod = $cod_sett->value;

                            if ($request->payment_mode == 1 and $allow_cod == 1) {
                                $request->is_paid = 1;
                                Log::info('allow_cod');
                            } elseif ($request->payment_mode == 2) {
                                // paypal
                                Log::info('paypal payment');
                            } else {

                                Log::info('normal payment. Stored cards');
                                if ($total == 0) {
                                    $request->is_paid = 1;

                                } else {
                                    $payment_data = Payment::where('owner_id', $request->owner_id)->where('is_default', 1)->first();
                                    if (!$payment_data)
                                        $payment_data = Payment::where('owner_id', $request->owner_id)->first();

                                    if ($payment_data) {
                                        $customer_id = $payment_data->customer_id;

                                        $setransfer = Settings::where('key', 'transfer')->first();
                                        $transfer_allow = $setransfer->value;
                                        if (Config::get('app.default_payment') == 'stripe') {
                                            Stripe::setApiKey(Config::get('app.stripe_secret_key'));
                                            try {
                                                Stripe_Charge::create(array(
                                                        "amount" => floor($total) * 100,
                                                        "currency" => "usd",
                                                        "customer" => $customer_id)
                                                );
                                            } catch (Stripe_InvalidRequestError $e) {
                                                // Invalid parameters were supplied to Stripe's API
                                                $ownr = Owner::find($request->owner_id);
                                                $ownr->debt = $total;
                                                $ownr->save();
                                                $response_array = array('error' => $e->getMessage());
                                                $response_code = 200;
                                                $response = Response::json($response_array, $response_code);
                                                return $response;
                                            }
                                            $request->is_paid = 1;

                                            $settng = Settings::where('key', 'service_fee')->first();

                                            $merchant_id = "";

                                            if($walker_data = Walker::find($request->confirmed_walker)) {
                                                $merchant_id = $walker_data->merchant_id;
                                            } else {
                                                $merchant_id = "";
                                            }
                                            if ($transfer_allow == 1 &&  $merchant_id != "") {

                                                $transfer = Stripe_Transfer::create(array(
                                                        "amount" => floor($total - ($settng->value * $total / 100)) * 100, // amount in cents
                                                        "currency" => "usd",
                                                        "recipient" => $merchant_id)
                                                );
                                                $request->transfer_amount = floor($total - $settng->value * $total / 100);
                                            }
                                        } else {
                                            try {
                                                Braintree_Configuration::environment(Config::get('app.braintree_environment'));
                                                Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
                                                Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
                                                Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
                                                if ($transfer_allow == 1) {
                                                    $sevisett = Settings::where('key', 'service_fee')->first();
                                                    $service_fee = $sevisett->value * $total / 100;
                                                    $result = Braintree_Transaction::sale(array(
                                                        'amount' => $total - $service_fee,
                                                        'paymentMethodNonce' => $customer_id,
                                                        'merchantAccountId' => $walker_data->merchant_id,
                                                        'options' => array(
                                                            'submitForSettlement' => true,
                                                            'holdInEscrow' => true,
                                                        ),
                                                        'serviceFeeAmount' => $service_fee
                                                    ));
                                                } else {
                                                    $result = Braintree_Transaction::sale(array(
                                                        'amount' => $total,
                                                        'paymentMethodNonce' => $customer_id
                                                    ));
                                                }

                                                if ($result->success) {
                                                    $request->is_paid = 1;
                                                } else {
                                                    $request->is_paid = 0;
                                                }

                                            } catch (Exception $e) {
                                                $response_array = array('success' => false, 'error' => $e, 'error_code' => 405);
                                                $response_code = 200;
                                                $response = Response::json($response_array, $response_code);
                                                return $response;
                                            }
                                        }
                                        $request->card_payment = $total;
                                        $request->ledger_payment = $request->total - $total;
                                    }
                                }
                            }

                            $request->save();

                            if ($request->is_paid == 1) {

                                $owner = Owner::find($request->owner_id);
                                $settings = Settings::where('key', 'sms_request_unanswered')->first();
                                $pattern = $settings->value;
                                $pattern = str_replace('%user%', $owner->first_name . " " . $owner->last_name, $pattern);
                                $pattern = str_replace('%id%', $request->id, $pattern);
                                $pattern = str_replace('%user_mobile%', $owner->phone, $pattern);
                                sms_notification(1, 'admin', $pattern);
                            }

                            // Send Notification
                            $walker = Walker::find($request->confirmed_walker);
                            $walker_data = array();
                            $walker_data['first_name'] = $walker->first_name;
                            $walker_data['last_name'] = $walker->last_name;
                            $walker_data['phone'] = $walker->phone;
                            $walker_data['bio'] = $walker->bio;
                            $walker_data['picture'] = $walker->picture;
                            $walker_data['latitude'] = $walker->latitude;
                            $walker_data['longitude'] = $walker->longitude;
                            $walker_data['type'] = $walker->type;
                            $walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ? : 0;
                            $walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count();

                            $requestserv = RequestServices::where('request_id', $request->id)->first();
                            $bill = array();
                            $currency_selected = Keywords::find(5);
                            if ($request->is_completed == 1) {
                                $settings = Settings::where('key', 'default_distance_unit')->first();
                                $unit = $settings->value;
                                $bill['payment_mode'] = $request->payment_mode;
                                $bill['distance'] = (string)$distance;
                                if ($unit == 0) {
                                    $unit_set = 'kms';
                                } elseif ($unit == 1) {
                                    $unit_set = 'miles';
                                }
                                $bill['unit'] = $unit_set;
                                $bill['time'] = $request->time;
                                if ($requestserv->base_price != 0) {
                                    $bill['base_price'] = currency_converted($requestserv->base_price);
                                    $bill['distance_cost'] = currency_converted($requestserv->distance_cost);
                                    $bill['time_cost'] = currency_converted($requestserv->time_cost);
                                } else {
                                    $setbase_price = Settings::where('key', 'base_price')->first();
                                    $bill['base_price'] = currency_converted($setbase_price->value);
                                    $setdistance_price = Settings::where('key', 'price_per_unit_distance')->first();
                                    $bill['distance_cost'] = currency_converted($setdistance_price->value);
                                    $settime_price = Settings::where('key', 'price_per_unit_time')->first();
                                    $bill['time_cost'] = currency_converted($settime_price->value);
                                }
                                $admins = Admin::first();
                                $bill['walker']['email'] = $walker->email;
                                $bill['admin']['email'] = $admins->username;
                                if ($request->transfer_amount != 0) {
                                    $bill['walker']['amount'] = currency_converted($request->total - $request->transfer_amount);
                                    $bill['admin']['amount'] = currency_converted($request->transfer_amount);
                                } else {
                                    $bill['walker']['amount'] = currency_converted($request->transfer_amount);
                                    $bill['admin']['amount'] = currency_converted($request->total - $request->transfer_amount);
                                }
                                $bill['currency'] = $currency_selected->keyword;
                                $bill['actual_total'] = currency_converted($actual_total);
                                $bill['total'] = currency_converted($request->total);
                                $bill['is_paid'] = $request->is_paid;
                                $bill['promo_discount'] = currency_converted($promo_discount);
                            }

                            $rservc = RequestServices::where('request_id', $request->id)->get();
                            $typs = array();
                            $typi = array();
                            $typp = array();
                            foreach ($rservc as $typ) {
                                $typ1 = ProviderType::where('id', $typ->type)->first();
                                $typ_price = ProviderServices::where('provider_id', $request->confirmed_walker)->where('type', $typ->type)->first();

                                if ($typ_price->base_price > 0) {
                                    $typp1 = 0.00;
                                    $typp1 = $typ_price->base_price;
                                } elseif ($typ_price->price_per_unit_distance > 0) {
                                    $typp1 = 0.00;
                                    foreach ($rservc as $key) {
                                        $typp1 = $typp1 + $key->distance_cost;
                                    }
                                } else
                                    $typp1 = 0.00;

                                $typs['name'] = $typ1->name;
                                // $typs['icon']=$typ1->icon;
                                $typs['price'] = $typp1;

                                array_push($typi, $typs);
                            }
                            $bill['type'] = $typi;
                            $rserv = RequestServices::where('request_id', $request_id)->get();
                            $typs = array();
                            foreach ($rserv as $typ) {
                                $typ1 = ProviderType::where('id', $typ->type)->first();
                                array_push($typs, $typ1->name);
                            }

                            $response_array = array(
                                'success' => true,
                                'request_id' => $request_id,
                                'total' => $request->total,
                                'Request_service_total' => $requestserv->total,
                                'status' => $request->status,
                                'confirmed_walker' => $request->confirmed_walker,
                                'is_walker_started' => $request->is_walker_started,
                                'is_walker_arrived' => $request->is_walker_arrived,
                                'is_walk_started' => $request->is_started,
                                'is_completed' => $request->is_completed,
                                'is_walker_rated' => $request->is_walker_rated,
                                'walker' => $walker_data,
                                'payment_mode' => $request->payment_data,
                                'bill' => $bill,
                            );
                            $var = Keywords::where('id', 4)->first();
                            $title = 'Your ' . $var->keyword . ' is completed';

                            $message = $response_array;

                            send_notifications($request->owner_id, "owner", $title, $message);

                            // Send SMS 
                            $owner = Owner::find($request->owner_id);
                            $settings = Settings::where('key', 'sms_when_provider_completes_job')->first();
                            $pattern = $settings->value;
                            $pattern = str_replace('%user%', $owner->first_name . " " . $owner->last_name, $pattern);
                            $pattern = str_replace('%driver%', $walker->first_name . " " . $walker->last_name, $pattern);
                            $pattern = str_replace('%driver_mobile%', $walker->phone, $pattern);
                            $pattern = str_replace('%amount%', $request->total, $pattern);
                            sms_notification($request->owner_id, 'owner', $pattern);

                            // send email
                            $settings = Settings::where('key', 'email_request_finished')->first();
                            $pattern = $settings->value;
                            $pattern = str_replace('%id%', $request->id, $pattern);
                            $pattern = str_replace('%url%', web_url() . "/admin/request/map/" . $request->id, $pattern);
                            $subject = "Request Completed";
                            email_notification(2, 'admin', $pattern, $subject);

                            // $settings = Settings::where('key','email_invoice_generated_user')->first();
                            // $pattern = $settings->value;
                            // $pattern = str_replace('%id%', $request->id, $pattern);
                            // $pattern = str_replace('%amount%', $request->total, $pattern);

                            $email_data = array();

                            $email_data['name'] = $owner->first_name;
                            $email_data['emailType'] = 'user';
                            $email_data['base_price'] = $bill['base_price'];
                            $email_data['distance'] = $bill['distance'];
                            $email_data['time'] = $bill['time'];
                            $email_data['unit'] = $bill['unit'];
                            $email_data['total'] = $bill['total'];
                            $email_data['payment_mode'] = $bill['payment_mode'];
                            $email_data['actual_total'] = currency_converted($actual_total);
                            $email_data['is_paid'] = $request->is_paid;
                            $email_data['promo_discount'] = currency_converted($promo_discount);

                            $request_services = RequestServices::where('request_id', $request->id)->first();

                            $locations = WalkLocation::where('request_id', $request->id)
                                ->orderBy('id')
                                ->get();
                            $start = WalkLocation::where('request_id', $request->id)
                                ->orderBy('id')
                                ->first();
                            $end = WalkLocation::where('request_id', $request->id)
                                ->orderBy('id', 'desc')
                                ->first();

                            $map = "https://maps-api-ssl.google.com/maps/api/staticmap?size=249x249&style=feature:landscape|visibility:off&style=feature:poi|visibility:off&style=feature:transit|visibility:off&style=feature:road.highway|element:geometry|lightness:39&style=feature:road.local|element:geometry|gamma:1.45&style=feature:road|element:labels|gamma:1.22&style=feature:administrative|visibility:off&style=feature:administrative.locality|visibility:on&style=feature:landscape.natural|visibility:on&scale=2&markers=shadow:false|scale:2|icon:http://d1a3f4spazzrp4.cloudfront.net/receipt-new/marker-start@2x.png|$start->latitude,$start->longitude&markers=shadow:false|scale:2|icon:http://d1a3f4spazzrp4.cloudfront.net/receipt-new/marker-finish@2x.png|$end->latitude,$end->longitude&path=color:0x2dbae4ff|weight:4";

                            foreach ($locations as $location) {
                                $map .= "|$location->latitude,$location->longitude";
                            }

                            $start_location = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?latlng=$start->latitude,$start->longitude"), TRUE);
                            $start_address = $start_location['results'][0]['formatted_address'];

                            $end_location = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?latlng=$end->latitude,$end->longitude"), TRUE);
                            $end_address = $end_location['results'][0]['formatted_address'];

                            $email_data['start_location'] = $start_location;
                            $email_data['end_location'] = $end_location;

                            $email_data['map'] = $map;

                            //send email to owner
                            $subject = "Invoice Generated";
                            send_email($request->owner_id, 'owner', $email_data, $subject, 'invoice');

                            //send email to walker
                            $subject = "Invoice Generated";
                            $email_data['emailType'] = 'walker';
                            send_email($request->confirmed_walker, 'walker', $email_data, $subject, 'invoice');

                            if ($request->is_paid == 1) {
                                // send email
                                $settings = Settings::where('key', 'email_payment_charged')->first();
                                $pattern = $settings->value;

                                $pattern = str_replace('%id%', $request->id, $pattern);
                                $pattern = str_replace('%url%', web_url() . "/admin/request/map/" . $request->id, $pattern);

                                $subject = "Payment Charged";
                                email_notification(1, 'admin', $pattern, $subject);
                            } else {
                                // send email
                                $pattern = "Payment Failed for the request id " . $request->id . ".";

                                $subject = "Payment Failed";
                                email_notification(1, 'admin', $pattern, $subject);
                            }

                            $response_array = array(
                                'success' => true,
                                'total' => currency_converted($total),
                                'currency' => $currency_selected->keyword,
                                'is_paid' => $request->is_paid,
                                'request_id' => $request_id,
                                'status' => $request->status,
                                'confirmed_walker' => $request->confirmed_walker,
                                'is_walker_started' => $request->is_walker_started,
                                'is_walker_arrived' => $request->is_walker_arrived,
                                'is_walk_started' => $request->is_started,
                                'is_completed' => $request->is_completed,
                                'is_walker_rated' => $request->is_walker_rated,
                                'walker' => $walker_data,
                                'payment_mode' => $request->payment_data,
                                'bill' => $bill,
                            );
                            $response_code = 200;

                        } else {
                            $response_array = array('success' => false, 'error' => 'Request ID doesnot matches with ' . $var->keyword . ' ID', 'error_code' => 407);
                            $response_code = 200;
                        }

                    } else {
                        $response_array = array('success' => false, 'error' => 'Request ID Not Found', 'error_code' => 408);
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);
                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function verify_otp() {
        $owner_id = Input::get('id');
        $token = Input::get('token');
        $otp_code = Input::get('otp_code');

        $validator = Validator::make(
            array(
                'owner_id' => $owner_id,
                'token' => $token,
                'otp_code' => $otp_code,
            ),
            array(
                'owner_id' => 'required|integer',
                'token' => 'required',
                'otp_code' => 'required',
            )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    if($owner_data->is_otp_verified == 0) {
                        if($owner_data->otp_code == $otp_code) {
                            $owner_data->is_otp_verified = 1;
                            $owner_data->save();

                            $response_array = ['success' => 'true' ,'error' => 'OTP Verified Successfully'];
                            $response_code = 200;
                        } else {
                            $response_array = ['success' => false , 'error' => 'Enter Correct OTP Code' , 'error_code' => 212];
                            $response_code = 200;
                        }
                    } else {
                        $response_array = ['success' => false , 'error' => ' Your OTP is already verified' , 'error_code' => 211];
                        $response_code = 200 ;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }

            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);
                }
                $response_code = 200;
            }

        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function resend_otp()
    {

        $owner_id = Input::get('id');
        $token = Input::get('token');

        $validator = Validator::make(
            array(
                'owner_id' => $owner_id,
                'token' => $token,
            ),
            array(
                'owner_id' => 'required|integer',
                'token' => 'required',
            )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    $owner = Owner::find($owner_id);
                    $owner->otp_code = mt_rand(100000, 999999);
                    $owner->save();

                    $pattern = "Your One Time Password is " . $owner->otp_code;
                    sms_notification($owner->id, 'owner', $pattern);

                    Session::put('msg', "A new OTP is sent to your mobile");

                    $response_array = array('success' => true, 'otp_code' => $owner->otp_code);
                    $response_code = 200;

                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }

            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);
                }
                $response_code = 200;
            }

        }
        $response = Response::json($response_array, $response_code);
        return $response;

    }

    public function search_user()
    {
        $owner_id = Input::get('id');
        $token = Input::get('token');
        $email = Input::get('email');

        $validator = Validator::make(
            array(
                'owner_id' => $owner_id,
                'token' => $token,
                'email' => $email
            ),
            array(
                'owner_id' => 'required|integer',
                'token' => 'required',
                'email' => 'required|email'
            )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    $search_user = Owner::where('email', $email)->get();
                    if (count($search_user) > 0) {
                        $response_array = array('success' => true, 'search_user' => $search_user);
                        $response_code = 200;

                    } else {
                        $response_array = array('success' => false, 'error' => 'No users found');
                        $response_code = 200;
                    }

                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }

            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);
                }
                $response_code = 200;
            }

        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function request_send_friend()
    {
        $owner_id = Input::get('id');
        $token = Input::get('token');
        $request_id = Input::get('request_id');
        $friend_id = Input::get('friend_id');

        $validator = Validator::make(
            array(
                'owner_id' => $owner_id,
                'token' => $token,
                'request_id' => $request_id,
                'friend_id' => $friend_id,
            ),
            array(
                'owner_id' => 'required|integer',
                'token' => 'required',
                'request_id' => 'required|integer',
                'friend_id' => 'required|integer',
            )
        );
        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    $request = Requests::find($request_id);
                    $payment_data_friend = Payment::where('owner_id', $friend_id)->first();
                    if ($payment_data_friend) {
                        $payfriend = new PayFriend;
                        $payfriend->request_id = $request_id;
                        $payfriend->friend_id = $friend_id;
                        $payfriend->owner_id = $owner_id;
                        $payfriend->total = $request->total;
                        $payfriend->save();

                        $request = Requests::where('id', $request_id)->first();

                        $var = Keywords::where('id', 5)->first();

                        $msg_array = array();

                        $msg_array['request_id'] = $request->id;
                        $msg_array['pay_request_id'] = $payfriend->id;
                        $msg_array['currency_selected'] = $var->keyword;
                        $msg_array['total'] = currency_converted($request->total);
                        $msg_array['unique_id'] = 10;

                        $owner = Owner::find($owner_id);
                        $owner_data = array();
                        $owner_data['owner'] = array();
                        $owner_data['owner']['name'] = $owner->first_name . " " . $owner->last_name;
                        $owner_data['owner']['picture'] = $owner->picture;
                        $owner_data['owner']['phone'] = $owner->phone;
                        $owner_data['owner']['address'] = $owner->address;
                        $owner_data['owner']['latitude'] = $owner->latitude;
                        $owner_data['owner']['longitude'] = $owner->longitude;

                        $msg_array['owner_data'] = $owner_data;

                        $title = "New Pay Request";
                        $message = $msg_array;

                        Log::info('owner_id = ' . print_r($owner_id, true));
                        Log::info('New Pay request = ' . print_r($message, true));

                        //Notifications
                        send_notifications($friend_id, "owner", $title, $message);

                        //SMS Notifications
                        $pattern = "New Pay Request.....";
                        sms_notification($friend_id, 'owner', $pattern);

                        $response_array = array('success' => true, 'message' => $message);
                        $response_code = 200;
                    } else {
                        $response_array = ['success' => false, 'error' => 'Your Friend is Not Added Payment Details'];
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }

            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);
                }
                $response_code = 200;
            }

        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function reject_pay_request()
    {
        $owner_id = Input::get('id');
        $token = Input::get('token');
        $pay_request_id = Input::get('pay_request_id');

        $validator = Validator::make(
            array(
                'owner_id' => $owner_id,
                'token' => $token,
                'pay_request_id' => $pay_request_id,
            ),
            array(
                'owner_id' => 'required|integer',
                'token' => 'required',
                'pay_request_id' => 'required|integer',
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    $payfriend = PayFriend::find($pay_request_id);
                    $request = Requests::where('id', $payfriend->request_id)->first();

                    if ($payfriend->friend_id == $owner_id) {

                        if ($request->is_completed == 1) {

                            $payfriend->status = 2;
                            $payfriend->save();

                            $request->payer_id = $request->owner_id;
                            $request->save();

                            $msg_array = array();

                            $msg_array['request_id'] = $request->id;
                            $msg_array['total'] = $request->total;
                            $msg_array['unique_id'] = 3;

                            $owner = Owner::find($owner_id);
                            $owner_data = array();
                            $owner_data['owner'] = array();
                            $owner_data['owner']['name'] = $owner->first_name . " " . $owner->last_name;
                            $owner_data['owner']['picture'] = $owner->picture;
                            $owner_data['owner']['phone'] = $owner->phone;
                            $owner_data['owner']['address'] = $owner->address;
                            $owner_data['owner']['latitude'] = $owner->latitude;
                            $owner_data['owner']['longitude'] = $owner->longitude;

                            $msg_array['owner_data'] = $owner_data;

                            $title = "Your request is Rejected.....!!!!";
                            $message = $msg_array;

                            Log::info('owner_id = ' . print_r($owner_id, true));
                            Log::info('Pay Request Reject= ' . print_r($message, true));

                            send_notifications($payfriend->owner_id, "owner", $title, $message);

                            $response_array = array('success' => true, 'message' => "successfully rejected");
                            $response_code = 200;
                        } else {
                            $response_array = array('success' => false, 'error' => 'Request is not completed.....');
                            $response_code = 200;
                        }
                    } else {
                        $response_array = array('success' => false, 'error' => 'Friend ID is not match with pay request friend ID', 'error_code' => 410);
                        $response_code = 200;
                    }

                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }

            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);
                }
                $response_code = 200;
            }

        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }


    public function accept_pay_request()
    {
        $owner_id = Input::get('id');
        $token = Input::get('token');
        $pay_request_id = Input::get('pay_request_id');

        $validator = Validator::make(
            array(
                'owner_id' => $owner_id,
                'token' => $token,
                'pay_request_id' => $pay_request_id,
            ),
            array(
                'owner_id' => 'required|integer',
                'token' => 'required',
                'pay_request_id' => 'required|integer',
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);

            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                if (is_token_active($owner_data->token_expiry) || $is_admin) {

                    $payfriend = PayFriend::find($pay_request_id);


                    if ($payfriend) {

                        $request = Requests::where('id', $payfriend->request_id)->first();

                        if ($payfriend->friend_id == $owner_id) {

                            if ($request->is_completed == 1) {

                                $walker_data = Walker::find($request->confirmed_walker);

                                $request->payer_id = $payfriend->friend_id;
                                $payfriend->status = 1;

                                $payfriend->save();

                                $msg_array = array();

                                $msg_array['pay_request_id'] = $request->id;
                                $msg_array['total'] = $request->total;
                                $msg_array['unique_id'] = 1;

                                $friend = Owner::find($payfriend->friend_id);

                                $friendr_data = array();
                                $friendr_data['friend'] = array();
                                $friendr_data['friend']['name'] = $friend->first_name . " " . $friend->last_name;
                                $friendr_data['friend']['picture'] = $friend->picture;
                                $friendr_data['friend']['phone'] = $friend->phone;
                                $friendr_data['friend']['address'] = $friend->address;

                                $msg_array['friendr_data'] = $friendr_data;

                                $message = $msg_array;
                                $title = "Your friend " . $friend->first_name . " " . $friend->last_name . " accept the pay request";

                                Log::info('owner_id = ' . print_r($owner_id, true));
                                Log::info('Pay Request Accept= ' . print_r($message, true));

                                send_notifications($payfriend->owner_id, "owner", $title, $message);

                                $total = $payfriend->total;

                                $cod_sett = Settings::where('key', 'cod')->first();
                                $allow_cod = $cod_sett->value;

                                if ($request->payment_mode == 1 and $allow_cod == 1) {
                                    $request->is_paid = 1;
                                    Log::info('allow_cod');
                                } elseif ($request->payment_mode == 2) {
                                    // paypal
                                    Log::info('paypal payment');
                                } else {

                                    Log::info('normal payment. Stored cards');
                                    if ($total == 0) {
                                        $request->is_paid = 1;

                                    } else {
                                        $payment_data = Payment::where('owner_id', $payfriend->friend_id)->where('is_default', 1)->first();

                                        if (!$payment_data)

                                            $payment_data = Payment::where('owner_id', $payfriend->friend_id)->first();

                                        if ($payment_data) {
                                            $customer_id = $payment_data->customer_id;

                                            $setransfer = Settings::where('key', 'transfer')->first();
                                            $transfer_allow = $setransfer->value;

                                            if (Config::get('app.default_payment') == 'stripe') {
                                                Stripe::setApiKey(Config::get('app.stripe_secret_key'));
                                                try {
                                                    Stripe_Charge::create(array(
                                                            "amount" => floor($total) * 100,
                                                            "currency" => "usd",
                                                            "customer" => $customer_id)
                                                    );
                                                } catch (Stripe_InvalidRequestError $e) {
                                                    // Invalid parameters were supplied to Stripe's API
                                                    $ownr = Owner::find($payfriend->friend_id);
                                                    $ownr->debt = $total;
                                                    $ownr->save();
                                                    $response_array = array('error' => $e->getMessage());
                                                    $response_code = 200;
                                                    $response = Response::json($response_array, $response_code);
                                                    return $response;
                                                }
                                                $request->is_paid = 1;

                                                $settng = Settings::where('key', 'service_fee')->first();
                                                
                                                if($walker_data) {
                                                    $merchant_id = $walker_data->merchant_id;
                                                } else {
                                                    $merchant_id =" ";
                                                }

                                                if ($transfer_allow == 1 && $walker_data->merchant_id != "") {

                                                    $transfer = Stripe_Transfer::create(array(
                                                            "amount" => floor($total - ($settng->value * $total / 100)) * 100, // amount in cents
                                                            "currency" => "usd",
                                                            "recipient" => $walker_data->merchant_id)
                                                    );
                                                    $request->transfer_amount = floor($total - $settng->value * $total / 100);
                                                }
                                            } else {
                                                try {
                                                    Braintree_Configuration::environment(Config::get('app.braintree_environment'));
                                                    Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
                                                    Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
                                                    Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
                                                    if ($transfer_allow == 1) {
                                                        $sevisett = Settings::where('key', 'service_fee')->first();
                                                        $service_fee = $sevisett->value * $total / 100;
                                                        $result = Braintree_Transaction::sale(array(
                                                            'amount' => $total - $service_fee,
                                                            'paymentMethodNonce' => $customer_id,
                                                            'merchantAccountId' => $walker_data->merchant_id,
                                                            'options' => array(
                                                                'submitForSettlement' => true,
                                                                'holdInEscrow' => true,
                                                            ),
                                                            'serviceFeeAmount' => $service_fee
                                                        ));
                                                    } else {
                                                        $result = Braintree_Transaction::sale(array(
                                                            'amount' => $total,
                                                            'paymentMethodNonce' => $customer_id
                                                        ));
                                                    }

                                                    if ($result->success) {
                                                        $request->is_paid = 1;
                                                    } else {
                                                        $request->is_paid = 0;
                                                    }

                                                } catch (Exception $e) {
                                                    $response_array = array('success' => false, 'error' => $e, 'error_code' => 405);
                                                    $response_code = 200;
                                                    $response = Response::json($response_array, $response_code);
                                                    return $response;
                                                }
                                            }
                                            $request->card_payment = $total;
                                            $request->ledger_payment = $request->total - $total;
                                        }
                                    }
                                }

                                $request->save();

                                if ($request->is_paid == 1) {

                                    $owner = Owner::find($request->owner_id);
                                    $settings = Settings::where('key', 'sms_request_unanswered')->first();
                                    $pattern = $settings->value;
                                    $pattern = str_replace('%user%', $owner->first_name . " " . $owner->last_name, $pattern);
                                    $pattern = str_replace('%id%', $request->id, $pattern);
                                    $pattern = str_replace('%user_mobile%', $owner->phone, $pattern);
                                    sms_notification(1, 'admin', $pattern);

                                    //Send notifications to the user
                                    $msg_array['friend']['is_paid'] = $request->is_paid;
                                    $msg_array['unique_id'] = 2;
                                    $message = $msg_array;

                                    $title = "Your friend " . $friend->first_name . " " . $friend->last_name . " is paid the amonut";

                                    Log::info('owner_id = ' . print_r($owner_id, true));
                                    Log::info('Paid amount = ' . print_r($message, true));

                                    send_notifications($payfriend->owner_id, "owner", $title, $message);
                                }
                                $response_array = array('success' => 'true', 'bill' => '');
                                $response_code = 200;

                            } else {
                                $response_array = array('success' => false, 'error' => 'Request is not completed.....');
                                $response_code = 200;
                            }
                        } else {
                            $response_array = array('success' => false, 'error' => 'Friend ID is not match with pay request friend ID', 'error_code' => 410);
                            $response_code = 200;
                        }
                    } else {
                        $response_array = array('success' => false, 'error' => 'Friend ID is Not Found', 'error_code' => 410);
                        $response_code = 200;
                    }
                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }

            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);
                }
                $response_code = 200;
            }

        }
        $response = Response::json($response_array, $response_code);
        return $response;
    }

    public function check_friend_payment()
    {
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $request_id = Input::get('request_id');

        $validator = Validator::make(
            array(
                'token' => $token,
                'owner_id' => $owner_id,
                'request_id' => $request_id,
            ),
            array(
                'token' => 'required',
                'owner_id' => 'required|integer',
                'request_id' => 'required',
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    if ($request = Requests::find($request_id)) {
                        if ($request->owner_id == $owner_data->id) {
                            if ($request->is_paid == 1) {
                                $response_array = ['success' => true, 'payer_id' => $request->payer_id, 'is_paid' => $request->is_paid];
                            } else {
                                $response_array = ['success' => false, 'error' => 'Friend is not paid still', 'error_code' => 405];
                            }
                        } else {
                            $response_array = ['success' => false, 'error' => 'User ID is Mismatch with Request User ID'];
                        }
                        $response_code = 200;
                    } else {
                        $response_array = ['success' => false, 'error' => 'Request ID Not Found', 'error_code' => 410];
                        $response_code = 200;
                    }

                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;

    }
    public function accept_reject_payment(){
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $request_id = Input::get('request_id');
        $accept_reject =Input::get('accept_reject');
        //0 reject
        //1 accept
        $validator = Validator::make(
            array(
                'token' => $token,
                'owner_id' => $owner_id,
                'request_id' => $request_id,
                'accept_reject'=>$accept_reject,
            ),
            array(
                'token' => 'required',
                'owner_id' => 'required|integer',
                'request_id' => 'required',
                'accept_reject'=>'required'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    if ($request = Requests::find($request_id)) {
                        
                        if($accept_reject == 0){

                        }else{
                            $message = $request_id;
                            $response_array = array('success' => true,'message'=>$message);            
                        }

                       
                        $response_code = 200;
                    } else {
                        $response_array = ['success' => false, 'error' => 'Request ID Not Found', 'error_code' => 410];
                        $response_code = 200;
                    }

                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;        
    }
    public function pay_for_another(){
      $token = Input::get('token');
        $owner_id = Input::get('id');
        $request_id = Input::get('request_id');
        $total =Input::get('total');
        //0 reject
        //1 accept
        $validator = Validator::make(
            array(
                'token' => $token,
                'owner_id' => $owner_id,
                'request_id' => $request_id,
                'total'=>$total
                
            ),
            array(
                'token' => 'required',
                'owner_id' => 'required|integer',
                'request_id' => 'required',
                'total'=>'required'
            
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                    if ($request = Requests::find($request_id)) {
                        
                        if($accept_reject == 0){

                        }else{
                            $message = $request_id;
                            $response_array = array('success' => true,'message'=>$message);            
                        }

                       
                        $response_code = 200;
                    } else {
                        $response_array = ['success' => false, 'error' => 'Request ID Not Found', 'error_code' => 410];
                        $response_code = 200;
                    }

                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;   
    }
    public function friend_request(){
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $friend_email=Input::get('friend_email'); 
        
        $validator = Validator::make(
            array(
                'token' => $token,
                'owner_id' => $owner_id,
                'friend_email'=>$friend_email,        
            ),
            array(
                'token' => 'required',
                'owner_id' => 'required',
                'friend_email'=>'required'
            
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                        $owner_check = Owner::where('email',$friend_email)->first();
                        if($owner_check){
                            $pay_user_name_image= Owner::where('id',$owner_id)->first();
                            $pay_user_name = $pay_user_name_image->first_name." ".$pay_user_name_image->last_name;
                            $pay_user_image = $pay_user_name_image->picture;
                            
                            $payment_check_user =Payment::where('owner_id',$owner_check->id)->first();
                            if($payment_check_user){
                                //$owner_id =$owner_check->id;
                                $delete_friend = Friend::where('user_id',$owner_id)->delete();
                                $friend = new Friend;
                                $friend->user_id=$owner_id;
                                $friend->friend_id =$owner_check->id;
                                $friend->status=0;
                                $friend->save();
                                $title = "Pay for your friend";
                                $other_user_id = $owner_check->id;
                                $response_array = array(
                                    'success' => true,
                                    'friend_request_id' => $friend->id,
                                    'pay_user_id'=>$owner_id,
                                    'pay_user_name'=>$pay_user_name,
                                    'pay_user_image'=>$pay_user_image,
                                    'push_id'=>3
                                );
                         
                                $message = $response_array;
                                Log::info('User id ckhdscbdsscbhsk ' . print_r($other_user_id, true));
                                send_notifications($other_user_id, "owner", $title, $message);
                                $response_array = array('success' => true);
                                $response_code = 200;

                            }else{
                                    $response_array = array('success' => false, 'error' => 'The selected user is unable to support this function.', 'error_code' => 403);
                                    $response_code = 200;    
                            }
                           

                        }else{
                            $response_array = array('success' => false, 'error' => 'No user with this email', 'error_code' => 403);
                            $response_code = 200;        
                        }

                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                }
                $response_code = 200;
            }
        }
        $response_code = 200;
        $response = Response::json($response_array, $response_code);
        return $response;    
    }

    public function accept_reject_friend(){
        
        $token = Input::get('token');
        $owner_id = Input::get('id');
        $friend_id =Input::get('friend_id');
        $accept_reject = Input::get('accept_reject'); 
        
        $validator = Validator::make(
            array(
                'token' => $token,
                'owner_id' => $owner_id,
                'accept_reject'=>$accept_reject,
                'friend_id'=>$friend_id        
            ),
            array(
                'token' => 'required',
                'owner_id' => 'required',
                'accept_reject'=>'required',
                'friend_id'=>'required'
            
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $is_admin = $this->isAdmin($token);
            if ($owner_data = $this->getOwnerData($owner_id, $token, $is_admin)) {
                // check for token validity
                if (is_token_active($owner_data->token_expiry) || $is_admin) {
                        $friend = Friend::where('friend_id',$owner_id)->where('user_id',$friend_id)->first();
                        //Log::info('aceeeeeeeepptt= '.print_r($accept_reject), true);
                        
                        if($friend){
                              
                            $friend->status = $accept_reject;
                            $friend->save();
                            if($accept_reject == 0){
                                $title = "Rejected by your friend";
                                $push_id =10;
                            }else{
                                $title = "Accepted by your friend";
                                $push_id=4;
                            }                            
                        
                            
                            $response_array = array(
                                'success' => true,
                                'friend_request_id' => $friend->id,
                                'pay_user_id'=>$owner_id,
                              
                                'push_id'=>$push_id
                            );
                         
                            $messagess = $response_array;
                           
                         //Log::info('frienddddddffhjshbhsbhs = ' . print_r($owner_id, true));                         

                           // Log::info('messaginvhgtyg friend= '.print_r($message), true);
                            send_notifications($friend_id, "owner", $title, $messagess);
                            $response_array = array('success' => true);
                            $response_code = 200;

                        }else{
                            $response_array = array('success' => false, 'error' => 'No user with this email', 'error_code' => 403);
                            $response_code = 200;        
                        }

                } else {
                    $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                    $response_code = 200;
                }
            } else {
                if ($is_admin) {
                    $var = Keywords::where('id', 2)->first();
                    $response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

                } else {
                    $response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

                }
                $response_code = 200;
            }
        }

        $response = Response::json($response_array, $response_code);
        return $response;    
    }
   

}
