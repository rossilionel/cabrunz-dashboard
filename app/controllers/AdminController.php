<?php
 
class AdminController extends BaseController {

    public function __construct() {
        $this->beforeFilter(function () {
            if (!Auth::check()) {
                $url = URL::current();
                $routeName = Route::currentRouteName();
                Log::info('current route =' . print_r(Route::currentRouteName(), true));

                if ($routeName != "AdminLogin" && $routeName != 'admin') {
                    Session::put('pre_admin_login_url', $url);
                }
                return Redirect::to('/admin/login');
            }
        }, array('except' => array('login', 'verify', 'add', 'walker_xml')));
    }

    private function _braintreeConfigure() {
        Braintree_Configuration::environment(Config::get('app.braintree_environment'));
        Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
        Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
        Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
    }
    public function cheking(){

        $account_sid = 'ACc0ba18b8a25c9a75274b32092c4dc555'; 
        $auth_token = 'b18438112e0839de5e8a70a8c52f6066'; 
        $client = new Services_Twilio($account_sid, $auth_token);

        $client->account->messages->create(array( 
        'To' => "+447903285659", 
        'From' => "Cabrunz", 
        'Body' => "Test from Alphanumeric SenderID",

        ));
    }

    public function add() {
        $user = new Admin;
        $user->username = Input::get('username');
        $user->password = $user->password = Hash::make(Input::get('password'));
        $user->save();
    }

    private function get_timezone_offset($remote_tz, $origin_tz = null)
    {
        if ($origin_tz === null) {
            if (!is_string($origin_tz = date_default_timezone_get())) {
                return false; // A UTC timestamp was returned -- bail out!
            }
        }
        $origin_dtz = new DateTimeZone($origin_tz);
        $remote_dtz = new DateTimeZone($remote_tz);
        $origin_dt = new DateTime("now", $origin_dtz);
        $remote_dt = new DateTime("now", $remote_dtz);
        $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
        return $offset;
    }


    public function report() {
        $braintree_environment = Config::get('app.braintree_environment');
        $braintree_merchant_id = Config::get('app.braintree_merchant_id');
        $braintree_public_key = Config::get('app.braintree_public_key');
        $braintree_private_key = Config::get('app.braintree_private_key');
        $braintree_cse = Config::get('app.braintree_cse');
        $twillo_account_sid = Config::get('app.twillo_account_sid');
        $twillo_auth_token = Config::get('app.twillo_auth_token');
        $twillo_number = Config::get('app.twillo_number');
        $stripe_publishable_key = Config::get('app.stripe_publishable_key');
        $default_payment = Config::get('app.default_payment');
        $stripe_secret_key = Config::get('app.stripe_secret_key');
        $mail_driver = Config::get('mail.mail_driver');
        $email_name = Config::get('mail.from.name');
        $email_address = Config::get('mail.from.address');
        $mandrill_secret = Config::get('services.mandrill_secret');
        $install = array(
            'braintree_environment' => $braintree_environment,
            'braintree_merchant_id' => $braintree_merchant_id,
            'braintree_public_key' => $braintree_public_key,
            'braintree_private_key' => $braintree_private_key,
            'braintree_cse' => $braintree_cse,
            'twillo_account_sid' => $twillo_account_sid,
            'twillo_auth_token' => $twillo_auth_token,
            'twillo_number' => $twillo_number,
            'stripe_publishable_key' => $stripe_publishable_key,
            'stripe_secret_key' => $stripe_secret_key,
            'mail_driver' => $mail_driver,
            'email_address' => $email_address,
            'email_name' => $email_name,
            'mandrill_secret' => $mandrill_secret,
            'default_payment' => $default_payment);
        $start_date = Input::get('start_date');
        $end_date = Input::get('end_date');
        $submit = Input::get('submit');
        $walker_id = Input::get('walker_id');
        $owner_id = Input::get('owner_id');
        $status = Input::get('status');

        $start_time = date("Y-m-d H:i:s", strtotime($start_date));
        $end_time = date("Y-m-d H:i:s", strtotime($end_date));
        $start_date = date("Y-m-d", strtotime($start_date));
        $end_date = date("Y-m-d", strtotime($end_date));

        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
                ->orderBy('request.id', 'desc');
                

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('owner_id') && Input::get('owner_id') != 0) {
            $query = $query->where('request.owner_id', '=', $owner_id);
        }

        if (Input::get('status') && Input::get('status') != 0) {
            if ($status == 1) {
                $query = $query->where('request.is_completed', '=', 1);
            } else {
                $query = $query->where('request.is_cancelled', '=', 1);
            }
        } else {

            $query = $query->where(function ($que) {
                $que->where('request.is_completed', '=', 1)
                        ->orWhere('request.is_cancelled', '=', 1);
            });
        }

        $walks = $query->select('request.request_start_time', 'request.amount_by','walker_type.name as type', 'request.ledger_payment', 'request.card_payment', 'owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.payment_mode', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                , 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled');
        $walks = $walks->paginate(10);

        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
                ->orderBy('request.id', 'desc');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('owner_id') && Input::get('owner_id') != 0) {
            $query = $query->where('request.owner_id', '=', $owner_id);
        }

        $completed_rides = $query->where('request.is_completed', 1)->count();


        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
                ->orderBy('request.id', 'desc');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('owner_id') && Input::get('owner_id') != 0) {
            $query = $query->where('request.owner_id', '=', $owner_id);
        }
        $cancelled_rides = $query->where('request.is_cancelled', 1)->count();


        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
                ->orderBy('request.id', 'desc');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('owner_id') && Input::get('owner_id') != 0) {
            $query = $query->where('request.owner_id', '=', $owner_id);
        }
        $card_payment = $query->where('request.is_completed', 1)->sum('request.card_payment');


        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
                ->orderBy('request.id', 'desc');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('owner_id') && Input::get('owner_id') != 0) {
            $query = $query->where('request.owner_id', '=', $owner_id);
        }
        $credit_payment = $query->where('request.is_completed', 1)->sum('request.ledger_payment');
        
        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
                ->orderBy('request.id', 'desc');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('owner_id') && Input::get('owner_id') != 0) {
            $query = $query->where('request.owner_id', '=', $owner_id);
        }
        $cash_payment = $query->where('request.payment_mode', 1)->where('payment_mode' ,1)->sum('request.total');


        if (Input::get('submit') && Input::get('submit') == 'Download_Report') {

           header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=data.csv');
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array('ID', 'Date', 'Type of Service', 'Provider', 'Owner', 'Distance (Miles)', 'Time (Minutes)', 'Earning', 'Ledger Payment', 'Card Payment'));

            foreach ($walks as $request) {
                $query = RequestServices::where('request_id',$request->id)->first();
                $type_name = ProviderType::where('id',$query->type)->first();
                fputcsv($handle, array(
                    $request->id,
                    date('l, F d Y h:i A', strtotime($request->request_start_time)),
                    $type_name->name,
                    $request->walker_first_name . " " . $request->walker_last_name,
                    $request->owner_first_name . " " . $request->owner_last_name,
                    $request->distance,
                    $request->time,
                    $request->total,
                    $request->ledger_payment,
                    $request->card_payment,
                ));
            }

            fputcsv($handle, array());
            fputcsv($handle, array());
            fputcsv($handle, array('Total Trips', $completed_rides + $cancelled_rides));
            fputcsv($handle, array('Completed Trips', $completed_rides));
            fputcsv($handle, array('Cancelled Trips', $cancelled_rides));
            fputcsv($handle, array('Total Payments', $credit_payment + $card_payment));
            fputcsv($handle, array('Card Payment', $card_payment));
            fputcsv($handle, array('Credit Payment', $credit_payment));

            fclose($handle);

            $headers = array(
                'Content-Type' => 'text/csv',
            );
        } else {
            $currency_selected = Keywords::where('alias', 'Currency')->first();
            $currency_sel = $currency_selected->keyword;
            $walkers = Walker::paginate(10);
            $owners = Owner::paginate(10);
            return View::make('dashboard')
                            ->with('title', 'Dashboard')
                            ->with('page', 'dashboard')
                            ->with('walks', $walks)
                            ->with('owners', $owners)
                            ->with('walkers', $walkers)
                            ->with('completed_rides', $completed_rides)
                            ->with('cancelled_rides', $cancelled_rides)
                            ->with('card_payment', $card_payment)
                            ->with('install', $install)
                            ->with('currency_sel', $currency_sel)
                            ->with('cash_payment', $cash_payment)
                            ->with('credit_payment', $credit_payment);
        }
    }

    //admin control

    public function admins() {
        Session::forget('type');
        Session::forget('valu');
        $admins = Admin::paginate(10);
        return View::make('admins')
                        ->with('title', 'Admin Control')
                        ->with('page', 'admins')
                        ->with('admin', $admins);
    }

    public function add_admin() {
        $admin = Admin::all();
        $roles = Roles::all();
        return View::make('add_admin')
                        ->with('title', 'Add Admin')
                        ->with('page', 'add_admin')
                        ->with('admin', $admin)
                        ->with('roles',$roles);
    }

    public function add_admin_do() {
        
        $username = Input::get('username');
        $password = Input::get('password');

        $roles =Input::get('roles');

        $validator = Validator::make(
                        array(
                    'username' => $username,
                    'password' => $password,
                    'roles'=>$roles
                        ), array(
                    'username' => 'required',
                    'password' => 'required|min:6',
                    'roles'=>'required'
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->first();
            Session::put('msg', $error_messages);
            $admin = Admin::all();

            return View::make('add_admin')
                            ->with('title', 'Add Admin')
                            ->with('page', 'add_admin')
                            ->with('admin', $admin);
        } else {

            $admin = new Admin;

            $email_data= array();
            $email_data['username']=$username;
            $email_data['password']=$password;
            $password = Hash::make(Input::get('password'));
            $admin->username = $username;
            $admin->password = $admin->password = $password;
            $admin->role_id=2;
           
            $admin->roles = $roles;

            $admin->save();
            $subject = "Welcome";

            send_email($admin->id, 'sub_admin', $email_data, $subject, 'sub_admin_register');

            
            return Redirect::back();
        }
    }

    public function save_edit_admin(){
        $admin_id =Input::get('id');  
        $role_id =Input::get('roles');
        $admin =Admin::where('id',$admin_id)->first();
        $admin->roles=$role_id;
        $admin->save();
        return Redirect::back();
    }

    public function edit_admins() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $admin = Admin::find($id);
        $roles = Roles::all();
        $sub_action = SubActions::where('admin_id',$id)->first();
        $subadmin =SubAdmin::where('admin_id',$id)->first();
       
        Log::info("admin = " . print_r($admin, true));
        if ($admin) {
            return View::make('edit_admin')
                            ->with('title', 'Edit Admin')
                            ->with('page', 'admins')
                            ->with('success', $success)
                            ->with('admin', $admin)
                            ->with('roles',$roles)
                            ->with('sub_actions',$sub_action)
                            ->with('subadmin',$subadmin);
        } else {
            return View::make('notfound')->with('title', 'Error Page Not Found')->with('page', 'Error Page Not Found');
        }
    }

    public function update_admin() {

        $admin = Admin::find(Input::get('id'));
        $username = Input::get('username');
        $old_pass = Input::get('old_password');
        $new_pass = Input::get('new_password');
        $address = Input::get('my_address');
        $latitude = Input::get('latitude');
        $longitude = Input::get('longitude');

        $validator = Validator::make(
                        array(
                    'username' => $username,
                    'old_pass' => $old_pass,
                    'new_pass' => $new_pass,
                        ), array(
                    'username' => 'required',
                    'old_pass' => 'required',
                    'new_pass' => 'required|min:6'
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->first();
            Session::put('msg', $error_messages);
            if ($admin) {
                return View::make('edit_admin')
                                ->with('title', 'Edit Admin')
                                ->with('page', 'admins')
                                ->with('success', '')
                                ->with('admin', $admin);
            } else {
                return View::make('notfound')->with('title', 'Error Page Not Found')->with('page', 'Error Page Not Found');
            }
        } else {

            $admin->username = $username;
            $admin->latitude = $latitude;
            $admin->longitude = $longitude;
            $admin->address = $address;

            if ($new_pass != NULL) {
                $check_pass = Hash::check($old_pass, $admin->password);
                if ($check_pass) {
                    $admin->password = $admin->password = Hash::make($new_pass);
                    Log::info('admin password changed');
                }
            }
            $admin->save();
            return Redirect::to("/admin/admins");
        }
    }

    public function delete_admin() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $admin = Admin::find($id);
        if ($admin) {
            Admin::where('id', $id)->delete();
            return Redirect::to("/admin/admins?success=1");
        } else {
            return View::make('notfound')->with('title', 'Error Page Not Found')->with('page', 'Error Page Not Found');
        }
    }
    public function cancel_requests(){
          $id = Request::segment(4);
          $requests = Requests::where('id',$id)->first();
          if($requests){
            $requests->is_cancelled=1;
            $requests->save();
          }
          return Redirect::back();
    }
    public function banking_provider() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $provider = Walker::find($id);
        if ($provider) {
            if (Config::get('app.default_payment') == 'stripe') {
                return View::make('banking_provider_stripe')
                                ->with('title', 'Banking Details Provider')
                                ->with('page', 'providers')
                                ->with('success', $success)
                                ->with('provider', $provider);
            } else {
                return View::make('banking_provider_braintree')
                                ->with('title', 'Banking Details Provider')
                                ->with('page', 'providers')
                                ->with('success', $success)
                                ->with('provider', $provider);
            }
        } else {
            return View::make('notfound')->with('title', 'Error Page Not Found')->with('page', 'Error Page Not Found');
        }
    }

    public function providerB_bankingSubmit() {
        $this->_braintreeConfigure();
        $result = new stdClass();
        $result = Braintree_MerchantAccount::create(
                        array(
                            'individual' => array(
                                'firstName' => Input::get('first_name'),
                                'lastName' => Input::get('last_name'),
                                'email' => Input::get('email'),
                                'phone' => Input::get('phone'),
                                'dateOfBirth' => date('Y-m-d', strtotime(Input::get('dob'))),
                                'ssn' => Input::get('ssn'),
                                'address' => array(
                                    'streetAddress' => Input::get('streetAddress'),
                                    'locality' => Input::get('locality'),
                                    'region' => Input::get('region'),
                                    'postalCode' => Input::get('postalCode')
                                )
                            ),
                            'funding' => array(
                                'descriptor' => 'UberForX',
                                'destination' => Braintree_MerchantAccount::FUNDING_DESTINATION_BANK,
                                'email' => Input::get('bankemail'),
                                'mobilePhone' => Input::get('bankphone'),
                                'accountNumber' => Input::get('accountNumber'),
                                'routingNumber' => Input::get('routingNumber')
                            ),
                            'tosAccepted' => true,
                            'masterMerchantAccountId' => Config::get('app.masterMerchantAccountId'),
                            'id' => "taxinow" . Input::get('id')
                        )
        );

        Log::info('res = ' . print_r($result, true));
        if ($result->success) {
            $pro = Walker::where('id', Input::get('id'))->first();
            $pro->merchant_id = $result->merchantAccount->id;
            $pro->save();
            Log::info(print_r($pro, true));
            Log::info('Adding banking details to provider from Admin = ' . print_r($result, true));
            return Redirect::to("/admin/providers");
        } else {
            Log::info('Error in adding banking details: ' . $result->message);
            return Redirect::to("/admin/providers");
        }
    }

    public function providerS_bankingSubmit() {
        $id = Input::get('id');
        Stripe::setApiKey(Config::get('app.stripe_secret_key'));
        $token_id = Input::get('stripeToken');
        // Create a Recipient
        try {
            $recipient = Stripe_Recipient::create(array(
                        "name" => Input::get('first_name') . " " . Input::get('last_name'),
                        "type" => Input::get('type'),
                        "bank_account" => $token_id,
                        "email" => Input::get('email')
                            )
            );

            Log::info('recipient = ' . print_r($recipient, true));

            $pro = Walker::where('id', Input::get('id'))->first();
            $pro->merchant_id = $recipient->id;
            $pro->account_id = $recipient->active_account->id;
            $pro->last_4 = $recipient->active_account->last4;
            $pro->save();

            Log::info('recipient added = ' . print_r($recipient, true));
        } catch (Exception $e) {
            Log::info('Error in Stripe = ' . print_r($e, true));
        }
        return Redirect::to("/admin/providers");
    }

    public function index() {
        return Redirect::to('/admin/login');
    }

    public function get_document_types() {
        Session::forget('type');
        Session::forget('valu');
        $types = Document::paginate(10);
        return View::make('list_document_types')
                        ->with('title', 'Document Types')
                        ->with('page', 'document-type')
                        ->with('types', $types);
    }

    public function get_promo_codes() {
        Session::forget('type');
        Session::forget('valu');
        $promo_codes = PromoCodes::paginate(10);
        return View::make('list_promo_codes')
                        ->with('title', 'Promo Codes')
                        ->with('page', 'promo_codes')
                        ->with('promo_codes', $promo_codes);
    }

    public function searchdoc() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'docid') {
            $types = Document::where('id', $valu)->paginate(10);
        } elseif ($type == 'docname') {
            $types = Document::where('name', 'like', '%ar' . $valu . '%')->paginate(10);
        }

        return View::make('list_document_types')
                        ->with('title', 'Document Types')
                        ->with('page', 'document-type')
                        ->with('types', $types);
    }

    public function delete_document_type() {
        $id = Request::segment(4);
        Document::where('id', $id)->delete();
        return Redirect::to("/admin/document-types");
    }

    public function edit_document_type() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $document_type = Document::find($id);

        if ($document_type) {
            $id = $document_type->id;
            $name = $document_type->name;
        } else {
            $id = 0;
            $name = "";
        }

        return View::make('edit_document_type')
                        ->with('title', 'Document Types')
                        ->with('page', 'document-type')
                        ->with('success', $success)
                        ->with('id', $id)
                        ->with('name', $name);
    }

    public function update_document_type() {
        $id = Input::get('id');
        $name = Input::get('name');

        if ($id == 0) {
            $document_type = new Document;
        } else {
            $document_type = Document::find($id);
        }


        $document_type->name = $name;
        $document_type->save();

        return Redirect::to("/admin/document-type/edit/$document_type->id?success=1");
    }

    public function get_provider_types() {

        $types = ProviderType::paginate(10);
        return View::make('list_provider_types')
                        ->with('title', 'Provider Types')
                        ->with('page', 'provider-type')
                        ->with('types', $types);
    }

    public function searchpvtype() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'provid') {
            $types = ProviderType::where('id', $valu)->paginate(10);
        } elseif ($type == 'provname') {
            $types = ProviderType::where('name', 'like', '%' . $valu . '%')->paginate(10);
        }

        return View::make('list_provider_types')
                        ->with('title', 'Provider Types')
                        ->with('page', 'provider-type')
                        ->with('types', $types);
    }

    public function delete_provider_type() {
        $id = Request::segment(4);
        ProviderType::where('id', $id)->where('is_default', 0)->delete();
        return Redirect::to("/admin/provider-types");
    }

    public function edit_provider_type() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $providers_type = ProviderType::find($id);

        if ($providers_type) {
            $id = $providers_type->id;
            $name = $providers_type->name;
            $is_default = $providers_type->is_default;
            $base_price = $providers_type->base_price;
            $price_per_unit_distance = $providers_type->price_per_unit_distance;
            $price_per_unit_time = $providers_type->price_per_unit_time;
            $icon = $providers_type->icon;
        } else {
            $id = 0;
            $name = "";
            $is_default = "";
            $base_price = "";
            $price_per_unit_time = "";
            $price_per_unit_distance = "";
            $icon = "";
        }

        return View::make('edit_provider_type')
                        ->with('title', 'Provider Types')
                        ->with('page', 'provider-type')
                        ->with('success', $success)
                        ->with('id', $id)
                        ->with('name', $name)
                        ->with('is_default', $is_default)
                        ->with('base_price', $base_price)
                        ->with('icon', $icon)
                        ->with('price_per_unit_time', $price_per_unit_time)
                        ->with('price_per_unit_distance', $price_per_unit_distance);
    }

    public function update_provider_type() {
        $id = Input::get('id');
        $name = Input::get('name');
        $is_default = Input::get('is_default');

        if ($is_default) {
            if ($is_default == 1) {
                ProviderType::where('is_default', 1)->update(array('is_default' => 0));
            }
        } else {
            $is_default = 0;
        }


        if ($id == 0) {
            $providers_type = new ProviderType;
        } else {
            $providers_type = ProviderType::find($id);
        }
        if (Input::hasFile('icon')) {
            // Upload File
            $file_name = time();
            $file_name .= rand();
            $ext = Input::file('icon')->getClientOriginalExtension();
            Input::file('icon')->move(public_path() . "/uploads", $file_name . "." . $ext);
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

            $providers_type->icon = $s3_url;
        }


        $providers_type->name = $name;
        $providers_type->is_default = $is_default;
        $providers_type->save();

        return Redirect::to("/admin/provider-type/edit/$providers_type->id?success=1");
    }

    public function get_info_pages() {

        $informations = Information::paginate(10);
        return View::make('list_info_pages')
                        ->with('title', 'Information Pages')
                        ->with('page', 'information')
                        ->with('informations', $informations);
    }

    public function searchinfo() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'infoid') {
            $informations = Information::where('id', $valu)->paginate(10);
        } elseif ($type == 'infotitle') {
            $informations = Information::where('title', 'like', '%' . $valu . '%')->paginate(10);
        }
        return View::make('list_info_pages')
                        ->with('title', 'Information Pages | Search Result')
                        ->with('page', 'information')
                        ->with('informations', $informations);
    }

    public function delete_info_page() {
        $id = Request::segment(4);
        Information::where('id', $id)->delete();
        return Redirect::to("/admin/informations");
    }

    public function skipSetting() {
        setcookie("skipInstallation", "admincookie", time() + (86400 * 30));
        return Redirect::to("/admin/report/");
    }

    public function edit_info_page() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $information = Information::find($id);
        if ($information) {
            $id = $information->id;
            $title = $information->title;
            $description = $information->content;
            $icon = $information->icon;

            $title_new = str_replace(' ', '_', $title);

            $file = base_path() . "/app/views/website/" . $title . ".blade.php";

            if (file_exists($file)) {
                $fp = fopen($file, "w");
                $body = generate_generic_page_layout($description);
                fwrite($fp, $body);
                fclose($fp);
            } else {
                $success = 2;
            }
        } else {
            $id = 0;
            $title = "";
            $description = "";
            $icon = "";
        }
        return View::make('edit_info_page')
                        ->with('title', 'Information Page')
                        ->with('page', 'information')
                        ->with('success', $success)
                        ->with('id', $id)
                        ->with('info_title', $title)
                        ->with('icon', $icon)
                        ->with('description', $description);
    }

    public function update_info_page() {
        $id = Input::get('id');
        $title = Input::get('title');
        $description = Input::get('description');
        if ($id == 0) {
            $information = new Information;
        } else {
            $information = Information::find($id);
        }

        if (Input::hasFile('icon')) {
            // Upload File
            $file_name = time();
            $file_name .= rand();
            $ext = Input::file('icon')->getClientOriginalExtension();
            Input::file('icon')->move(public_path() . "/uploads", $file_name . "." . $ext);
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

            $information->icon = $s3_url;
        }

        $information->title = $title;
        $information->content = $description;
        $information->save();

        $title_new = str_replace(' ', '_', $title);

        $file = base_path() . "/app/views/website/" . $title . ".blade.php";

        if (!file_exists($file)) {
            $fp = fopen($file, "w");
            $body = generate_generic_page_layout($description);
            fwrite($fp, $body);
            fclose($fp);
        }

        return Redirect::to("/admin/information/edit/$information->id?success=1");
    }

    public function map_view() {
        $settings = Settings::where('key', 'map_center_latitude')->first();
        $center_latitude = $settings->value;
        $settings = Settings::where('key', 'map_center_longitude')->first();
        $center_longitude = $settings->value;
        return View::make('map_view')
                        ->with('title', 'Map View')
                        ->with('page', 'map-view')
                        ->with('center_longitude', $center_longitude)
                        ->with('center_latitude', $center_latitude);
    }

    public function walkers() {
        Session::forget('type');
        Session::forget('valu');
        Session::forget('che');
        //$query = "SELECT *,(select count(*) from request_meta where walker_id = walker.id  and status != 0 ) as total_requests,(select count(*) from request_meta where walker_id = walker.id and status=1) as accepted_requests FROM `walker`";
        //$walkers = DB::select(DB::raw($query));
        $walkers1 = DB::table('walker')
                ->leftJoin('request_meta', 'walker.id', '=', 'request_meta.walker_id')
                ->where('request_meta.status', '!=', 0)
                ->orderBy('walker.created_at', 'DESC')
                ->count();

        $walkers2 = DB::table('walker')
                ->leftJoin('request_meta', 'walker.id', '=', 'request_meta.walker_id')
                ->where('request_meta.status', '=', 1)
                ->orderBy('walker.created_at', 'DESC')
                ->count();

        $walkers = Walker::orderBy('walker.created_at', 'DESC')->paginate(10);
       

        return View::make('walkers')
                        ->with('title', 'Providers')
                        ->with('page', 'walkers')
                        ->with('walkers', $walkers)
                        ->with('total_requests', $walkers1)
                        ->with('accepted_requests', $walkers2);
    }

    //Referral Statistics
    public function referral_details() {
        $owner_id = Request::segment(4);
        $ledger = Ledger::where('owner_id', $owner_id)->first();
        $owners = Owner::where('referred_by', $owner_id)->paginate(10);

        return View::make('referred')
                        ->with('page', 'owners')
                        ->with('title', 'Owner | Coupon Statistics')
                        ->with('owners', $owners)
                        ->with('ledger', $ledger);
    }

    // Search Walkers from Admin Panel
    public function searchpv() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'provid') {
            $walkers = Walker::where('id', $valu)->paginate(10);
        } elseif ($type == 'pvname') {
            $walkers = Walker::where('first_name', 'like', '%' . $valu . '%')->orWhere('last_name', 'like', '%' . $valu . '%')->orderBy('created_at', 'DESC')->paginate(10);
        } elseif ($type == 'pvemail') {
            $walkers = Walker::where('email', 'like', '%' . $valu . '%')->orderBy('created_at', 'DESC')->paginate(10);
        } elseif ($type == 'bio') {
            $walkers = Walker::where('bio', 'like', '%' . $valu . '%')->orderBy('created_at', 'DESC')->paginate(10);
        }
        return View::make('walkers')
                        ->with('title', 'Providers | Search Result')
                        ->with('page', 'walkers')
                        ->with('walkers', $walkers);
    }

    public function walkers_xml() {

        $walkers = Walker::where('');
        $response = "";
        $response .= '<markers>';

        // busy walkers
        $walkers = DB::table('walker')
                ->where('walker.is_active', 1)
                ->where('walker.is_available', 0)
                ->where('walker.is_approved', 1)
                ->select('walker.id', 'walker.phone', 'walker.first_name', 'walker.last_name', 'walker.latitude', 'walker.longitude')
                ->paginate(10);

        $walker_ids = array();


        foreach ($walkers as $walker) {
            $response .= '<marker ';
            $response .= 'name="' . $walker->first_name . " " . $walker->last_name . '" ';
            $response .= 'client_name="' . $walker->first_name . " " . $walker->last_name . '" ';
            $response .= 'contact="' . $walker->phone . '" ';
            $response .= 'amount="' . 0 . '" ';
            $response .= 'lat="' . $walker->latitude . '" ';
            $response .= 'lng="' . $walker->longitude . '" ';
            $response .= 'id="' . $walker->id . '" ';
            $response .= 'type="client_pay_done" ';
            $response .= '/>';
            array_push($walker_ids, $walker->id);
        }

        $walker_ids = array_unique($walker_ids);
        $walker_ids_temp = implode(",", $walker_ids);

        $walkers = DB::table('walker')
                ->where('walker.is_active', 0)
                ->where('walker.is_approved', 1)
                ->select('walker.id', 'walker.phone', 'walker.first_name', 'walker.last_name', 'walker.latitude', 'walker.longitude')
                ->paginate(10);


        foreach ($walkers as $walker) {
            $response .= '<marker ';
            $response .= 'name="' . $walker->first_name . " " . $walker->last_name . '" ';
            $response .= 'client_name="' . $walker->first_name . " " . $walker->last_name . '" ';
            $response .= 'contact="' . $walker->phone . '" ';
            $response .= 'amount="' . 0 . '" ';
            $response .= 'lat="' . $walker->latitude . '" ';
            $response .= 'lng="' . $walker->longitude . '" ';
            $response .= 'id="' . $walker->id . '" ';
            $response .= 'type="client_no_pay" ';
            $response .= '/>';
            array_push($walker_ids, $walker->id);
        }

        $walker_ids = array_unique($walker_ids);
        $walker_ids = implode(",", $walker_ids);
        if ($walker_ids) {
            $query = "select * from walker where is_approved = 1 and id NOT IN ($walker_ids)";
        } else {
            $query = "select * from walker where is_approved = 1";
        }


        // free walkers
        $walkers = DB::select(DB::raw($query));

        foreach ($walkers as $walker) {
            $response .= '<marker ';
            $response .= 'name="' . $walker->first_name . " " . $walker->last_name . '" ';
            $response .= 'client_name="' . $walker->first_name . " " . $walker->last_name . '" ';
            $response .= 'contact="' . $walker->phone . '" ';
            $response .= 'amount="' . 0 . '" ';
            $response .= 'lat="' . $walker->latitude . '" ';
            $response .= 'lng="' . $walker->longitude . '" ';
            $response .= 'id="' . $walker->id . '" ';
            $response .= 'type="client" ';
            $response .= '/>';
        }


        $response .= '</markers>';
        $content = View::make('walkers_xml')->with('response', $response);
        return Response::make($content, '200')->header('Content-Type', 'text/xml');
    }

    public function owners() {
        Session::forget('type');
        Session::forget('valu');
        $owners = Owner::orderBy('created_at', 'DESC')->paginate(15);

        return View::make('owners')
                        ->with('title', 'Owners')
                        ->with('page', 'owners')
                        ->with('owners', $owners);
    }

    public function searchur() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'userid') {
            $owners = Owner::where('id', $valu)->paginate(10);
        } elseif ($type == 'username') {
            $owners = Owner::where('first_name', 'like', '%' . $valu . '%')->orWhere('last_name', 'like', '%' . $valu . '%')->orderBy('created_at', 'DESC')->paginate(10);
        } elseif ($type == 'useremail') {
            $owners = Owner::where('email', 'like', '%' . $valu . '%')->paginate(10);
        } elseif ($type == 'useraddress') {
            $owners = Owner::where('address', 'like', '%' . $valu . '%')->orWhere('state', 'like', '%' . $valu . '%')->orWhere('country', 'like', '%' . $valu . '%')->orderBy('created_at', 'DESC')->paginate(10);
        }
        return View::make('owners')
                        ->with('title', 'Owners | Search Result')
                        ->with('page', 'owners')
                        ->with('owners', $owners);
    }

    public function walks() {
        Session::forget('type');
        Session::forget('valu');
        $walks = DB::table('request')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->select('owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'walker.merchant_id as walker_merchant', 'request.id as id', 'request.created_at as date', 'request.payment_mode', 'request.is_started', 'request.is_walker_arrived', 'request.payment_mode', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                        , 'request.status', 'request.time', 'request.instruction','request.distance', 'request.total', 'request.is_cancelled', 'request.transfer_amount','request.source_address as source_address','request.destination_address as destination_address')
                ->orderBy('request.created_at', 'DESC')
                ->paginate(10);
        $setting = Settings::find(37);

        return View::make('walks')
                        ->with('title', 'Requests')
                        ->with('page', 'walks')
                        ->with('walks', $walks)
                        ->with('setting', $setting);
    }

    // Search Walkers from Admin Panel
    public function searchreq() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'reqid') {
            $walks = DB::table('request')
                    ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                    ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                    ->groupBy('request.id')
                    ->select('owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.*', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                            , 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.payment_mode')
                    ->where('request.id', $valu)
                    ->orderBy('request.created_at', 'DESC')
                    ->paginate(10);
        } elseif ($type == 'owner') {
            $walks = DB::table('request')
                    ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                    ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                    ->groupBy('request.id')
                    ->select('owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.*', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                            , 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.payment_mode')
                    ->where('owner.first_name', 'like', '%' . $valu . '%')
                    ->orWhere('owner.last_name', 'like', '%' . $valu . '%')
                    ->orderBy('request.created_at', 'DESC')
                    ->paginate(10);
        } elseif ($type == 'walker') {
            $walks = DB::table('request')
                    ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                    ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                    ->groupBy('request.id')
                    ->select('owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.*', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                            , 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.payment_mode')
                    ->where('walker.first_name', 'like', '%' . $valu . '%')
                    ->orWhere('walker.last_name', 'like', '%' . $valu . '%')
                    ->orderBy('request.created_at', 'DESC')
                    ->paginate(10);
        } elseif ($type == 'payment') {
            if ($valu == "Stored Cards" || $valu == "cards" || $valu == "Cards" || $valu == "Card") {
                $value = 0;
            } elseif ($valu == "Pay by Cash" || $valu == "cash" || $valu == "Cash") {
                $value = 1;
            } elseif ($valu == "Paypal" || $valu == "paypal") {
                $value = 2;
            }

            $walks = DB::table('request')
                    ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                    ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                    ->groupBy('request.id')
                    ->select('owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                            , 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.payment_mode')
                    ->Where('request.payment_mode', $value)
                    ->orderBy('request.created_at', 'DESC')
                    ->paginate(10);
        }

        $setting = Settings::find(37);

        return View::make('walks')
                        ->with('title', 'Requests | Search Result')
                        ->with('page', 'walks')
                        ->with('setting', $setting)
                        ->with('valu', $valu)
                        ->with('walks', $walks);
    }

    public function reviews() {
        Session::forget('type');
        Session::forget('valu');
        $provider_reviews = DB::table('review_walker')
                ->leftJoin('walker', 'review_walker.walker_id', '=', 'walker.id')
                ->leftJoin('owner', 'review_walker.owner_id', '=', 'owner.id')
                ->select('review_walker.id as review_id', 'review_walker.rating', 'review_walker.comment', 'owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'review_walker.created_at')
                ->paginate(10);

        $user_reviews = DB::table('review_dog')
                ->leftJoin('walker', 'review_dog.walker_id', '=', 'walker.id')
                ->leftJoin('owner', 'review_dog.owner_id', '=', 'owner.id')
                ->select('review_dog.id as review_id', 'review_dog.rating', 'review_dog.comment', 'owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'review_dog.created_at')
                ->paginate(10);

        return View::make('reviews')
                        ->with('title', 'Reviews')
                        ->with('page', 'reviews')
                        ->with('provider_reviews', $provider_reviews)
                        ->with('user_reviews', $user_reviews);
    }

    public function searchrev() {

        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'walker') {
            $provider_reviews = DB::table('review_walker')
                ->leftJoin('walker', 'review_walker.walker_id', '=', 'walker.id')
                ->leftJoin('owner', 'review_walker.owner_id', '=', 'owner.id')
                ->select('review_walker.id as review_id', 'review_walker.rating', 'review_walker.comment', 'owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'review_walker.created_at')
                ->where('walker.first_name', 'like', '%' . $valu . '%')->orWhere('walker.last_name', 'like', '%' . $valu . '%')
                ->paginate(10);
            $user_reviews=$provider_reviews;
        }elseif ($type == 'owner') {
            $user_reviews = DB::table('review_dog')
                ->leftJoin('walker', 'review_dog.walker_id', '=', 'walker.id')
                ->leftJoin('owner', 'review_dog.owner_id', '=', 'owner.id')
                ->select('review_dog.id as review_id', 'review_dog.rating', 'review_dog.comment', 'owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'review_dog.created_at')
                ->where('owner.first_name', 'like', '%' . $valu . '%')->orWhere('owner.last_name', 'like', '%' . $valu . '%')
                ->paginate(10);
            $provider_reviews=$user_reviews;
        }
        return View::make('reviews')
            ->with('title', 'Reviews')
            ->with('page', 'reviews')
            ->with('provider_reviews', $provider_reviews)
            ->with('user_reviews', $user_reviews);
    }

    public function search() {
        Session::forget('type');
        Session::forget('valu');
        $type = Input::get('type');
        $q = Input::get('q');
        if ($type == 'user') {
            $owners = Owner::where('first_name', 'like', '%' . $q . '%')
                    ->orWhere('last_name', 'like', '%' . $q . '%')
                    ->paginate(10);

            return View::make('owners')
                            ->with('title', 'Users')
                            ->with('page', 'owners')
                            ->with('owners', $owners);
        } else {

            $walkers = Walker::where('first_name', 'like', '%' . $q . '%')
                    ->orWhere('last_name', 'like', '%' . $q . '%')
                    ->paginate(10);

            return View::make('walkers')
                            ->with('title', 'Providers')
                            ->with('page', 'walkers')
                            ->with('walkers', $walkers);
        }
    }

    public function logout() {
        Auth::logout();
        return Redirect::to('/admin/login');
    }

    public function verify() {
        $username = Input::get('username');
        $password = Input::get('password');
        if (!Admin::count()) {
            $user = new Admin;
            $user->username = Input::get('username');
            $user->password = $user->password = Hash::make(Input::get('password'));
            $user->save();
            return Redirect::to('/admin/login');
        } else {
            if (Auth::attempt(array('username' => $username, 'password' => $password))) {
                if (Session::has('pre_admin_login_url')) {
                    $url = Session::get('pre_admin_login_url');
                    Session::forget('pre_admin_login_url');
                    return Redirect::to($url);
                } else {
                    $admin = Admin::where('username', 'like', '%' . $username . '%')->first();
                    Session::put('admin_id', $admin->id);
                    return Redirect::to('/admin/report')->with('notify', 'installation Notification');
                }
            } else {
                return Redirect::to('/admin/login?error=1');
            }
        }
    }

    public function login() {
        $error = Input::get('error');
        if (Admin::count()) {

            return View::make('login')->with('title', 'Login')->with('button', 'Login')->with('error', $error);
        } else {
            return View::make('login')->with('title', 'Create Admin')->with('button', 'Create')->with('error', $error);
        }
    }

    public function edit_walker() {
        $id = Request::segment(4);
        $type = ProviderType::all();
        $provserv = ProviderServices::where('provider_id', $id)->get();
        $success = Input::get('success');
        $walker = Walker::find($id);
        if ($walker) {
            return View::make('edit_walker')
                            ->with('title', 'Edit Provider')
                            ->with('page', 'walkers')
                            ->with('success', $success)
                            ->with('type', $type)
                            ->with('ps', $provserv)
                            ->with('walker', $walker);
        } else {
            return View::make('notfound')->with('title', 'Error Page Not Found')->with('page', 'Error Page Not Found');
        }
    }

    public function provider_availabilty() {
        $id = Request::segment(5);
        $type = ProviderType::all();
        $provserv = ProviderServices::where('provider_id', $id)->get();
        $success = Input::get('success');
        $walker = Walker::find($id);
        return View::make('edit_walker_availability')
                        ->with('title', 'Edit Provider Availability')
                        ->with('page', 'walkers')
                        ->with('success', $success)
                        ->with('type', $type)
                        ->with('ps', $provserv)
                        ->with('walker', $walker);
    }

    public function add_walker() {
        $type = ProviderType::all();
        $success = Input::get('success');
        return View::make('add_walker')
                        ->with('title', 'Add Provider')
                        ->with('type',$type)
                        ->with('page', 'walkers');
    }

    public function add_promo_code() {
        return View::make('add_promo_code')
                        ->with('title', 'Add Promo Code')
                        ->with('page', 'promo_codes');
    }

    public function edit_promo_code() {
        $id = Request::segment(4);
        $promo_code = PromoCodes::where('id', $id)->first();
        return View::make('edit_promo_code')
                        ->with('title', 'Edit Promo Code')
                        ->with('page', 'promo_codes')
                        ->with('promo_code', $promo_code);
    }

    public function deactivate_promo_code() {
        $id = Request::segment(4);
        $promo_code = PromoCodes::where('id', $id)->first();
        $promo_code->state = 2;
        $promo_code->save();
        return Redirect::route('AdminPromoCodes');
    }

    public function activate_promo_code() {
        $id = Request::segment(4);
        $promo_code = PromoCodes::where('id', $id)->first();
        $promo_code->state = 1;
        $promo_code->save();
        return Redirect::route('AdminPromoCodes');
    }

    public function update_promo_code() {
        if (Input::get('id') != 0) {
            $promo = PromoCodes::find(Input::get('id'));
        } else {
            $promo = new PromoCodes;
        }

        $code_name = Input::get('code_name');
        $code_value = Input::get('code_value');
        $code_type = Input::get('code_type');
        $code_uses = Input::get('code_uses');
        $code_expiry = Input::get('code_expiry');

        $validator = Validator::make(
                        array(
                    'code_name' => $code_name,
                    'code_value' => $code_value,
                    'code_type' => $code_type,
                    'code_uses' => $code_uses,
                    'code_expiry' => $code_expiry
                        ), array(
                    'code_name' => 'required',
                    'code_value' => 'required|integer',
                    'code_type' => 'required|integer',
                    'code_uses' => 'required|integer',
                    'code_expiry' => 'required'
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->first();
            Session::put('msg', $error_messages);
            return View::make('add_promo_code')
                            ->with('title', 'Add Promo Code')
                            ->with('page', 'promo_codes');
        } else {
            $expirydate = date("Y-m-d H:i:s", strtotime($code_expiry));

            $promo->coupon_code = $code_name;
            $promo->value = $code_value;
            $promo->type = $code_type;
            $promo->uses = $code_uses;
            $promo->expiry = $expirydate;
            $promo->state = 1;
            $promo->save();
        }
        return Redirect::route('AdminPromoCodes');
    }

    public function update_walker() {

        if (Input::get('id') != 0) {
            $walker = Walker::find(Input::get('id'));
        } else {

            $findWalker = Walker::where('email', Input::get('email'))->first();

            if ($findWalker) {
                Session::put('new_walker', 0);
                $error_messages = "This Email Id is already registered.";
                Session::put('msg', $error_messages);
                return View::make('add_walker')
                                ->with('title', 'Add Provider')
                                ->with('page', 'walkers');
            } else {
                Session::put('new_walker', 1);
                $walker = new Walker;
            }
        }
        if (Input::has('service') != NULL) {
            foreach (Input::get('service') as $key) {
                $serv = ProviderType::where('id', $key)->first();
                $pserv[] = $serv->name;
            }
        }

        $first_name = Input::get('first_name');
        $last_name = Input::get('last_name');
        $email = Input::get('email');
        $phone = Input::get('phone');
        $bio = Input::get('bio');
        $address = Input::get('address');
        $state = Input::get('state');
        $country = Input::get('country');
        $zipcode = Input::get('zipcode');
        $vehicle_no =Input::get('vehicle_no');
        $model_no =Input::get('model_no');
        $validator = Validator::make(
                        array(
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'bio' => $bio,
                    'state' => $state,
                    'country' => $country,
                    'model_no'=>$model_no,
                    'vehicle_no'=>$vehicle_no,
                    'zipcode' => $zipcode,
                        ), array(
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required|email',
                    'phone' => 'required',
                    'bio' => 'required',
                    'state' => 'required',
                    'country' => 'required',
                    'zipcode' => 'required|integer',
                    'model_no' =>'required',
                    'vehicle_no'=>'required'
                        )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->first();
            Session::put('msg', $error_messages);
            return View::make('add_walker')
                            ->with('title', 'Add Provider')
                            ->with('page', 'walkers');
        } else {

            $walker->first_name = Input::get('first_name');
            $walker->last_name = Input::get('last_name');
            $walker->email = Input::get('email');
            $walker->phone = Input::get('phone');
            $walker->bio = Input::get('bio');
            $walker->address = Input::get('address');
            $walker->state = Input::get('state');
            $walker->vehicle_no= Input::get('vehicle_no');
            $walker->model_no=Input::get('model_no');
            // adding password to new provider

            if (Input::get('id') == 0) {
            $new_password = time();
            $new_password .= rand();
            $new_password = sha1($new_password);
            $new_password = substr($new_password, 0, 8);
            $walker->password = Hash::make($new_password);
            }
            $walker->country = Input::get('country');
            $walker->zipcode = Input::get('zipcode');
            $walker->is_approved = 1;
            $walker->email_activation = 1;



            if (Input::hasFile('pic')) {
                if ($walker->picture != "") {
                    $path = $walker->picture;
                    Log::info($path);
                    $filename = basename($path);
                    Log::info($filename);
                    unlink(public_path() . "/uploads/" . $filename);
                }
                // Upload File
                $file_name = time();
                $file_name .= rand();
                $ext = Input::file('pic')->getClientOriginalExtension();
                Input::file('pic')->move(public_path() . "/uploads", $file_name . "." . $ext);
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
            $walker->save();

            if (Session::get('new_walker') == 1) {
                // send email
                $settings = Settings::where('key', 'email_forgot_password')->first();
                $pattern = $settings->value;
                $pattern = "Hi, " . Config::get('app.website_title') . " is Created a New Account for you , Your Username is:" . Input::get('email') . " and Your Password is " . $new_password . ". Please dont forget to change the password once you log in next time.";
                $subject = "Welcome On Board";
                email_notification($walker->id, 'walker', $pattern, $subject);
            }
           Session::forget('new_walker');

             $proviserv = ProviderServices::where('provider_id', $walker->id)->first();
            if ($proviserv != NULL) {
                DB::delete("delete from walker_services where provider_id = '" . $walker->id . "';");
            }

           /* if (Input::has('service') != NULL) {
                $service = Input::get('service');                
                $base_price = Input::get('service_base_price');
                $service_price_distance = Input::get('service_price_distance');
                $service_price_time = Input::get('service_price_time');
                Log::info('service = ' . print_r(Input::get('service'), true));
                $cnkey = count(Input::get('service'));

                Log::info('cnkey = ' . print_r($cnkey, true));
                for ($i = 1; $i <= $cnkey; $i++) {
                    $prserv = new ProviderServices;
                    $prserv->provider_id = $walker->id;
                    $prserv->type = $service[$i-1];
                    Log::info('service price = ' . print_r($base_price[$i - 1], true));
                    if (Input::has('service_base_price')) {
                        $t = Input::get('service_base_price');
                        
                        $prserv->base_price = $base_price[$i - 1];
                    } else {
                        $prserv->base_price = 0;
                    }
                    if (Input::has('service_price_distance')) {

                        $prserv->price_per_unit_distance = $service_price_distance[$i - 1];
                    } else {
                        $prserv->price_per_unit_distance = 0;
                    }
                    if (Input::has('service_price_time')) {
                        $prserv->price_per_unit_time = $service_price_time[$i - 1];
                    } else {
                        $prserv->price_per_unit_distance = 0;
                    }
                    $prserv->save();
                }
            }*/

            $service = Input::get('service');                
            $base_price = Input::get('service_base_price');
            $service_price_distance = Input::get('service_price_distance');
            $service_price_time = Input::get('service_price_time');

            foreach ($service as $key){
                if($service[$key] != ''){
                    $prserv = new ProviderServices;
                    $prserv->provider_id = $walker->id;
                    $prserv->type = $key;
                
                    if ($base_price[$key] != '') {
                        $t = Input::get('service_base_price');
                        $prserv->base_price = $base_price[$key];
                    } else {
                        $prserv->base_price = 0;
                    }
                    if ($service_price_distance[$key] != '') {

                        $prserv->price_per_unit_distance = $service_price_distance[$key];
                    } else {
                        $prserv->price_per_unit_distance = 0;
                    }
                    if ($service_price_time[$key] != '') {
                        $prserv->price_per_unit_time = $service_price_time[$key];
                    } else {
                        $prserv->price_per_unit_distance = 0;
                    }
                    $prserv->save();
                }    
                
            }

            return Redirect::to("/admin/providers");
        }
    }

    public function approve_walker() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $walker = Walker::find($id);
        $walker->is_approved = 1;
        $walker->save();
        $pattern = "Hi " . $walker->first_name . ", Your Documents are verified by the Admin and your account is Activated, Please Login to Continue";
        $subject = "Your Account Activated";
        email_notification($walker->id, 'walker', $pattern, $subject);
        return Redirect::to("/admin/providers");
    }

    public function decline_walker() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $walker = Walker::find($id);
        $walker->is_approved = 0;
        $walker->save();
        $pattern = "Hi " . $walker->first_name . ", Your account is deactivated, Please contact admin to Continue";
        $subject = "Your Account Deactivated";
        email_notification($walker->id, 'walker', $pattern, $subject);
        return Redirect::to("/admin/providers");
    }

    public function delete_walker() {
        $id = Request::segment(4);
        $success = Input::get('success');
        RequestMeta::where('walker_id', $id)->delete();
        Walker::where('id', $id)->delete();
        return Redirect::to("/admin/providers");
    }

    public function delete_owner() {
        $id = Request::segment(4);
        $success = Input::get('success');
        Owner::where('id', $id)->delete();
        return Redirect::to("/admin/users");
    }

    public function walker_history() {
        $walker_id = Request::segment(4);
        $walks = DB::table('request')
                ->where('request.confirmed_walker', $walker_id)
                ->where('request.is_completed', 1)
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->select('owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker', 'request.status', 'request.time', 'request.distance', 'request.total', 'request.instruction','request.is_cancelled', 'request.payment_mode','request.source_address','request.destination_address')
                ->orderBy('request.created_at', 'DESC')
                ->paginate(10);

        $setting = Settings::where('key', 'transfer')->first();

        return View::make('walks')
                        ->with('title', 'Trip History')
                        ->with('page', 'walkers')
                        ->with('setting', $setting)
                        ->with('walks', $walks);
    }

    public function walker_upcoming_walks() {
        $walker_id = Request::segment(4);
        $walks = DB::table('request')
                ->where('request.walker_id', $walker_id)
                ->where('request.is_completed', 0)
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->select('owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker', 'request.status', 'request.time', 'request.distance', 'request.total')
                ->orderBy('request.created_at', 'DESC')
                ->paginate(10);

        return View::make('walks')
                        ->with('title', 'Upcoming Walks')
                        ->with('page', 'walkers')
                        ->with('walks', $walks);
    }

    public function edit_owner() {
        $id = Request::segment(4);
        $success = Input::get('success');
        $owner = Owner::find($id);
        if ($owner) {
            return View::make('edit_owner')
                            ->with('title', 'Edit User')
                            ->with('page', 'owners')
                            ->with('success', $success)
                            ->with('owner', $owner);
        } else {
            return View::make('notfound')
                            ->with('title', 'Error Page Not Found')
                            ->with('page', 'Error Page Not Found');
        }
    }

    public function update_owner() {
        $owner = Owner::find(Input::get('id'));
        $owner->first_name = Input::get('first_name');
        $owner->last_name = Input::get('last_name');
        $owner->email = Input::get('email');
        $owner->phone = Input::get('phone');
        $owner->address = Input::get('address');
        $owner->state = Input::get('state');
        $owner->zipcode = Input::get('zipcode');
        $owner->save();
        return Redirect::to("/admin/user/edit/$owner->id?success=1");
    }

    public function owner_history() {
        $setting = Settings::where('key', 'transfer')->first();
        $owner_id = Request::segment(4);
        $owner = Owner::find($owner_id);
        $walks = DB::table('request')
                ->where('request.owner_id', $owner->id)
                ->where('request.is_completed', 1)
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->select('owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'request.instruction','walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker', 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled', 'request.payment_mode','request.source_address','request.destination_address')
                ->orderBy('request.created_at', 'DESC')
                ->paginate(10);

        return View::make('walks')
                        ->with('title', 'Trip History')
                        ->with('page', 'owners')
                        ->with('setting', $setting)
                        ->with('walks', $walks);
    }
    public function owner_sms(){
        $owner_id = Request::segment(4);
        return View::make('owner_sms')
                        ->with('title', 'Sms Owner')
                        ->with('page', 'owner_sms')
                        ->with('owner_id', $owner_id);

    }
    public function add_owner_sms(){

        $owner_id = Input::get('owner_id');

        $sms =Input::get('sms');
        
        $title =Input::get('title');
      
        $title = $title;
        send_notifications($owner_id, "owner", $title, $sms);
        return Redirect::to("/admin/users");               
    }

    public function owner_upcoming_walks() {
        $owner_id = Request::segment(4);
        $owner = Owner::find($owner_id);
        $walks = DB::table('request')
                ->where('request.owner_id', $owner->id)
                ->where('request.is_completed', 0)
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->select('owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker', 'request.status', 'request.time', 'request.distance', 'request.total')
                ->orderBy('request.created_at', 'DESC')
                ->paginate(10);

        return View::make('walks')
                        ->with('title', 'Upcoming Walks')
                        ->with('page', 'owners')
                        ->with('walks', $walks);
    }

    public function delete_review_walker() {
        $id = Request::segment(4);
        $walker = WalkerReview::where('id', $id)->delete();
        return Redirect::to("/admin/reviews");
    }

    public function delete_review_client() {
        $id = Request::segment(4);
        $walker = DogReview::where('id', $id)->delete();
        return Redirect::to("/admin/reviews");
    }

    public function approve_walk() {
        $id = Request::segment(4);
        $walk = Walk::find($id);
        $walk->is_confirmed = 1;
        $walk->save();
        return Redirect::to("/admin/walks");
    }

    public function decline_walk() {
        $id = Request::segment(4);
        $walk = Walk::find($id);
        $walk->is_confirmed = 0;
        $walk->save();
        return Redirect::to("/admin/walks");
    }

    public function view_map() {
        $id = Request::segment(4);
        $request = Requests::find($id);
        $walker = Walker::where('id', $request->confirmed_walker)->first();
        $owner = Owner::where('id', $request->owner_id)->first();

        if ($request->is_paid) {
            $status = "Payment Done";
        } elseif ($request->is_completed) {
            $status = "Request Completed";
        } elseif ($request->is_started) {
            $status = "Request Started";
        } elseif ($request->is_walker_started) {
            $status = "Provider Started";
        } elseif ($request->confirmed_walker) {
            $status = "Provider Yet to start";
        } else {
            $status = "Provider Not Confirmed";
        }


        if ($request->is_completed) {
            $walk_location_start = WalkLocation::where('request_id', $id)->orderBy('created_at')->first();
            $walk_location_end = WalkLocation::where('request_id', $id)->orderBy('created_at', 'desc')->first();
            $walker_latitude = $walk_location_start->latitude;
            $walker_longitude = $walk_location_start->longitude;
            $owner_latitude = $walk_location_end->latitude;
            $owner_longitude = $walk_location_end->longitude;
        } else {
            if ($request->confirmed_walker) {
                $walker_latitude = $walker->latitude;
                $walker_longitude = $walker->longitude;
            } else {
                $walker_latitude = 0;
                $walker_longitude = 0;
            }
            $owner_latitude = $owner->latitude;
            $owner_longitude = $owner->longitude;
        }

        $request_meta = DB::table('request_meta')
                ->where('request_id', $id)
                ->leftJoin('walker', 'request_meta.walker_id', '=', 'walker.id')
                ->paginate(10);

        if ($walker) {
            $walker_name = $walker->first_name . " " . $walker->last_name;
            $walker_phone = $walker->phone;
        } else {
            $walker_name = "";
            $walker_phone = "";
        }

        if ($request->confirmed_walker) {
            return View::make('walk_map')
                            ->with('title', 'Maps')
                            ->with('page', 'walks')
                            ->with('walk_id', $id)
                            ->with('is_started', $request->is_started)
                            ->with('owner_name', $owner->first_name . " " . $owner->last_name)
                            ->with('walker_name', $walker_name)
                            ->with('walker_latitude', $walker_latitude)
                            ->with('walker_longitude', $walker_longitude)
                            ->with('owner_latitude', $owner_latitude)
                            ->with('owner_longitude', $owner_longitude)
                            ->with('walker_phone', $walker_phone)
                            ->with('owner_phone', $owner->phone)
                            ->with('status', $status)
                            ->with('request_meta', $request_meta);
        } else {
            return View::make('walk_map')
                            ->with('title', 'Maps')
                            ->with('page', 'walks')
                            ->with('walk_id', $id)
                            ->with('is_started', $request->is_started)
                            ->with('owner_name', $owner->first_name . " ", $owner->last_name)
                            ->with('walker_name', "")
                            ->with('walker_latitude', $walker_latitude)
                            ->with('walker_longitude', $walker_longitude)
                            ->with('owner_latitude', $owner_latitude)
                            ->with('owner_longitude', $owner_longitude)
                            ->with('walker_phone', "")
                            ->with('owner_phone', $owner->phone)
                            ->with('request_meta', $request_meta)
                            ->with('status', $status);
        }
    }

    public function change_walker() {
        $id = Request::segment(4);
        return View::make('reassign_walker')
                        ->with('title', 'Map View')
                        ->with('page', 'walks')
                        ->with('walk_id', $id);
    }

    public function alternative_walkers_xml() {
        $id = Request::segment(4);
        $walk = Walk::find($id);
        $schedule = Schedules::find($walk->schedule_id);
        $dog = Dog::find($walk->dog_id);
        $owner = Owner::find($dog->owner_id);
        $current_walker = Walker::find($walk->walker_id);
        $latitude = $owner->latitude;
        $longitude = $owner->longitude;
        $distance = 5;


        // Get Latitude
        $schedule_meta = ScheduleMeta::where('schedule_id', '=', $schedule->id)
                ->orderBy('started_on', 'DESC')
                ->get();

        $flag = 0;
        $date = "0000-00-00";
        $days = array();
        foreach ($schedule_meta as $meta) {
            if ($flag == 0) {
                $date = $meta->started_on;
                $flag++;
            }
            array_push($days, $meta->day);
        }

        $start_time = date('H:i:s', strtotime($schedule->start_time) - (60 * 60));
        $end_time = date('H:i:s', strtotime($schedule->end_time) + (60 * 60));
        $days_str = implode(',', $days);

        $query = "SELECT walker.id,walker.bio,walker.first_name,walker.last_name,walker.phone,walker.latitude,walker.longitude from walker where id NOT IN ( SELECT distinct schedules.walker_id FROM `schedule_meta` left join schedules on schedule_meta.schedule_id = schedules.id where schedules.is_confirmed	 != 0 and schedule_meta.day IN ($days_str) and schedule_meta.ends_on >= '$date' and schedule_meta.started_on <= '$date' and ((schedules.start_time > '$start_time' and schedules.start_time < '$end_time') OR ( schedules.end_time > '$start_time' and schedules.end_time < '$end_time' )) ) and (1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance ";

        $walkers = DB::select(DB::raw($query));
        $response = "";
        $response .= '<markers>';

        foreach ($walkers as $walker) {
            $response .= '<marker ';
            $response .= 'name="' . $walker->first_name . " " . $walker->last_name . '" ';
            $response .= 'client_name="' . $walker->first_name . " " . $walker->last_name . '" ';
            $response .= 'contact="' . $walker->phone . '" ';
            $response .= 'amount="' . 0 . '" ';
            $response .= 'lat="' . $walker->latitude . '" ';
            $response .= 'lng="' . $walker->longitude . '" ';
            $response .= 'id="' . $walker->id . '" ';
            $response .= 'type="client" ';
            $response .= '/>';
        }

        // Add Current walker
        if ($current_walker) {
            $response .= '<marker ';
            $response .= 'name="' . $current_walker->first_name . " " . $current_walker->last_name . '" ';
            $response .= 'client_name="' . $current_walker->first_name . " " . $current_walker->last_name . '" ';
            $response .= 'contact="' . $current_walker->phone . '" ';
            $response .= 'amount="' . 0 . '" ';
            $response .= 'lat="' . $current_walker->latitude . '" ';
            $response .= 'lng="' . $current_walker->longitude . '" ';
            $response .= 'id="' . $current_walker->id . '" ';
            $response .= 'type="driver" ';
            $response .= '/>';
        }

        // Add Owner
        $response .= '<marker ';
        $response .= 'name="' . $owner->first_name . " " . $owner->last_name . '" ';
        $response .= 'client_name="' . $owner->first_name . " " . $owner->last_name . '" ';
        $response .= 'contact="' . $owner->phone . '" ';
        $response .= 'amount="' . 0 . '" ';
        $response .= 'lat="' . $owner->latitude . '" ';
        $response .= 'lng="' . $owner->longitude . '" ';
        $response .= 'id="' . $owner->id . '" ';
        $response .= 'type="client_pay_done" ';
        $response .= '/>';

        // Add Busy Walkers

        $walkers = DB::table('request')
                ->where('walk.is_started', 1)
                ->where('walk.is_completed', 0)
                ->join('walker', 'walk.walker_id', '=', 'walker.id')
                ->select('walker.id', 'walker.phone', 'walker.first_name', 'walker.last_name', 'walker.latitude', 'walker.longitude')
                ->distinct()
                ->get();


        foreach ($walkers as $walker) {
            $response .= '<marker ';
            $response .= 'name="' . $walker->first_name . " " . $walker->last_name . '" ';
            $response .= 'client_name="' . $walker->first_name . " " . $walker->last_name . '" ';
            $response .= 'contact="' . $walker->phone . '" ';
            $response .= 'amount="' . 0 . '" ';
            $response .= 'lat="' . $walker->latitude . '" ';
            $response .= 'lng="' . $walker->longitude . '" ';
            $response .= 'id="' . $owner->id . '" ';
            $response .= 'type="client_no_pay" ';
            $response .= '/>';
        }


        $response .= '</markers>';

        $content = View::make('walkers_xml')->with('response', $response);
        return Response::make($content, '200')->header('Content-Type', 'text/xml');
    }

    public function save_changed_walker() {
        $walk_id = Input::get('walk_id');
        $type = Input::get('type');
        $walker_id = Input::get('walker_id');
        $walk = Walk::find($walk_id);
        if ($type == 1) {
            $walk->walker_id = $walker_id;
            $walk->save();
        } else {
            Walk::where('schedule_id', $walk->schedule_id)->where('is_started', 0)->update(array('walker_id' => $walker_id));
            Schedules::where('id', $walk->schedule_id)->update(array('walker_id' => $walker_id));
        }
        return Redirect::to('/admin/walk/change_walker/' . $walk_id);
    }

    public function pay_walker() {
        $walk_id = Input::get('walk_id');
        $amount = Input::get('amount');
        $walk = Walk::find($walk_id);
        $walk->is_paid = 1;
        $walk->amount = $amount;
        $walk->save();

        return Redirect::to('/admin/walk/map/' . $walk_id);
    }

//settings
    public function get_settings() {
        $braintree_environment = Config::get('app.braintree_environment');
        $braintree_merchant_id = Config::get('app.braintree_merchant_id');
        $braintree_public_key = Config::get('app.braintree_public_key');
        $braintree_private_key = Config::get('app.braintree_private_key');
        $braintree_cse = Config::get('app.braintree_cse');
        $twillo_account_sid = Config::get('app.twillo_account_sid');
        $twillo_auth_token = Config::get('app.twillo_auth_token');
        $twillo_number = Config::get('app.twillo_number');
        $timezone = Config::get('app.timezone');
        $stripe_publishable_key = Config::get('app.stripe_publishable_key');
        $url = Config::get('app.url');
        $website_title = Config::get('app.website_title');
        $s3_bucket = Config::get('app.s3_bucket');
        $default_payment = Config::get('app.default_payment');
        $stripe_secret_key = Config::get('app.stripe_secret_key');
        $mail_driver = Config::get('mail.mail_driver');
        $email_name = Config::get('mail.from.name');
        $email_address = Config::get('mail.from.address');
        $mandrill_secret = Config::get('services.mandrill_secret');
        $host = Config::get('mail.host');
        $install = array(
            'braintree_environment' => $braintree_environment,
            'braintree_merchant_id' => $braintree_merchant_id,
            'braintree_public_key' => $braintree_public_key,
            'braintree_private_key' => $braintree_private_key,
            'braintree_cse' => $braintree_cse,
            'twillo_account_sid' => $twillo_account_sid,
            'twillo_auth_token' => $twillo_auth_token,
            'twillo_number' => $twillo_number,
            'stripe_publishable_key' => $stripe_publishable_key,
            'stripe_secret_key' => $stripe_secret_key,
            'mail_driver' => $mail_driver,
            'email_address' => $email_address,
            'email_name' => $email_name,
            'mandrill_secret' => $mandrill_secret,
            'default_payment' => $default_payment);
        $success = Input::get('success');
        $settings = Settings::all();
        $theme = Theme::all();
        return View::make('settings')
                        ->with('title', 'Settings')
                        ->with('page', 'settings')
                        ->with('settings', $settings)
                        ->with('success', $success)
                        ->with('install', $install)
                        ->with('theme', $theme);
    }

    public function edit_keywords() {
        $success = Input::get('success');
        $keywords = Keywords::all();
        $icons = Icons::all();

        $UIkeywords = array();

        $UIkeywords['keyProvider'] = Lang::get('customize.Provider');
        $UIkeywords['keyUser'] = Lang::get('customize.User');
        $UIkeywords['keyTaxi'] = Lang::get('customize.Taxi');
        $UIkeywords['keyTrip'] = Lang::get('customize.Trip');
        $UIkeywords['keyWalk'] = Lang::get('customize.Walk');
        $UIkeywords['keyRequest'] = Lang::get('customize.Request');

        return View::make('keywords')
                        ->with('title', 'Customize')
                        ->with('page', 'Customize')
                        ->with('keywords', $keywords)
                        ->with('icons', $icons)
                        ->with('Uikeywords', $UIkeywords)
                        ->with('success', $success);
    }

    public function save_keywords() {
        $keywords = Keywords::all();
        foreach ($keywords as $keyword) {
            Log::info('keyword = ' . print_r(Input::get($keyword->id), true));
            if (Input::get($keyword->id) != NULL) {
                Log::info('keyword = ' . print_r(Input::get($keyword->id), true));
                $temp = Input::get($keyword->id);
                $temp_setting = Keywords::find($keyword->id);
                $temp_setting->keyword = Input::get($keyword->id);
                $temp_setting->save();
            }
        }

        $keyword = Keywords::find(6);
        $keyword->alias = Input::get('total_trip');
        $keyword->save();

        $keyword = Keywords::find(7);
        $keyword->alias = Input::get('cancelled_trip');
        $keyword->save();

        $keyword = Keywords::find(8);
        $keyword->alias = Input::get('total_payment');
        $keyword->save();

        $keyword = Keywords::find(9);
        $keyword->alias = Input::get('completed_trip');
        $keyword->save();

        $keyword = Keywords::find(10);
        $keyword->alias = Input::get('card_payment');
        $keyword->save();

        $keyword = Keywords::find(11);
        $keyword->alias = Input::get('credit_payment');
        $keyword->save();

        return Redirect::to('/admin/edit_keywords?success=1');
    }

    public function save_keywords_UI() {
        $provider = Input::get('val_provider');
        $user = Input::get('val_user');
        $taxi = Input::get('val_taxi');
        $trip = Input::get('val_trip');
        $walk = Input::get('val_walk');
        $request = Input::get('val_request');


        $appfile = fopen(app_path() . "/lang/en/customize.php", "w") or die("Unable to open file!");
        $appfile_config = generate_custome_key($provider, $user, $taxi, $trip, $walk, $request);
        fwrite($appfile, $appfile_config);
        fclose($appfile);

        return Redirect::to('/admin/edit_keywords?success=1');
    }

    public function adminCurrency() {
        $currency_selected = $_POST['currency_selected'];
        $keycurrency = Keywords::find(5);
        $original_selection = $keycurrency->keyword;
        if ($original_selection == '$') {
            $original_selection = "USD";
        }
        if ($currency_selected == '$') {
            $currency_selected = "USD";
        }
        if ($currency_selected == $original_selection) {
            // same currency
            $data['success'] = false;
            $data['error_message'] = 'Same Currency.';
        } else {
            $httpAdapter = new \Ivory\HttpAdapter\FileGetContentsHttpAdapter();
            // Create the Yahoo Finance provider
            $yahooProvider = new \Swap\Provider\YahooFinanceProvider($httpAdapter);
            // Create Swap with the provider
            $swap = new \Swap\Swap($yahooProvider);
            $rate = $swap->quote($original_selection . "/" . $currency_selected);
            $rate = json_decode($rate, true);
            $data['success'] = true;
            $data['rate'] = $rate;
        }
        return $data;
    }

    public function save_settings() {
        $settings = Settings::all();
        foreach ($settings as $setting) {
            if (Input::get($setting->id) != NULL) {
                $temp_setting = Settings::find($setting->id);
                $temp_setting->value = Input::get($setting->id);
                $temp_setting->save();
            }
        }
        return Redirect::to('/admin/settings?success=1');
    }

//Installation Settings
    public function installation_settings() {
        $braintree_environment = Config::get('app.braintree_environment');
        $braintree_merchant_id = Config::get('app.braintree_merchant_id');
        $braintree_public_key = Config::get('app.braintree_public_key');
        $braintree_private_key = Config::get('app.braintree_private_key');
        $braintree_cse = Config::get('app.braintree_cse');
        $twillo_account_sid = Config::get('app.twillo_account_sid');
        $twillo_auth_token = Config::get('app.twillo_auth_token');
        $twillo_number = Config::get('app.twillo_number');
        $timezone = Config::get('app.timezone');
        $stripe_publishable_key = Config::get('app.stripe_publishable_key');
        $url = Config::get('app.url');
        $website_title = Config::get('app.website_title');
        $s3_bucket = Config::get('app.s3_bucket');
        $default_payment = Config::get('app.default_payment');
        $stripe_secret_key = Config::get('app.stripe_secret_key');
        $mail_driver = Config::get('mail.driver');
        $email_name = Config::get('mail.from.name');
        $email_address = Config::get('mail.from.address');
        $mandrill_secret = Config::get('services.mandrill.secret');
        $mandrill_username = Config::get('services.mandrill.username');
        $host = Config::get('mail.host');
        $install = array(
            'braintree_environment' => $braintree_environment,
            'braintree_merchant_id' => $braintree_merchant_id,
            'braintree_public_key' => $braintree_public_key,
            'braintree_private_key' => $braintree_private_key,
            'braintree_cse' => $braintree_cse,
            'twillo_account_sid' => $twillo_account_sid,
            'twillo_auth_token' => $twillo_auth_token,
            'twillo_number' => $twillo_number,
            'stripe_publishable_key' => $stripe_publishable_key,
            'stripe_secret_key' => $stripe_secret_key,
            'mail_driver' => $mail_driver,
            'email_address' => $email_address,
            'mandrill_username' => $mandrill_username,
            'email_name' => $email_name,
            'host' => $host,
            'mandrill_secret' => $mandrill_secret,
            'default_payment' => $default_payment);
        $success = Input::get('success');
        $cert_def = 0;
        $cer = Certificates::where('file_type', 'certificate')->where('client', 'apple')->get();
        foreach ($cer as $key) {
            if ($key->default == 1) {
                $cert_def = $key->type;
            }
        }
        return View::make('install_settings')
                        ->with('title', 'Installation Settings')
                        ->with('success', $success)
                        ->with('page', 'settings')
                        ->with('cert_def', $cert_def)
                        ->with('install', $install);
    }

    public function finish_install() {
        $braintree_environment = Config::get('app.braintree_environment');
        $braintree_merchant_id = Config::get('app.braintree_merchant_id');
        $braintree_public_key = Config::get('app.braintree_public_key');
        $braintree_private_key = Config::get('app.braintree_private_key');
        $braintree_cse = Config::get('app.braintree_cse');
        $twillo_account_sid = Config::get('app.twillo_account_sid');
        $twillo_auth_token = Config::get('app.twillo_auth_token');
        $twillo_number = Config::get('app.twillo_number');
        $timezone = Config::get('app.timezone');
        $stripe_publishable_key = Config::get('app.stripe_publishable_key');
        $url = Config::get('app.url');
        $website_title = Config::get('app.website_title');
        $s3_bucket = Config::get('app.s3_bucket');
        $default_payment = Config::get('app.default_payment');
        $stripe_secret_key = Config::get('app.stripe_secret_key');
        $mail_driver = Config::get('mail.driver');
        $email_name = Config::get('mail.from.name');
        $email_address = Config::get('mail.from.address');
        $mandrill_secret = Config::get('services.mandrill.secret');
        $host = Config::get('mail.host');
        $install = array(
            'braintree_environment' => $braintree_environment,
            'braintree_merchant_id' => $braintree_merchant_id,
            'braintree_public_key' => $braintree_public_key,
            'braintree_private_key' => $braintree_private_key,
            'braintree_cse' => $braintree_cse,
            'twillo_account_sid' => $twillo_account_sid,
            'twillo_auth_token' => $twillo_auth_token,
            'twillo_number' => $twillo_number,
            'stripe_publishable_key' => $stripe_publishable_key,
            'stripe_secret_key' => $stripe_secret_key,
            'mail_driver' => $mail_driver,
            'email_address' => $email_address,
            'email_name' => $email_name,
            'mandrill_secret' => $mandrill_secret,
            'default_payment' => $default_payment);        // Modifying Database Config
        if (isset($_POST['sms'])) {
            $twillo_account_sid = Input::get('twillo_account_sid');
            $twillo_auth_token = Input::get('twillo_auth_token');
            $twillo_number = Input::get('twillo_number');

            $appfile = fopen(app_path() . "/config/app.php", "w") or die("Unable to open file!");
            $appfile_config = generate_app_config($braintree_cse, $stripe_publishable_key, $url, $timezone, $website_title, $s3_bucket, $twillo_account_sid, $twillo_auth_token, $twillo_number, $default_payment, $stripe_secret_key, $braintree_environment, $braintree_merchant_id, $braintree_public_key, $braintree_private_key);
            fwrite($appfile, $appfile_config);
            fclose($appfile);
        }

        if (isset($_POST['payment'])) {
            $default_payment = Input::get('default_payment');

            if ($default_payment == 'stripe') {
                $stripe_secret_key = Input::get('stripe_secret_key');
                $stripe_publishable_key = Input::get('stripe_publishable_key');
                $braintree_environment = '';
                $braintree_merchant_id = '';
                $braintree_public_key = '';
                $braintree_private_key = '';
                $braintree_cse = '';
                $appfile = fopen(app_path() . "/config/app.php", "w") or die("Unable to open file!");
                $appfile_config = generate_app_config($braintree_cse, $stripe_publishable_key, $url, $timezone, $website_title, $s3_bucket, $twillo_account_sid, $twillo_auth_token, $twillo_number, $default_payment, $stripe_secret_key, $braintree_environment, $braintree_merchant_id, $braintree_public_key, $braintree_private_key);
                fwrite($appfile, $appfile_config);
                fclose($appfile);
            } else {
                $stripe_secret_key = '';
                $stripe_publishable_key = '';
                $braintree_environment = Input::get('braintree_environment');
                $braintree_merchant_id = Input::get('braintree_merchant_id');
                $braintree_public_key = Input::get('braintree_public_key');
                $braintree_private_key = Input::get('braintree_private_key');
                $braintree_cse = Input::get('braintree_cse');
                $appfile = fopen(app_path() . "/config/app.php", "w") or die("Unable to open file!");
                $appfile_config = generate_app_config($braintree_cse, $stripe_publishable_key, $url, $timezone, $website_title, $s3_bucket, $twillo_account_sid, $twillo_auth_token, $twillo_number, $default_payment, $stripe_secret_key, $braintree_environment, $braintree_merchant_id, $braintree_public_key, $braintree_private_key);
                fwrite($appfile, $appfile_config);
                fclose($appfile);
            }
        }

        // Modifying Mail Config File

        if (isset($_POST['mail'])) {
            $mail_driver = Input::get('mail_driver');
            $email_name = Input::get('email_name');
            $email_address = Input::get('email_address');
            $mandrill_secret = Input::get('mandrill_secret');
            $mandrill_hostname = "";
            if ($mail_driver == 'mail') {
                $mandrill_hostname = "localhost";
            } elseif ($mail_driver == 'mandrill') {
                $mandrill_hostname = Input::get('host_name');
            }
            $mailfile = fopen(app_path() . "/config/mail.php", "w") or die("Unable to open file!");
            $mailfile_config = generate_mail_config($mandrill_hostname, $mail_driver, $email_name, $email_address);
            fwrite($mailfile, $mailfile_config);
            fclose($mailfile);

            if ($mail_driver == 'mandrill') {
                $mandrill_username = Input::get('user_name');
                $servicesfile = fopen(app_path() . "/config/services.php", "w") or die("Unable to open file!");
                $servicesfile_config = generate_services_config($mandrill_secret, $mandrill_username);
                fwrite($servicesfile, $servicesfile_config);
                fclose($servicesfile);
            }
        }
        $install = array(
            'braintree_environment' => $braintree_environment,
            'braintree_merchant_id' => $braintree_merchant_id,
            'braintree_public_key' => $braintree_public_key,
            'braintree_private_key' => $braintree_private_key,
            'braintree_cse' => $braintree_cse,
            'twillo_account_sid' => $twillo_account_sid,
            'twillo_auth_token' => $twillo_auth_token,
            'twillo_number' => $twillo_number,
            'stripe_publishable_key' => $stripe_publishable_key,
            'stripe_secret_key' => $stripe_secret_key,
            'mail_driver' => $mail_driver,
            'email_address' => $email_address,
            'email_name' => $email_name,
            'mandrill_secret' => $mandrill_secret,
            'default_payment' => $default_payment);
        return Redirect::to('/admin/settings?success=1')
                        ->with('install', $install);
    }

    public function addcerti() {
        $count = 0;

        // apple user
        if (Input::hasFile('user_certi_a')) {
            $certi_user_a = Certificates::where('client', 'apple')->where('user_type', 0)->where('file_type', 'certificate')->where('type', Input::get('cert_type_a'))->first();
            if ($certi_user_a != NULL) {
                //user
                $path = $certi_user_a->name;
                Log::info($path);
                $filename = basename($path);
                Log::info($filename);
                if (file_exists($path)) {
                    unlink(public_path() . "/apps/ios_push/iph_cert/" . $filename);
                }
                $key = Certificates::where('client', 'apple')->where('user_type', 0)->where('file_type', 'certificate')->first();
            } else {
                $key = new Certificates();
                $key->client = 'apple';
                $key->type = Input::get('cert_type_a');
                $key->user_type = 0;
                $key->file_type = 'certificate';
            }
            // upload image
            $file_name = time();
            $file_name .= rand();
            $file_name = sha1($file_name);

            Log::info(Input::file('user_certi_a'));

            $ext = Input::file('user_certi_a')->getClientOriginalExtension();
            Input::file('user_certi_a')->move(public_path() . "/apps/ios_push/iph_cert", $file_name . "." . $ext);
            $local_url = $file_name . "." . $ext;

            // Upload to S3
            if (Config::get('app.s3_bucket') != "") {
                $s3 = App::make('aws')->get('s3');
                $pic = $s3->putObject(array(
                    'Bucket' => Config::get('app.s3_bucket'),
                    'Key' => $file_name,
                    'SourceFile' => public_path() . "/apps/ios_push/iph_cert/" . $local_url,
                ));
                $s3->putObjectAcl(array(
                    'Bucket' => Config::get('app.s3_bucket'),
                    'Key' => $file_name,
                    'ACL' => 'public-read'
                ));
                $s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name);
            }
            Log::info('path = ' . print_r($local_url, true));
            $key->name = $local_url;
            $count = $count + 1;
            $key->save();
        }

        // User passphrase file.
        if (Input::has('user_pass_a')) {
            $user_key_db = Certificates::where('client', 'apple')->where('user_type', 0)->where('file_type', 'passphrase')->where('type', Input::get('cert_type_a'))->first();
            if ($user_key_db == NULL) {
                $key = new Certificates();
                $key->client = 'apple';
                $key->type = Input::get('cert_type_a');
                $key->user_type = 0;
                $key->file_type = 'passphrase';
            } else {
                $key = Certificates::where('client', 'apple')->where('user_type', 0)->where('file_type', 'passphrase')->first();
            }
            $key->name = Input::get('user_pass_a');
            $count = $count + 1;
            $key->save();
        }

        // apple provider
        if (Input::hasFile('prov_certi_a')) {
            $certi_prov_a = Certificates::where('client', 'apple')->where('user_type', 1)->where('file_type', 'certificate')->where('type', Input::get('cert_type_a'))->first();
            if ($certi_prov_a != NULL) {
                //user
                $path = $certi_prov_a->name;
                Log::info($path);
                $filename = basename($path);
                Log::info($filename);
                unlink(public_path() . "/apps/ios_push/walker/iph_cert/" . $filename);
                $key = Certificates::where('client', 'apple')->where('user_type', 1)->where('file_type', 'certificate')->first();
            } else {
                $key = new Certificates();
                $key->client = 'apple';
                $key->type = Input::get('cert_type_a');
                $key->user_type = 1;
                $key->file_type = 'certificate';
            }
            // upload image
            $file_name = time();
            $file_name .= rand();
            $file_name = sha1($file_name);

            $ext = Input::file('prov_certi_a')->getClientOriginalExtension();
            Input::file('prov_certi_a')->move(public_path() . "/apps/ios_push/walker/iph_cert", $file_name . "." . $ext);
            $local_url = $file_name . "." . $ext;

            // Upload to S3
            if (Config::get('app.s3_bucket') != "") {
                $s3 = App::make('aws')->get('s3');
                $pic = $s3->putObject(array(
                    'Bucket' => Config::get('app.s3_bucket'),
                    'Key' => $file_name,
                    'SourceFile' => public_path() . "/apps/ios_push/walker/iph_cert/" . $local_url,
                ));
                $s3->putObjectAcl(array(
                    'Bucket' => Config::get('app.s3_bucket'),
                    'Key' => $file_name,
                    'ACL' => 'public-read'
                ));
            }
            Log::info('path = ' . print_r($local_url, true));
            $key->name = $local_url;
            $count = $count + 1;
            $key->save();
        }

        // Provider passphrase file.
        if (Input::has('prov_pass_a')) {
            $user_key_db = Certificates::where('client', 'apple')->where('user_type', 1)->where('file_type', 'passphrase')->where('type', Input::get('cert_type_a'))->first();
            if ($user_key_db == NULL) {
                $key = new Certificates();
                $key->client = 'apple';
                $key->type = Input::get('cert_type_a');
                $key->user_type = 1;
                $key->file_type = 'passphrase';
            } else {
                $key = Certificates::where('client', 'apple')->where('user_type', 1)->where('file_type', 'passphrase')->first();
            }
            $key->name = Input::get('prov_pass_a');
            $count = $count + 1;
            $key->save();
        }

        // gcm key file.
        if (Input::has('gcm_key')) {
            $gcm_key_db = Certificates::where('client', 'gcm')->first();
            if ($gcm_key_db == NULL) {
                $key = new Certificates();
                $key->client = 'gcm';
                $key->type = Input::get('cert_type_a');
                $key->user_type = 0;
                $key->file_type = 'browser_key';
            } else {
                $key = Certificates::where('client', 'gcm')->first();
            }
            $key->name = Input::get('gcm_key');
            $count = $count + 1;
            $key->save();
        }

        Log::info("count = " . print_r($count, true));

        $cert_def = Input::get('cert_default');
        $certa = Certificates::where('client', 'apple')->get();
        foreach ($certa as $ca) {
            $def = Certificates::where('id', $ca->id)->first();
            $def->default = 0;
            $def->save();
        }
        $certs = Certificates::where('client', 'apple')->where('type', $cert_def)->get();
        foreach ($certs as $defc) {
            $def = Certificates::where('id', $defc->id)->first();
            Log::info('def = ' . print_r($def, true));
            $def->default = 1;
            $def->save();
        }

        return Redirect::to('/admin/settings/installation?success=1');
    }

    //Sort Owners
    public function sortur() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'userid') {
            $typename = "Owner ID";
            $users = Owner::orderBy('id', $valu)->orderBy('created_at', 'DESC')->paginate(10);
        } elseif ($type == 'username') {
            $typename = "Owner Name";
            $users = Owner::orderBy('first_name', $valu)->orderBy('created_at', 'DESC')->paginate(10);
        } elseif ($type == 'useremail') {
            $typename = "Owner Email";
            $users = Owner::orderBy('email', $valu)->orderBy('created_at', 'DESC')->paginate(10);
        } elseif ($type == 'userdate') {
            $typename = "Date";
            $users = Owner::orderBy('created_at', $valu)->paginate(10);
        } 
        return View::make('owners')
                        ->with('title', 'Owners | Sorted by ' . $typename . ' in ' . $valu)
                        ->with('page', 'owners')
                        ->with('owners', $users);
    }

    public function sortpv() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'provid') {
            $typename = "Providers ID";
            $providers = Walker::orderBy('id', $valu)->paginate(10);
        } elseif ($type == 'pvname') {
            $typename = "Providers Name";
            $providers = Walker::orderBy('first_name', $valu)->paginate(10);
        } elseif ($type == 'pvemail') {
            $typename = "Providers Email";
            $providers = Walker::orderBy('email', $valu)->paginate(10);
        } elseif ($type == 'pvaddress') {
            $typename = "Providers Address";
            $providers = Walker::orderBy('address', $valu)->paginate(10);
        } elseif ($type == 'pvdate') {
            $typename = "Date";
            $providers = Walker::orderBy('created_at', $valu)->paginate(10);
        }
        return View::make('walkers')
                        ->with('title', 'Providers | Sorted by ' . $typename . ' in ' . $valu)
                        ->with('page', 'walkers')
                        ->with('walkers', $providers);
    }

    public function sortpvtype() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'provid') {
            $typename = "Providers Type ID";
            $providers = ProviderType::orderBy('id', $valu)->paginate(10);
        } elseif ($type == 'pvname') {
            $typename = "Providers Name";
            $providers = ProviderType::orderBy('name', $valu)->paginate(10);
        }
        return View::make('list_provider_types')
                        ->with('title', 'Provider Types | Sorted by ' . $typename . ' in ' . $valu)
                        ->with('page', 'list_provider_types')
                        ->with('types', $providers);
    }

    public function sortreq() {
        $valu = $_GET["valu"];
        $type = $_GET["type"];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'reqid') {
            $typename = "Request ID";
            $requests = DB::table('request')
                    ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                    ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                    ->groupBy('request.id')
                    ->select('owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                            , 'request.status', 'request.time',  'request.instruction','request.distance', 'request.total', 'request.is_cancelled', 'request.transfer_amount', 'request.payment_mode')
                    ->orderBy('request.id', $valu)
                    ->paginate(10);
        } elseif ($type == 'owner') {
            $typename = "Owner Name";
            $requests = DB::table('request')
                    ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                    ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                    ->groupBy('request.id')
                    ->select('owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                            , 'request.status', 'request.time', 'request.distance', 'request.instruction','request.total', 'request.is_cancelled', 'request.transfer_amount', 'request.payment_mode')
                    ->orderBy('owner.first_name', $valu)
                    ->paginate(10);
        } elseif ($type == 'walker') {
            $typename = "Provider Name";
            $requests = DB::table('request')
                    ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                    ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                    ->groupBy('request.id')
                    ->select('owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                            , 'request.status', 'request.time', 'request.instruction', 'request.distance', 'request.total', 'request.is_cancelled', 'request.transfer_amount', 'request.payment_mode')
                    ->orderBy('walker.first_name', $valu)
                    ->paginate(10);
        } elseif ($type == 'payment') {
            $typename = "Payment Mode";
            $requests = DB::table('request')
                    ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                    ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                    ->groupBy('request.id')
                    ->select('owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                            , 'request.status', 'request.time',  'request.instruction','request.distance', 'request.total', 'request.is_cancelled', 'request.transfer_amount', 'request.payment_mode')
                    ->orderBy('request.payment_mode', $valu)
                    ->paginate(10);
        } elseif ($type == 'current') {
             $typename = "Current Date";
             $date = date('Y-m-d h:i:s');
             $user_timezone = Config::get('app.timezone');
             $default_timezone = Config::get('app.timezone');

             $date_time = get_user_time($default_timezone, $user_timezone, $date);

              $requests = DB::table('request')
                    ->leftJoin('walker', 'request.current_walker', '=', 'walker.id')
                    ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                    ->where('request.created_at' ,">=" , "$date_time")
                    ->groupBy('request.id')
                    ->select('owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.is_started', 'request.is_walker_arrived', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                            , 'request.status', 'request.time',  'request.instruction','request.distance', 'request.total', 'request.is_cancelled', 'request.transfer_amount', 'request.payment_mode')
                    ->orderBy('request.created_at', $valu)
                    ->paginate(10);
        }
        $setting = Settings::find(37);
        return View::make('walks')
                        ->with('title', 'Requests | Sorted by ' . $typename . ' in ' . $valu)
                        ->with('page', 'walks')
                        ->with('walks', $requests)
                        ->with('setting', $setting);
    }

    public function sortpromo() {
        $valu = $_GET["valu"];
        $type = $_GET["type"];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'promoid') {
            $typename = "Promo Code ID";
            $promo_codes = DB::table('promo_codes')
                    ->orderBy('id', $valu)
                    ->paginate(10);
        } elseif ($type == 'promo') {
            $typename = "Promo Code";
            $promo_codes = DB::table('promo_codes')
                    ->orderBy('coupon_code', $valu)
                    ->paginate(10);
        } elseif ($type == 'uses') {
            $typename = "No Of Uses";
            $promo_codes = DB::table('promo_codes')
                    ->orderBy('uses', $valu)
                    ->paginate(10);
        }
        $setting = Settings::find(37);
        return View::make('list_promo_codes')
                        ->with('title', 'Promocodes | Sorted by ' . $typename . ' in ' . $valu)
                        ->with('page', 'Promo Codes')
                        ->with('promo_codes', $promo_codes)
                        ->with('setting', $setting);
    }

    public function searchpromo() {
        $valu = $_GET['valu'];
        $type = $_GET['type'];
        Session::put('valu', $valu);
        Session::put('type', $type);
        if ($type == 'promo_id') {
            $promo_codes = PromoCodes::where('id', $valu)->paginate(10);
        } elseif ($type == 'promo_name') {
            $promo_codes = PromoCodes::where('coupon_code', 'like', '%' . $valu . '%')->paginate(10);
        } elseif ($type == 'promo_type') {
            if ($valu == '%') {
                $promo_codes = PromoCodes::where('type', 1)->paginate(10);
            } elseif ($val = '$') {
                $promo_codes = PromoCodes::where('type', 2)->paginate(10);
            }
        } elseif ($type == 'promo_state') {
            if ($valu == 'active' || $valu == 'Active') {
                $promo_codes = PromoCodes::where('state', 1)->paginate(10);
            } elseif ($val = 'Deactivated' || $val = 'deactivated') {
                $promo_codes = PromoCodes::where('state', 2)->paginate(10);
            }
        }
        return View::make('list_promo_codes')
                        ->with('title', 'Promo Codes | Search Result')
                        ->with('page', 'Promo Codes')
                        ->with('promo_codes', $promo_codes);
    }

// Provider Availability

    public function allow_availability() {
        Settings::where('key', 'allowcal')->update(array('value' => 1));
        return Redirect::to("/admin/providers");
    }

    public function disable_availability() {
        Settings::where('key', 'allowcal')->update(array('value' => 0));
        return Redirect::to("/admin/providers");
    }

    public function availability_provider() {
        $id = Request::segment(4);
        $provider = Walker::where('id', $id)->first();
        if ($provider) {
            $success = Input::get('success');
            $pavail = ProviderAvail::where('provider_id', $id)->paginate(10);
            $prvi = array();
            foreach ($pavail as $pv) {
                $prv = array();
                $prv['title'] = 'available';
                $prv['start'] = date('Y-m-d', strtotime($pv->start)) . "T" . date('H:i:s', strtotime($pv->start));
                $prv['end'] = date('Y-m-d', strtotime($pv->end)) . "T" . date('H:i:s', strtotime($pv->end));
                ;
                array_push($prvi, $prv);
            }
            $pvjson = json_encode($prvi);
            Log::info('Provider availability json = ' . print_r($pvjson, true));
            return View::make('availability_provider')
                            ->with('title', 'Provider Availability')
                            ->with('page', 'availability_provider')
                            ->with('success', $success)
                            ->with('pvjson', $pvjson)
                            ->with('provider', $provider);
        } else {
            return View::make('admin.notfound')->with('title', 'Error Page Not Found')->with('page', 'Error Page Not Found');
        }
    }

    public function provideravailabilitySubmit() {
        $id = Request::segment(4);
        $proavis = $_POST['proavis'];
        $proavie = $_POST['proavie'];
        $length = $_POST['length'];
        Log::info('Start end time Array Length = ' . print_r($length, true));
        DB::delete("delete from provider_availability where provider_id = '" . $id . "';");
        for ($l = 0; $l < $length; $l++) {
            $pv = new ProviderAvail;
            $pv->provider_id = $id;
            $pv->start = $proavis[$l];
            $pv->end = $proavie[$l];
            $pv->save();
        }
        Log::info('providers availability start = ' . print_r($proavis, true));
        Log::info('providers availability end = ' . print_r($proavie, true));
        return Response::json(array('success' => true));
    }

    public function view_documents_provider() {
        $id = Request::segment(4);
        $provider = Walker::where('id', $id)->first();
        $provider_documents = WalkerDocument::where('walker_id', $id)->paginate(10);
        if ($provider) {
            return View::make('view_documents')
                            ->with('title', 'Provider View Documents')
                            ->with('page', 'view_documents')
                            ->with('docs', $provider_documents)
                            ->with('provider', $provider);
        } else {
            return View::make('admin.notfound')->with('title', 'Error Page Not Found')->with('page', 'Error Page Not Found');
        }
    }

    //Providers Who currently walking
    public function current() {
        Session::put('che', 'current');

        $walks = DB::table('request')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->select('walker.id as id', 'walker.first_name as first_name', 'walker.last_name as last_name', 'walker.phone as phone', 'walker.email as email', 'walker.picture as picture', 'walker.merchant_id as merchant_id', 'walker.bio as bio', 'request.total as total_requests', 'walker.is_approved as is_approved','walker.is_active','walker.is_available','walker.vehicle_no','walker.model_no')
                ->where('request.is_started', 1)
                ->where('request.is_completed', 0)
                ->paginate(10);
        return View::make('walkers')
                        ->with('title', 'Providers | Currently Providing')
                        ->with('page', 'walkers')
                        ->with('walkers', $walks);
    }

    public function theme() {
        $th = Theme::all()->count();

        if ($th == 1) {
            $theme = Theme::first();
        } else {
            $theme = new Theme;
        }

        $theme->theme_color = '#' . Input::get('color1');
        $theme->secondary_color = '#' . Input::get('color3');
        $theme->primary_color = '#' . Input::get('color2');
        $theme->hover_color = '#' . Input::get('color4');
        $theme->active_color = '#' . Input::get('color5');

        $css_msg = ".btn-default {
  color: #ffffff;
  background-color: $theme->theme_color;
}
.navbar-nav > li {
  float: left;
}
.btn-info{
    color: #000;
    background: #fff;
    border-radius: 0px;
    border:1px solid $theme->theme_color;
}
.nav-admin .dropdown :hover, .nav-admin .dropdown :hover {
    background: $theme->hover_color;
    color: #000;
}
.navbar-nav > li > a {
  border-radius: 0px;
}
.navbar-nav > li + li {
  margin-left: 2px;
}
.navbar-nav > li.active > a,
.navbar-nav> li.active > a:hover,
.navbar-nav > li.active > a:focus {
  color: #ffffff;
  background-color: $theme->active_color!important;
}
.logo_img_login{
border-radius: 30px;border: 4px solid $theme->theme_color;
}
.btn-success {
  color: #ffffff;
  background-color: $theme->theme_color;
  border-color: $theme->theme_color;
}
.btn-success:hover,
.btn-success:focus,
.btn-success:active,
.btn-success.active,
.open .dropdown-toggle.btn-success {
  color: #ffffff;
  background-color: $theme->theme_color;
  border-color: $theme->theme_color;

}


.btn-success.disabled,
.btn-success[disabled],
fieldset[disabled] .btn-success,
.btn-success.disabled:hover,
.btn-success[disabled]:hover,
fieldset[disabled] .btn-success:hover,
.btn-success.disabled:focus,
.btn-success[disabled]:focus,
fieldset[disabled] .btn-success:focus,
.btn-success.disabled:active,
.btn-success[disabled]:active,
fieldset[disabled] .btn-success:active,
.btn-success.disabled.active,
.btn-success[disabled].active,
fieldset[disabled] .btn-success.active {

  background-color: $theme->theme_color;
  border-color: $theme->theme_color;
}
.btn-success .badge {
  color: $theme->theme_color;
  background-color: #ffffff;
}
.btn-info {
  color: #ffffff;
  background-color: $theme->theme_color;
  border-color: $theme->theme_color;
}
.btn-info:hover,
.btn-info:focus,
.btn-info:active,
.btn-info.active,
.open .dropdown-toggle.btn-info {
  color: #000;
  background-color: #FFFF;
  border-color: $theme->theme_color;
}
.btn-info:active,
.btn-info.active,
.open .dropdown-toggle.btn-info {
  background-image: none;
}
.btn-info.disabled,
.btn-info[disabled],
fieldset[disabled] .btn-info,
.btn-info.disabled:hover,
.btn-info[disabled]:hover,
fieldset[disabled] .btn-info:hover,
.btn-info.disabled:focus,
.btn-info[disabled]:focus,
fieldset[disabled] .btn-info:focus,
.btn-info.disabled:active,
.btn-info[disabled]:active,
fieldset[disabled] .btn-info:active,
.btn-info.disabled.active,
.btn-info[disabled].active,
fieldset[disabled] .btn-info.active {
  background-color: $theme->theme_color;
  border-color: $theme->theme_color;
}
.btn-info .badge {
  color: $theme->theme_color;
  background-color: #029acf;
  border-color: #029acf;
}
.btn-success,
.btn-success:hover {
  background-image: -webkit-linear-gradient($theme->theme_color $theme->theme_color 6%, $theme->theme_color);
  background-image: linear-gradient($theme->theme_color, $theme->theme_color 6%, $theme->theme_color);
  background-repeat: no-repeat;
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='$theme->theme_color', endColorstr='$theme->theme_color', GradientType=0);
  filter: none;
  border: 1px solid $theme->theme_color;
}
.btn-info,
.btn-info:hover {
  background-image: -webkit-linear-gradient($theme->theme_color, $theme->theme_color 6%, $theme->theme_color);
  background-image: linear-gradient($theme->theme_color, $theme->theme_color 6%, $theme->theme_color);
  background-repeat: no-repeat;
  filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='$theme->theme_color', endColorstr='$theme->theme_color', GradientType=0);
  filter: none;
  border: 1px solid $theme->theme_color;
}
.logo h3{
    margin: 0px;
    color: $theme->theme_color;
}

.second-nav{
    background: $theme->theme_color;
}
.login_back{background-color: $theme->theme_color;}
.no_radious:hover{background-image: -webkit-linear-gradient($theme->theme_color, $theme->theme_color 6%, $theme->theme_color);background-image: linear-gradient(#5d4dd1, #5d4dd1 6%, #5d4dd1);background-repeat: no-repeat;filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#5d4dd1', endColorstr='#5d4dd1', GradientType=0);filter: none;border: 1px solid #5d4dd1;}
.navbar-nav li:nth-child(1) a{
    background: $theme->primary_color;
}

.navbar-nav li:nth-child(2) a{
    background: $theme->secondary_color;
}

.navbar-nav li:nth-child(3) a{
    background: $theme->primary_color;
}

.navbar-nav li:nth-child(4) a{
    background: $theme->secondary_color;
}

.navbar-nav li:nth-child(5) a{
    background: $theme->primary_color;
}

.navbar-nav li:nth-child(6) a{
    background: $theme->secondary_color;
}

.navbar-nav li:nth-child(7) a{
    background: $theme->primary_color;
}

.navbar-nav li:nth-child(8) a{
    background: $theme->secondary_color;
}

.navbar-nav li:nth-child(9) a{
    background: $theme->primary_color;
}

.navbar-nav li:nth-child(10) a{
    background: $theme->secondary_color;
}

.navbar-nav li a:hover{
    background: $theme->hover_color;
}
.btn-green{

    background: $theme->theme_color;
    color: #fff;
}
.btn-green:hover{
    background: $theme->hover_color;
    color: #fff;
}
";
        $t = file_put_contents(public_path() . '/stylesheet/theme_cus.css', $css_msg);

        if (Input::hasFile('logo')) {
            // Upload File
            $file_name = time();
            $file_name .= rand();
            $ext = Input::file('logo')->getClientOriginalExtension();

            Input::file('logo')->move(public_path() . "/uploads", $file_name . "." . $ext);
            $local_url = $file_name . "." . $ext;

            $new = Image::make(public_path() . "/uploads/" . $local_url)->resize(70, 70)->save();

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
            $theme->logo = $local_url;
        }

        if (Input::hasFile('icon')) {
            // Upload File
            $file_name1 = time();
            $file_name1 .= rand();
            $file_name1 .= 'icon';
            $ext1 = Input::file('icon')->getClientOriginalExtension();
            Input::file('icon')->move(public_path() . "/uploads", $file_name1 . "." . $ext1);
            $local_url1 = $file_name1 . "." . $ext1;

            // Upload to S3
            if (Config::get('app.s3_bucket') != "") {
                $s3 = App::make('aws')->get('s3');
                $pic = $s3->putObject(array(
                    'Bucket' => Config::get('app.s3_bucket'),
                    'Key' => $file_name1,
                    'SourceFile' => public_path() . "/uploads/" . $local_url1,
                ));

                $s3->putObjectAcl(array(
                    'Bucket' => Config::get('app.s3_bucket'),
                    'Key' => $file_name1,
                    'ACL' => 'public-read'
                ));

                $s3_url1 = $s3->getObjectUrl(Config::get('app.s3_bucket'), $file_name1);
            } else {
                $s3_url1 = asset_url() . '/uploads/' . $local_url1;
            }
            $theme->favicon = $local_url1;
        }
        $theme->save();
        return Redirect::to("/admin/settings");
    }

    public function transfer_amount() {
        $request = Requests::where('id', Input::get('request_id'))->first();
        $walker = Walker::where('id', $request->confirmed_walker)->first();
        $amount = Input::get("amount");

        if (($amount + $request->transfer_amount) <= $request->total && ($amount + $request->transfer_amount) > 0) {
            if (Config::get('app.default_payment') == 'stripe') {
                try{
                    if($walker->merchant_id != '') {
                        Stripe::setApiKey(Config::get('app.stripe_secret_key'));
                        // dd($amount$request->transfer_amount);
                        $transfer = Stripe_Transfer::create(array(
                                "amount" => $amount * 100, // amount in cents
                                "currency" => "usd",
                                "recipient" => $walker->merchant_id)
                        );
                    }else{
                        $flashErrorMessage = 'Stripe payment error: Merchant Id can\'t be empty';
                        Log::info($flashErrorMessage);
                        return Redirect::back()
                                        ->with('flash_error', $flashErrorMessage)
                                        ->with('request', $request)
                                        ->with('title', 'Transfer amount')
                                        ->with('page', 'walkers');
                    }
                }
                catch(Exception $stripeError){
                    $flashErrorMessage = 'Stripe payment error: '.$stripeError->getMessage();
                    Session::put('flash_error', "Sessions working");
                    Log::info($flashErrorMessage);
                    return Redirect::back()
                                    ->with('flash_error', $flashErrorMessage)
                                    ->with('request', $request)
                                    ->with('title', 'Transfer amount')
                                    ->with('page', 'walkers');
                }
            } else {
                Braintree_Configuration::environment(Config::get('app.braintree_environment'));
                Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
                Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
                Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
                $payment_data = Payment::where('owner_id', $request->owner_id)->first();
                $customer_id = $payment_data->customer_id;
                $result = Braintree_Transaction::sale(
                                array(
                                    'merchantAccountId' => $walker->merchant_id,
                                    'paymentMethodNonce' => $customer_id,
                                    'options' => array(
                                        'submitForSettlement' => true,
                                        'holdInEscrow' => true,
                                    ),
                                    'amount' => $amount
                                )
                );
            }
            $request->transfer_amount += $amount;
            $request->save();
            return Redirect::to("/admin/requests");
        } else {
            Session::put('error', "Amount exceeds the total amount to be paid");
            return View::make('transfer_amount')
                            ->with('request', $request)
                            ->with('title', 'Transfer amount')
                            ->with('page', 'walkers');
        }
    }

    public function pay_provider($id) {
        $request = Requests::find($id);
        $walker = Walker::where('id',$request->confirmed_walker)->first();
        /*Check the Driver exists in db because the driver may be deleted softly*/
        if($walker) {
            /*Check the MerchantId exists for the Driver or not*/
            if ($walker->merchant_id != '') {
                if (Config::get('app.default_payment') == 'stripe') {
                    return View::make('transfer_amount')
                        ->with('request', $request)
                        ->with('title', 'Transfer amount')
                        ->with('page', 'walks');
                } else {
                    $this->_braintreeConfigure();
                    $clientToken = Braintree_ClientToken::generate();
                    Session::put('error', 'Manual Transfer is not available in braintree.');
                    return View::make('transfer_amount')
                        ->with('request', $request)
                        ->with('clientToken', $clientToken)
                        ->with('title', 'Transfer amount')
                        ->with('page', 'walks');
                }
            } else {
                return Redirect::to('/admin/provider/banking/' . $walker->id)->with('flash_error', 'Please add bank details of the driver to transfer amount');
            }
        }else{
            return Redirect::back()->with('flash_error','Driver not found!');
        }
    }

    public function charge_user($id) {
        $request = Requests::find($id);
        Log::info('Charge User from admin');
        $total = $request->total;
        $payment_data = Payment::where('owner_id', $request->owner_id)->first();
        $owner = Owner::find($request->owner_id);
        $walker_data = Walker::find($request->confirmed_walker);
        if($walker_data) {
            $merchant_id = $walker_data->merchant_id;
        } else {
            $merchant_id = NULL;
        }

        if($payment_data) 
        {
            $customer_id = $payment_data->customer_id;
            $setransfer = Settings::where('key', 'transfer')->first();

            $transfer_allow = $setransfer->value;
            if (Config::get('app.default_payment') == 'stripe') {

                Stripe::setApiKey(Config::get('app.stripe_secret_key'));
                try {
                    $charge = Stripe_Charge::create(array(
                                "amount" => $total * 100,
                                "currency" => "usd",
                                "customer" => $customer_id)
                    );
                    Log::info('charge stripe = ' . print_r($charge, true));
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
                if ($transfer_allow == 1 && $merchant_id != NULL) {
                    $transfer = Stripe_Transfer::create(array(
                                "amount" => ($total - $settng->value) * 100, // amount in cents
                                "currency" => "usd",
                                "recipient" => $walker_data->merchant_id)
                    );
                    $request->transfer_amount = ($total - $settng->value);
                }
            } else {
                try {
                    Braintree_Configuration::environment(Config::get('app.braintree_environment'));
                    Braintree_Configuration::merchantId(Config::get('app.braintree_merchant_id'));
                    Braintree_Configuration::publicKey(Config::get('app.braintree_public_key'));
                    Braintree_Configuration::privateKey(Config::get('app.braintree_private_key'));
                    if ($transfer_allow == 1 && $merchant_id != NULL) {
                        $sevisett = Settings::where('key', 'service_fee')->first();
                        $service_fee = $sevisett->value;
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
                    Log::info('result of braintree = ' . print_r($result, true));
                    if ($result->success) {
                        $request->is_paid = 1;
                    } else {
                        $request->is_paid = 0;
                    }
                } catch (Exception $e) {
                    Log::info('error in braintree payment = ' . print_r($e, true));
                }
            }
            $request->card_payment = $total;
            $request->ledger_payment = $request->total - $total;
            $request->save();
            return Redirect::to('/admin/requests');
        } else {
            Session::put('msg', 'The '." ".$owner->first_name." ".$owner->last_name .' owner doesnt have payment account');
            return Redirect::to('/admin/requests');    
        }
    }

    public function add_request() {
        Log::info('add request from admin panel.');
        $owner_id = Request::segment(3);
        $owner = Owner::find($owner_id);
        $services = ProviderType::all();
        $total_services = ProviderType::count();
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
        $settdestination = Settings::where('key', 'get_destination')->first();
        $settdestination = $settdestination->value;
        return View::make('add_request')
                        ->with('owner', $owner)
                        ->with('services', $services)
                        ->with('total_services', $total_services)
                        ->with('payment_option', $payment_options)
                        ->with('settdestination', $settdestination)
                        ->with('title', 'Add Request')
                        ->with('page', 'walks');
    }

  
    public function payment_details() {
        $braintree_environment = Config::get('app.braintree_environment');
        $braintree_merchant_id = Config::get('app.braintree_merchant_id');
        $braintree_public_key = Config::get('app.braintree_public_key');
        $braintree_private_key = Config::get('app.braintree_private_key');
        $braintree_cse = Config::get('app.braintree_cse');
        $twillo_account_sid = Config::get('app.twillo_account_sid');
        $twillo_auth_token = Config::get('app.twillo_auth_token');
        $twillo_number = Config::get('app.twillo_number');
        $stripe_publishable_key = Config::get('app.stripe_publishable_key');
        $default_payment = Config::get('app.default_payment');
        $stripe_secret_key = Config::get('app.stripe_secret_key');
        $mail_driver = Config::get('mail.mail_driver');
        $email_name = Config::get('mail.from.name');
        $email_address = Config::get('mail.from.address');
        $mandrill_secret = Config::get('services.mandrill_secret');
        $install = array(
            'braintree_environment' => $braintree_environment,
            'braintree_merchant_id' => $braintree_merchant_id,
            'braintree_public_key' => $braintree_public_key,
            'braintree_private_key' => $braintree_private_key,
            'braintree_cse' => $braintree_cse,
            'twillo_account_sid' => $twillo_account_sid,
            'twillo_auth_token' => $twillo_auth_token,
            'twillo_number' => $twillo_number,
            'stripe_publishable_key' => $stripe_publishable_key,
            'stripe_secret_key' => $stripe_secret_key,
            'mail_driver' => $mail_driver,
            'email_address' => $email_address,
            'email_name' => $email_name,
            'mandrill_secret' => $mandrill_secret,
            'default_payment' => $default_payment);
        $start_date = Input::get('start_date');
        $end_date = Input::get('end_date');
        $submit = Input::get('submit');
        $walker_id = Input::get('walker_id');
        $owner_id = Input::get('owner_id');
        $status = Input::get('status');

        $start_time = date("Y-m-d H:i:s", strtotime($start_date));
        $end_time = date("Y-m-d H:i:s", strtotime($end_date));
        $start_date = date("Y-m-d", strtotime($start_date));
        $end_date = date("Y-m-d", strtotime($end_date));

        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
                ->orderBy('request.id', 'desc');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('owner_id') && Input::get('owner_id') != 0) {
            $query = $query->where('request.owner_id', '=', $owner_id);
        }

        if (Input::get('status') && Input::get('status') != 0) {
            if ($status == 1) {
                $query = $query->where('request.is_completed', '=', 1);
            } else {
                $query = $query->where('request.is_cancelled', '=', 1);
            }
        } else {

            $query = $query->where(function ($que) {
                $que->where('request.is_completed', '=', 1)
                        ->orWhere('request.is_cancelled', '=', 1);
            });
        }

        $walks = $query->select('request.request_start_time', 'walker_type.name as type', 'request.ledger_payment', 'request.card_payment', 'owner.first_name as owner_first_name', 'owner.last_name as owner_last_name', 'walker.first_name as walker_first_name', 'walker.last_name as walker_last_name', 'owner.id as owner_id', 'walker.id as walker_id', 'request.id as id', 'request.created_at as date', 'request.*', 'request.is_walker_arrived', 'request.payment_mode', 'request.is_completed', 'request.is_paid', 'request.is_walker_started', 'request.confirmed_walker'
                , 'request.status', 'request.time', 'request.distance', 'request.total', 'request.is_cancelled');
        $walks = $walks->paginate(10);

        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
                ->orderBy('request.id', 'desc');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('owner_id') && Input::get('owner_id') != 0) {
            $query = $query->where('request.owner_id', '=', $owner_id);
        }

        $completed_rides = $query->where('request.is_completed', 1)->count();


        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
                ->orderBy('request.id', 'desc');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('owner_id') && Input::get('owner_id') != 0) {
            $query = $query->where('request.owner_id', '=', $owner_id);
        }
        $cancelled_rides = $query->where('request.is_cancelled', 1)->count();


        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
                ->orderBy('request.id', 'desc');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('owner_id') && Input::get('owner_id') != 0) {
            $query = $query->where('request.owner_id', '=', $owner_id);
        }
        $card_payment = $query->where('request.is_completed', 1)->sum('request.card_payment');

        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
                 ->orderBy('request.id', 'desc');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('owner_id') && Input::get('owner_id') != 0) {
            $query = $query->where('request.owner_id', '=', $owner_id);
        }
        $cash_payment = $query->where('request.is_completed', 1)->where('request.payment_mode' , '=' , 1)->sum('request.total');


        $query = DB::table('request')
                ->leftJoin('owner', 'request.owner_id', '=', 'owner.id')
                ->leftJoin('walker', 'request.confirmed_walker', '=', 'walker.id')
                ->leftJoin('walker_type', 'walker.type', '=', 'walker_type.id')
                ->orderBy('request.id', 'desc');

        if (Input::get('start_date') && Input::get('end_date')) {
            $query = $query->where('request_start_time', '>=', $start_time)
                    ->where('request_start_time', '<=', $end_time);
        }

        if (Input::get('walker_id') && Input::get('walker_id') != 0) {
            $query = $query->where('request.confirmed_walker', '=', $walker_id);
        }

        if (Input::get('owner_id') && Input::get('owner_id') != 0) {
            $query = $query->where('request.owner_id', '=', $owner_id);
        }
        $credit_payment = $query->where('request.is_completed', 1)->sum('request.ledger_payment');


        if (Input::get('submit') && Input::get('submit') == 'Download_Report') {

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=data.csv');
            $handle = fopen('php://output', 'w');
            fputcsv($handle, array('ID', 'Date', 'Type of Service', 'Provider', 'Owner', 'Distance (Miles)', 'Time (Minutes)', 'Earning', 'Ledger Payment', 'Card Payment'));

            foreach ($walks as $request) {
                fputcsv($handle, array(
                    $request->id,
                    date('l, F d Y h:i A', strtotime($request->request_start_time)),
                    $request->type,
                    $request->walker_first_name . " " . $request->walker_last_name,
                    $request->owner_first_name . " " . $request->owner_last_name,
                    $request->distance,
                    $request->time,
                    $request->total,
                    $request->ledger_payment,
                    $request->card_payment,
                    $request->cash_payment,

                ));
            }

            fputcsv($handle, array());
            fputcsv($handle, array());
            fputcsv($handle, array('Total Trips', $completed_rides + $cancelled_rides));
            fputcsv($handle, array('Completed Trips', $completed_rides));
            fputcsv($handle, array('Cancelled Trips', $cancelled_rides));
            fputcsv($handle, array('Total Payments', $credit_payment + $card_payment));
            fputcsv($handle, array('Card Payment', $card_payment));
            fputcsv($handle, array('Credit Payment', $credit_payment));
            fputcsv($handle, array('Cash Payment', $cash_payment));

            fclose($handle);

            $headers = array(
                'Content-Type' => 'text/csv',
            );
        } else {
            $currency_selected = Keywords::where('alias', 'Currency')->first();
            $currency_sel = $currency_selected->keyword;
            $walkers = Walker::paginate(10);
            $owners = Owner::paginate(10);
            $payment_default = ucfirst(Config::get('app.default_payment'));
            return View::make('payment')
                            ->with('title', 'Payments')
                            ->with('page', 'payments')
                            ->with('walks', $walks)
                            ->with('owners', $owners)
                            ->with('walkers', $walkers)
                            ->with('completed_rides', $completed_rides)
                            ->with('cancelled_rides', $cancelled_rides)
                            ->with('card_payment', $card_payment)
                            ->with('cash_payment', $cash_payment)
                            ->with('install', $install)
                            ->with('currency_sel', $currency_sel)
                            ->with('credit_payment', $credit_payment)
                            ->with('payment_default', $payment_default);
        }
    }

    public function push_noti(){

        $walk_started =Input::get('walking_started');
        $walk_completed =Input::get('walk_completed');
        $walk_arrived =Input::get('walk_arrived');
        $walking_started=Input::get('walking_started');
        $user_request_cancelled=Input::get('user_request_cancelled');
        $accepted_friend=Input::get('accepted_friend');
        $rejected_friend=Input::get('rejected_friend');
        $pay_for_friend=Input::get('pay_for_friend');
        $create_request=Input::get('create_request');
        

    }

    public function roles(){

        $roles = Roles::get();
        return View::make('roles')
                        ->with('title', 'Roles')
                        ->with('page', 'roles')
                        ->with('roles', $roles);

    }

    public function add_role(){
        $roles = Roles::all();
        return View::make('add_roling')
                        ->with('title', 'Add Roles')
                        ->with('page', 'add_roles')
                        ->with('roles', $roles);

    }
    public function role_saving(){
        $name =Input::get('name');
        $roles = new Roles;
        $roles->name =$name;
        $roles->save();
        
        $role_action=new RoleAction;
        $sub_role_actions = new SubRoleAction;
        $role_action->role_id=$roles->id;
        if(Input::get('dash')){
            $role_action->dash=1;
        }if(Input::get('map')){
            $role_action->map=1;
        }if(Input::get('prov')){
            $role_action->prov=1;
            $provi = Input::get('provi');
            if($provi){
                $pr ="";
                foreach ($provi as $p) {
                    $pr .= $p .",";
                }
                $pr = rtrim($pr,',');
                $sub_role_actions->provider =$pr;
                $role_action->actions=1;     
            }
           

        }if(Input::get('req')){
            $role_action->req=1;
            $reqi = Input::get('reqi');
            if($reqi){
                $rq ="";
                foreach ($reqi as $r) {
                    $rq .= $r .",";
                }
                $rq = rtrim($rq,',');
                $sub_role_actions->request=$rq;
                $role_action->actions=1; 
            }
           
        }if(Input::get('usr')){
            $role_action->user=1;
            $useri= Input::get('usri');
            if($useri){
                 $us ="";
                foreach ($useri as $u) {
                    $us .= $u .",";
                }
                $us = rtrim($us,',');
                $sub_role_actions->user=$us;
                $role_action->actions=1;
            }
           
        }if(Input::get('rev')){
            $role_action->review=1;

            $revi =Input::get('revi');
            if($revi){
                $sub_role_actions->review=$revi;
            }
            /*foreach ($revi as $r) {
                $rv .= $r .",";
            }
            $rv = rtrim($rv,',');
           
            $role_action->actions=1;*/
        }if(Input::get('sett')){
            $role_action->setting=1;
        }if(Input::get('info')){
            $role_action->info=1;
        }if(Input::get('typ')){
            $role_action->type=1;

            $revi =Input::get('revi');
            if($revi){
                $sub_role_actions->review=$revi;
            }
            
        }if(Input::get('doc')){
            $role_action->doc=1;
            $doci = Input::get('doci');
            if($doci){
                $do ="";
                foreach ($doci as $d) {
                    $do .= $d .",";
                }
                $do = rtrim($do,',');
                $sub_role_actions->doc=$do;
                $role_action->actions=1;    
            }

        }if(Input::get('promo')){
            $role_action->promo=1;
            $promoi = Input::get('promoi');
            if($promoi){
                $pr ="";
                foreach ($promoi as $p) {
                    $pr .= $p .",";
                }
                $pr = rtrim($pr,',');
                $sub_role_actions->promo=$pr;
                $role_action->actions=1;    
            }
            
        }if(Input::get('customize')){
            $role_action->customize=1;
        }if(Input::get('payment')){
            $role_action->payment=1;
            $paymenti =Input::get('paymenti');
           
            if($paymenti){
                $sub_role_actions->payment=$paymenti;
                $role_action->actions=1;
            }
           
        }
        if(Input::get('role')){
            $role_action->role=1;
        }

        if(Input::get('manual_assign')){
            $role_action->manual_assign=1;
        }

        $sub_role_actions->role_id = $roles->id;
        $sub_role_actions->save();
        
        $role_action->save();
        return Redirect::back();
    }

    public function edit_roles(){

        $id = Request::segment(3);
      
        $role_action = RoleAction::where('role_id',$id)->first();
        $sub_role_action =SubRoleAction::where('role_id',$id)->first();
        //dd($sub_action->dash);

        return View::make('edit_role')
                        ->with('title', 'Edit Role')
                        ->with('page', 'roles')
                        ->with('role_id',$id)
                        ->with('role_action',$role_action)
                        ->with('sub_role_action',$sub_role_action);
        
    }

    public function save_edit_roles(){
        $role_id= Input::get('role_id');
        $role_action = RoleAction::where('role_id',$role_id)->first();
        $sub_role_actions =  SubRoleAction::where('role_id',$role_id)->first();

        //$role_action->role_id=$roles->id;
        if(Input::get('dash')){
            $role_action->dash = 1;
        }else{
            $role_action->dash = 0;
        }

        if(Input::get('map')){
            $role_action->map = 1;
        }else{
            $role_action->map = 0;
        }

        if(Input::get('prov')){
            $role_action->prov = 1;
            $provi = Input::get('provi');
            if($provi){
                $pr ="";
                foreach ($provi as $p) {
                    $pr .= $p .",";
                }
                $pr = rtrim($pr,',');
                $sub_role_actions->provider =$pr;
                $role_action->actions=1;     
            }
        }else{
            $role_action->prov = 0;
            $sub_role_actions->provider = "";
        }

        if(Input::get('req')){
            $role_action->req = 1;
            $reqi = Input::get('reqi');
            if($reqi){
                $rq ="";
                foreach ($reqi as $r) {
                    $rq .= $r .",";
                }
                $rq = rtrim($rq,',');
                $sub_role_actions->request=$rq;
                $role_action->actions=1; 
            }   
        }else{
            $role_action->req = 0;
            $sub_role_actions->request= "";
        }

        if(Input::get('usr')){
            $role_action->user = 1;
            $useri= Input::get('usri');
            if($useri){
                 $us ="";
                foreach ($useri as $u) {
                    $us .= $u .",";
                }
                $us = rtrim($us,',');
                $sub_role_actions->user=$us;
                $role_action->actions=1;
            }
        }else{
            $role_action->user = 0;
            $sub_role_actions->user = "";
        }

        if(Input::get('rev')){
            $role_action->review=1;
            $revi =Input::get('revi');
            if($revi){
                $sub_role_actions->review=$revi;
                $role_action->actions=1;
            }
        }else{
            $role_action->review = 0;
            $sub_role_actions->review= "";

        }

        if(Input::get('sett')){
            $role_action->setting = 1;
        }else{
            $role_action->setting = 0;
        }
        
        if(Input::get('info')){
            $role_action->info= 1;
        }else{
            $role_action->info= 0;
        }
        
        if(Input::get('typ')){
            $role_action->type= 1;
            $typi =Input::get('typi');
            if($typi){
                $sub_role_actions->type=$typi;
                $role_action->actions=1;
            }
        }else{
            $role_action->type= 0;
            $sub_role_actions->type= "";
        }

        if(Input::get('doc')){
            $role_action->doc= 1;
            $doci =Input::get('doci');
            if($doci){
                $do ="";
                foreach ($doci as $d) {
                    $do .= $d .",";
                }
                $do = rtrim($do,',');
                $sub_role_actions->doc=$do;
                $role_action->actions=1;    
            }
        }else{
            $role_action->doc= 0;
            $sub_role_actions->doc= "";
        }

        if(Input::get('promo')){
            $role_action->promo= 1;
            $promoi = Input::get('promoi');
            if($promoi){
                $pr ="";
                foreach ($promoi as $p) {
                    $pr .= $p .",";
                }
                $pr = rtrim($pr,',');
                $sub_role_actions->promo=$pr;
                $role_action->actions=1;    
            }  
        }else{
            $role_action->promo= 0;
            $sub_role_actions->promo = "";
        }
        
        if(Input::get('customize')){
            $role_action->customize = 1;
        }else{
            $role_action->customize = 0;
        }
        
        if(Input::get('payment')){
            $role_action->payment=1;
            $paymenti =Input::get('paymenti');
            
            if($paymenti){
                $sub_role_actions->payment=$paymenti;
                $role_action->actions=1;
            }
           
        }else{
            $role_action->payment = 0;
            $sub_role_actions->payment = "";
        }

        //$sub_role_actions->role_id = $roles->id;
        $sub_role_actions->save();
        
        if(Input::get('role')){
            $role_action->role = 1;
        }else{
            $role_action->role = 0;
        }

        if(Input::get('manual_assign')){
         
            $role_action->manual_assign= 1;
        }else{
            $role_action->manual_assign= 0;
        }

        $role_action->save();
        return Redirect::back();

    }

    public function delete_roles(){
        $id = Request::segment(3);
        $roles = Roles::where('id',$id)->delete();
        return Redirect::back();
    }

    public function create_manual_assign(){
        $owners = Owner::all();
        return View::make('manual_assign')
                        ->with('title', 'Manual Assign')
                        ->with('page', 'manuals')
                        ->with('owner',$owners);
    }

    public function manual_assign(){

        $owner_id=Input::get('owner_id');
        $payment_mode=Input::get('payment_mode');
        $promo_code =Input::get('promo_code');
        $source_address =Input::get('source_address');
        $destination_address=Input::get('destination_address');
        $d_latitude=Input::get('d_latitude');
        $d_longitude =Input::get('d_longitude');
        $type = Input::get('type');
        $distance =Input::get('distance');
        $latitude =Input::get('latitude');
        $longitude=Input::get('longitude');
        $instruction =Input::get('instruction');
        if(!$instruction){
            $instruction="";
        }
        if (!$type) {
            // choose default type
            $provider_type = ProviderType::where('is_default', 1)->first();

            if (!$provider_type) {
                $type = 1;
            } else {
                $type = $provider_type->id;
            }
        }

        $typequery = "SELECT distinct provider_id from walker_services where type IN($type)";
        $typewalkers = DB::select(DB::raw($typequery));

        Log::info('typewalkers = ' . print_r($typewalkers, true));

        if (count($typewalkers) > 0) {

            foreach ($typewalkers as $key) {

                $types[] = $key->provider_id;
            }

            $typestring = implode(",", $types);
            Log::info('typestring = ' . print_r($typestring, true));

        } else {
            $driver = Keywords::where('id', 1)->first();
            send_notifications($owner_id, "owner", 'No ' . $driver->keyword . ' Found', 'No ' . $driver->keyword . ' found matching the service type.');

            $response_array = array('success' => false, 'error' => 'No ' . $driver->keyword . ' found matching the service type.', 'error_code' => 416);
            $response_code = 200;
            return Response::json($response_array, $response_code);

        }

        $settings = Settings::where('key', 'default_search_radius')->first();
        $distance = $settings->value;
        $query = "SELECT walker.*, 1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) as distance from walker where is_available = 1 and is_active = 1 and is_approved = 1 and (1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance and walker.deleted_at IS NULL and walker.id IN($typestring) order by distance";

        $walkers = DB::select(DB::raw($query));
        $walker_list = array();

        $owner = Owner::find($owner_id);
       /* $owner->latitude = $latitude;
        $owner->longitude = $longitude;
        $owner->save();*/

        $request = new Requests;
        $request->owner_id = $owner_id;
        $request->instruction = $instruction;
        $request->destination_address = $destination_address;
        $request->source_address = $source_address;
        
        if (Input::has('payment_mode')) {
            $request->payment_mode = Input::get('payment_mode');
        }
        
        if (Input::has('promo_code')) {
            $pcode = PromoCodes::where('coupon_code', Input::get('promo_code'))->first();

            if($pcode){
                // promo history
                $promohistory = PromoHistory::where('user_id',$owner_id)->where('promo_code',Input::get('promo_code'))->first();
                if(!$promohistory){
                    $promo_code = $pcode->id;
                    $request->promo_code = $promo_code;
                    if ($pcode->uses == 1) {
                        $pcode->state = 3;
                    }
                    $pcode->uses = $pcode->uses - 1;
                    $pcode->save();
                    $phist = new PromoHistory();
                    $phist->user_id = $owner_id;
                    $phist->promo_code = Input::get('promo_code');
                    $phist->amount_earned = $pcode->value;
                    $phist->save();
                    if($pcode->type==2){
                        // Absolute discount
                        // Add to ledger amount
                        $led = Ledger::where('owner_id',$owner_id)->first();
                        if($led){
                            $led->amount_earned = $led->amount_earned + $pcode->value;
                            $led->save();
                        }else{
                            $ledger = new Ledger();
                            $ledger->owner_id = $owner_id;
                            $ledger->referral_code = "0";
                            $ledger->total_referrals = 0;
                            $ledger->amount_earned = $pcode->value;
                            $ledger->amount_spent = 0;
                            $ledger->save();
                        }
                    }
                }else{
                    $response_array = array('success' => false, 'Promo Code already Used', 'error_code' => 425);
                    $response_code = 200;
                    return Response::json($response_array, $response_code);
                }
            }else{
                $response_array = array('success' => false, 'Invalid Promo Code', 'error_code' => 415);
                $response_code = 200;
                return Response::json($response_array, $response_code);
            }
        }

        $user_timezone = $owner->timezone;
        $default_timezone = Config::get('app.timezone');
        $offset = $this->get_timezone_offset($default_timezone, $user_timezone);

        if (isset($d_latitude)) {
            $request->D_latitude = Input::get('d_latitude');
        }
        if (isset($d_longitude)) {
            $request->D_longitude = Input::get('d_longitude');
        }
        $request->request_start_time = date("Y-m-d H:i:s");
        $request->save();

        $reqserv = new RequestServices;
        $reqserv->request_id = $request->id;
        $reqserv->type = $type;
        $reqserv->save();
       
        $i = 0;
        $first_walker_id = 0;
        foreach ($walkers as $walker) {
            $request_meta = new RequestMeta;
            $request_meta->request_id = $request->id;
            $request_meta->walker_id = $walker->id;
            if ($i == 0) {
                $first_walker_id = $walker->id;
                $i++;
            }
            $request_meta->save();
        }
        $req = Requests::find($request->id);
        $req->current_walker = $first_walker_id;
        $req->save();

        $settings = Settings::where('key', 'provider_timeout')->first();
        $time_left = $settings->value;

        // Send Notification
        $walker = Walker::find($first_walker_id);
        if ($walker) {
            $msg_array = array();
            $msg_array['unique_id'] = 1;
            $msg_array['request_id'] = $request->id;
            $msg_array['time_left_to_respond'] = $time_left;

            if (Input::has('payment_mode')) {
                $msg_array['payment_mode'] = Input::get('payment_mode');
            }

            $owner = Owner::find($owner_id);
            $request_data = array();
            $request_data['owner'] = array();
            $request_data['owner']['name'] = $owner->first_name . " " . $owner->last_name;
            $request_data['owner']['picture'] = $owner->picture;
            $request_data['owner']['phone'] = $owner->phone;
            $request_data['owner']['address'] = $owner->address;
            $request_data['owner']['latitude'] = $owner->latitude;
            $request_data['owner']['longitude'] = $owner->longitude;
            if ($d_latitude != NULL) {
                $request_data['owner']['d_latitude'] = $d_latitude;
                $request_data['owner']['d_longitude'] = $d_longitude;
            }
            $request_data["owner"]["destination_address"] = $destination_location;
            $request_data["owner"]["source_address"]=$source_location;
            $request_data['owner']['rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->avg('rating') ?: 0;
            $request_data['owner']['num_rating'] = DB::table('review_dog')->where('owner_id', '=', $owner->id)->count();
            $msg_array['request_data'] = $request_data;

            $title = "New Request";
            $message = $msg_array;
            Log::info('response = ' . print_r($message, true));
            Log::info('first_walker_id = ' . print_r($first_walker_id, true));
            Log::info('New request = ' . print_r($message, true));
            /* don't do json_encode in above line because if */
            send_notifications($first_walker_id, "walker", $title, $message);
        } else {
            Log::info('No provider found in your area');

            $driver = Keywords::where('id', 1)->first();
            send_notifications($owner_id, "owner", 'No ' . $driver->keyword . ' Found', 'No ' . $driver->keyword . ' found for the selected service in your area currently');

            $response_array = array('success' => false, 'error' => 'No ' . $driver->keyword . ' found for the selected service in your area currently', 'error_code' => 415);
            $response_code = 200;
            return Response::json($response_array, $response_code);
        }
        // Send SMS 
        $settings = Settings::where('key', 'sms_request_created')->first();
        $pattern = $settings->value;
        $pattern = str_replace('%user%', $owner_data->first_name . " " . $owner_data->last_name, $pattern);
        $pattern = str_replace('%id%', $request->id, $pattern);
        $pattern = str_replace('%user_mobile%', $owner_data->phone, $pattern);
        sms_notification(1, 'admin', $pattern);

        // send email
        $settings = Settings::where('key', 'email_new_request')->first();
        $pattern = $settings->value;
        $pattern = str_replace('%id%', $request->id, $pattern);
        $pattern = str_replace('%url%', web_url() . "/admin/request/map/" . $request->id, $pattern);
        $subject = "New Request Created";
        email_notification(1, 'admin', $pattern, $subject);

        if($request->id) {
            $response_array = array(
            'success' => true,
            'request_id' => $request->id,
            );
            $response_code = 200;   
        } else {
            $response_array = ['success' => false , 'error' => 'Request ID Not Found' , 'error_code' => 210];
            $response_code = 200;
        }
        $response = Response::json($response_array, $response_code);
        return $response;        

    }


    public function advance_trips(){
        $owners = Owner::all();
        return View::make('advance_trips')
                        ->with('title', 'Advance Trip')
                        ->with('page', 'advance_trips')
                        ->with('owner',$owners);
    }

    public function save_advance_trips(){
        
        $owner_id = Input::get('owner_id');
    
        $date_time = Input::get('datetime');
        $instruction = Input::get('instruction');
        $type =Input::get('type');
        $d_latitude = Input::get('d_latitude');
        $d_longitude = Input::get('d_longitude');
        $source_address =Input::get('source_address');
        $destination_address =Input::get('destination_address');
        $payment_mode=Input::get('payment_mode');
        $distance=1;

        // dd(date('Y-m-d h:i:s', strtotime("$date_time + 2 hours")));


        $validator = Validator::make(
            array(
              
                'owner_id' => $owner_id,
               
                'datetime' => $date_time,
                'd_longitude'=>$d_longitude,
                'd_latitude'=>$d_latitude,
                'source_address'=>$source_address,
                'destination_address'=>$destination_address,
                'type'=>'required'
            ), array(
              
                'owner_id' => 'required|integer',
              
                'datetime' => 'required',
                'd_longitude'=>'required',
                'd_latitude'=>'required',
                'source_address'=>'required',
                'destination_address'=>'required',
                'type'=>'required'
            )
        );

        if ($validator->fails()) {
            $error_messages = $validator->messages()->all();
            $response_array = array('success' => false, 'error' => 'Invalid Input', 'error_code' => 401, 'error_messages' => $error_messages);
            $response_code = 200;
        } else {
            $owner_data = Owner::where('id',$owner_id)->first();
            if ($owner_data->debt > 0) {

                $response_array = array('success' => false, 'error' => "You are already in \$$owner->debt debt", 'error_code' => 417);
                $response_code = 200;
                $response = Response::json($response_array, $response_code);
                return $response;
            }

            $owner = Owner::find($owner_id);
            
            $request = new Requests;
            $request->destination_address = $destination_address;
            $request->source_address = $source_address;
            $request->owner_id = $owner_id;
            if($instruction){
                $request->instruction = $instruction;
            }else{
                $request->instruction = "";
            }
            
            $request->request_start_time = $date_time;
            $request->later = 1;
            if (Input::has('cod')) {
                if (Input::get('cod') == 1) {
                    $request->cod = 1;
                } else {
                    $request->cod = 0;
                }
            }

            if (isset($d_latitude)) {
                $request->D_latitude = Input::get('d_latitude');
            }
            if (isset($d_longitude)) {
                $request->D_longitude = Input::get('d_longitude');
            }
            if($instruction){
                    $request->instruction = $instruction;

            }
            $request->request_start_time = $date_time;
            $request->payment_mode=$payment_mode;
            $request->source_address=$source_address;
            $request->destination_address=$destination_address;
            $request->distance=$distance;
            
            
            $request->save();

            $reqserv = new RequestServices;
            $reqserv->request_id = $request->id;
            $reqserv->type = $type;
            
            $reqserv->save();
        
            
            // Send SMS 
            $settings = Settings::where('key', 'sms_request_created')->first();
            $pattern = $settings->value;
            $pattern = str_replace('%user%', $owner_data->first_name . " " . $owner_data->last_name, $pattern);
            $pattern = str_replace('%id%', $request->id, $pattern);
            $pattern = str_replace('%user_mobile%', $owner_data->phone, $pattern);
            sms_notification(1, 'admin', $pattern);

            // send email
            $settings = Settings::where('key', 'email_new_request')->first();
            $pattern = $settings->value;
            $pattern = str_replace('%id%', $request->id, $pattern);
            $pattern = str_replace('%url%', web_url() . "/admin/request/map/" . $request->id, $pattern);
            $subject = "New Request Created";
            email_notification(1, 'admin', $pattern, $subject);

            $response_array = array(
                'success' => true,
                'request_id' => $request->id,
            );
            $response_code = 200;
        }

        $response = Response::json($response_array, $response_code);
        return $response;
   }


}
