<!DOCTYPE html>
<html>
    <!-- START Head -->
    <head>
    <?php $theme = Theme::all();?>
        <!-- START META SECTION -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="author" content="pampersdry.info">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
        <title><?= $title ?> | <?= Config::get('app.website_title') ?> Web Dashboard</title>
        
        <?php 
            $active='#000066';
            $logo = '/image/logo.png';
            $favicon='/image/favicon.ico';
            foreach($theme as $themes) {
                $active = $themes->active_color; 
                $favicon = '/uploads/'.$themes->favicon;
                $logo= '/uploads/'.$themes->logo;
            }
            if($logo=='/uploads/')
            {
                $logo = '/image/logo.png';
            }
            if($favicon=='/uploads/')
            {
                $favicon='/image/favicon.ico';
            }
        ?>


        <link rel="icon" type="image/ico" href="<?php echo asset_url(); ?><?php echo $favicon;?>">

        <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="//code.ionicframework.com/ionicons/1.5.2/css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="<?php echo asset_url(); ?>/admins/css/AdminLTE.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo asset_url(); ?>/admins/css/custom-admin.css" rel="stylesheet" type="text/css" />



        <link href="/admins/css/bootstrap-datetimepicker.min.css" rel="stylesheet">

        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.3/jquery-ui.js"></script>
        <script src="<?php echo asset_url(); ?>/admins/js/validator/jquery.validate.min.js"></script>


        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->

        <style type="text/css">
            .error{
                color:red;
            }
        </style>
    </head>
    <?php $role_id = Auth::user()->role_id; ?>
    <?php /*$subadmin =SubAdmin::where('admin_id',Auth::user()->id)->first();*/ 
    $roleaction =RoleAction::where('role_id',Auth::user()->roles)->first();
    ?>

    <body class="skin-blue" >
        <!-- header logo: style can be found in header.less -->
        <header class="header">
         <a  class="logo" href="{{ URL::Route('AdminMapview') }}" >
                <!-- Add the class icon to your logo image or logo icon to add the margining -->
                <img src="<?php echo asset_url(); ?><?php echo $logo; ?>"  width="40" height="40"> <?php  $siteTitle = Config::get('app.website_title'); echo $siteTitle;  ?>
            </a>
            <!-- Header Navbar: style can be found in header.less -->
                   <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <div class="navbar-right">
                    <ul class="nav navbar-nav">
            
                        <!-- User Account: style can be found in dropdown.less -->
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="glyphicon glyphicon-user"></i>
                                <span>Admin</span>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if($role_id == 0){ ?>
                                <!-- Menu Body -->
                                <li class="user-body">
                                    <div class="col-xs-12 text-center">
                                        <a href="{{ URL::Route('AdminAdmins') }}">Admin Control</a>
                                    </div>
                                    
                                </li>
                                <?php } ?>
                                <!-- Menu Footer-->
                                <li class="user-footer">
                                    <div class="col-md-12">
                                        <a class="btn btn-default btn-flat btn-block" href="{{ URL::Route('AdminLogout') }}">Log Out</a>
                                    </div>
                                   
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>

        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="left-side sidebar-offcanvas">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <ul class="sidebar-menu">
                     
                    <?php if($role_id == 0) { ?>

                        <li id="dashboard" title="Dashboard">
                            <a href="{{ URL::Route('AdminReport') }}"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
                        </li>

                        <li id="map-view" title="Map View">
                            <a href="{{ URL::Route('AdminMapview') }}"><i class="fa fa-map-marker"></i> <span>Map View</span></a>
                        </li>

                        <li id="walkers" title="Providers" >
                            <a href="{{ URL::Route('AdminProviders') }}"><i class="fa fa-users"></i> <span>{{trans('customize.Provider').'s'; }}</span></a>
                        </li> 
                        <li id="walks" title="Requests">
                            <a href="{{ URL::Route('AdminRequests') }}"><i class="fa fa-location-arrow"></i> <span>{{ trans('customize.Request').'s'; }}</span></a>
                        </li>
                        <li id="owners" title="Users">
                            <a href="{{ URL::Route('AdminUsers') }}"><i class="fa fa-users"></i> <span>{{ trans('customize.User').'s'; }}</span></a>
                        </li>
                        <li id="reviews" title="Reviews">
                            <a href="{{ URL::Route('AdminReviews') }}"><i class="fa fa-thumbs-o-up"></i> <span>Reviews</span></a>
                        </li>
                        <li id="settings" title="Setings">
                            <a href="{{ URL::Route('AdminSettings') }}"><i class="fa fa-cogs"></i> <span>Settings</span></a>
                        </li>
                        <li id="information" title="Information">
                            <a href="{{ URL::Route('AdminInformations') }}"><i class="fa fa-info-circle"></i> <span>Information</span></a>
                        </li>
                        <li id="provider-type" title="Provider Types">
                            <a href="{{ URL::Route('AdminProviderTypes') }}"><i class="fa fa-tags"></i> <span>Types</span></a>
                        </li>
                        <li id="document-type" title="Provider Types">
                            <a href="{{ URL::Route('AdminDocumentTypes') }}"><i class="fa fa-file-text-o"></i> <span>Documents</span></a>
                        </li>
                        <li id="promo_code" title="Promo Code">
                            <a href="{{ URL::Route('AdminPromoCodes') }}"><i class="fa fa-gift"></i> <span>Promo Codes</span></a>
                        </li>
                        <li id="keywords" title="Kewords">
                            <a href="{{ URL::Route('AdminKeywords') }}"><i class="fa fa-pencil-square"></i> <span>Customize</span></a>
                        </li>
                        <li id="payments" title="Payment Details">
                            <a href="{{ URL::Route('AdminPayment') }}"><i class="fa fa-pencil-square"></i> <span>Payment Details</span></a>
                        </li>
                        <li id="roles" title="Roles">
                            <a href="{{ URL::Route('AdminRoles') }}"><i class="fa fa-pencil-square"></i> <span>Admin Roles</span></a>
                        </li>

                        <li id="roles" title="Roles">
                            <a href="{{ URL::Route('create_manual_assign') }}"><i class="fa fa-pencil-square"></i> <span>Manual Assign</span></a>
                        </li>

                      <!--   <li id="roles" title="Roles">
                            <a href="{{ URL::Route('create_manual_assign') }}"><i class="fa fa-pencil-square"></i> <span> Advance trips</span></a>
                        </li> -->

                     
                   <?php }elseif($role_id==2){ 
                        if($roleaction->dash==1){?>
                        <li id="dashboard" title="Dashboard">
                            <a href="{{ URL::Route('AdminReport') }}"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a>
                        </li>
                        <?php }if($roleaction->map==1) { ?>
                        <li id="map-view" title="Map View">
                            <a href="{{ URL::Route('AdminMapview') }}"><i class="fa fa-map-marker"></i> <span>Map View</span></a>
                        </li>
                        <?php }if($roleaction->prov==1) { ?>
                         <li id="walkers" title="Providers" >
                            <a href="{{ URL::Route('AdminProviders') }}"><i class="fa fa-users"></i> <span>Providers</span></a>
                        </li> 
                        <?php }if($roleaction->req==1) { ?>
                        <li id="walks" title="Requests">
                            <a href="{{ URL::Route('AdminRequests') }}"><i class="fa fa-location-arrow"></i> <span>Requests</span></a>
                        </li>
                        <?php }if($roleaction->user==1) { ?>
                         <li id="owners" title="Users">
                            <a href="{{ URL::Route('AdminUsers') }}"><i class="fa fa-users"></i> <span>Users</span></a>
                        </li>
                         <?php }if($roleaction->review==1) { ?>
                         <li id="reviews" title="Reviews">
                            <a href="{{ URL::Route('AdminReviews') }}"><i class="fa fa-thumbs-o-up"></i> <span>Reviews</span></a>
                        </li>
                        <?php }if($roleaction->setting==1) { ?>
                        <li id="settings" title="Setings">
                            <a href="{{ URL::Route('AdminSettings') }}"><i class="fa fa-cogs"></i> <span>Settings</span></a>
                        </li>
                        <?php }if($roleaction->info==1) { ?>
                        <li id="information" title="Information">
                            <a href="{{ URL::Route('AdminInformations') }}"><i class="fa fa-info-circle"></i> <span>Information</span></a>
                        </li>
                        <?php }if($roleaction->type==1) { ?>
                         <li id="provider-type" title="Provider Types">
                            <a href="{{ URL::Route('AdminProviderTypes') }}"><i class="fa fa-tags"></i> <span>Types</span></a>
                        </li>
                        <?php }if($roleaction->doc==1) { ?>
                        <li id="document-type" title="Provider Types">
                            <a href="{{ URL::Route('AdminDocumentTypes') }}"><i class="fa fa-file-text-o"></i> <span>Documents</span></a>
                        </li>
                         <?php }if($roleaction->promo==1) { ?>
                         <li id="promo_code" title="Promo Code">
                            <a href="{{ URL::Route('AdminPromoCodes') }}"><i class="fa fa-gift"></i> <span>Promo Codes</span></a>
                        </li>
                        <?php }if($roleaction->customize==1) { ?>
                        <li id="keywords" title="Kewords">
                            <a href="{{ URL::Route('AdminKeywords') }}"><i class="fa fa-pencil-square"></i> <span>Customize</span></a>
                        </li>

                         <?php }if($roleaction->payment==1) { ?>
                         <li id="payments" title="Payment Details">
                            <a href="{{ URL::Route('AdminPayment') }}"><i class="fa fa-gift"></i> <span>Payment</span></a>
                        </li>
                         <?php }if($roleaction->role==1) { ?>
                         <li id="promo_code" title="Promo Code">
                            <a href="{{ URL::Route('AdminRoles') }}"><i class="fa fa-gift"></i> <span>Roles</span></a>
                        </li>
                           <?php }if($roleaction->manual_assign==1) { ?>
                         <li id="promo_code" title="Promo Code">
                            <a href="{{ URL::Route('create_manual_assign') }}"><i class="fa fa-gift"></i> <span>Manual Assign</span></a>
                        </li>


                       
                      
                        <?php } } ?>
                    </ul>
                    
                </section>
                <!-- /.sidebar -->
            </aside>

            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">

                  <section class="content-header">
                    <h1>
                       <?= $title ?>
                       
                    </h1>
                
                </section>
 
                <!-- Main content -->
                <section class="content">
                    @yield('content')
                </section><!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->

        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js" type="text/javascript"></script>
        <!-- AdminLTE App -->
        <script src="<?php echo asset_url(); ?>/admins/js/AdminLTE/app.js" type="text/javascript"></script>

        <!-- AdminLTE for demo purposes -->
        <script src="<?php echo asset_url(); ?>/admins/js/AdminLTE/demo.js" type="text/javascript"></script>

        <script type="text/javascript">
            $("#<?= $page ?>").addClass("active");
            $('#option3').show();
            $('.fade').css('opacity', '1');
            $('.nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus').css('color', '#ffffff');
            $('.nav-pills > li.active > a, .nav-pills > li.active > a:hover, .nav-pills > li.active > a:focus').css('background-color', '<?php echo $active;?>');
        </script>
 <script type="text/javascript" src="http://momentjs.com/downloads/moment.js" ></script>

 <script type="text/javascript" src="/admins/js/bootstrap-datetimepicker.min.js" ></script>



    <!--     <script>
            $(function() {

                $( "#start-date").datepicker({
                    defaultDate: "+1w",
                    changeMonth: true,
                    numberOfMonths: 1,
                    onClose: function( selectedDate ) {
                        $( "#end-date" ).datepicker( "option", "minDate", selectedDate );
                    }
                });
                $( "#end-date" ).datepicker({
                    defaultDate: "+1w",
                    changeMonth: true,
                    numberOfMonths: 1,
                    onClose: function( selectedDate ) {
                        $( "#start-date" ).datepicker( "option", "maxDate", selectedDate );
                    }
                });
            });
        </script> -->
        <script type="text/javascript">
            $(document).ready(function(){
                $("#myModal").modal('show');
            });
        </script>

        
        <script type="text/javascript">
          $(document).ready(function(){
            $(function () {
                $('#datetimepicker1').datetimepicker({ 
                  minDate: new Date() });
            });
         });

        </script>

    </body>
    <!--/ END Body -->
</html>