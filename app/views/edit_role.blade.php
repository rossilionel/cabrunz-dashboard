@extends('layout')

@section('content')

<div class="box-body ">
<form method="post" action="{{ URL::Route('SaveEditRoles') }}">

<div class="form-group">
<input type="hidden" value =<?php echo $role_id?> name="role_id" >
<div class="form-group">
<label>Available Options for Roles</label>

<?php
$dash_main ="";
$map_main="";
$prov_main="";
$req_main="";
$user_main="";
$review_main="";
$setting_main="";
$info_main="";
$type_main="";
$doc_main="";
$promo_main="";
$customize_main="";
$payment_main="";
$role_main ="";
$manual_main="";
$dash = $role_action->dash;
if($dash == 1){
$dash_main ="checked";
}
$map =$role_action->map;
if($map ==1){
$map_main="checked";
}
$prov=$role_action->prov;
if($prov == 1){
$prov_main="checked";
}
$req=$role_action->req;
if($req == 1){
$req_main= "checked";
}
$user=$role_action->user;
if($user == 1){
$user_main="checked";
}
$review =$role_action->review;
if($review == 1){
$review_main ="checked";
}
$setting=$role_action->setting;
if($setting ==1){
$setting_main="checked";
}
$info =$role_action->info;
if($info == 1){
$info_main="checked";
}
$type =$role_action->type;
if($type ==1){
$type_main="checked";
}
$doc =$role_action->doc;
if($doc ==1){
$doc_main="checked";
}
$promo =$role_action->promo;
if($promo == 1){
$promo_main="checked";
}
$customize=$role_action->customize;
if($customize ==1){
$customize_main="checked";
}
$payment =$role_action->payment;
if($payment == 1){
$payment_main="checked";
}
$role =$role_action->role;
if($role ==1){
$role_main="checked";
}

$manual_assign =$role_action->manual_assign;
if($manual_assign ==1){
$manual_main="checked";
}
?>
<div class=".checkbox-inline">
  <div class ="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 0px">
         <label style="margin: 0px;padding: 10px 20px;width: 100%;background: #AAA none repeat scroll 0% 0%;"><input type="checkbox" name="dash" value="dash" <?php echo $dash_main;?>> Dashboard </label>
  </div>

  <div class ="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 0px">
      <label style="margin: 0px;padding: 10px 20px;width: 100%;background: #AAA none repeat scroll 0% 0%;"><input type="checkbox" name="map" value="map" <?php echo $map_main;?> > Map View
      </label>
  </div>
  <div class="main-heading" style="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 6px;">
      <label class="mh-label" style="width: 100%;background: rgb(170, 170, 170) none repeat scroll 0% 0%;
              padding: 8px 12px;"><input type="checkbox" name="prov" value="prov" <?php echo $prov_main;?> > Drivers</label>
      <div>
          <?php 
            $pro = $sub_role_action->provider;
            $edit = strpos($pro, 1);
            $edit_detail ="";

            $bank = strpos($pro,2);
            $bank_detail ="";

            $history = strpos($pro,3);
            $history_detail ="";

            $pro_add = strpos($pro,0);
            $pro_add_detail ="";

            $pro_approve = strpos($pro,7);
            $pro_approve_detail ="";

            $pro_decline = strpos($pro,8);
            $pro_decline_detail ="";

            $pro_delete = strpos($pro,9);
            $pro_delete_detail ="";

            $calender = strpos($pro,5);
            $calender_detail ="";

            $doc = strpos($pro,6);
            $doc_detail ="";

            if (strpos($pro, '1') !== false) {
              $edit_detail ="checked";
            }
            if (strpos($pro, '2') !== false) {
              $bank_detail ="checked";
            }
            if (strpos($pro, '3') !== false) {
              $history_detail ="checked";
            }
            if (strpos($pro, '4') !== false) {
              $app_dec_del_detail ="checked";
            }
            if (strpos($pro, '5') !== false) {
              $calender_detail="checked";
            }
            if (strpos($pro, '6') !== false) {
              $doc_detail ="checked";
            }
            if (strpos($pro, '0') !== false) {
              $pro_add_detail ="checked";
            }
            if (strpos($pro, '7') !== false) {
              $pro_approve_detail ="checked";
            }
            if (strpos($pro, '8') !== false) {
              $pro_decline_detail ="checked";
            }
            if (strpos($pro, '9') !== false) {
              $pro_delete_detail ="checked";
            }

            ?>
          <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" value="0" <?php echo $pro_add_detail ;?> > Add Driver</label>
          <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" value="1" <?php echo $edit_detail ;?> > Edit Details</label>
          <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" value="2" <?php echo $bank_detail?> > Add Banking Details</label> 
          <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" value="3" <?php echo $history_detail ;?>> View Histroy</label>
          <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" value="7" <?php echo $pro_approve_detail ;?>> Approve </label>
          <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" value="8" <?php echo $pro_decline_detail ;?>> Decline</label>
          <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" value="9" <?php echo $pro_delete_detail ;?>> Delete</label>
                   
         <!--  <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" value="5" <?php echo $calender_detail ;?>>View Calender</label> -->
          <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" value="6" <?php echo $doc_detail ;?>> View Documents</label>    
              
      </div>
  </div>

 <?php 
    $req = $sub_role_action->request;
    $map = strpos($req, 1);
    $map_detail ="";

    $transfer = strpos($req,2);
    $transfer_detail ="";

    $cancel = strpos($req,3);
    $cancel_detail ="";

    if (strpos($req, '1') !== false) {
      $map_detail ="checked";
    }
    if (strpos($req, '2') !== false) {
      $tranfer_detail ="checked";
    }
    if (strpos($req, '3') !== false) {
      $cancel_detail ="checked";
    }
  
  ?>

   <div class="main-heading" style="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 6px;">
     
               <label class="mh-label" style="width: 100%;background: rgb(170, 170, 170) none repeat scroll 0% 0%;
              padding: 8px 12px;"><input type="checkbox" name="req" value="req" <?php echo $req_main;?>> Requests</label>
      <div>

          <label style="margin: 10px 20px;"><input type="checkbox" name="reqi[]" value="1" <?php echo $map_detail ;?> > View Map </label>
          <label style="margin: 10px 20px;"><input type="checkbox" name="reqi[]" value="2" <?php echo $transfer_detail ;?>> Transfer Amount</label> 
          <label style="margin: 10px 20px;"><input type="checkbox" name="reqi[]" value="3" <?php echo $cancel_detail ;?>> Cancel Request</label>            
      </div>
  </div>
 
  <div class="main-heading" style="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 6px;">
     
      <label class="mh-label" style="width: 100%;background: rgb(170, 170, 170) none repeat scroll 0% 0%;
              padding: 8px 12px;"><input type="checkbox" name="usr" value="usr" <?php echo $user_main;?> > Users</label>
      <div>
       <?php 
            $user = $sub_role_action->user;
            $sms =strpos($user, 1);
            $sms_detail ="";

            $edit = strpos($user, 2);
            $edit_detail ="";

            $history = strpos($user,3);
            $history_detail ="";

            $coupon = strpos($user,4);
            $coupon_detail ="";

            $del = strpos($user,6);
            $del_detail ="";

            if (strpos($user, '1') !== false) {
              $sms_detail ="checked";
            }
            if (strpos($user, '2') !== false) {
              $edit_detail ="checked";
            }
            if (strpos($user, '3') !== false) {
              $history_detail ="checked";
            }
            if (strpos($user, '4') !== false) {
              $coupon_detail ="checked";
            }
            if (strpos($user, '5') !== false) {
              $calender_detail="checked";
            }
            if (strpos($pro, '6') !== false) {
              $del_detail ="checked";
            }

            ?>

          <label style="margin: 10px 20px;"><input type="checkbox" name="usri[]" value="1" <?php echo $sms_detail ;?> > Send Sms </label>
          <label style="margin: 10px 20px;"><input type="checkbox" name="usri[]" value="2" <?php echo $edit_detail ;?>> Edit Users</label> 
          <label style="margin: 10px 20px;"><input type="checkbox" name="usri[]" value="3" <?php echo $history_detail;?>> View History</label> 
          <label style="margin: 10px 20px;"><input type="checkbox" name="usri[]" value="4" <?php echo $coupon_detail;?>> Coupon Details</label>
          <label style="margin: 10px 20px;"><input type="checkbox" name="usri[]" value="5" <?php echo $del_detail;?>> Delete</label>  
                     
      </div>
  </div>
   <?php 
            $review = $sub_role_action->review;
           

            if (strpos($user, '1') !== false) {
              $review_detail ="checked";
            }
          

            ?>
  <div class ="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 6px">
      <label class="mh-label" style="width: 100%;background: rgb(170, 170, 170) none repeat scroll 0% 0%;
              padding: 8px 12px;"><input type="checkbox" name="rev" value="1" <?php echo $review_main;?> > Review
      </label>

       <div>
        <?php 
            $review = $sub_role_action->review;
            
           $delete_detail="";
            if (strpos($review, '1') !== false) {
              $delete_detail ="checked";
            }
           

            ?>
          <label style="margin: 10px 20px;"><input type="checkbox" name="revi" value="1"  <?php echo $delete_detail;?>> Delete</label>  
                     
      </div>

  </div>
  <div class ="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 0px">
      <label style="margin: 0px;padding: 10px 20px;width: 100%;background: #AAA none repeat scroll 0% 0%;"><input type="checkbox" name="sett" value="sett" <?php echo $setting_main;?> > Settings
      </label>
  </div>
  <div class ="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 0px">
      <label style="margin: 0px;padding: 10px 20px;width: 100%;background: #AAA none repeat scroll 0% 0%;"><input type="checkbox" name="info" value="info" <?php echo $info_main;?> > Information
      </label>
  </div>
  <div class ="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 6px">
      <label style="margin: 0px;padding: 10px 20px;width: 100%;background: #AAA none repeat scroll 0% 0%;"><input type="checkbox" name="typ" value="typ" <?php echo $type_main;?> > Types
      </label>
      <div>
      <?php 
            $type = $sub_role_action->type;
            $edit =strpos($type, 1);
            $edit_detail ="";

            $delete = strpos($type, 2);
            $delete_detail ="";

            if (strpos($type, '1') !== false) {
              $edit_detail ="checked";
            }
            if (strpos($doc, '2') !== false) {
              $delete_detail ="checked";
            }
           

            ?>

       
          <label style="margin: 10px 20px;"><input type="checkbox" name="typi" value="1" <?php echo $edit_detail ;?>> Edit</label> 
                     
      </div>
  </div>

<div class ="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 6px">
    <label style="margin: 0px;padding: 10px 20px;width: 100%;background: #AAA none repeat scroll 0% 0%;"><input type="checkbox" name="doc" value="doc" <?php echo $doc_main;?> > Documents
    </label>
    <div>
      <?php 
          $doc = $sub_role_action->doc;
          $edit =strpos($doc, 1);
          $edit_detail ="";

          $delete = strpos($doc, 2);
          $delete_detail ="";

          if (strpos($doc, '1') !== false) {
            $edit_detail ="checked";
          }
          if (strpos($doc, '2') !== false) {
            $delete_detail ="checked";
          }
         

          ?>

        <label style="margin: 10px 20px;">
        <input type="checkbox" name="doci[]" value="1" <?php echo $edit_detail ;?> > Edit </label>
        <label style="margin: 10px 20px;"><input type="checkbox" name="doci[]" value="2"  <?php echo $delete_detail;?> > Delete</label> 
                   
    </div>
</div>

<?php 
$promo = $sub_role_action->promo;
$edit =strpos($promo, 1);
$edit_detail ="";

$deactivate = strpos($promo, 2);
$deactivate_detail ="";

$add_promo =strpos($promo, 3);
$add_promo_detail ="";

if (strpos($promo, '1') !== false) {
  $edit_detail ="checked";
}

if (strpos($promo, '2') !== false) {
  $deactivate_detail ="checked";
}

if (strpos($promo, '3') !== false) {
  $add_promo_detail ="checked";
}


?>

<div class="main-heading" style="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 6px;">
  <label class="mh-label" style="width: 100%;background: rgb(170, 170, 170) none repeat scroll 0% 0%;
          padding: 8px 12px;"><input type="checkbox" name="promo" value="promo" <?php echo $promo_main;?> > Promo Codes</label>
  <div>
      <label style="margin: 10px 20px;"><input type="checkbox" name="promoi[]" value="3" <?php echo $add_promo_detail;?>> Add Promo Code</label> 
      <label style="margin: 10px 20px;"><input type="checkbox" name="promoi[]" value="1" <?php echo $edit_detail;?>> Edit Promo Code</label>
      <label style="margin: 10px 20px;"><input type="checkbox" name="promoi[]" value="2" <?php echo $deactivate_detail;?>> Deactivate</label> 
                  
  </div>
</div>
  <div class ="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 0px">
      <label style="margin: 0px;padding: 10px 20px;width: 100%;background: #AAA none repeat scroll 0% 0%;"><input type="checkbox" name="customize" value="customize" <?php echo $customize_main;?>> Customize
      </label>
  </div>

<?php 
$payment = $sub_role_action->payment;
$map =strpos($payment, 1);
$map_detail ="";

if (strpos($map_detail, '1') !== false) {
  $map_detail ="checked";
}
?>

   <div class="main-heading" style="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 0px;">
      <label class="mh-label" style="width: 100%;background: rgb(170, 170, 170) none repeat scroll 0% 0%;
              padding: 8px 12px;"><input type="checkbox" name="payment" value="payment" <?php echo $map_detail;?> <?php echo $payment_main;?> > Payment Details</label>
      <div>

          <label style="margin: 10px 20px;"><input type="checkbox" name="paymenti" value="1"> View Map</label>
                    
      </div>
  </div>

   <div class="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 0px">
      <label class="mh-label" style="margin-bottom: 0px;width: 100%;background: rgb(170, 170, 170) none repeat scroll 0% 0%;
              padding: 8px 12px;"><input type="checkbox" name="role" value="1" <?php echo $role_main;?> > Role</label>
    
  </div>

   <div class="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 0px">
      <label class="mh-label" style="margin-bottom: 0px;width: 100%;background: rgb(170, 170, 170) none repeat scroll 0% 0%;
              padding: 8px 12px;"><input type="checkbox" name="manual_assign" value="1" <?php echo $manual_main;?> > Manual Assign</label>
    
  </div>



</div>

</div>

<div class="box-footer">

      <button type="submit" id="btnsearch" class="btn btn-flat btn-block btn-success">Save Roles</button>

      
</div>

</form>
</div>
         
@stop