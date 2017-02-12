@extends('layout')

@section('content')

@if (Session::has('msg'))
<h4 class="alert alert-info">
    {{{ Session::get('msg') }}}
    {{{Session::put('msg',NULL)}}}
</h4>
@endif
<div class="box box-primary">
    <div class="box-header">
        <h3 class="box-title">Add Promo Code</h3>
    </div><!-- /.box-header -->
    <!-- form start -->
    <form role="form" id="form" method="post" action="{{ URL::Route('AdminPromoUpdate') }}"  enctype="multipart/form-data">
        <input type="hidden" name="id" value="0>">
        <div class="box-body col-md-12 col-sm-12">
            <div class="form-group col-md-6 col-sm-6">
                <label>Promo Code Name</label>
                <input type="text" class="form-control" name="code_name" value="" placeholder="ProvenLogic" >
            </div>
            <div class="form-group col-md-6 col-sm-6">
                <label>Promo Code Value</label>
                <input class="form-control" type="text" name="code_value" value="" placeholder="20">
            </div>
            <div class="form-group col-md-6 col-sm-6">
                <label>Promo Code Type</label>
                <select name="code_type" class="form-control">
                    <option value="1">Percent</option>
                   {{--  <option value="2">Absolute</option> --}}
                </select>
            </div>
            <div class="form-group col-md-6 col-sm-6">
                <label>Uses Allowed</label>
                <input class="form-control" type="text" name="code_uses" value="" placeholder="50">
            </div>
            <div class="form-group col-md-6 col-sm-6">
                <label>Expiry</label>
                <br>
                <div class='input-group date' id='startDate'>
                    <input type='text' class="form-control" name="code_expiry" placeholder="Expiry date" value="{{date("m/d/Y h:m A")}}"/>
                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
                <!--<input type='text' class="form-control" name="code_expiry" value="{{date("m/d/Y h:m A")}}" placeholder="Expiry date"/>
                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>-->
            </div>
        </div><!-- /.box-body -->
        <div class="box-footer">
            <button type="submit" class="btn btn-primary btn-flat btn-block">Update Changes</button>
        </div>
    </form>
</div>
<script type="text/javascript">
    $("#form").validate({
        rules: {
            code_name: "required",
            code_value: "required",
            code_uses: "required",
            code_expiry: "required",
        }
    });

</script>
<script type="text/javascript">

    jQuery(function () {
        jQuery('#startDate').datetimepicker();
        jQuery("#startDate").on("dp.change", function (e) {
            jQuery('#endDate').data("DateTimePicker").setMinDate(e.date);
        });
        jQuery("#endDate").on("dp.change", function (e) {
            jQuery('#startDate').data("DateTimePicker").setMaxDate(e.date);
        });
    });

</script>

<script type="text/javascript" src="{{asset('/js/moment.js')}}"></script>
<script type="text/javascript" src="{{asset('/js/bootstrap-datetimepicker.js')}}"></script>
@stop