@extends('layout')
@section('content')
<?php $role_id=Auth::user()->role_id;
if($role_id != 0){
$sub_action = SubRoleAction::where('role_id',Auth::user()->roles)->first(); $promo_action = $sub_action->promo;
}else{
$promo_action = "";
}
if($role_id == 0 || (strpos($promo_action, '3') !== false) ){ ?>

<a id="addpromo" href="{{ URL::Route('AdminPromoAdd') }}"><button class="btn btn-flat btn-block btn-info" type="button">Add Promo Code</button></a>
<?php   } ?>
<br/>


                    <div class="box box-danger">
        <form method="get" action="{{ URL::Route('/admin/sortpromo') }}">
            <div class="box-header">
                <h3 class="box-title">Sort</h3>
            </div>
            <div class="box-body row">
                <div class="col-md-6 col-sm-12">
                    <select id="sortdrop" class="form-control" name="type">
                        <option value="promoid" <?php if(isset($_GET['type']) && $_GET['type']=='promoid') {echo 'selected="selected"';}?> id="promoid">Promo Code ID</option>
                        <option value="promo" <?php if(isset($_GET['type']) && $_GET['type']=='promo') {echo 'selected="selected"';}?> id="promo">Promo Code</option>
                        <option value="uses" <?php if(isset($_GET['type']) && $_GET['type']=='uses') {echo 'selected="selected"';}?> id="promovalue">Uses Remaining</option>
                    </select>
                    <br>
                </div>
                <div class="col-md-6 col-sm-12">
                    <select id="sortdroporder" class="form-control" name="valu">
                        <option value="asc" <?php if(isset($_GET['valu']) && $_GET['valu']=='asc') {echo 'selected="selected"';}?> id="asc">Ascending</option>
                        <option value="desc" <?php if(isset($_GET['valu']) && $_GET['valu']=='desc') {echo 'selected="selected"';}?> id="desc">Descending</option>
                    </select>
                    <br>
                </div>
            </div>
            <div class="box-footer">
                <button type="submit" id="btnsort" class="btn btn-flat btn-block btn-success">Sort</button>
            </div>
        </form>
    </div>


<div class="box box-danger">
        <form method="get" action="{{ URL::Route('/admin/searchpromo') }}">
            <div class="box-header">
                <h3 class="box-title">Filter</h3>
            </div>
            <div class="box-body row">

            <div class="col-md-6 col-sm-12">

                 <select class="form-control" id="searchdrop" name="type">
                    <option value="promo_id" id="promo_id">Promo Code ID</option>
                    <option value="promo_name" id="promo_name">Promo Code Name</option>
                    <option value="promo_type" id="promo_type">Promo Code Type</option>
                    <option value="promo_state" id="promo_state">Promo Code State</option>
                </select>
                <br>
            </div>
            <div class="col-md-6 col-sm-12">
                <input class="form-control" type="text" name="valu" id="insearch" placeholder="keyword"/>
                <br>
            </div>
            </div>
            <div class="box-footer">
                    <button type="submit" id="btnsearch" class="btn btn-flat btn-block btn-success">Search</button>
            </div>
        </form>
    </div>



    <div class="box box-info tbl-box">
<div align="left" id="paglink"><?php echo $promo_codes->appends(array('type'=>Session::get('type'), 'valu'=>Session::get('valu')))->links(); ?></div>
   
                <?php /*$role_actions =SubAdmin::where('admin_id',Auth::user()->id)->first();*/
                $role_actions =RoleAction::where('role_id',Auth::user()->roles)->first(); ?>
<table class="table table-bordered">
    <tbody>
        <tr>
            <th>ID</th>
            <th>Promo Code</th>
            <th>Value</th>
            <th>Uses Remaining</th>
            <th>State</th>
            <th>Expiry</th>
            <?php if(($role_id == 0) || ($role_id==2 && $role_actions->actions==1) ) { ?>
                                                <th>Actions</th>
                                            <?php } ?>

        </tr>
        <?php foreach ($promo_codes as $promo) { ?>
        <tr>
            <td><?= $promo->id ?></td>
            <td><?= $promo->coupon_code ?></td>
            <td><?php if($promo->type==1){echo $promo->value." %";}elseif ($promo->type==2){echo "$ ".$promo->value;} ?></td>
            <td><?= $promo->uses ?></td>
            <td><?php if($promo->state == 1){ echo "Active";}elseif ($promo->state == 0){ echo "Expired";}elseif ($promo->state == 2){ echo "Deactivated";}elseif ($promo->state == 3){ echo "Max Limit Reached";} ?></td>
            <td><?= $promo->expiry ?></td>
            <td>

             <?php if(($role_id == 0) || ($role_id==2 && $role_actions->actions==1) ) { ?>
            <div class="dropdown">
              <button class="btn btn-flat btn-info dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown">
                Actions
                <span class="caret"></span>
              </button>
              <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
             

               <?php if($role_id == 0){ ?>
                        <li role="presentation"><a role="menuitem" tabindex="-1" id="edit" href="{{ URL::Route('AdminPromoCodeEdit',$promo->id) }}">Edit Promo Code</a></li>
                         <?php if($promo->state==1){ ?>
                    <li role="presentation"><a role="menuitem" tabindex="-1" id="edit" href="{{ URL::Route('AdminPromoCodeDeactivate',$promo->id) }}">Deactivate</a></li>
                <?php }elseif($promo->state==2){ ?>
                    <li role="presentation"><a role="menuitem" tabindex="-1" id="edit" href="{{ URL::Route('AdminPromoCodeActivate',$promo->id) }}">Activate</a></li>
                <?php } ?>

                                             <?php } else{?>
                <?php if (strpos($promo_action, '1') !== false) {
                ?>
                <li role="presentation"><a role="menuitem" tabindex="-1" id="edit" href="{{ URL::Route('AdminPromoCodeEdit',$promo->id) }}">Edit Promo Code</a></li>
                <?php }?>
                <?php if (strpos($promo_action, '2') !== false) {
                ?>
                <?php if($promo->state==1){ ?>
                    <li role="presentation"><a role="menuitem" tabindex="-1" id="edit" href="{{ URL::Route('AdminPromoCodeDeactivate',$promo->id) }}">Deactivate</a></li>
                <?php }elseif($promo->state==2){ ?>
                    <li role="presentation"><a role="menuitem" tabindex="-1" id="edit" href="{{ URL::Route('AdminPromoCodeActivate',$promo->id) }}">Activate</a></li>
                <?php } ?>
                <?php }?>
                <!--li role="presentation"><a role="menuitem" tabindex="-1" id="history" href="">View History</a></li>
                <li role="presentation"><a role="menuitem" tabindex="-1" id="coupon" href="">Delete</a></li-->
                <?php }?>
              </ul>
            </div>
            </td>
            <?php }?>
        </tr>
        <?php } ?>
    </tbody>
</table>
<div align="left" id="paglink"><?php echo $promo_codes->appends(array('type'=>Session::get('type'), 'valu'=>Session::get('valu')))->links(); ?></div>
</div>


@stop