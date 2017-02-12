@extends('layout')

@section('content')
    @include('flash_msg')
    <script src="https://bitbucket.org/pellepim/jstimezonedetect/downloads/jstz-1.0.4.min.js"></script>
    <script src="http://momentjs.com/downloads/moment.min.js"></script>
    <script src="http://momentjs.com/downloads/moment-timezone-with-data.min.js"></script> 

@if (Session::has('msg'))
<h4 class="alert alert-danger">
    {{{ Session::get('msg') }}}
    {{{Session::put('msg',NULL)}}}
</h4>
@endif
    <div class="row">
            <div class="col-md-6 col-sm-12">

                    <div class="box box-danger">

                         <form method="get" action="{{ URL::Route('/admin/sortreq') }}">
                                <div class="box-header">
                                    <h3 class="box-title">Sort</h3>
                                </div>
                                <div class="box-body row">

                                <div class="col-md-6 col-sm-12">

                                     <select class="form-control" id="sortdrop" name="type">
                                    <option value="reqid" <?php if(isset($_GET['type']) && $_GET['type']=='reqid') {echo 'selected="selected"';}?>  id="reqid">Request ID</option>
                                    <option value="current" <?php if(isset($_GET['type']) && $_GET['type']=='current') {echo 'selected="selected"';}?>  id="current">Current Date</option>
                                    <option value="owner" <?php if(isset($_GET['type']) && $_GET['type']=='owner') {echo 'selected="selected"';}?>  id="owner">{{ trans('customize.User');}} Name</option>
                                    <option value="walker" <?php if(isset($_GET['type']) && $_GET['type']=='walker') {echo 'selected="selected"';}?>  id="walker">{{ trans('customize.Provider');}}</option>
                                     <option value="payment" <?php if(isset($_GET['type']) && $_GET['type']=='payment') {echo 'selected="selected"';}?>  id="payment">Payment Mode</option>
                                    </select>

                                    <br>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <select class="form-control" id="sortdroporder" name="valu">
                                    <option value="asc" <?php if(isset($_GET['type']) && $_GET['valu']=='asc') {echo 'selected="selected"';}?>  id="asc">Ascending</option>
                                    <option value="desc" <?php if(isset($_GET['type']) && $_GET['valu']=='desc') {echo 'selected="selected"';}?>  id="desc">Descending</option>
                                    </select>

                                    <br>
                                </div>

                                </div>

                                <div class="box-footer">

                                        <button type="submit" id="btnsort" class="btn btn-flat btn-block btn-success">Sort</button>

                                        
                                </div>
                        </form>

                    </div>
                </div>


                <div class="col-md-6 col-sm-12">

                    <div class="box box-danger">

                        <form method="get" action="{{ URL::Route('/admin/searchreq') }}">
                                <div class="box-header">
                                    <h3 class="box-title">Filter</h3>
                                </div>
                                <div class="box-body row">

                                <div class="col-md-6 col-sm-12">

                                    <select class="form-control" id="searchdrop" name="type">
                                    <option value="reqid" id="reqid">Request ID</option>
                                    <option value="owner" id="owner">{{ trans('customize.User');}} Name</option>
                                    <option value="walker" id="walker">{{ trans('customize.Provider');}}</option>
                                    <option value="payment" id="payment">Payment Mode</option>
                                    </select>

                                    <br>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                
                                    <input class="form-control" type="text" name="valu" value="<?php if(Session::has('valu')){echo Session::get('valu');} ?>" id="insearch" placeholder="keyword"/>
                                    <br>
                                </div>

                                </div>

                                <div class="box-footer">
                                  
                                        <button type="submit" id="btnsearch" class="btn btn-flat btn-block btn-success">Search</button>

                                        
                                </div>
                        </form>

                    </div>
                </div>
            </div>


                <div class="box box-info tbl-box">
                <div align="left" id="paglink"><?php echo $walks->appends(array('type'=>Session::get('type'), 'valu'=>Session::get('valu')))->links(); ?></div>
                

                <?php $role_id=Auth::user()->role_id;?>
                <?php /*$role_actions =SubAdmin::where('admin_id',Auth::user()->id)->first();*/
                $role_actions =RoleAction::where('role_id',Auth::user()->roles)->first(); ?>

                <table class="table table-bordered">
                                <tbody>
                                        <tr>
                                            <th>Request ID</th>
                                            <th>{{ trans('customize.User');}} Name</th>
                                            <th>{{ trans('customize.Provider');}}</th>
                                            <th>Date/Time</th>
                                            <th>Instruction</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                            <th>Payment Mode</th>
                                            <th>Payment Status</th>
                                            <th>Pick Up Location</th>
                                            <th>Drop Location</th>
                                            <th>Trip Duration</th>
                                            <th>Trip Distance</th>
                                            <?php if(($role_id == 0) || ($role_id==2 && $role_actions->actions==1) ) { ?>
                                                <th>Actions</th>
                                            <?php } ?>
                                        </tr>
                                        <?php $i =0; ?>

                                        <?php foreach ($walks as $walk) {?>
                        <tr>
                            <td><?= $walk->id ?></td>
                            <td><?php echo $walk->owner_first_name." ".$walk->owner_last_name; ?> </td>
                            <td>
                            <?php 
                            if($walk->confirmed_walker)
                            {
                             echo $walk->walker_first_name." ".$walk->walker_last_name; 
                            }
                            else{
                            echo "Un Assigned";
                            }
                            ?>
                            </td>
                            <td >
                            <?= $walk->date ?>
                            </td>
                            <td><?= $walk->instruction ?></td>
                            <td>
                                <?php 
                                if($walk->is_cancelled == 1) {
                                    echo "<span class='badge bg-red'>Cancelled</span>";
                                }
                                elseif ($walk->is_completed == 1) {
                                    echo "<span class='badge bg-green'>Completed</span>";
                                }
                                elseif ($walk->is_started == 1) {
                                    echo "<span class='badge bg-yellow'>Started</span>";
                                }
                                elseif ($walk->is_walker_arrived == 1) {
                                    echo "<span class='badge bg-yellow'>Walker Arrived</span>";
                                }
                                elseif ($walk->is_walker_started == 1) {
                                    echo "<span class='badge bg-yellow'>Walker Started</span>";
                                }

                                else{
                                    echo "<span class='badge bg-light-blue'>Yet To Start</span>";
                                }
                                ?>
                            </td>
                            <td>
                            <?php echo $walk->total; ?>
                            </td>
                            <td>
                                <?php 
                                if ($walk->payment_mode == 0) {
                                    echo "<span class='badge bg-orange'>Stored Cards</span>";
                                }
                                elseif ($walk->payment_mode == 1) {
                                    echo "<span class='badge bg-blue'>Pay by Cash</span>";
                                }
                                elseif ($walk->payment_mode == 2) {
                                    echo "<span class='badge bg-purple'>Paypal</span>";
                                }
                                elseif ($walk->payment_mode == 3) {
                                    echo "<span class='badge bg-green'>Pay by Friend</span>";
                                }
                                
                                ?>
                            </td>
                            <td>
                                <?php 
                                if ($walk->is_paid == 1) {
                                    echo "<span class='badge bg-green'>Completed</span>";
                                }
                                elseif ($walk->is_paid == 0 && $walk->is_completed == 1) {
                                    echo "<span class='badge bg-red'>Pending</span>";
                                }
                                else {
                                    echo "<span class='badge bg-yellow'>Request Not Completed</span>";
                                }
                                
                                ?>
                            </td>
                            <td><?php echo $walk->source_address ?></td>
                            <td><?php echo $walk->destination_address ?></td>
                            <td>{{$walk->time}}</td>
                            <td>{{$walk->distance}}</td>
                            <?php if(($role_id == 0) || ($role_id==2 && $role_actions->actions==1) ) { ?>
                            <td>
                              <div class="dropdown">
                                  <button class="btn btn-flat btn-info dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown">
                                    Actions
                                    <span class="caret"></span>
                                  </button>
                                  <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                                  <?php if($role_id == 0){ ?>
                                   <li role="presentation"><a role="menuitem" id="map" tabindex="-1" href="{{ URL::Route('AdminRequestsMap', $walk->id) }}">View Map</a></li>
                                   @if($setting->value==1 && $walk->is_completed==1)
                                        <li role="presentation"><a role="menuitem" id="map" tabindex="-1" href="{{ URL::Route('AdminPayProvider', $walk->id) }}">Transfer Amount</a></li>
                                    @endif

                                     @if($walk->is_paid==0 && $walk->is_completed==1 && $walk->payment_mode!=1 && $walk->total!=0)
                                        <li role="presentation"><a role="menuitem" id="map" tabindex="-1" href="{{ URL::Route('AdminChargeUser', $walk->id) }}">Charge {{ trans('customize.User');}}</a></li>
                                    @endif




                                    <?php }else{?>
                                     
                                    <?php 
                                        $sub_action = SubRoleAction::where('role_id',Auth::user()->roles)->first();
                                        $request_action = $sub_action->request;

                                    ?>

                                    <?php if (strpos($request_action, '1') !== false) {
                                    ?>   
                                    <li role="presentation"><a role="menuitem" id="map" tabindex="-1" href="{{ URL::Route('AdminRequestsMap', $walk->id) }}">View Map</a></li>
                                    <?php }?>

                                    <?php if (strpos($request_action, '2') !== false) {
                                    ?>
                                    @if($setting->value==1 && $walk->is_completed==1)
                                        <li role="presentation"><a role="menuitem" id="map" tabindex="-1" href="{{ URL::Route('AdminPayProvider', $walk->id) }}">Transfer Amount</a></li>
                                    @endif
                                    <?php }?>
                                    @if($walk->is_paid==0 && $walk->is_completed==1 && $walk->payment_mode!=1 && $walk->total!=0)
                                        <li role="presentation"><a role="menuitem" id="map" tabindex="-1" href="{{ URL::Route('AdminChargeUser', $walk->id) }}">Charge {{ trans('customize.User');}}</a></li>
                                    @endif


                                    
                                    <!--
                                    <li role="presentation" class="divider"></li>
                                    <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo web_url(); ?>/admin/walk/delete/<?= $walk->id; ?>">Delete Walk</a></li>
                                    -->
                                
                                    @if($walk->is_cancelled!=1 || $walk->is_completed!=1)
                                    <?php if (strpos($request_action, '3') !== false) { ?>
                                      <li role="presentation"><a role="menuitem" id="cancel" tabindex="-1" href="{{ URL::Route('AdminCancelRequest', $walk->id) }}">Cancel Request</a></li>
                                    <?php } ?>
                                  @endif
                                  <?php } ?>
                                  </ul>

                                </div>  

                            </td>
                            <?php }?>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div align="left" id="paglink"><?php echo $walks->appends(array('type'=>Session::get('type'), 'valu'=>Session::get('valu')))->links(); ?></div>




                </div>

<!--
  <script>
  $(function() {
    $( "#start-date" ).datepicker({
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
  </script>
-->

<script type="text/javascript">
</script>
@stop