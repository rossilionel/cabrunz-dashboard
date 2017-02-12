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
                                    <h3 class="box-title">Add {{ trans('customize.Provider');}}</h3>
                                </div><!-- /.box-header -->
                                <!-- form start -->
                                <form role="form" class="form" id="main-form" method="post" action="{{ URL::Route('AdminAddUserSms') }}"  enctype="multipart/form-data">
                                <input type="hidden" name="owner_id" value="{{$owner_id}}">

                                    <div class="box-body">
                                        
                                        <div class="form-group">
                                            <label>Title</label>
                                            <input type="textarea" class="form-control" name="title" value="" placeholder="Title" >
                                          
                                        </div>

                                        <div class="form-group">
                                            <label>Sms</label>
                                            <input type="textarea" class="form-control" name="sms" value="" placeholder="Sms" >
                                          
                                        </div>

                                        
                                   
                                    </div><!-- /.box-body -->

                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary btn-flat btn-block">Update Changes</button>
                                    </div>
                                </form>
                            </div>

<script type="text/javascript">
$("#main-form").validate({
  rules: {
    sms: "required",

  }
});
</script>


@stop