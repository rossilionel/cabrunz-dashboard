<?php

class WalkerController extends BaseController
{
	public function isAdmin($token)
	{
		return false;
	}

	public function getWalkerData($walker_id, $token, $is_admin)
	{

		if ($walker_data = Walker::where('token', '=', $token)->where('id', '=', $walker_id)->first()) {
			return $walker_data;
		} elseif ($is_admin) {
			$walker_data = Walker::where('id', '=', $walker_id)->first();
			if (!$walker_data) {
				return false;
			}
			return $walker_data;
		} else {
			return false;
		}

	}

	public function register()
	{
		$first_name = Input::get('first_name');
		$last_name = Input::get('last_name');
		$email = Input::get('email');
		$phone = Input::get('phone');
		$password = Input::get('password');
		$type = Input::get('type');
		$picture = Input::get('picture');
        $picture_ext = Input::get('pictureext');
		$device_token = Input::get('device_token');
		$device_type = Input::get('device_type');
		$bio = Input::get('bio');
		$address = Input::get('address');
		$state = Input::get('state');
		$country = Input::get('country');
		$zipcode = Input::get('zipcode');
		$login_by = Input::get('login_by');
		$social_unique_id = Input::get('social_unique_id');
		$vehicle_no =Input::get('vehicle_no');
		$model_no =Input::get('model_no');

		if ($password != "" and $social_unique_id == "") {
			$validator = Validator::make(
				array(
					'password' => $password,
					'email' => $email,
					'first_name' => $first_name,
					'last_name' => $last_name,
					// 'picture' => $picture,
					'device_token' => $device_token,
					'device_type' => $device_type,

					'zipcode' => $zipcode,
					'login_by' => $login_by,
					'vehicle_no'=>$vehicle_no,
					'model_no'=>$model_no
				),
				array(
					'password' => 'required',
					'email' => 'required|email',
					'first_name' => 'required',
					'last_name' => 'required',
					// 'picture' => 'required|mimes:jpeg,bmp,png',
					'device_token' => 'required',
					'device_type' => 'required|in:android,ios',
					'zipcode' => 'integer',
					'login_by' => 'required|in:manual,facebook,google',
					'vehicle_no'=>'required',
					'model_no'=>'required'
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
					'phone' => $phone,
					'first_name' => $first_name,
					'last_name' => $last_name,
					// 'picture' => $picture,
					'device_token' => $device_token,
					'device_type' => $device_type,
					'bio' => $bio,
					'address' => $address,
					'state' => $state,
					'country' => $country,
					'zipcode' => $zipcode,
					'login_by' => $login_by,
					'social_unique_id' => $social_unique_id,
					'vehicle_no'=>$vehicle_no,
					'model_no'=>$model_no
				),
				array(
					'email' => 'required|email',
					'phone' => 'required',
					'first_name' => 'required',
					'last_name' => 'required',
					// 'picture' => 'required|mimes:jpeg,bmp,png',
					'device_token' => 'required',
					'device_type' => 'required|in:android,ios',
					'bio' => '',
					'address' => '',
					'state' => '',
					'country' => '',
					'zipcode' => 'integer',
					'login_by' => 'required|in:manual,facebook,google',
					'social_unique_id' => 'required|unique:walker',
					'vehicle_no'=>'required',
					'model_no'=>'required'
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
			$error_messages = $validator->messages();
			Log::info('Error while during walker registration = ' . print_r($error_messages, true));
			$error_messages = $validator->messages()->all();
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
			$response_code = 200;
		}else if ($validatorPhone->fails()) {
			$error_messages = $validator->messages()->all();
			$response_array = array('success' => false, 'error' => 'Invalid Phone Number', 'error_code' => 401, 'error_messages' => $error_messages );
			$response_code = 200;
		}
		 else {

			if (Walker::where('email', '=', $email)->first()) {
				$response_array = array('success' => false, 'error' => 'Email ID already Registred', 'error_code' => 402);
				$response_code = 200;
			} else {

				if (!$type) {
					// choose default type
					$provider_type = ProviderType::where('is_default', 1)->first();

					if (!$provider_type) {
						$type = 0;
					} else {
						$type = $provider_type->id;
					}
				}
				$activation_code = uniqid();

				$walker = new Walker;
				$walker->first_name = $first_name;
				$walker->last_name = $last_name;
				$walker->email = $email;
				$walker->phone = $phone;
				$walker->vehicle_no=$vehicle_no;
				$walker->model_no=$model_no;
				$walker->activation_code = $activation_code;
				if ($password != "") {
					$walker->password = Hash::make($password);
				}
				$walker->token = generate_token();
				$walker->token_expiry = generate_expiry();
				// upload image
				$file_name = time();
				$file_name .= rand();
				$file_name = sha1($file_name);

				// $ext = Input::file('picture')->getClientOriginalExtension();
				// Input::file('picture')->move(public_path() . "/uploads", $file_name . "." . $ext);
				// $local_url = $file_name . "." . $ext;
				if ($picture) {
					$local_url = $file_name . "." . $picture_ext;

	                    
	                    $binary=base64_decode($picture);
	                    header('Content-Type: bitmap; charset=utf-8');
	                    // Images will be saved under 'www/imgupload/uplodedimages' folder
	                    $file = fopen(public_path() . "/uploads/".$local_url, 'wb');
	                    // Create File
	                    fwrite($file, $binary);
	                    fclose($file);

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
					$walker->picture = $s3_url;
				}
				$walker->device_token = $device_token;
				$walker->device_type = $device_type;
				$walker->bio = $bio;
				$walker->address = $address;
				$walker->state = $state;
				$walker->country = $country;
				$walker->zipcode = $zipcode;
				$walker->login_by = $login_by;
				$walker->is_available = 1;
				$walker->is_active = 0;
				$walker->is_approved = 0;
				if ($social_unique_id != "") {
					$walker->social_unique_id = $social_unique_id;
				}

				If (Input::has('timezone')) {
					$walker->timezone = Input::get('timezone');
				}

				$walker->save();
				if (Input::has('type') != NULL) {
					$ke = Input::get('type');
					$proviserv = ProviderServices::where('provider_id', $walker->id)->first();
					if ($proviserv != NULL) {
						DB::delete("delete from walker_services where provider_id = '" . $walker->id . "';");
					}
					$base_price = Input::get('service_base_price');
					$service_price_distance = Input::get('service_price_distance');
					$service_price_time = Input::get('service_price_time');

					$type = Input::get('type');
					$myType = explode(',', $type);
					$cnkey = count($myType);

					if (Input::has('service_base_price')) {
						$base_price = Input::get('service_base_price');
						$base_price_array = explode(',', $base_price);
					}

					Log::info('cnkey = ' . print_r($cnkey, true));
					for ($i = 0; $i < $cnkey; $i++) {
						$key = $myType[$i];
						$prserv = new ProviderServices;
						$prserv->provider_id = $walker->id;
						$prserv->type = $key;
						Log::info('key = ' . print_r($key, true));

						if (Input::has('service_base_price')) {

							$prserv->base_price = $base_price_array[$i];
						} else {
							$prserv->base_price = 0;
						}
						if (Input::has('service_price_distance')) {
							$prserv->price_per_unit_distance = $service_price_distance[$i];
						} else {
							$prserv->price_per_unit_distance = 0;
						}
						if (Input::has('service_price_time')) {
							$prserv->price_per_unit_time = $service_price_time[$i];
						} else {
							$prserv->price_per_unit_distance = 0;
						}
						$prserv->save();
					}
				}
				$subject = "Welcome On Board";
				$email_data['name'] = $walker->first_name;
				$url = URL::to('/provider/activation') . '/' . $activation_code;
				$email_data['url'] = $url;

				send_email($walker->id, 'walker', $email_data, $subject, 'providerregister');

				$response_array = array(
					'success' => true,
					'id' => $walker->id,
					'first_name' => $walker->first_name,
					'last_name' => $walker->last_name,
					'phone' => $walker->phone,
					'email' => $walker->email,
					'picture' => $walker->picture,
					'bio' => $walker->bio,
					'address' => $walker->address,
					'state' => $walker->state,
					'country' => $walker->country,
					'zipcode' => $walker->zipcode,
					'login_by' => $walker->login_by,
					'social_unique_id' => $walker->social_unique_id ? $walker->social_unique_id : "",
					'device_token' => $walker->device_token,
					'device_type' => $walker->device_type,
					'token' => $walker->token,
					'timezone' => $walker->timezone,
					'type' => $myType,
					'vehicle_no'=>$vehicle_no,
					'model_no'=>$model_no
				);
				$response_code = 200;

				// $response_array = array(
				// 	'success' => false,
				// 	'id' => 5,
					
				// );
				// $response_code = 200;

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
				$error_messages = $validator->messages();
				Log::error('Validation error during manual login for walker = ' . print_r($error_messages, true));
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
				$response_code = 200;
			} else {
				if ($walker = Walker::where('email', '=', $email)->first()) {
					if (Hash::check($password, $walker->password)) {
						if ($login_by != "manual") {
							$response_array = array('success' => false, 'error' => 'Login by mismatch', 'error_code' => 417);
							$response_code = 200;
						} else {
							if ($walker->device_type != $device_type) {
								$walker->device_type = $device_type;
							}
							if ($walker->device_token != $device_token) {
								$walker->device_token = $device_token;
							}
							$walker->token = generate_token();
							$walker->token_expiry = generate_expiry();
							$walker->save();

							$response_array = array(
								'success' => true,
								'id' => $walker->id,
								'first_name' => $walker->first_name,
								'last_name' => $walker->last_name,
								'phone' => $walker->phone,
								'email' => $walker->email,
								'picture' => $walker->picture,
								'bio' => $walker->bio,
								'address' => $walker->address,
								'state' => $walker->state,
								'country' => $walker->country,
								'zipcode' => $walker->zipcode,
								'login_by' => $walker->login_by,
								'social_unique_id' => $walker->social_unique_id,
								'device_token' => $walker->device_token,
								'device_type' => $walker->device_type,
								'token' => $walker->token,
								'type' => $walker->type,
								'timezone' => $walker->timezone,
								'is_approved' => $walker->is_approved,
							);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Invalid Username and Password', 'error_code' => 403);
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
					'social_unique_id' => 'required|exists:walker,social_unique_id',
					'device_token' => 'required',
					'device_type' => 'required|in:android,ios',
					'login_by' => 'required|in:manual,facebook,google'
				)
			);
			if ($socialValidator->fails()) {
				$error_messages = $socialValidator->messages();
				Log::error('Validation error during social login for walker = ' . print_r($error_messages, true));
				$error_messages = $socialValidator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
				$response_code = 200;
			} else {
				if ($walker = Walker::where('social_unique_id', '=', $social_unique_id)->first()) {
					if (!in_array($login_by, array('facebook', 'google'))) {
						$response_array = array('success' => false, 'error' => 'Login by mismatch', 'error_code' => 417);
						$response_code = 200;
					} else {
						if ($walker->device_type != $device_type) {
							$walker->device_type = $device_type;
						}
						if ($walker->device_token != $device_token) {
							$walker->device_token = $device_token;
						}
						$walker->token_expiry = generate_expiry();
						$walker->save();

						$response_array = array(
							'success' => true,
							'id' => $walker->id,
							'first_name' => $walker->first_name,
							'last_name' => $walker->last_name,
							'phone' => $walker->phone,
							'email' => $walker->email,
							'picture' => $walker->picture,
							'bio' => $walker->bio,
							'address' => $walker->address,
							'state' => $walker->state,
							'country' => $walker->country,
							'zipcode' => $walker->zipcode,
							'login_by' => $walker->login_by,
							'social_unique_id' => $walker->social_unique_id,
							'device_token' => $walker->device_token,
							'device_type' => $walker->device_type,
							'token' => $walker->token,
							'timezone' => $walker->timezone,
							'type' => $walker->type,
						);
						$response_code = 200;
					}
				} else {
					$response_array = array('success' => false, 'error' => 'Not a valid social registration User', 'error_code' => 404);
					$response_code = 200;
				}
			}
		} else {
			$response_array = array('success' => false, 'error' => 'Invalid Input');
			$response_code = 200;
		}
		$response = Response::json($response_array, $response_code);
		return $response;
	}


	// Rate Dog

	public function set_dog_rating()
	{
		if (Request::isMethod('post')) {
			$comment = Input::get('comment');
			$request_id = Input::get('request_id');
			$rating = Input::get('rating');
			$token = Input::get('token');
			$walker_id = Input::get('id');

			$validator = Validator::make(
				array(
					'request_id' => $request_id,
					'rating' => $rating,
					'token' => $token,
					'walker_id' => $walker_id,
				),
				array(
					'request_id' => 'required|integer',
					'rating' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer'
				)
			);
			$var = Keywords::where('id', 1)->first();
			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($request = Requests::find($request_id)) {
							if ($request->confirmed_walker == $walker_id) {

								if ($request->is_dog_rated == 0) {

									$owner = Owner::find($request->owner_id);

									$dog_review = new DogReview;
									$dog_review->request_id = $request_id;
									$dog_review->walker_id = $walker_id;
									$dog_review->rating = $rating;
									$dog_review->owner_id = $owner->id;
									$dog_review->comment = $comment;
									$dog_review->save();

									$request->is_dog_rated = 1;
									$request->save();

									$response_array = array('success' => true);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Already Rated', 'error_code' => 409);
									$response_code = 200;
								}
							} else {
								$response_array = array('success' => false, 'error' => 'Service ID doesnot matches with ' . $var->keyword . ' ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Service ID Not Found', 'error_code' => 408);
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
		}
		$response = Response::json($response_array, $response_code);
		return $response;

	}


	// Cancel Walk

	public function cancel_walk()
	{
		if (Request::isMethod('post')) {
			$walk_id = Input::get('walk_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');

			$validator = Validator::make(
				array(
					'walk_id' => $walk_id,
					'token' => $token,
					'walker_id' => $walker_id,
				),
				array(
					'walk_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer'
				)
			);

			$var = Keywords::where('id', 1)->first();

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($walk = Walk::find($walk_id)) {
							if ($walk->walker_id == $walker_id) {

								if ($walk->is_walk_started == 0) {
									$walk->walker_id = 0;
									$walk->is_confirmed = 0;
									$walk->save();

									$response_array = array('success' => true);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Service Already Started', 'error_code' => 416);
									$response_code = 200;
								}
							} else {
								$response_array = array('success' => false, 'error' => 'Service ID doesnot matches with' . $var->keyword . ' ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Service ID Not Found', 'error_code' => 408);
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
		}
		$response = Response::json($response_array, $response_code);
		return $response;

	}

	// Add walker Location Data
	public function walker_location()
	{
		if (Request::isMethod('post')) {
			$token = Input::get('token');
			$walker_id = Input::get('id');
			$latitude = Input::get('latitude');
			$longitude = Input::get('longitude');

			$validator = Validator::make(
				array(
					'token' => $token,
					'walker_id' => $walker_id,
					'latitude' => $latitude,
					'longitude' => $longitude,
				),
				array(
					'token' => 'required',
					'walker_id' => 'required|integer',
					'latitude' => 'required',
					'longitude' => 'required',
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						$walker = Walker::find($walker_id);
						$walker->latitude = $latitude;
						$walker->longitude = $longitude;
						$walker->last_seen = time();
						$walker->save();
						$response_array = array('success' => true);
					}else{
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 412);
					}
				} else {
					if ($is_admin) {
						$driver = Keywords::where('id', 1)->first();
						$response_array = array('success' => false, 'error' => '' . $driver->keyword . ' ID not Found', 'error_code' => 410);
					} else {
						$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);
					}
				}
				$response_code = 200;
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;
	}

	// Get Profile

	public function get_requests()
	{

		$token = Input::get('token');
		$walker_id = Input::get('id');

		$validator = Validator::make(
			array(
				'token' => $token,
				'walker_id' => $walker_id,
			),
			array(
				'token' => 'required',
				'walker_id' => 'required|integer'
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
			$response_code = 200;;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry)) {
					$time = date("Y-m-d H:i:s");
					$provider_timeout = Settings::where('key', 'provider_timeout')->first();
					$timeout = $provider_timeout->value;

					$query = "SELECT id, later, D_latitude,destination_address,source_address, D_longitude, payment_mode, request_start_time , owner_id,TIMESTAMPDIFF(SECOND,updated_at, '$time') as diff from request where is_cancelled = 0 and status = 0 and current_walker=$walker_id and TIMESTAMPDIFF(SECOND,updated_at, '$time') <= $timeout";

					$requests = DB::select(DB::raw($query));
					$all_requests = array();
					foreach ($requests as $request) {
						$data['request_id'] = $request->id;
						$data['destination_address']=$request->destination_address;
						$data['source_address']=$request->source_address;
						$requestData = RequestServices::where('request_id', $request->id)->first();
						$data['request_services'] = $requestData->type;

						$rservc = RequestServices::where('request_id',$request->id)->get();
                        $typs=array();
                        $typi=array();
                        $typp=array();
                        $totalPrice = 0;
                        
                        foreach ($rservc as $typ) {
		            		$typ1=ProviderType::where('id',$typ->type)->first();
			 				$typ_price=ProviderServices::where('provider_id',$walker_id)->where('type',$typ->type)->first();
                                                
					 	if($typ_price->base_price>0){
                            $typp1=0.00;
                            $typp1=$typ_price->base_price;
                        }else{
                        	 $typp1 =0.00;
                        }
                        $typs['distance_cost_only']=$typ_price->price_per_unit_distance;
						$typs['time_cost_only']=$typ_price->price_per_unit_time;
								
                        $typs['name']=$typ1->name;
                        $typs['price']=$typp1;
						$totalPrice = $totalPrice + $typp1;

                        array_push($typi, $typs);
						}
						$data['type']=$typi;

						if ($request->later == 0)
							$data['time_left_to_respond'] = $timeout - $request->diff;
						else
							$data['time_left_to_respond'] = $timeout;

						$owner = Owner::find($request->owner_id);
						$user_timezone = $owner->timezone;
						$default_timezone = Config::get('app.timezone');
														
						$date_time = get_user_time($default_timezone, $user_timezone, $request->request_start_time);


						$data['later'] = $request->later;
						$data['datetime'] = $date_time;
						
						$request_data = array();
						$request_data['owner'] = array();
						$request_data['owner']['name'] = $owner->first_name . " " . $owner->last_name;
						$request_data['owner']['picture'] = $owner->picture;
						$request_data['owner']['phone'] = $owner->phone;
						$request_data['owner']['address'] = $owner->address;
						$request_data['owner']['latitude'] = $owner->latitude;
						$request_data['owner']['longitude'] = $owner->longitude;
						if ($request->D_latitude != NULL) {
							Log::info('D_latitude = ' . print_r($request->D_latitude, true));
							$request_data['owner']['d_latitude'] = $request->D_latitude;
							$request_data['owner']['d_longitude'] = $request->D_longitude;
						}
						$request_data['owner']['rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->avg('rating') ?: 0;
						$request_data['owner']['num_rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->count();
						$request_data['payment_mode'] = $request->payment_mode;
						$request_data['dog'] = array();
						if ($dog = Dog::find($owner->dog_id)) {

							$request_data['dog']['name'] = $dog->name;
							$request_data['dog']['age'] = $dog->age;
							$request_data['dog']['breed'] = $dog->breed;
							$request_data['dog']['likes'] = $dog->likes;
							$request_data['dog']['picture'] = $dog->image_url;
						}
						$data['request_data'] = $request_data;
						array_push($all_requests, $data);

					}

					$response_array = array('success' => true, 'incoming_requests' => $all_requests);
					$response_code = 200;


				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$var = Keywords::where('id', 1)->first();
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


	// Respond To Request

	public function respond_request()
	{

		$token = Input::get('token');
		$walker_id = Input::get('id');
		$request_id = Input::get('request_id');
		$accepted = Input::get('accepted');

		$date_time = Input::get('datetime');


		$validator = Validator::make(
			array(
				'token' => $token,
				'walker_id' => $walker_id,
				'request_id' => $request_id,
				'accepted' => $accepted,
			),
			array(
				'token' => 'required',
				'walker_id' => 'required|integer',
				'accepted' => 'required|integer',
				'request_id' => 'required|integer'
			)
		);

		$driver = Keywords::where('id', 1)->first();

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
			$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry) || $is_admin) {
					// Retrive and validate the Request
					if ($request = Requests::find($request_id)) {
						if ($request->current_walker == $walker_id) {
							if ($accepted == 1) {
								if ($request->later == 1) {
									// request ended
									Requests::where('id', '=', $request_id)->update(array('confirmed_walker' => $walker_id, 'status' => 1));
								} else {
									Requests::where('id', '=', $request_id)->update(array('confirmed_walker' => $walker_id, 'status' => 1, 'request_start_time' => date('Y-m-d H:i:s')));
								}
								// confirm walker
								RequestMeta::where('request_id', '=', $request_id)->where('walker_id', '=', $walker_id)->update(array('status' => 1));

								// Update Walker availability

								Walker::where('id', '=', $walker_id)->update(array('is_available' => 0));

								// remove other schedule_meta
								RequestMeta::where('request_id', '=', $request_id)->where('status', '=', 0)->delete();
								
								$request = Requests::find($request_id);
								// Send Notification
								$walker = Walker::find($walker_id);
								$walker_data = array();
								$walker_data['first_name'] = $walker->first_name;
								$walker_data['last_name'] = $walker->last_name;
								$walker_data['phone'] = $walker->phone;
								$walker_data['bio'] = $walker->bio;
								$walker_data['picture'] = $walker->picture;
								$walker_data['latitude'] = $walker->latitude;
								$walker_data['longitude'] = $walker->longitude;
								$walker_data["vehicle_no"] =$walker->vehicle_no;
								$walker_data["model_no"]=$walker->model_no;
								$walker_data['destination_address']=$walker->destination_address;
								$walker_data['source_address']=$walker->source_address;

								if($request->D_latitude)
										$walker_data['d_latitude'] = $request->D_latitude;
									else
										$walker_data['d_latitude'] = '';

									if($request->D_longitude)
										$walker_data['d_longitude'] = $request->D_longitude;
									else
										$walker_data['d_longitude'] = '';
								$walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ?: 0;
								$walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count();

								$settings = Settings::where('key', 'default_distance_unit')->first();
								$unit = $settings->value;
								if ($unit == 0) {
									$unit_set = 'kms';
								} elseif ($unit == 1) {
									$unit_set = 'miles';
								}
								$bill = array();
								if ($request->is_completed == 1) {
									$bill['distance'] = (string)convert($request->distance, $unit);
									$bill['unit'] = $unit_set;
									$bill['time'] = $request->time;
									$bill['base_price'] = $request->base_price;
									$bill['distance_cost'] = $request->distance_cost;
									$bill['time_cost'] = $request->time_cost;
									$bill['total'] = $request->total;
									$bill['is_paid'] = $request->is_paid;
								}


								$setting = Settings::where('key', 'allow_calendar')->first();

								if ($request->later == 1 && $setting->value == 1) {

									$date_time = $request->request_start_time;

									$datewant = new DateTime($date_time);
									$datetime = $datewant->format('Y-m-d H:i:s');

									$end_time = $datewant->add(new DateInterval('P0Y0M0DT2H0M0S'))->format('Y-m-d H:i:s');

									$provavail = ProviderAvail::where('provider_id', $walker_id)->where('start', '<=', $datetime)->where('end', '>=', $end_time)->first();
									$starttime = $provavail->start;
									$endtime = $provavail->end;
									$provavail->delete();

									if ($starttime == $datetime) {
										$provavail1 = new ProviderAvail;
										$provavail1->provider_id = $walker_id;
										$provavail1->start = $end_time;
										$provavail1->end = $endtime;
										$provavail1->save();
									} elseif ($endtime == $end_time) {
										$provavail1 = new ProviderAvail;
										$provavail1->provider_id = $walker_id;
										$provavail1->start = $starttime;
										$provavail1->end = $datetime;
										$provavail1->save();
									} else {
										$provavail1 = new ProviderAvail;
										$provavail1->provider_id = $walker_id;
										$provavail1->start = $starttime;
										$provavail1->end = $datetime;
										$provavail1->save();

										$provavail2 = new ProviderAvail;
										$provavail2->provider_id = $walker_id;
										$provavail2->start = $end_time;
										$provavail2->end = $endtime;
										$provavail2->save();
									}

								}


								$response_array = array(
									'success' => true,
									'request_id' => $request_id,
									'status' => $request->status,
									'confirmed_walker' => $request->confirmed_walker,
									'is_walker_started' => $request->is_walker_started,
									'is_walker_arrived' => $request->is_walker_arrived,
									'is_walk_started' => $request->is_started,
									'is_completed' => $request->is_completed,
									'is_walker_rated' => $request->is_walker_rated,
									'walker' => $walker_data,
									'bill' => $bill,
								);
								Log::info('!!!!!!!!!!!!! /************* Walker **********/ = ' . print_r($request->confirmed_walker, true));
								$driver = Keywords::where('id', 1)->first();
								$trip = Keywords::where('id', 4)->first();

								$title = '' . $driver->keyword . ' has accepted the ' . $trip->keyword;

								$message = $response_array;

								send_notifications($request->owner_id, "owner", $title, $message);

								// Send SMS 
								$owner = Owner::find($request->owner_id);
								$settings = Settings::where('key', 'sms_when_provider_accepts')->first();
								$pattern = $settings->value;
								$pattern = str_replace('%user%', $owner->first_name . " " . $owner->last_name, $pattern);
								$pattern = str_replace('%driver%', $walker->first_name . " " . $walker->last_name, $pattern);

								$pattern = str_replace('%driver_mobile%', $walker->phone, $pattern);
								sms_notification($request->owner_id, 'owner', $pattern);

								// Send SMS 

								$settings = Settings::where('key', 'sms_request_completed')->first();
								$pattern = $settings->value;
								$pattern = str_replace('%user%', $owner->first_name . " " . $owner->last_name, $pattern);
								$pattern = str_replace('%id%', $request->id, $pattern);
								$pattern = str_replace('%user_mobile%', $owner->phone, $pattern);
								sms_notification(1, 'admin', $pattern);
							} else {
								$time = date("Y-m-d H:i:s");
								$query = "SELECT id,owner_id,current_walker,TIMESTAMPDIFF(SECOND,request_start_time, '$time') as diff from request where id = '$request_id'";
								$results = DB::select(DB::raw($query));
								$settings = Settings::where('key', 'provider_timeout')->first();
								$timeout = $settings->value;

								if($accepted == 2){
									// Archiving Old Walker
									RequestMeta::where('request_id', '=', $request_id)->where('walker_id', '=', $walker_id)->update(array('status' => 2));
								}else{
									// Archiving Old Walker
									RequestMeta::where('request_id', '=', $request_id)->where('walker_id', '=', $walker_id)->update(array('status' => 3));
								}
								
								$request_meta = RequestMeta::where('request_id', '=', $request_id)->where('status', '=', 0)->orderBy('created_at')->first();

								// update request
								if (isset($request_meta->walker_id)) {

									Requests::where('id', '=', $request_id)->update(array('current_walker' => $request_meta->walker_id, 'request_start_time' => date("Y-m-d H:i:s")));

									// Send Notification

									$walker = Walker::find($request_meta->walker_id);
									$settings = Settings::where('key', 'provider_timeout')->first();
									$time_left = $settings->value;

									$msg_array = array();
									$msg_array['unique_id'] = 1;
									$msg_array['request_id'] = $request->id;
									$msg_array['time_left_to_respond'] = $time_left;

									if (Input::has('payment_mode')) {
										$msg_array['payment_mode'] = $request->payment_mode;
									}

									$owner_data = Owner::find($request->owner_id);
									$request_data = array();
									$request_data['owner'] = array();
									$request_data['owner']['name'] = $owner_data->first_name . " " . $owner_data->last_name;
									$request_data['owner']['picture'] = $owner_data->picture;
									$request_data['owner']['phone'] = $owner_data->phone;
									$request_data['owner']['address'] = $owner_data->address;
									$request_data['owner']['latitude'] = $owner_data->latitude;
									$request_data['owner']['longitude'] = $owner_data->longitude;
									if ($request->d_latitude != NULL) {
										$request_data['owner']['d_latitude'] = $request->d_latitude;
										$request_data['owner']['d_longitude'] = $request->d_longitude;
									}
									$request_data['owner']['rating'] = DB::table('review_dog')->where('owner_id', '=', $owner_data->id)->avg('rating') ?: 0;
									$request_data['owner']['num_rating'] = DB::table('review_dog')->where('owner_id', '=', $owner_data->id)->count();
									$msg_array['request_data'] = $request_data;

									$title = "New Request";

									$message = $msg_array;

									send_notifications($request_meta->walker_id, "walker", $title, $message);

								} else {
									// request ended
									Requests::where('id', '=', $request_id)->update(array('current_walker' => 0, 'status' => 1));
									$driver = Keywords::where('id', 1)->first();
									$owne = Owner::where('id', $request->owner_id)->first();
									$driver_keyword = $driver->keyword;
									$owner_data_id = $owne->id;
									send_notifications($owner_data_id, "owner", 'No ' . $driver_keyword . ' Found', 'No ' . $driver_keyword . ' are available right now in your area. Kindly try after sometime.');
								}
							}
							$response_array = array('success' => true);
							$response_code = 200;
						} else {
							$response_array = array('success' => false, 'error' => 'Request ID does not matches' . $driver->keyword . ' ID', 'error_code' => 472);
							$response_code = 200;
						}

					} else {
						$response_array = array('success' => false, 'error' => 'Request ID Not Found', 'error_code' => 405);
						$response_code = 200;
					}


				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$response_array = array('success' => false, 'error' => '' . $driver->keyword . ' ID not Found', 'error_code' => 410);

				} else {
					$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

				}
				$response_code = 200;
			}
		}

		$response = Response::json($response_array, $response_code);
		return $response;

	}


	// Get Request Status
	public function request_in_progress()
	{

		$token = Input::get('token');
		$walker_id = Input::get('id');

		$validator = Validator::make(
			array(
				'token' => $token,
				'walker_id' => $walker_id,
			),
			array(
				'token' => 'required',
				'walker_id' => 'required|integer',
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
			$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry) || $is_admin) {

					$request = Requests::where('status', '=', 1)->where('is_cancelled', '=', 0)->where('is_completed', '=', 0)->where('confirmed_walker', '=', $walker_id)->first();
					if ($request) {
						$request_id = $request->id;
					} else {
						$request_id = -1;
					}
					$response_array = array(
						'request_id' => $request_id,
						'success' => true,
					);
					$response_code = 200;

				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$driver = Keywords::where('id', 1)->first();
					$response_array = array('success' => false, 'error' => '' . $driver->keyword . ' ID not Found', 'error_code' => 410);

				} else {
					$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

				}
				$response_code = 200;
			}
		}

		$response = Response::json($response_array, $response_code);
		return $response;

	}


	// Get Request Status
	public function get_request()
	{

		$request_id = Input::get('request_id');
		$token = Input::get('token');
		$walker_id = Input::get('id');

		$validator = Validator::make(
			array(
				'request_id' => $request_id,
				'token' => $token,
				'walker_id' => $walker_id,
			),
			array(
				'request_id' => 'required|integer',
				'token' => 'required',
				'walker_id' => 'required|integer',
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
			$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry)) {
					// Do necessary operations
					if ($request = Requests::find($request_id)) {
						if ($request->confirmed_walker == $walker_id) {

							$owner = Owner::find($request->owner_id);
							$request_data = array();
							$request_data['is_walker_started'] = $request->is_walker_started;
							$request_data['is_walker_arrived'] = $request->is_walker_arrived;
							$request_data['is_started'] = $request->is_started;
							$request_data['is_completed'] = $request->is_completed;
							$request_data['is_dog_rated'] = $request->is_dog_rated;
							
							$user_timezone = $owner->timezone;
							$default_timezone = Config::get('app.timezone');
								
							$date_time = get_user_time($default_timezone, $user_timezone, $request->request_start_time);

							$request_data['accepted_time'] = $date_time;
							
							if($request->promo_code!=NULL){
								if ($request->promo_code != NULL) {
									$promo_code = PromoCodes::where('id', $request->promo_code)->first();
									$promo_value = $promo_code->value;
									$promo_type = $promo_code->type;
									if ($promo_type == 1) {
										$discount = $request->total * $promo_value / 100;
									} elseif ($promo_type == 2) {
										$discount = $promo_value;
									}
									$request_data['promo_discount'] = $discount;
								}
							}
							if ($request->is_started == 1) {

								$time = DB::table('walk_location')
									->where('request_id', $request_id)
									->min('created_at');

								$date_time = get_user_time($default_timezone, $user_timezone, $time);

								$request_data['start_time'] = $date_time;

								$settings = Settings::where('key', 'default_distance_unit')->first();
								$unit = $settings->value;

								$distance = DB::table('walk_location')->where('request_id', $request_id)->max('distance');
								$request_data['distance'] = (string)convert($distance, $unit);
								if ($unit == 0) {
									$unit_set = 'kms';
								} elseif ($unit == 1) {
									$unit_set = 'miles';
								}
								$request_data['unit'] = $unit_set;

								$loc1 = WalkLocation::where('request_id', $request->id)->first();
								$loc2 = WalkLocation::where('request_id', $request->id)->orderBy('id', 'desc')->first();
								if ($loc1) {
									$time1 = strtotime($loc2->created_at);
									$time2 = strtotime($loc1->created_at);
									$difference = intval(($time1 - $time2) / 60);
								} else {
									$difference = 0;
								}
								$request_data['time'] = $difference;
							}

							if ($request->is_completed == 1) {
								$request_data['distance'] = (string)convert($distance, $unit);
								if ($unit == 0) {
									$unit_set = 'kms';
								} elseif ($unit == 1) {
									$unit_set = 'miles';
								}
								$request_data['unit'] = $unit_set;

								$time = DB::table('walk_location')
									->where('request_id', $request_id)
									->max('created_at');

								$end_time = get_user_time($default_timezone, $user_timezone, $time);

								$request_data['end_time'] = $end_time;
							}

							$request_data['owner'] = array();
							$request_data['owner']['name'] = $owner->first_name . " " . $owner->last_name;
							$request_data['owner']['picture'] = $owner->picture;
							$request_data['owner']['phone'] = $owner->phone;
							$request_data['owner']['address'] = $owner->address;
							$request_data['owner']['latitude'] = $owner->latitude;
							$request_data['owner']['longitude'] = $owner->longitude;
							if ($request->D_latitude != NULL) {
								$request_data['owner']['d_latitude'] = $request->D_latitude;
								$request_data['owner']['d_longitude'] = $request->D_longitude;
							}
							$request_data['owner']['rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->avg('rating') ?: 0;
							$request_data['owner']['num_rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->count();
							$request_data['destination_address']=$request->destination_address;
							$request_data['source_address']=$request->source_address;
							$request_data['bill'] = array();
							$bill = array();
							$settings = Settings::where('key', 'default_distance_unit')->first();
							$unit = $settings->value;
							if ($unit == 0) {
								$unit_set = 'kms';
							} elseif ($unit == 1) {
								$unit_set = 'miles';
							}
							$requestserv = RequestServices::where('request_id', $request->id)->first();
							$distance_time_cost = ProviderServices::where('provider_id', $request->confirmed_walker)->first();
							$currency_selected = Keywords::find(5);
							if ($request->is_completed == 1) {
								$bill['distance'] = (string)$request->distance;
								$bill['unit'] = $unit_set;
								$bill['time'] = $request->time;
								if ($requestserv->base_price != 0) {
									$bill['base_price'] = currency_converted($requestserv->base_price);
									$bill['distance_cost'] = currency_converted($requestserv->distance_cost);
									$bill['time_cost'] = currency_converted($requestserv->time_cost);
										$adding_three = $bill['base_price'] + $bill['distance_cost'] + $bill['time_cost']; 
									
								} else {
									$setbase_price = Settings::where('key', 'base_price')->first();
									$bill['base_price'] = currency_converted($setbase_price->value);
									$setdistance_price = Settings::where('key', 'price_per_unit_distance')->first();
									$bill['distance_cost'] = currency_converted($setdistance_price->value);
									$settime_price = Settings::where('key', 'price_per_unit_time')->first();
									$bill['time_cost'] = currency_converted($settime_price->value);
										$adding_three = $bill['base_price'] + $bill['distance_cost'] + $bill['time_cost']; 
									
								}

									if ( $distance_time_cost->price_per_unit_distance != 0 ) 
											{
											$bill['distance_cost_only']=$distance_time_cost->price_per_unit_distance;
											
											}else{
												$setdistance_price = Settings::where('key', 'price_per_unit_distance')->first();
												$bill['distance_cost_only']=currency_converted($setdistance_price->value);
											
											}
											if($distance_time_cost->price_per_unit_time != 0 ){
											$bill['time_cost_only']=$distance_time_cost->price_per_unit_time;
	
											}else{
												$settime_price = Settings::where('key', 'price_per_unit_time')->first();
												$bill['time_cost_only']=currency_converted($settime_price->value);
										
												}

								// $bill['distance_cost_only']=$distance_time_cost->price_per_unit_distance;
								// $bill['time_cost_only']=$distance_time_cost->price_per_unit_time;
								$bill['payment_mode'] = $request->payment_mode;	
								$admins = Admin::first();
								$walker = Walker::where('id',$walker_id)->first();
								$bill['walker']['email'] = $walker->email;
								$bill['admin']['email'] = $admins->username;
								if ($request->transfer_amount != 0) {
									$bill['walker']['amount'] = currency_converted($request->total - $request->transfer_amount);
									$bill['admin']['amount'] = currency_converted($request->transfer_amount);
								} else {
									$bill['walker']['amount'] = currency_converted($request->transfer_amount);
									$bill['admin']['amount'] = currency_converted($request->total - $request->transfer_amount);
								}
										
								$service_price = Settings::where('key', 'service_fee')->first();
								$service_fee= currency_converted($service_price->value);

								$total_invoice_admin =($request->total * $service_fee) /100 ;

								$bill['admin']['amount']= ($request->total * $service_fee) /100;
								$bill['walker']['amount']= $request->total - $total_invoice_admin;
										
										

							
								$discount = 0;
								if($request->promo_code !== NULL){
									if ($request->promo_code !== NULL) {
										$promo_code = PromoCodes::where('id', $request->promo_code)->first();
										if($promo_code){
											$promo=1;
											$promo_value = $promo_code->value;
											$promo_type = $promo_code->type;
											if ($promo_type == 1) {
												// Percent Discount
												$discount = $adding_three * $promo_value / 100;
											} elseif ($promo_type == 2) {
												// Absolute Discount
												$discount = $promo_value;
											}
											$actual_total_promo = $discount + $request->total; 
										}
										else{
											$discount =0;

										}
									}
								}else{
									$discount =0;
									$promo =0;
									$actual_total_promo = $request->total; 
								}
								$bill['promo']=$promo;
								$bill["promo_discount"]=$discount;
								
								$bill['currency'] = $currency_selected->keyword;
								$bill['total'] = currency_converted($request->total);
								$bill['actual_total'] = currency_converted($request->total + $request->ledger_payment + $discount);
								$bill['is_paid'] = $request->is_paid;
							}

							$response_array = array('success' => true, 'request' => $request_data,'bill' => $bill);
							$response_code = 200;

						} else {
							$driver = Keywords::where('id', 1)->first();
							$response_array = array('success' => false, 'error' => 'Service ID doesnot matches with ' . $driver->keyword . ' ID', 'error_code' => 407);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Service ID Not Found', 'error_code' => 408);
						$response_code = 200;
					}
				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$driver = Keywords::where('id', 1)->first();
					$response_array = array('success' => false, 'error' => '' . $driver->keyword . ' ID not Found', 'error_code' => 410);

				} else {
					$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

				}
				$response_code = 200;
			}
		}

		$response = Response::json($response_array, $response_code);
		return $response;

	}


	// Get Request Status
	public function get_walk_location()
	{

		$request_id = Input::get('request_id');
		$token = Input::get('token');
		$walker_id = Input::get('id');
		$timestamp = Input::get('ts');

		$validator = Validator::make(
			array(
				'request_id' => $request_id,
				'token' => $token,
				'walker_id' => $walker_id,
			),
			array(
				'request_id' => 'required|integer',
				'token' => 'required',
				'walker_id' => 'required|integer',
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
			$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry) || $is_admin) {
					// Do necessary operations
					if ($request = Requests::find($request_id)) {
						if ($request->confirmed_walker == $walker_id) {

							if (isset($timestamp)) {
								$walk_locations = WalkLocation::where('request_id', '=', $request_id)->where('created_at', '>', $timestamp)->orderBy('created_at')->get();
							} else {
								$walk_locations = WalkLocation::where('request_id', '=', $request_id)->orderBy('created_at')->get();

							}
							$locations = array();
							$settings = Settings::where('key', 'default_distance_unit')->first();
							$unit = $settings->value;
							foreach ($walk_locations as $walk_location) {
								$location = array();
								$location['latitude'] = $walk_location->latitude;
								$location['longitude'] = $walk_location->longitude;
								$location['distance'] = convert($walk_location->distance, $unit);
								$location['timestamp'] = $walk_location->created_at;
								array_push($locations, $location);
							}

							$response_array = array('success' => true, 'locationdata' => $locations);
							$response_code = 200;

						} else {
							$driver = Keywords::where('id', 1)->first();
							$response_array = array('success' => false, 'error' => 'Service ID doesnot matches with ' . $driver->keyword . ' ID', 'error_code' => 407);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Service ID Not Found', 'error_code' => 408);
						$response_code = 200;
					}
				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$driver = Keywords::where('id', 1)->first();
					$response_array = array('success' => false, 'error' => '' . $driver->keyword . ' ID not Found', 'error_code' => 410);

				} else {
					$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

				}
				$response_code = 200;
			}
		}

		$response = Response::json($response_array, $response_code);
		return $response;

	}


	// walker started
	public function request_walker_started()
	{
		if (Request::isMethod('post')) {
			$request_id = Input::get('request_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');
			$latitude = Input::get('latitude');
			$longitude = Input::get('longitude');

			$validator = Validator::make(
				array(
					'request_id' => $request_id,
					'token' => $token,
					'walker_id' => $walker_id,
					'latitude' => $latitude,
					'longitude' => $longitude,
				),
				array(
					'request_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
					'latitude' => 'required',
					'longitude' => 'required',
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($request = Requests::find($request_id)) {
							if ($request->confirmed_walker == $walker_id) {

								if ($request->confirmed_walker != 0) {
									$request->is_walker_started = 1;
									$request->save();

									$walker_data->latitude = $latitude;
									$walker_data->longitude = $longitude;
									$walker_data->save();

									// Send Notification
									$msg_array = array();
									$walker = Walker::find($request->confirmed_walker);
									$walker_data = array();
									$walker_data['first_name'] = $walker->first_name;
									$walker_data['last_name'] = $walker->last_name;
									$walker_data['phone'] = $walker->phone;
									$walker_data['bio'] = $walker->bio;
									$walker_data['picture'] = $walker->picture;
									$walker_data['latitude'] = $walker->latitude;
									$walker_data['longitude'] = $walker->longitude;
									$walker_data['vehicle_no']=$walker->vehicle_no;
									$walker_data['model_no']=$walker->model_no;
									$walker_data['type'] = $walker->type;
									if($request->D_latitude)
										$walker_data['d_latitude'] = $request->D_latitude;
									else
										$walker_data['d_latitude'] = '';

									if($request->D_longitude)
										$walker_data['d_longitude'] = $request->D_longitude;
									else
										$walker_data['d_longitude'] = '';
									$walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ?: 0;
									$walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count();

									$settings = Settings::where('key', 'default_distance_unit')->first();
									$unit = $settings->value;
									if ($unit == 0) {
										$unit_set = 'kms';
									} elseif ($unit == 1) {
										$unit_set = 'miles';
									}
									$bill = array();
									if ($request->is_completed == 1) {
										$bill['distance'] = (string)convert($request->distance, $unit);
										$bill['unit'] = $unit_set;
										$bill['time'] = $request->time;
										$bill['base_price'] = $request->base_price;
										$bill['distance_cost'] = $request->distance_cost;
										$bill['time_cost'] = $request->time_cost;
										$bill['total'] = $request->total;
										$bill['is_paid'] = $request->is_paid;
									}

									$response_array = array(
										'success' => true,
										'request_id' => $request_id,
										'status' => $request->status,
										'confirmed_walker' => $request->confirmed_walker,
										'is_walker_started' => $request->is_walker_started,
										'is_walker_arrived' => $request->is_walker_arrived,
										'is_walk_started' => $request->is_started,
										'is_completed' => $request->is_completed,
										'is_walker_rated' => $request->is_walker_rated,
										'payment_mode' => $request->payment_data,
										'walker' => $walker_data,
										'bill' => $bill,
									);

									$message = $response_array;
									$driver = Keywords::where('id', 1)->first();
									//$title = '' . $driver->keyword . ' has started moving towards you';

									$walking_started = Settings::where('key', 'walking_started')->first();
									$walking_started = $walking_started->value;
									$title = $walking_started; 


									send_notifications($request->owner_id, "owner", $title, $message);


									$response_array = array('success' => true);
									$response_code = 200;
								} else {
									$driver = Keywords::where('id', 1)->first();
									$response_array = array('success' => false, 'error' => '' . $driver->keyword . ' not yet confirmed', 'error_code' => 413);
									$response_code = 200;
								}
							} else {
								$driver = Keywords::where('id', 1)->first();
								$response_array = array('success' => false, 'error' => 'Service ID doesnot matches with ' . $driver->keyword . ' ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Service ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$driver = Keywords::where('id', 1)->first();
						$response_array = array('success' => false, 'error' => '' . $driver->keyword . ' ID not Found', 'error_code' => 410);

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


	// walked arrived
	public function request_walker_arrived()
	{
		if (Request::isMethod('post')) {
			$request_id = Input::get('request_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');
			$latitude = Input::get('latitude');
			$longitude = Input::get('longitude');

			$validator = Validator::make(
				array(
					'request_id' => $request_id,
					'token' => $token,
					'walker_id' => $walker_id,
					'latitude' => $latitude,
					'longitude' => $longitude,
				),
				array(
					'request_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
					'latitude' => 'required',
					'longitude' => 'required',
				)
			);

			$driver = Keywords::where('id', 1)->first();

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($request = Requests::find($request_id)) {
							if ($request->confirmed_walker == $walker_id) {

								if ($request->is_walker_started == 1) {
									$request->is_walker_arrived = 1;
									$request->save();

									$walker_data->latitude = $latitude;
									$walker_data->longitude = $longitude;
									$walker_data->save();

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
									$walker_data['vehicle_no']=$walker->vehicle_no;
									$walker_data['model_no']=$walker->model_no;
									$walker_data['type'] = $walker->type;
									if($request->D_latitude)
										$walker_data['d_latitude'] = $request->D_latitude;
									else
										$walker_data['d_latitude'] = '';

									if($request->D_longitude)
										$walker_data['d_longitude'] = $request->D_longitude;
									else
										$walker_data['d_longitude'] = '';
									$walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ?: 0;
									$walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count();


									$settings = Settings::where('key', 'default_distance_unit')->first();
									$unit = $settings->value;
									if ($unit == 0) {
										$unit_set = 'kms';
									} elseif ($unit == 1) {
										$unit_set = 'miles';
									}
									$bill = array();
									if ($request->is_completed == 1) {
										$bill['distance'] = (string)convert($request->distance, $unit);
										$bill['unit'] = $unit_set;
										$bill['time'] = $request->time;
										$bill['base_price'] = $request->base_price;
										$bill['distance_cost'] = $request->distance_cost;
										$bill['time_cost'] = $request->time_cost;
										$bill['total'] = $request->total;
										$bill['is_paid'] = $request->is_paid;
									}

									$response_array = array(
										'success' => true,
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
									$driver = Keywords::where('id', 1)->first();

									$walk_arrived = Settings::where('key', 'walk_arrived')->first();
									$walk_arrived = $walk_arrived->value;
									
									//$title = 'Your ' . $var->keyword . ' has been started';

									$title = $walk_arrived; 
									$title = '' . $driver->keyword . ' has arrived at your place';

									$message = $response_array;

									send_notifications($request->owner_id, "owner", $title, $message);

									// Send SMS 
									$owner = Owner::find($request->owner_id);
									$settings = Settings::where('key', 'sms_when_provider_arrives')->first();
									$pattern = $settings->value;
									$pattern = str_replace('%user%', $owner->first_name . " " . $owner->last_name, $pattern);
									$pattern = str_replace('%driver%', $walker->first_name . " " . $walker->last_name, $pattern);
									$pattern = str_replace('%driver_mobile%', $walker->phone, $pattern);
									//sms_notification($request->owner_id, 'owner', $pattern);

									$response_array = array('success' => true);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Service not yet started', 'error_code' => 413);
									$response_code = 200;
								}
							} else {
								$response_array = array('success' => false, 'error' => 'Service ID doesnot matches with ' . $driver->keyword . ' ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Service ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$response_array = array('success' => false, 'error' => '' . $driver->keyword . ' ID not Found', 'error_code' => 410);

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

	// walk started
	public function request_walk_started()
	{
		if (Request::isMethod('post')) {
			$request_id = Input::get('request_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');
			$latitude = Input::get('latitude');
			$longitude = Input::get('longitude');

			$validator = Validator::make(
				array(
					'request_id' => $request_id,
					'token' => $token,
					'walker_id' => $walker_id,
					'latitude' => $latitude,
					'longitude' => $longitude,
				),
				array(
					'request_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
					'latitude' => 'required',
					'longitude' => 'required',
				)
			);

			$var = Keywords::where('id', 1)->first();

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($request = Requests::find($request_id)) {
							if ($request->confirmed_walker == $walker_id) {

								if ($request->is_walker_arrived == 1) {
									$request->is_started = 1;
									$request->save();

									$walk_location = new WalkLocation;
									$walk_location->latitude = $latitude;
									$walk_location->longitude = $longitude;
									$walk_location->request_id = $request_id;
									$walk_location->save();

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
									$walker_data['vehicle_no']=$walker->vehicle_no;
									$walker_data['model_no']=$walker->model_no;
									$walker_data['type'] = $walker->type;

									if($request->D_latitude) {
										$walker_data['d_latitude'] = $request->D_latitude; 
									}
									else
										$walker_data['d_latitude'] = '';

									if($request->D_longitude) {
										$walker_data['d_longitude'] = $request->D_longitude;
									}
									else
										$walker_data['d_longitude'] = '';

									$walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ?: 0;
									$walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count();

									$settings = Settings::where('key', 'default_distance_unit')->first();
									$unit = $settings->value;
									if ($unit == 0) {
										$unit_set = 'kms';
									} elseif ($unit == 1) {
										$unit_set = 'miles';
									}
									$bill = array();
									if ($request->is_completed == 1) {
										$bill['distance'] = (string)convert($request->distance, $unit);
										$bill['unit'] = $unit_set;
										$bill['time'] = $request->time;
										$bill['base_price'] = $request->base_price;
										$bill['distance_cost'] = $request->distance_cost;
										$bill['time_cost'] = $request->time_cost;
										$bill['total'] = $request->total;
										$bill['is_paid'] = $request->is_paid;
									}
									

									$response_array = array(
										'success' => true,
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
									$var = Keywords::where('id', 4)->first();

									$walk_started = Settings::where('key', 'walk_started')->first();
									$walk_started = $walk_started->value;
									
									//$title = 'Your ' . $var->keyword . ' has been started';

									$title = $walk_started; 
									$message = $response_array;

									send_notifications($request->owner_id, "owner", $title, $message);


									$response_array = array('success' => true);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => '' . $var->keyword . ' not yet arrived', 'error_code' => 413);
									$response_code = 200;
								}
							} else {
								$response_array = array('success' => false, 'error' => 'Service ID doesnot matches with ' . $var->keyword . ' ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Service ID Not Found', 'error_code' => 408);
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
		}
		$response = Response::json($response_array, $response_code);
		return $response;

	}

	// walk completed
	public function request_walk_completed()
	{
		if (Request::isMethod('post')) {
			$request_id = Input::get('request_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');
			$latitude = Input::get('latitude');
			$longitude = Input::get('longitude');
			$distance = Input::get('distance');
			$time = Input::get('time');

			Log::info('distance input = ' . print_r($distance, true));
			Log::info('time input = ' . print_r($time, true));

			$validator = Validator::make(
				array(
					'request_id' => $request_id,
					'token' => $token,
					'walker_id' => $walker_id,
					'latitude' => $latitude,
					'longitude' => $longitude,
					'distance' => $distance,
					'time' => $time,
				),
				array(
					'request_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
					'latitude' => 'required',
					'longitude' => 'required',
					'distance' => 'required',
					'time' => 'required',
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations 

						if ($request = Requests::find($request_id)) {
							if ($request->confirmed_walker == $walker_id) {

								if ($request->is_started == 1) {

									$settings = Settings::where('key', 'default_charging_method_for_users')->first();
									$pricing_type = $settings->value;
									$settings = Settings::where('key', 'default_distance_unit')->first();
									$unit = $settings->value;

									Log::info('distance = ' . print_r($distance, true));

									$reqserv = RequestServices::where('request_id',$request_id)->get();
									$actual_total = 0;
									$price_per_unit_distance = 0;
									$price_per_unit_time = 0;
									$base_price = 0;
									foreach ($reqserv as $rse) {
										Log::info('type = '.print_r($rse->type,true));
										$protype = ProviderType::where('id',$rse->type)->first();
										$pt = ProviderServices::where('provider_id',$walker_id)->where('type',$rse->type)->first();
											if($pt->base_price==0){
												$setbase_price = Settings::where('key','base_price')->first();
												$base_price = $setbase_price->value;
												$rse->base_price = $base_price;
											}else{
												$base_price = $pt->base_price;
												$rse->base_price = $base_price;
											}

											$is_multiple_service = Settings::where('key', 'allow_multiple_service')->first();
											if($is_multiple_service->value == 0){

											if($pt->price_per_unit_distance==0){
												$setdistance_price = Settings::where('key','price_per_unit_distance')->first();
												$price_per_unit_distance = $setdistance_price->value*$distance;
												$rse->distance_cost = $price_per_unit_distance;
											}else{
												$price_per_unit_distance = $pt->price_per_unit_distance*$distance;
												$rse->distance_cost = $price_per_unit_distance;
											}

											if($pt->price_per_unit_time ==0){
												$settime_price = Settings::where('key','price_per_unit_time')->first();
												$price_per_unit_time = $settime_price->value*$time;
												$rse->time_cost = $price_per_unit_time;
											}else{
												$price_per_unit_time = $pt->price_per_unit_time*$time;
												$rse->time_cost = $price_per_unit_time;
											}
										}	
								
										Log::info('total price = '.print_r($base_price+$price_per_unit_distance+$price_per_unit_time, true));
										$rse->total = $base_price+$price_per_unit_distance+$price_per_unit_time;
										$rse->save();
										$actual_total = $actual_total + $base_price+$price_per_unit_distance+$price_per_unit_time;
										Log::info('total_price = '.print_r($actual_total,true));
									}

									$rs = RequestServices::where('request_id', $request_id)->get();
									$total = 0;
									foreach ($rs as $key) {
										Log::info('total = ' . print_r($key->total, true));
										$total = $total + $key->total;
									}

									$request->is_completed = 1;
									$request->distance = $distance;
									$request->time = $time;

									$request->security_key = NULL;
									$request->total = $total;
									
									// charge client
									$ledger = Ledger::where('owner_id', $request->owner_id)->first();

									if ($ledger) {
										$balance = $ledger->amount_earned - $ledger->amount_spent;
										Log::info('ledger balance = '.print_r($balance,true));
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
									if($pcode = PromoCodes::where('id',$request->promo_code)->where('type',1)->first()){
										$discount = ($pcode->value)/100;
										$promo_value_code = $pcode->value;
										$discount = $request->total * $promo_value_code / 100;
										$promo_discount = $discount;
										$total = $total-$promo_discount;
										if($total < 0){
											$total = 0;
										}
										$promo = 1;
										$promo_discount = $discount;
										$actual_total_promo = $discount + $total;
									}else{
										$promo ="0";
										$promo_discount = 0;
										$discount =0;
										$actual_total_promo = $total;
									}

									$request->total = $total;

									Log::info('final total = ' . print_r($total, true));

									$cod_sett = Settings::where('key', 'cod')->first();
									$allow_cod = $cod_sett->value;
									if ($request->payment_mode == 1 and $allow_cod == 1) {
										$request->is_paid = 1;
										Log::info('allow_cod');

									}
									elseif($request->payment_mode == 3){
										
										$friend_id = Friend::where('user_id',$request->owner_id)
													->where('status',1)
													->first();
										$owner_friend_id = $friend_id->friend_id;
										$payment_data = Payment::where('owner_id', $owner_friend_id)->where('is_default', 1)->first();
											if (!$payment_data)
												$payment_data = Payment::where('owner_id', $owner_friend_id)->first();

											if ($payment_data) {
												$customer_id = $payment_data->customer_id;

												$setransfer = Settings::where('key', 'transfer')->first();
												$transfer_allow = $setransfer->value;
												if (Config::get('app.default_payment') == 'stripe') {
													//dd($customer_id);
													Stripe::setApiKey(Config::get('app.stripe_secret_key'));
													try {
														Stripe_Charge::create(array(
																"amount" => floor($total) * 100,
																"currency" => "ngn",
																"customer" => $customer_id)
														);
													} catch (Stripe_InvalidRequestError $e) {
														// Invalid parameters were supplied to Stripe's API
														$ownr = Owner::find($owner_friend_id);
														$ownr->debt = $total;
														$ownr->save();
														$response_array = array('error' => $e->getMessage());
														$response_code = 200;
														$response = Response::json($response_array, $response_code);
														return $response;
													}
													$request->is_paid = 1;
													$settng = Settings::where('key', 'service_fee')->first();
													if ($transfer_allow == 1 && $walker_data->merchant_id != "") {

														$transfer = Stripe_Transfer::create(array(
																"amount" => floor($total - ($settng->value*$total/100)) * 100, // amount in cents
																"currency" => "usd",
																"recipient" => $walker_data->merchant_id)
														);
														$request->transfer_amount = floor($total - $settng->value*$total/100);
													}
												}
												$request->card_payment = $total;
												$request->ledger_payment = $request->total - $total;
											}
										}
								
									 elseif ($request->payment_mode == 2) {
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
													//dd($customer_id);
													Stripe::setApiKey(Config::get('app.stripe_secret_key'));
													try {
														Stripe_Charge::create(array(
																"amount" => floor($total) * 100,
																"currency" => "ngn",
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
													if ($transfer_allow == 1 && $walker_data->merchant_id != "") {

														$transfer = Stripe_Transfer::create(array(
																"amount" => floor($total - ($settng->value*$total/100)) * 100, // amount in cents
																"currency" => "usd",
																"recipient" => $walker_data->merchant_id)
														);
														$request->transfer_amount = floor($total - $settng->value*$total/100);
													}
												} else {
													try {
														Braintree_Configuration::environment(Config::get('app.braintree_environment'));
														Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
														Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
														Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
														if ($transfer_allow == 1) {
															$sevisett = Settings::where('key', 'service_fee')->first();
															$service_fee = $sevisett->value*$total/100;
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
										//for testing
										sms_notification(1, 'admin', $pattern);
									}

									$walker = Walker::find($walker_id);
									$walker->is_available = 1;
									$walker->save();
									Log::info('distance walk location = ' . print_r($distance, true));
									$walk_location = new WalkLocation;
									$walk_location->latitude = $latitude;
									$walk_location->longitude = $longitude;
									$walk_location->request_id = $request_id;
									$walk_location->distance = $distance;
									$walk_location->save();

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
									$walker_data['vehicle_no']=$walker->vehicle_no;
									$walker_data['model_no']=$walker->model_no;
									$walker_data['type'] = $walker->type;
									if($request->D_latitude)
										$walker_data['d_latitude'] = $request->D_latitude;
									else
										$walker_data['d_latitude'] = '';

									if($request->D_longitude)
										$walker_data['d_longitude'] = $request->D_longitude;
									else
										$walker_data['d_longitude'] = '';
									$walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ?: 0;
									$walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count();

									$requestserv = RequestServices::where('request_id', $request->id)->first();
									$distance_time_cost = ProviderServices::where('provider_id', $request->confirmed_walker)->first();
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
										if ( $distance_time_cost->price_per_unit_distance != 0 ) 
											{
											$bill['distance_cost_only']=$distance_time_cost->price_per_unit_distance;
											
											}else{
												$setdistance_price = Settings::where('key', 'price_per_unit_distance')->first();
												$bill['distance_cost_only']=currency_converted($setdistance_price->value);
											
											}
											if($distance_time_cost->price_per_unit_time != 0 ){
											$bill['time_cost_only']=$distance_time_cost->price_per_unit_time;
	
											}else{
												$settime_price = Settings::where('key', 'price_per_unit_time')->first();
												$bill['time_cost_only']=currency_converted($settime_price->value);
										
												}

										// $bill['distance_cost_only']=$distance_time_cost->price_per_unit_distance;
										// $bill['time_cost_only']=$distance_time_cost->price_per_unit_time;
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
										$service_price = Settings::where('key', 'service_fee')->first();
										$service_fee= currency_converted($service_price->value);

										$total_invoice_admin =($total * $service_fee) /100 ;

										
										$bill['admin']['amount']= ($total * $service_fee) /100;
										$bill['walker']['amount']= $total - $total_invoice_admin;
										
										
										$bill['currency'] = $currency_selected->keyword;
										$bill['actual_total'] = currency_converted($actual_total);
										$bill['total'] = currency_converted($request->total);
										$bill['is_paid'] = $request->is_paid;
										$bill['promo_discount'] = currency_converted($promo_discount);
										
										/*$promo = 1;
										$promo_discount = $discount;
										$actual_total_promo = $discount + $total;*/

										$bill["promo"]=$promo;
										//$bill["promo_discount"]=$discount;
										
									}

									$rservc = RequestServices::where('request_id',$request->id)->get();
                                    $typs=array();
                                    $typi=array();
                                    $typp=array();
                                     foreach ($rservc as $typ) {
                                    $typ1=ProviderType::where('id',$typ->type)->first();
                             		$typ_price=ProviderServices::where('provider_id',$request->confirmed_walker)->where('type',$typ->type)->first();

                                    if($typ_price->base_price>0){
                                        $typp1=0.00;
                                        $typp1=$typ_price->base_price;
                                    }
                                    elseif($typ_price->price_per_unit_distance>0){
                                        $typp1=0.00;
                                        foreach ($rservc as $key) {
                                            $typp1 = $typp1 + $key->distance_cost;
                                        }
                                    }
                                    else
                                        $typp1=0.00;

                                    $typs['name']=$typ1->name;
                                   // $typs['icon']=$typ1->icon;
                                    $typs['price']=$typp1;

	                                    array_push($typi, $typs);
									}   $bill['type']=$typi;
									$rserv = RequestServices::where('request_id',$request_id)->get();
									$typs=array();
		                            foreach ($rserv as $typ) {
		                                    $typ1 = ProviderType::where('id',$typ->type)->first();
		                                    array_push($typs, $typ1->name);
		                            }
		                            if ($request->is_paid == 3) {
										/*$friend_id = Friend::where('user_id',$request->owner_id)
													->where('status',1)
													->first();
										$owner_friend_id = $friend_id->friend_id;
										send_email($owner_friend_id, 'owner', $email_data, $subject, 'invoice');*/
										$push_id = 8;
									}
									else{
										$push_id=9;
									}

									$response_array = array(
										'success' => true,
										'request_id' => $request_id,
										'status' => $request->status,
										'confirmed_walker' => $request->confirmed_walker,
										'is_walker_started' => $request->is_walker_started,
										'is_walker_arrived' => $request->is_walker_arrived,
										'is_walk_started' => $request->is_started,
										'is_completed' => $request->is_completed,
										'is_walker_rated' => $request->is_walker_rated,
										'walker' => $walker_data,
										
										'bill' => $bill,
										
									);
									$var = Keywords::where('id', 4)->first();
									//$title = 'Your ' . $var->keyword . ' is completed';


									$walk_completed = Settings::where('key', 'walk_completed')->first();
									$walk_completed = $walk_completed->value;
									$title = $walk_completed; 

									$message = $response_array;

									
									send_notifications($request->owner_id, "owner", $title, $message);
										
									

									if ($request->payment_mode == 3) {
									
										$friend_id = Friend::where('user_id',$request->owner_id)
													->where('status',1)
													->first();
										$owner_friend_id = $friend_id->friend_id;

										$title = 'Your friend' . $var->keyword . ' is completed';
									
										
									$msg = array(
										'success' => true,
										'actual_total' => $bill['actual_total'],
										'push_id'=>9
									);	
										send_notifications($owner_friend_id, "owner", $title, $msg);
									}
									

									// Send SMS 
									$owner = Owner::find($request->owner_id);
									$settings = Settings::where('key', 'sms_when_provider_completes_job')->first();
									$pattern = $settings->value;
									$pattern = str_replace('%user%', $owner->first_name . " " . $owner->last_name, $pattern);
									$pattern = str_replace('%driver%', $walker->first_name . " " . $walker->last_name, $pattern);
									$pattern = str_replace('%driver_mobile%', $walker->phone, $pattern);
									$pattern = str_replace('%amount%', $request->total, $pattern);
									//for testing
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

									$request_services=RequestServices::where('request_id',$request->id)->first();
		
									$locations = WalkLocation::where('request_id',$request->id)
														->orderBy('id')
														->get();
									$start = WalkLocation::where('request_id',$request->id)
														->orderBy('id')
														->first();
									$end = WalkLocation::where('request_id',$request->id)
														->orderBy('id','desc')
														->first();

									$map = "https://maps-api-ssl.google.com/maps/api/staticmap?size=249x249&style=feature:landscape|visibility:off&style=feature:poi|visibility:off&style=feature:transit|visibility:off&style=feature:road.highway|element:geometry|lightness:39&style=feature:road.local|element:geometry|gamma:1.45&style=feature:road|element:labels|gamma:1.22&style=feature:administrative|visibility:off&style=feature:administrative.locality|visibility:on&style=feature:landscape.natural|visibility:on&scale=2&markers=shadow:false|scale:2|icon:http://d1a3f4spazzrp4.cloudfront.net/receipt-new/marker-start@2x.png|$start->latitude,$start->longitude&markers=shadow:false|scale:2|icon:http://d1a3f4spazzrp4.cloudfront.net/receipt-new/marker-finish@2x.png|$end->latitude,$end->longitude&path=color:0x2dbae4ff|weight:4";

									foreach ($locations as $location) {
									$map .= "|$location->latitude,$location->longitude";
									}

									$start_location = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?latlng=$start->latitude,$start->longitude"),TRUE);
									$start_address = $start_location['results'][0]['formatted_address'];
									
									$end_location = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?latlng=$end->latitude,$end->longitude"),TRUE);
									$end_address = $end_location['results'][0]['formatted_address'];

									$email_data['start_location'] = $start_location;
									$email_data['end_location'] = $end_location;

									$email_data['map'] = $map;

									//send email to owner
									$subject = "Invoice Generated";
									send_email($request->owner_id, 'owner', $email_data, $subject, 'invoice');

									// send email to friend also
									if ($request->payment_mode == 3) {
										$friend_id = Friend::where('user_id',$request->owner_id)
													->where('status',1)
													->first();
										$owner_friend_id = $friend_id->friend_id;
										send_email($owner_friend_id, 'owner', $email_data, $subject, 'invoice');
									}
									
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
									}else{
										// send email
										$pattern = "Payment Failed for the request id ".$request->id.".";

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
										
										'bill' => $bill,
									);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Service not yet started', 'error_code' => 413);
									$response_code = 200;
								}
							} else {
								$var = Keywords::where('id', 1)->first();
								$response_array = array('success' => false, 'error' => 'Service ID doesnot matches with ' . $var->keyword . ' ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Service ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$var = Keywords::where('id', 1)->first();
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

	//Payment before starting
	public function pre_payment()
	{
		if (Request::isMethod('post')) {
			$request_id = Input::get('request_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');
			$time = Input::get('time');

			$validator = Validator::make(
				array(
					'request_id' => $request_id,
					'token' => $token,
					'walker_id' => $walker_id,
					'time' => $time,
				),
				array(
					'request_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
					'time' => 'required',
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($request = Requests::find($request_id)) {
							if ($request->confirmed_walker == $walker_id) {

								if (!$walker_data->type) {
									$settings = Settings::where('key', 'price_per_unit_distance')->first();
									$price_per_unit_distance = $settings->value;
									$settings = Settings::where('key', 'price_per_unit_time')->first();
									$price_per_unit_time = $settings->value;
									$settings = Settings::where('key', 'base_price')->first();
									$base_price = $settings->value;
								} else {
									$provider_type = ProviderServices::find($walker_data->type);
									$base_price = $provider_type->base_price;
									$price_per_unit_distance = $provider_type->price_per_unit_distance;
									$price_per_unit_time = $provider_type->price_per_unit_time;
								}

								$settings = Settings::where('key', 'default_charging_method_for_users')->first();
								$pricing_type = $settings->value;
								$settings = Settings::where('key', 'default_distance_unit')->first();
								$unit = $settings->value;
								if ($pricing_type == 1) {
									$distance_cost = $price_per_unit_distance;
									$time_cost = $price_per_unit_time;
									$total = $base_price + $distance_cost + $time_cost;
								} else {
									$distance_cost = 0;
									$time_cost = 0;
									$total = $base_price;
								}

								Log::info('req');
								$request_service = RequestServices::find($request_id);
								$request_service->base_price = $base_price;
								$request_service->distance_cost = $distance_cost;
								$request_service->time_cost = $time_cost;
								$request_service->total = $total;
								$request_service->save();
								$request->distance = $distance_cost;
								$request->time = $time_cost;
								$request->total = $total;

								Log::info('in ');

								// charge client
								$ledger = Ledger::where('owner_id', $request->owner_id)->first();

								if ($ledger) {
									$balance = $ledger->amount_earned - $ledger->amount_spent;
									if ($balance > 0) {
										if ($total > $balance) {
											$ledger_temp = Ledger::find($ledger->id);
											$ledger_temp->amount_spent = $ledger_temp->amount_spent + $balance;
											$ledger_temp->save();
											$total = $total - $balance;
										} else {
											$ledger_temp = Ledger::find($ledger->id);
											$ledger_temp->amount_spent = $ledger_temp->amount_spent + $total;
											$ledger_temp->save();
											$total = 0;
										}

									}
								}

								Log::info('out');
								if ($total == 0) {
									$request->is_paid = 1;
								} else {

									$payment_data = Payment::where('owner_id', $request->owner_id)->where('is_default', 1)->first();
									if (!$payment_data)
										$payment_data = Payment::where('owner_id', $request->owner_id)->first();

									if ($payment_data) {
										$customer_id = $payment_data->customer_id;
										try {
											if (Config::get('app.default_payment') == 'stripe') {
												Stripe::setApiKey(Config::get('app.stripe_secret_key'));

												try {
													Stripe_Charge::create(array(

															"amount" => floor($total)*100,

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

												$setting = Settings::find(37);
												$settng1 = Settings::where('key', 'service_fee')->first();
												if ($setting->value == 2 && $walker_data->merchant_id != NULL) {
													// dd($amount$request->transfer_amount);
													$transfer = Stripe_Transfer::create(array(
															"amount" => ($total - $settng1->value) * 100, // amount in cents
															"currency" => "usd",
															"recipient" => $walker_data->merchant_id)
													);
												}
											} else {
												$amount = $total;
												Braintree_Configuration::environment(Config::get('app.braintree_environment'));
												Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
												Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
												Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
												$card_id = $payment_data->card_token;
												$setting = Settings::find(37);
												$settng1 = Settings::where('key', 'service_fee')->first();
												if ($setting->value == 2 && $walker_data->merchant_id != NULL) {
													// escrow
													$result = Braintree_Transaction::sale(array(
														'amount' => $amount,
														'paymentMethodToken' => $card_id
													));
												} else {
													$result = Braintree_Transaction::sale(array(
														'amount' => $amount,
														'paymentMethodToken' => $card_id
													));
												}
												Log::info('result = ' . print_r($result, true));
												if ($result->success) {
													$request->is_paid = 1;
												} else {
													$request->is_paid = 0;
												}
											}

										} catch (Exception $e) {
											$response_array = array('success' => false, 'error' => $e, 'error_code' => 405);
											$response_code = 200;
											$response = Response::json($response_array, $response_code);
											return $response;
										}

									}


								}

								$request->card_payment = $total;
								$request->ledger_payment = $request->total - $total;

								$request->save();
								Log::info('Request = ' . print_r($request, true));

								if ($request->is_paid == 1) {
									$owner = Owner::find($request->owner_id);
									$settings = Settings::where('key', 'sms_request_unanswered')->first();
									$pattern = $settings->value;
									$pattern = str_replace('%user%', $owner->first_name . " " . $owner->last_name, $pattern);
									$pattern = str_replace('%id%', $request->id, $pattern);
									$pattern = str_replace('%user_mobile%', $owner->phone, $pattern);
									sms_notification(1, 'admin', $pattern);
								}

								$walker = Walker::find($walker_id);
								$walker->is_available = 1;
								$walker->save();

								// Send Notification
								$walker = Walker::find($request->confirmed_walker);
								$walker_data = array();
								$walker_data['first_name'] = $walker->first_name;
								$walker_data['last_name'] = $walker->last_name;
								$walker_data['phone'] = $walker->phone;
								$walker_data['bio'] = $walker->bio;
								$walker_data['picture'] = $walker->picture;
								$walker_data['type'] = $walker->type;
								$walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ?: 0;
								$walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count();

								$settings = Settings::where('key', 'default_distance_unit')->first();
								$unit = $settings->value;
								if ($unit == 0) {
									$unit_set = 'kms';
								} elseif ($unit == 1) {
									$unit_set = 'miles';
								}
								$bill = array();
								if ($request->is_paid == 1) {
									$bill['distance'] = (string)convert($request->distance, $unit);
									$bill['unit'] = $unit_set;
									$bill['time'] = $request->time;
									$bill['base_price'] = currency_converted($base_price);
									$bill['distance_cost'] = currency_converted($distance_cost);
									$bill['time_cost'] = currency_converted($time_cost);
									$bill['total'] = currency_converted($request->total);
									$bill['is_paid'] = $request->is_paid;
								}

								$response_array = array(
									'success' => true,
									'request_id' => $request_id,
									'status' => $request->status,
									'confirmed_walker' => $request->confirmed_walker,
									'walker' => $walker_data,
									'bill' => $bill,
								);
								$title = "Payment Has Made";

								$message = $response_array;

								send_notifications($walker->id, "walker", $title, $message);


								$settings = Settings::where('key', 'email_notification')->first();
								$condition = $settings->value;
								if ($condition == 1) {
									$settings = Settings::where('key', 'payment_made_client')->first();
									$pattern = $settings->value;

									$pattern = str_replace('%id%', $request->id, $pattern);
									$pattern = str_replace('%amount%', $request->total, $pattern);

									$subject = "Payment Charged";
									email_notification($walker->id, 'walker', $pattern, $subject);
								}

								// Send SMS
								$owner = Owner::find($request->owner_id);
								$settings = Settings::where('key', 'sms_when_provider_completes_job')->first();
								$pattern = $settings->value;
								$pattern = str_replace('%user%', $owner->first_name . " " . $owner->last_name, $pattern);
								$pattern = str_replace('%driver%', $walker->first_name . " " . $walker->last_name, $pattern);
								$pattern = str_replace('%driver_mobile%', $walker->phone, $pattern);
								$pattern = str_replace('%damount%', $request->total, $pattern);
								sms_notification($request->owner_id, 'owner', $pattern);

								$email_data = array();

								$email_data['name'] = $owner->first_name;
								$email_data['emailType'] = 'user';
								$email_data['base_price'] = $bill['base_price'];
								$email_data['distance'] = $bill['distance'];
								$email_data['time'] = $bill['time'];
								$email_data['unit'] = $bill['unit'];
								$email_data['total'] = $bill['total'];

								if ($bill['payment_mode']) {
									$email_data['payment_mode'] = $bill['payment_mode'];
								} else {
									$email_data['payment_mode'] = '---';
								}

								$subject = "Invoice Generated";
								send_email($request->owner_id, 'owner', $email_data, $subject, 'invoice');

								$subject = "Invoice Generated";
								$email_data['emailType'] = 'walker';
								send_email($request->confirmed_walker, 'walker', $email_data, $subject, 'invoice');

								if ($request->is_paid == 1) {
									// send email
									$settings = Settings::where('key', 'email_payment_charged')->first();
									$pattern = $settings->value;

									$pattern = str_replace('%id%', $request->id, $pattern);
									$pattern = str_replace('%url%', web_url() . "/admin/request/" . $request->id, $pattern);

									$subject = "Payment Charged";
									email_notification(1, 'admin', $pattern, $subject);

								}

								$response_array = array(
									'success' => true,
									'base_fare' => currency_converted($base_price),
									'distance_cost' => currency_converted($distance_cost),
									'time_cost' => currency_converted($time_cost),
									'total' => currency_converted($total),
									'is_paid' => $request->is_paid,
								);
								$response_code = 200;

							} else {
								$var = Keywords::where('id', 1)->first();
								$response_array = array('success' => false, 'error' => 'Service ID doesnot matches with ' . $var->keyword . ' ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Service ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$var = Keywords::where('id', 1)->first();
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

// Add Location Data
	public function walk_location()
	{
		if (Request::isMethod('post')) {
			$request_id = Input::get('request_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');
			$latitude = Input::get('latitude');
			$longitude = Input::get('longitude');

			$validator = Validator::make(
				array(
					'request_id' => $request_id,
					'token' => $token,
					'walker_id' => $walker_id,
					'latitude' => $latitude,
					'longitude' => $longitude,
				),
				array(
					'request_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
					'latitude' => 'required',
					'longitude' => 'required',
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations
						if ($request = Requests::find($request_id)) {
							if ($request->confirmed_walker == $walker_id) {

								if ($request->is_started == 1) {

									$walk_location_last = WalkLocation::where('request_id', $request_id)->orderBy('created_at', 'desc')->first();

									if ($walk_location_last) {
										$distance_old = $walk_location_last->distance;
										$distance_new = distanceGeoPoints($walk_location_last->latitude, $walk_location_last->longitude, $latitude, $longitude);
										$distance = $distance_old + $distance_new;
										$settings = Settings::where('key', 'default_distance_unit')->first();
										$unit = $settings->value;
										if ($unit == 0) {
											$unit_set = 'kms';
										} elseif ($unit == 1) {
											$unit_set = 'miles';
										}
										$distancecon = convert($distance, $unit);
									} else {
										$distance = 0;
									}

									$walk_location = new WalkLocation;
									$walk_location->request_id = $request_id;
									$walk_location->latitude = $latitude;
									$walk_location->longitude = $longitude;
									$walk_location->distance = $distance;
									$walk_location->save();

									$loc1 = WalkLocation::where('request_id', $request->id)->first();
									$loc2 = WalkLocation::where('request_id', $request->id)->orderBy('id', 'desc')->first();
									if ($loc1) {
										$time1 = strtotime($loc2->created_at);
										$time2 = strtotime($loc1->created_at);
										$difference = intval(($time1 - $time2) / 60);
									} else {
										$difference = 0;
									}

									$response_array = array('success' => true, 'distance' => $distancecon, 'unit' => $unit_set, 'time' => $difference);
									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Service not yet started', 'error_code' => 414);
									$response_code = 200;
								}
							} else {
								$var = Keywords::where('id', 1)->first();
								$response_array = array('success' => false, 'error' => 'Request ID doesnot matches with ' . $var->keyword . ' ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Service ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$var = Keywords::where('id', 1)->first();
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


// Add Location Data
	public function check_state()
	{

		$walker_id = Input::get('id');
		$token = Input::get('token');

		$validator = Validator::make(
			array(
				'walker_id' => $walker_id,
				'token' => $token,
			),
			array(
				'walker_id' => 'required|integer',
				'token' => 'required',
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
			$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry) || $is_admin) {

					$response_array = array('success' => true, 'is_active' => $walker_data->is_active,'is_available'=>$walker_data->is_available);
					$response_code = 200;
				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$var = Keywords::where('id', 1)->first();
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

	// Add Location Data
	public function toggle_state()
	{

		$walker_id = Input::get('id');
		$token = Input::get('token');

		$validator = Validator::make(
			array(
				'walker_id' => $walker_id,
				'token' => $token,
			),
			array(
				'walker_id' => 'required|integer',
				'token' => 'required',
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
			$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry) || $is_admin) {
					$walker = Walker::find($walker_id);
					$walker->is_active = ($walker->is_active + 1) % 2;
					$walker->is_available = $walker->is_active;
					$walker->save();
					$response_array = array('success' => true, 'is_active' => $walker->is_active,'is_available'=>$walker->is_available);
					$response_code = 200;
				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$var = Keywords::where('id', 1)->first();
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


	// Update Profile

	public function update_profile()
	{

		$token = Input::get('token');
		$walker_id = Input::get('id');
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
				'walker_id' => $walker_id,
				'picture' => $picture,
				'zipcode' => $zipcode
			),
			array(
				'token' => 'required',
				'walker_id' => 'required|integer',
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
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry) || $is_admin) {

					$walker = Walker::find($walker_id);
					if ($first_name) {
						$walker->first_name = $first_name;
					}
					if ($last_name) {
						$walker->last_name = $last_name;
					}
					if ($phone) {
						$walker->phone = $phone;
					}
					if ($bio) {
						$walker->bio = $bio;
					}
					if ($address) {
						$walker->address = $address;
					}
					if ($state) {
						$walker->state = $state;
					}
					if ($country) {
						$walker->country = $country;
					}
					if ($zipcode) {
						$walker->zipcode = $zipcode;
					}
					if ($password) {
						$walker->password = Hash::make($password);
					}

					if (Input::hasFile('picture')) {
						if ($walker->picture != "") {
							$path = $walker->picture;
							Log::info($path);
							$filename = basename($path);
							Log::info($filename);
							if(file_exists($path)){
							unlink(public_path() . "/uploads/" . $filename);
							}
						}
						// upload image
						$file_name = time();
						$file_name .= rand();
						$file_name = sha1($file_name);

						$ext = Input::file('picture')->getClientOriginalExtension();
						Log::info('ext = ' . print_r($ext, true));
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

						$walker->picture = $s3_url;
					}
					If (Input::has('timezone')) {
						$walker->timezone = Input::get('timezone');
					}

					$walker->save();

					$response_array = array(
						'success' => true,
						'id' => $walker->id,
						'first_name' => $walker->first_name,
						'last_name' => $walker->last_name,
						'phone' => $walker->phone,
						'email' => $walker->email,
						'picture' => $walker->picture,
						'bio' => $walker->bio,
						'address' => $walker->address,
						'state' => $walker->state,
						'country' => $walker->country,
						'zipcode' => $walker->zipcode,
						'login_by' => $walker->login_by,
						'social_unique_id' => $walker->social_unique_id,
						'device_token' => $walker->device_token,
						'device_type' => $walker->device_type,
						'token' => $walker->token,
						'timezone' => $walker->timezone,
						'type' => $walker->type,
					);
					$response_code = 200;


				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$var = Keywords::where('id', 1)->first();
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
		$walker_id = Input::get('id');
		$token = Input::get('token');

		$validator = Validator::make(
			array(
				'walker_id' => $walker_id,
				'token' => $token,
			),
			array(
				'walker_id' => 'required|integer',
				'token' => 'required',
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
			$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry) || $is_admin) {

					$request_data = DB::table('request')
						->where('request.confirmed_walker', $walker_id)
						->where('request.is_completed', 1)
						->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
						->leftJoin('request_services', 'request_services.request_id', '=', 'request.id')
						->select('request.*', 'request.request_start_time', 'request.transfer_amount', 'owner.first_name', 'owner.last_name', 'owner.phone', 'owner.email', 'owner.picture', 'owner.bio', 'request.distance', 'request.time','request.promo_code', 'request_services.base_price', 'request_services.distance_cost', 'request_services.time_cost', 'request.total')
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
					$walker = Walker::where('id', $walker_id)->first();
					foreach ($request_data as $data) {

					
					$is_multiple_service = Settings::where('key', 'allow_multiple_service')->first();
					if($is_multiple_service->value == 0){


						$requestserv = RequestServices::where('request_id', $data->id)->first();
						$request['id'] = $data->id;
						$request['date'] = $data->request_start_time;
						$request['distance'] = (string)$data->distance;
						$request['unit'] = $unit_set;
						$request['time'] = $data->time;
						$currency = Keywords::where('alias', 'Currency')->first();
						$request['currency'] = $currency->keyword;
						if ($requestserv->base_price != 0) {
							$request['base_price'] = currency_converted($requestserv->base_price);
							$request['distance_cost'] = currency_converted($requestserv->distance_cost);
							$request['time_cost'] = currency_converted($requestserv->time_cost);
							$adding_three = $request['base_price'] + $request['distance_cost'] + $request['time_cost']; 
						} else {
							$setbase_price = Settings::where('key', 'base_price')->first();
							$request['base_price'] = currency_converted($setbase_price->value);
							$setdistance_price = Settings::where('key', 'price_per_unit_distance')->first();
							$request['distance_cost'] = currency_converted($setdistance_price->value);
							$settime_price = Settings::where('key', 'price_per_unit_time')->first();
							$request['time_cost'] = currency_converted($settime_price->value);$adding_three = $request['base_price'] + $request['distance_cost'] + $request['time_cost']; 
							$adding_three = $request['base_price'] + $request['distance_cost'] + $request['time_cost']; 
						}

						$admins = Admin::first();
						$request['walker']['email'] = $walker->email;
						$request['admin']['email'] = $admins->username;
						if ($data->transfer_amount != 0) {
							$request['walker']['amount'] = currency_converted($data->total - $data->transfer_amount);
							$request['admin']['amount'] = currency_converted($data->transfer_amount);
						} else {
							$request['walker']['amount'] = currency_converted($data->transfer_amount);
							$request['admin']['amount'] = currency_converted($data->total - $data->transfer_amount);
						}
						$discount = 0;
						

						if($data->promo_code !== NULL){
							if ($data->promo_code !== NULL) {
								$promo_code = PromoCodes::where('id', $data->promo_code)->first();
								if($promo_code){
									$promo=1;
									$promo_value = $promo_code->value;
									$promo_type = $promo_code->type;
									if ($promo_type == 1) {
										// Percent Discount
										//$discount = $data->total * $promo_value / 100;
										$discount = $adding_three * $promo_value / 100;
										$request['total'] = currency_converted($adding_three - $discount);
									} elseif ($promo_type == 2) {
										// Absolute Discount
										$discount = $promo_value;
										$request['total'] = currency_converted($adding_three - $discount);
									}
									$actual_total_promo = $discount + $data->total; 
								}else{
									$promo =0;
									$discount =0;
									$promo =0;
									$actual_total_promo = $data->total;

								}
							}
						}else{

							$promo =0;
							$discount =0;
							$promo =0;
							$actual_total_promo = $data->total;
							$request['total'] = currency_converted($data->total + $data->ledger_payment + $discount);
						}

					$request['promo']=$promo;
					$request["promo_discount"]=currency_converted($discount);
					$request["actual_total_promo"]=$actual_total_promo;
					$request['promo_discount'] = currency_converted($discount);
					$request['actual_total']= currency_converted($adding_three);


						
						$service_price = Settings::where('key', 'service_fee')->first();
						$service_fee= currency_converted($service_price->value);
						
						$total = $data->total;
						$total_invoice_admin =($total * $service_fee) /100 ;
						

						$request['admin']['amount']= ($total * $service_fee) /100;
						$request['walker']['amount']= $total - $total_invoice_admin;

						

					}else{

						$request['id'] = $data->id;
						$request['date'] = $data->request_start_time;
						$request['distance'] = (string)$data->distance;
						$request['unit'] = $unit_set;
						$request['time'] = $data->time;
						$currency = Keywords::where('alias', 'Currency')->first();
						$request['currency'] = $currency->keyword;
						
						$rserv = RequestServices::where('request_id',$data->id)->get();
						$typs=array();
                        $typi=array();
                        $typp=array();
						$total_price = 0;
                        foreach ($rserv as $typ) {
                          $typ1=ProviderType::where('id',$typ->type)->first();  
                          $typ_price=ProviderServices::where('provider_id',$data->confirmed_walker)->where('type',$typ->type)->first();

                            if($typ_price->base_price>0){
                            	$typp1=0.00;
                            	$typp1=$typ_price->base_price;
                            }
                            elseif($typ_price->price_per_unit_distance>0){
                            	$typp1=0.00;
                            	foreach ($rserv as $key) {
                            		$typp1 = $typp1 + $key->distance_cost;
                            	}
                            }
                            else{
                            	$typp1=0.00;
                            }
                           $typs['name']=$typ1->name;
                           $typs['price']=currency_converted($typp1);
						   $total_price = $total_price +$typp1;
                           array_push($typi, $typs);
                        }
                        $request['type']=$typi;
                            
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
						$request['owner']['first_name'] = $data->first_name;
						$request['owner']['last_name'] = $data->last_name;
						$request['owner']['phone'] = $data->phone;
						$request['owner']['email'] = $data->email;
						$request['owner']['picture'] = $data->picture;
						$request['owner']['bio'] = $data->bio;
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
					$var = Keywords::where('id', 1)->first();
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

	public function provider_services_update()
	{
		$token = Input::get('token');
		$walker_id = Input::get('id');

		$validator = Validator::make(
			array(
				'token' => $token,
				'walker_id' => $walker_id,
			),
			array(
				'token' => 'required',
				'walker_id' => 'required|integer',
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
			$response_code = 200;
			Log::info('validation error =' . print_r($response_array, true));
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry) || $is_admin) {
					foreach (Input::get('service') as $key) {
						$serv = ProviderType::where('id', $key)->first();
						$pserv[] = $serv->name;
					}
					foreach (Input::get('service') as $ke) {
						$proviserv = ProviderServices::where('provider_id', $walker_id)->first();
						if ($proviserv != NULL) {
							DB::delete("delete from walker_services where provider_id = '" . $walker_id . "';");
						}
					}
					$base_price = Input::get('service_base_price');
					$service_price_distance = Input::get('service_price_distance');
					$service_price_time = Input::get('service_price_time');
					foreach (Input::get('service') as $key) {
						$prserv = new ProviderServices;
						$prserv->provider_id = $walker_id;
						$prserv->type = $key;
						$prserv->base_price = $base_price[$key - 1];
						$prserv->price_per_unit_distance = $service_price_distance[$key - 1];
						$prserv->price_per_unit_time = $service_price_time[$key - 1];
						$prserv->save();
					}
					$response_array = array(
						'success' => true,
					);
					$response_code = 200;
					Log::info('success = ' . print_r($response_array, true));
				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$var = Keywords::where('id', 1)->first();
					$response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);
				} else {
					$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);
				}
				$response_code = 200;
			}
		}
		$response = Response::json($response_array, $response_code);
		Log::info('repsonse final = ' . print_r($response, true));
		return $response;
	}

	public function services_details()
	{
		$walker_id = Input::get('id');
		$token = Input::get('token');

		$validator = Validator::make(
			array(
				'walker_id' => $walker_id,
				'token' => $token,
			),
			array(
				'walker_id' => 'required|integer',
				'token' => 'required',
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
			$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				// check for token validity
				if (is_token_active($walker_data->token_expiry) || $is_admin) {
					$provserv = ProviderServices::where('provider_id', $walker_id)->get();
					foreach ($provserv as $key) {
						$type = ProviderType::where('id', $key->type)->first();
						$serv_name[] = $type->name;
						$serv_base_price[] = $key->base_price;
						$serv_per_distance[] = $key->price_per_unit_distance;
						$serv_per_time[] = $key->price_per_unit_time;
					}
					$response_array = array(
						'success' => true,
						'serv_name' => $serv_name,
						'serv_base_price' => $serv_base_price,
						'serv_per_distance' => $serv_per_distance,
						'serv_per_time' => $serv_per_time
					);
					$response_code = 200;

				} else {
					$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
					$response_code = 200;
				}
			} else {
				if ($is_admin) {
					$var = Keywords::where('id', 1)->first();
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

	public function panic()
	{
		$token = Input::get('token');
		$walker_id = Input::get('id');
		$is_admin = $this->isAdmin($token);
		if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
			// check for token validity
			if (is_token_active($walker_data->token_expiry) || $is_admin) {
				$lat = Input::get('latitude');
				$long = Input::get('longitude');
				$location = 'http://maps.google.com/maps?z=12&t=m&q=loc:lat+long';
				$location = str_replace('lat', $lat, $location);
				$location = str_replace('long', $long, $location);

				$var = Keywords::where('id', 1)->first();

				$email_body = '' . $var->keyword . ' id = ' . $walker_id . '. And my current location is:  <br/>' . $location;
				$subject = 'Panic Alert';
				email_notification($walker_id, 'admin', $email_body, $subject);
				$response_array = array('success' => true, 'is_active' => $walker_data->is_active);
				$response_code = 200;
			} else {
				$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
				$response_code = 200;
			}
		} else {
			if ($is_admin) {
				$var = Keywords::where('id', 1)->first();
				$response_array = array('success' => false, 'error' => '' . $var->keyword . ' ID not Found', 'error_code' => 410);

			} else {
				$response_array = array('success' => false, 'error' => 'Not a valid token', 'error_code' => 406);

			}
			$response_code = 200;
		}
	}

	public function check_banking()
	{
		$token = Input::get('token');
		$walker_id = Input::get('id');
		$is_admin = $this->isAdmin($token);
		if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
			// check for token validity
			if (is_token_active($walker_data->token_expiry) || $is_admin) {
				// do
				$default_banking = Config::get('app.default_payment');
				$resp = array();
				$resp['default_banking'] = $default_banking;
				$walker = Walker::where('id', $walker_id)->first();
				if ($walker->merchant_id != NULL) {
					$resp['walker']['merchant_id'] = $walker->merchant_id;
				}
				$response_array = array('success' => true, 'details' => $resp);
				$response_code = 200;
			}
		}
		$response = Response::json($response_array, $response_code);
		return $response;
	}

	public function request_walk_reached()
	{
		if (Request::isMethod('post')) {
			$request_id = Input::get('request_id');
			$token = Input::get('token');
			$walker_id = Input::get('id');
			$latitude = Input::get('latitude');
			$longitude = Input::get('longitude');
			$distance = Input::get('distance');
			$time = Input::get('time');

			Log::info('distance input = ' . print_r($distance, true));
			Log::info('time input = ' . print_r($time, true));

			$validator = Validator::make(
				array(
					'request_id' => $request_id,
					'token' => $token,
					'walker_id' => $walker_id,
					'latitude' => $latitude,
					'longitude' => $longitude,
					'distance' => $distance,
					'time' => $time,
				),
				array(
					'request_id' => 'required|integer',
					'token' => 'required',
					'walker_id' => 'required|integer',
					'latitude' => 'required',
					'longitude' => 'required',
					'distance' => 'required',
					'time' => 'required',
				)
			);

			if ($validator->fails()) {
				$error_messages = $validator->messages()->all();
				$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
				$response_code = 200;
			} else {
				$is_admin = $this->isAdmin($token);
				if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
					// check for token validity
					if (is_token_active($walker_data->token_expiry) || $is_admin) {
						// Do necessary operations 

						if ($request = Requests::find($request_id)) {
							if ($request->confirmed_walker == $walker_id) {

								if ($request->is_started == 1) {

									$settings = Settings::where('key', 'default_charging_method_for_users')->first();
									$pricing_type = $settings->value;
									$settings = Settings::where('key', 'default_distance_unit')->first();
									$unit = $settings->value;

									Log::info('distance = ' . print_r($distance, true));

									$reqserv = RequestServices::where('request_id',$request_id)->get();
									$actual_total = 0;
									$price_per_unit_distance = 0;
									$price_per_unit_time = 0;
									$base_price = 0;

									foreach ($reqserv as $rse) 
									{
										Log::info('type = '.print_r($rse->type,true));

										$protype = ProviderType::where('id',$rse->type)->first();
										$pt = ProviderServices::where('provider_id',$walker_id)->where('type',$rse->type)->first();
										
											if($pt->base_price==0){
												$setbase_price = Settings::where('key','base_price')->first();
												$base_price = $setbase_price->value;
												$rse->base_price = $base_price;
											}else{
												$base_price = $pt->base_price;
												$rse->base_price = $base_price;
											}

											$is_multiple_service = Settings::where('key', 'allow_multiple_service')->first();
											if($is_multiple_service->value == 0){

											if($pt->price_per_unit_distance==0){
												$setdistance_price = Settings::where('key','price_per_unit_distance')->first();
												$price_per_unit_distance = $setdistance_price->value*$distance;
												$rse->distance_cost = $price_per_unit_distance;
											}else{
												$price_per_unit_distance = $pt->price_per_unit_distance*$distance;
												$rse->distance_cost = $price_per_unit_distance;
											}

											if($pt->price_per_unit_time ==0){
												$settime_price = Settings::where('key','price_per_unit_time')->first();
												$price_per_unit_time = $settime_price->value*$time;
												$rse->time_cost = $price_per_unit_time;
											}else{
												$price_per_unit_time = $pt->price_per_unit_time*$time;
												$rse->time_cost = $price_per_unit_time;
											}
										}	
								
										Log::info('total price = '.print_r($base_price+$price_per_unit_distance+$price_per_unit_time, true));
										$rse->total = $base_price+$price_per_unit_distance+$price_per_unit_time;
										$rse->save();
										$actual_total = $actual_total + $base_price+$price_per_unit_distance+$price_per_unit_time;
										Log::info('total_price = '.print_r($actual_total,true));
									}

									$rs = RequestServices::where('request_id', $request_id)->get();
									$total = 0;
									foreach ($rs as $key) {
										Log::info('total = ' . print_r($key->total, true));
										$total = $total + $key->total;
									}

									//$request->is_completed = 1;
									$request->is_walker_reached = 1;
									$request->distance = $distance;
									$request->time = $time;

									$request->security_key = NULL;
									$request->total = $total;
									
									$request->save();

									Log::info('final total = ' . print_r($total, true));

									$walker = Walker::find($walker_id);
									$walker->is_available = 1;
									$walker->save();

									Log::info('distance walk location = ' . print_r($distance, true));

									$walk_location = new WalkLocation;
									$walk_location->latitude = $latitude;
									$walk_location->longitude = $longitude;
									$walk_location->request_id = $request_id;
									$walk_location->distance = $distance;
									$walk_location->save();

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
									$walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ?: 0;
									$walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count();


									// $var = Keywords::where('id', 4)->first();
									// $title = 'Your ' . $var->keyword . ' is completed';

									// $message = $response_array;

									// send_notifications($request->owner_id, "owner", $title, $message);

									$response_array = array(
										'success' =>true,
										//'request' => $request,
										'request_id' => $request->id,
										'total'  => $request->total,
										//'walker' => $walker,
										//'walker_data' => $walker_data,
										);

									$response_code = 200;
								} else {
									$response_array = array('success' => false, 'error' => 'Service not yet started', 'error_code' => 413);
									$response_code = 200;
								}
							} else {
								$var = Keywords::where('id', 1)->first();
								$response_array = array('success' => false, 'error' => 'Service ID doesnot matches with ' . $var->keyword . ' ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Service ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}
					} else {
						$response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
						$response_code = 200;
					}
				} else {
					if ($is_admin) {
						$var = Keywords::where('id', 1)->first();
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

	public function request_walk_edit_amount() 
	{
		$token = Input::get('token');
		$walker_id = Input::get('id');
		$request_id =Input::get('request_id');
		$amount = Input::get('sub_total');
		// $status = Input::get('');

		$validator = Validator::make(
			array(
				'token'=>$token,
				'walker_id'=>$walker_id,
				'request_id'=>$request_id,
				'sub_total'=>$amount,
				),
			array(
				'token' => 'required',
				'walker_id' => 'required|integer',
				'request_id' =>'required|integer',
				'sub_total' => 'required'
				)
		);
		if ($validator->fails()) {
                $error_messages = $validator->messages()->all();
                $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
                $response_code = 200;
            } else {
                $is_admin = $this->isAdmin($token);
                if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin))
                {
                    if (is_token_active($walker_data->token_expiry) || $is_admin)
                    {
                    	if ($request = Requests::find($request_id)) 
                    	{
							if ($request->confirmed_walker == $walker_id) 
							{
								if ($request->is_started == 1) 
								{
									$request->sub_total = $amount;
									$request->is_completed = 1 ;

									if($request->save()) 
									{
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
                                        $walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ?: 0;
                                        $walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count();

                                        $requestserv = RequestServices::where('request_id', $request->id)->first();

                                        $bill = array();

                                        $currency_selected = Keywords::find(5);

                                        if ($request->is_completed == 1) 
                                        {
                                            $settings = Settings::where('key', 'default_distance_unit')->first();
                                            $unit = $settings->value;
                                            $bill['payment_mode'] = $request->payment_mode;
                                            $bill['distance'] = (string)$request->distance;
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
                                            //$bill['actual_total'] = currency_converted($actual_total);
                                            $bill['total'] = currency_converted($request->total);
                                            $bill['is_paid'] = $request->is_paid;
                                            //$bill['promo_discount'] = currency_converted($promo_discount);
                                        }


                                        $response_array = array(
                                            'success' 	 => true,
                                            'request_id' => $request_id,
                                            'total'      =>$request->total,
                                            'sub_total'  => $request->sub_total,
                                            'status'     => $request->status,
                                            'confirmed_walker'  => $request->confirmed_walker,
                                            'is_walker_started' => $request->is_walker_started,
                                            'is_walker_arrived' => $request->is_walker_arrived,
                                            'is_walk_started'   => $request->is_started,
                                            'is_completed'      => $request->is_completed,
                                            'is_walker_reached' => $request->is_walker_reached,
                                            'is_walker_rated'   => $request->is_walker_rated,
                                            'walker'            => $walker_data,
                                            'payment_mode' 		=> $request->payment_data,
                                            'bill'              => $bill,
                                        );

										$response_code = 200;
									} else {
										$response_array = array('success' => false, 'error' => 'Some error occured ...Try again later!!!!!!', 'error_code' => 413);
										$response_code = 200;
									}

								} else {
									$response_array = array('success' => false, 'error' => 'Service not yet started', 'error_code' => 413);
									$response_code = 200;
								}

							} else {
								$var = Keywords::where('id', 1)->first();
								$response_array = array('success' => false, 'error' => 'Service ID doesnot matches with ' . $var->keyword . ' ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Service ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}	
                      
                    } else {
                        $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                        $response_code = 200;
                    }

                } else {
                    if ($is_admin) {
                        $var = Keywords::where('id', 1)->first();
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

	public function request_walk_agree_amount() 
	{
		$token = Input::get('token');
		$walker_id = Input::get('id');
		$request_id =Input::get('request_id');
		//$sub_total = Input::get('sub_total');

		$validator = Validator::make(
			array(
				'token'=>$token,
				'walker_id'=>$walker_id,
				'request_id'=>$request_id,
				//'sub_total'=>$sub_total,
				),
			array(
				'token' => 'required',
				'walker_id' => 'required|integer',
				'request_id' =>'required|integer',
				//'sub_total' => 'required'
				)
		);
		if ($validator->fails()) {
                $error_messages = $validator->messages()->all();
                $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
                $response_code = 200;
            } else {
                $is_admin = $this->isAdmin($token);
                if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin))
                {
                    if (is_token_active($walker_data->token_expiry) || $is_admin)
                    {
                    	if ($request = Requests::find($request_id)) 
                    	{
							if ($request->confirmed_walker == $walker_id) 
							{
								if ($request->is_started == 1) 
								{
									$request->sub_total = 0;
									$request->is_completed = 1 ;

									if($request->save()) 
									{
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
                                        $walker_data['rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->avg('rating') ?: 0;
                                        $walker_data['num_rating'] = DB::table('review_walker')->where('walker_id', '=', $walker->id)->count();

                                        $requestserv = RequestServices::where('request_id', $request->id)->first();

                                        $bill = array();

                                        $currency_selected = Keywords::find(5);

                                        if ($request->is_completed == 1) 
                                        {
                                            $settings = Settings::where('key', 'default_distance_unit')->first();
                                            $unit = $settings->value;
                                            $bill['payment_mode'] = $request->payment_mode;
                                            $bill['distance'] = (string)$request->distance;
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
                                            //$bill['actual_total'] = currency_converted($actual_total);
                                            $bill['total'] = currency_converted($request->total);
                                            $bill['is_paid'] = $request->is_paid;
                                            //$bill['promo_discount'] = currency_converted($promo_discount);
                                        }


                                        $response_array = array(
                                            'success' => true,
                                            'request_id' => $request_id,
                                            'status' => $request->status,
                                            'confirmed_walker' => $request->confirmed_walker,
                                            'is_walker_started' => $request->is_walker_started,
                                            'is_walker_arrived' => $request->is_walker_arrived,
                                            'is_walk_started' => $request->is_started,
                                            'is_completed' => $request->is_completed,
                                            'is_walker_reached' => $request->is_walker_reached,
                                            'is_walker_rated' => $request->is_walker_rated,
                                            'walker' => $walker_data,
                                            'payment_mode' => $request->payment_data,
                                            'bill' => $bill,
                                        );

										$response_code = 200;
									} else {
										$response_array = array('success' => false, 'error' => 'Some error occured ...Try again later!!!!!!', 'error_code' => 413);
										$response_code = 200;
									}

								} else {
									$response_array = array('success' => false, 'error' => 'Service not yet started', 'error_code' => 413);
									$response_code = 200;
								}

							} else {
								$var = Keywords::where('id', 1)->first();
								$response_array = array('success' => false, 'error' => 'Service ID doesnot matches with ' . $var->keyword . ' ID', 'error_code' => 407);
								$response_code = 200;
							}
						} else {
							$response_array = array('success' => false, 'error' => 'Service ID Not Found', 'error_code' => 408);
							$response_code = 200;
						}	
                      
                    } else {
                        $response_array = array('success' => false, 'error' => 'Token Expired', 'error_code' => 405);
                        $response_code = 200;
                    }

                } else {
                    if ($is_admin) {
                        $var = Keywords::where('id', 1)->first();
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

	public function show_req_later()
	{

		$walker_id = Input::get('id');
		$token = Input::get('token');

		$validator = Validator::make(
			array(
				'walker_id' => $walker_id,
				'token' => $token,
			),
			array(
				'walker_id' => 'required|integer',
				'token' => 'required',
			)
		);

		if ($validator->fails()) {
			$error_messages = $validator->messages()->all();
			$response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
			$response_code = 200;
		} else {
			$is_admin = $this->isAdmin($token);
			if ($walker_data = $this->getWalkerData($walker_id, $token, $is_admin)) {
				$results = Requests::where('confirmed_walker',$walker_id)->where('later',1)->get();
                    $store =array();
                    
                    foreach ($results as $result) {
                        $data['id']=$result['id'];
                        $owner=$result['owner_id'];
                        $owner_data = Owner::where('id',$owner)->first();
                        $request_data['owner'] = array();
						
						$data['owner']['name'] = $owner_data->first_name . " " . $owner_data->last_name;
						$data['owner_']['picture'] = $owner_data->picture;
						$data['owner']['phone'] = $owner_data->phone;
						$data['owner']['address'] = $owner_data->address;
						$data['owner']['latitude'] = $owner_data->latitude;
						$data['owner']['longitude'] = $owner_data->longitude;

                        $data['request_start_time']=$result['request_start_time'];
                        $data['is_walker_started'] =$result['is_walker_started'];
                        $data['is_walker_arrived']= $result['is_walker_arrived'];
                        $data['is_started']=$result['is_started'];
                        $data['is_completed']=$result['is_completed'];
                        $data['distance']=$result['distance'];
                        $data['time']=$result['time'];
                        $data['total']=$result['total'];
                        $data['is_paid']=$result['is_paid'];
                        $data['is_cancelled']=$result['is_cancelled'];
                        $data['d_latitude']=$result['D_latitude'];
                        $data['d_longitude']=$result['D_longitude'];
                        $data['destination_address']=$result['destination_address'];
                        $data['source_address']=$result['source_address'];
                        array_push($store,$data);



                    }
                    
                    $response_array = array('success' => true, 'data' => $store);
                    $response_code = 200;
				
			} else {
				if ($is_admin) {
					$var = Keywords::where('id', 1)->first();
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