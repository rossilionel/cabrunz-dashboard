@extends('layout')

@section('content')
<?php
  $message1=$message2=$message3=''; if($install['mail_driver'] == '' && $install['email_address'] == '' && $install['email_name']=='') {
  $message1='Mail Configuration Missing During Installation';
  }
  if($install['twillo_account_sid'] == '' && $install['twillo_auth_token'] =='' && $install['twillo_number'] =='') {
  $message2='SMS Configuration Missing During Installation';
  }
  if(($install['default_payment']=='' && $install['braintree_environment'] == ''  && $install['braintree_merchant_id'] == '' && $install['braintree_public_key'] == '' && $install['braintree_private_key']=='' && $install['braintree_cse']=='') && ( $install['stripe_publishable_key']=='')) {
  $message3='Payment Configuration Missing During Installation';
  }
?>
<p align="right"><a href="{{ URL::Route('AdminSettingInstallation') }}"><button class="btn btn-info btn-flat">Installation Settings</button></a></p>

<div class="row">

<div class="col-md-6 col-sm-12">
<div class="box box-primary">
<div class="box-header">
<h3 class="box-title">Basic App Settings</h3>
</div><!-- /.box-header -->
<!-- form start -->
<form method="post" action="{{ URL::Route('AdminSettingsSave') }}" id="basic"  enctype="multipart/form-data">


<div class="box-body">

<?php foreach ($settings as $setting) { if($setting->page == 1){ 
  if($setting->key != 'admin_email_address'){
    if( $setting->key != 'default_distance_unit' && $setting->key != 'sms_notification' && $setting->key != 'email_notification' && $setting->key != 'push_notification' && $setting->key != 'default_charging_method_for_users') { ?>
      <div class="form-group">
      <label><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo asset_url(); ?>/image/icon-tooltip.jpg"></a></label>

      <?php if((strstr($setting->key,'email_') || strstr($setting->key,'sms_'))& $setting->key != 'admin_email_address' ) {?>
        <textarea class="form-control" rows="5" cols="50" name="<?php echo $setting->id; ?>" ><?php echo $setting->value; ?></textarea>
      <?php } else{?>
        <input class="form-control" type="text" name="<?php echo $setting->id; ?>" value="<?php echo $setting->value; ?>">
      <?php } ?>

      </div>
    <?php } else{ ?>
    <div class="form-group">
    <label><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo asset_url(); ?>/image/icon-tooltip.jpg"></a></label>

    <select class="form-control" name="<?php echo $setting->id; ?>">
    <option value="1" <?php if($setting->value == 1) { echo "selected"; }?> >
    <?php if($setting->key == 'default_charging_method_for_users') {?>
    Time and Distance Based
    <?php } elseif($setting->key == 'default_distance_unit') { ?>
    Miles
    <?php }else{ ?>
    Yes
    <?php } ?>
    </option>
    <option value="0" <?php if($setting->value == 0) { echo "selected"; }?> >
    <?php if($setting->key == 'default_charging_method_for_users') {?>
    Fixed Price
    <?php } elseif($setting->key == 'default_distance_unit') { ?>
    KM
    <?php }else{ ?>
    No
    <?php } ?>
    </option>
    </select>

    </div>
    <?php } ?>
<?php }} ?>


<?php } ?>



</div><!-- /.box-body -->

<div class="box-footer">

<button type="submit" class="btn btn-primary btn-flat btn-block">Update Changes</button>
</div>

</div>
</div>



<div class="col-md-6 col-sm-12">
<div class="box box-primary">
<div class="box-header">
<h3 class="box-title">SMS Templates</h3>
</div><!-- /.box-header -->


<div class="box-body">
<?php foreach ($settings as $setting) { 
if($setting->page == 2){ 
if( $setting->key != 'sms_notification' && $setting->key != 'email_notification' && $setting->key != 'push_notification' && $setting->key != 'default_charging_method_for_users') { ?>
<div class="form-group">
<label><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo asset_url(); ?>/image/icon-tooltip.jpg"></a></label>

<?php if((strstr($setting->key,'email_') || strstr($setting->key,'sms_'))& $setting->key != 'admin_email_address' ) {?>
<textarea class="form-control" rows="5" cols="50" name="<?php echo $setting->id; ?>" ><?php echo $setting->value; ?></textarea>
<?php } else{?>
<input class="form-control" type="text" name="<?php echo $setting->id; ?>" value="<?php echo $setting->value; ?>">
<?php } ?>

</div>
<?php } else{ ?>
<div class="form-group">
<label><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo asset_url(); ?>/image/icon-tooltip.jpg"></a></label>

<select class="form-control" name="<?php echo $setting->id; ?>">
<option value="1" <?php if($setting->value == 1) { echo "selected"; }?> >
<?php if($setting->key == 'default_charging_method_for_users') {?>
Time and Distance Based
<?php } else { ?>
Yes
<?php } ?>
</option>
<option value="0" <?php if($setting->value == 0) { echo "selected"; }?> >
<?php if($setting->key == 'default_charging_method_for_users') {?>
Fixed Price
<?php } else { ?>
No
<?php } ?>
</option>
</select>

</div>
<?php } ?>
<?php } ?>


<?php } ?>

</div><!-- /.box-body -->

<div class="box-footer">

<button type="submit" class="btn btn-primary btn-flat btn-block">Update Changes</button>
</div>

</div>
</div>
</div>
<!--



<div class="col-md-6 col-sm-12">
<div class="box box-primary">
<div class="box-header">
<h3 class="box-title">Email Templates</h3>
</div>

<div class="box-body">

<?php foreach ($settings as $setting) { 
if($setting->page == 3){ 
if( $setting->key != 'sms_notification' && $setting->key != 'get_destination' && $setting->key != 'email_notification' && $setting->key != 'push_notification' && $setting->key != 'default_charging_method_for_users') { ?>
<div class="form-group">
<label><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo asset_url(); ?>/image/icon-tooltip.jpg"></a></label>

<?php if((strstr($setting->key,'email_') || strstr($setting->key,'sms_'))& $setting->key != 'admin_email_address' ) {?>
<textarea class="form-control" rows="5" cols="50" name="<?php echo $setting->id; ?>" ><?php echo $setting->value; ?></textarea>
<?php } else{?>
<input class="form-control" type="text" name="<?php echo $setting->id; ?>" value="<?php echo $setting->value; ?>">
<?php } ?>

</div>
<?php } else{ if( $setting->key != 'get_destination'){ ?>
<div class="form-group">
<label><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo asset_url(); ?>/image/icon-tooltip.jpg"></a></label>

<select class="form-control" name="<?php echo $setting->id; ?>">
<option value="1" <?php if($setting->value == 1) { echo "selected"; }?> >
<?php if($setting->key == 'default_charging_method_for_users') {?>
Time and Distance Based
<?php } else { ?>
Yes
<?php } ?>
</option>
<option value="0" <?php if($setting->value == 0) { echo "selected"; }?> >
<?php if($setting->key == 'default_charging_method_for_users') {?>
Fixed Price
<?php } else { ?>
No
<?php } ?>
</option>
</select>

</div>
<?php }}}} ?>                                       


</div>

<div class="box-footer">


<button type="submit" class="btn btn-primary btn-flat btn-block">Save</button>
</div>

</div>
</div>
-->


<div class="col-md-6 col-sm-12">
<div class="box box-primary">
<div class="box-header">
<h3 class="box-title">Advance Settings</h3>
</div><!-- /.box-header -->


<div class="box-body">



<?php foreach ($settings as $setting) {
if($setting->page == 4){
if($setting->key == 'provider_selection'){ ?>
<div class="form-group">
<label><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo asset_url(); ?>/image/icon-tooltip.jpg"></a></label>

<select class="form-control" name="<?php echo $setting->id; ?>">
<option value="1" <?php if($setting->value == 1) { echo "selected"; }?> >
Automatic
</option>
<option value="2" <?php if($setting->value == 2) { echo "selected"; }?> >
Manually
</option>
</select>

</div>
<?php }else{ ?>
<div class="form-group">
<label><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo asset_url(); ?>/image/icon-tooltip.jpg"></a></label>

<textarea class="form-control" rows="5" cols="50" name="<?php echo $setting->id; ?>" ><?php echo $setting->value; ?></textarea>

</div>
<?php }} ?>
<?php if($setting->page == 5){ ?>
<div class="form-group">
<label><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo asset_url(); ?>/image/icon-tooltip.jpg"></a></label>

<select class="form-control" name="<?php echo $setting->id; ?>">
<option value="1" <?php if($setting->value == 1) { echo "selected"; }?> >
Pre Payment
</option>
<option value="0" <?php if($setting->value == 0) { echo "selected"; }?> >
Post Payment
</option>
</select>

</div>
<?php }?>
<?php if($setting->key == 'get_destination'){ ?>
<div class="form-group">
<label><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo asset_url(); ?>/image/icon-tooltip.jpg"></a></label>

<select class="form-control" name="<?php echo $setting->id; ?>">
<option value="1" <?php if($setting->value == 1) { echo "selected"; }?> >
Yes
</option>
<option value="0" <?php if($setting->value == 0) { echo "selected"; }?> >
No
</option>
</select>

<p class="help-block">Note: If Destination is disabled, Share ETA and Fare Calculation will not work.</p>

</div>
<?php }?>

<?php if($setting->page == 6){ ?>
<div class="form-group">
<label><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo asset_url(); ?>/image/icon-tooltip.jpg"></a></label>
<select class="form-control" name="<?php echo $setting->id; ?>">
<option value="1" <?php if($setting->value == 1) { echo "selected"; }?> >
Admin
</option>
<option value="2" <?php if($setting->value == 2) { echo "selected"; }?> >
Provider
</option>
</select>
</div>
<?php }?>
<?php if($setting->page == 7){ ?>
<div class="form-group">
<label><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo asset_url(); ?>/image/icon-tooltip.jpg"></a></label>
<select class="form-control" name="<?php echo $setting->id; ?>">
<option value="1" <?php if($setting->value == 1) { echo "selected"; }?> >
Yes
</option>
<option value="2" <?php if($setting->value == 2) { echo "selected"; }?> >
No
</option>
</select>
</div>
<?php }?>
<?php if($setting->page == 8){if($setting->key!='paypal'){ ?>
<div class="form-group">
<label><?php echo ucwords(str_replace("_", " ", $setting->key)); ?>&nbsp;<a href="#" data-toggle="tooltip" title="<?= $setting->tool_tip; ?>"><img src="<?php echo asset_url(); ?>/image/icon-tooltip.jpg"></a></label>
<select class="form-control" name="<?php echo $setting->id; ?>">
<option value="1" <?php if($setting->value == 1) { echo "selected"; }?> >
Yes
</option>
<option value="0" <?php if($setting->value == 0) { echo "selected"; }?> >
No
</option>
</select>
</div>
<?php }}} ?>
</div><!-- /.box-body -->

<div class="box-footer">

<button type="submit" class="btn btn-primary btn-flat btn-block">Update Changes</button>
</div>
</form>
</div>
</div>

<div class="col-md-6 col-sm-12">
<div class="box box-primary">
<div class="box-header">
<h3 class="box-title">Theme Settings</h3>
</div><!-- /.box-header -->
<!-- form start -->
<form enctype="multipart/form-data" onsubmit="Checkfiles(this)" method="post" action="{{ URL::Route('AdminTheme') }}">

<div class="box-body">
<div class="form-group">
<label>Logo</label>

<input type="file" class="form-control" name="logo" id="logo"></input>
<p class="help-block">Please Upload image in .png format.</p>
                 
</div>

<div class="form-group">
<label>Title Icon</label>

<input type="file" class="form-control" name="icon" id="icon"></input>

<p class="help-block">Please Upload image in .ico format.</p>
                 
</div>




</div><!-- /.box-body -->

<div class="box-footer">



<button type="submit" id="theme" class="btn btn-primary btn-flat btn-block">Update Changes</button>
</div>
</form>
</div>
</div>





<script type="text/javascript">

$('#picker').colpick({
layout:'hex',
submit:0,
colorScheme:'dark',
onChange:function(hsb,hex,rgb,el,bySetColor) {
$(el).css('border-color','#'+hex);
// Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
if(!bySetColor) $(el).val(hex);
}
}).keyup(function(){
$(this).colpickSetColor(this.value);
});

$('#picker1').colpick({
layout:'hex',
submit:0,
colorScheme:'dark',
onChange:function(hsb,hex,rgb,el,bySetColor) {
$(el).css('border-color','#'+hex);
// Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
if(!bySetColor) $(el).val(hex);
}
}).keyup(function(){
$(this).colpickSetColor(this.value);
});


$('#picker2').colpick({
layout:'hex',
submit:0,
colorScheme:'light',
onChange:function(hsb,hex,rgb,el,bySetColor) {
$(el).css('border-color','#'+hex);
// Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
if(!bySetColor) $(el).val(hex);
}
}).keyup(function(){
$(this).colpickSetColor(this.value);
});

$('#picker3').colpick({
layout:'hex',
submit:0,
colorScheme:'dark',
onChange:function(hsb,hex,rgb,el,bySetColor) {
$(el).css('border-color','#'+hex);
// Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
if(!bySetColor) $(el).val(hex);
}
}).keyup(function(){
$(this).colpickSetColor(this.value);
});
$('#picker4').colpick({
layout:'hex',
submit:0,
colorScheme:'dark',
onChange:function(hsb,hex,rgb,el,bySetColor) {
$(el).css('border-color','#'+hex);
// Fill the text box just if the color was set using the picker, and not the colpickSetColor function.
if(!bySetColor) $(el).val(hex);
}
}).keyup(function(){
$(this).colpickSetColor(this.value);
});

</script>
<script type="text/javascript">
function Checkfiles()
{
var fup = document.getElementById('logo');
var fileName = fup.value;
if(fileName !='')
{
var ext = fileName.substring(fileName.lastIndexOf('.') + 1);

if(ext =="PNG" || ext=="png")
{
return true;
}
else
{
alert("Upload PNG Images only for Logo");
return false;
}
}
var fup = document.getElementById('icon');
var fileName1 = fup.value;
if(fileName1 !='')
{
var ext = fileName1.substring(fileName1.lastIndexOf('.') + 1);

if(ext =="ICO" || ext=="ico")
{
return true;
}
else
{
alert("Upload Icon Images only for Favicon");
return false;
}
}
}
</script>
<?php
if($success == 1) { ?>
<script type="text/javascript">
alert('Settings Updated Successfully');
</script>
<?php } ?>
<?php
if($success == 2) { ?>
<script type="text/javascript">
alert('Sorry Something went Wrong');
</script>
<?php } ?>

<script>
$(function () { $("[data-toggle='tooltip']").tooltip(); });
</script>

<script type="text/javascript">
$("#basic").validate({
rules: {
1: "required",
2: "required",
3: "required",
4: "required",
5: "required",
6: "required",
7: "required",
8: "required",
9: "required",
10: "required",
29: "required",
30: "required",
31: "required",

12: {
required: true,
email: true
},
11: {
required: true,
}
}
});

</script>

@stop