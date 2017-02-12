@extends('layout')

@section('content')


<div class="box box-success">
<br/>
<br/>
                    @if (Session::has('msg'))
                    <h4 class="alert alert-info">
                    {{{ Session::get('msg') }}}
                    {{{Session::put('msg',NULL)}}}
                    </h4>
                   @endif
                <br/>

                    <div class="box-body ">
            <form method="post" action="{{ URL::Route('AdminRoleAdding') }}">
          
            <div class="form-group">
                          <label>Name</label><input class="form-control" type="text" name="name" id="name" placeholder="Add name">
            </div>
 <script>
    //for preventing the default action of button and checking name should not be empty
    function check(){
        alert("Ssssssssss");
        event.preventDefault();
            if($("#name").val() == ""){

                alert("Please Enter Role Name");
            }else{
                alert("cc");
                $("#submit_check").submit();
            }
    }                 
</script>

          
            <div class="form-group">
                        
                        
                        <div class="form-group">
                          <label>Available Options for Sub Admin</label>

                       
                        <div class=".checkbox-inline">
                            <div class ="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 0px">
                                   <label style="margin: 0px;padding: 10px 20px;width: 100%;background: #AAA none repeat scroll 0% 0%;"><input type="checkbox" name="dash" value="dash"> &nbsp; Dashboard </label>
                            </div>
                         
                            <div class ="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 0px">
                                <label style="margin: 0px;padding: 10px 20px;width: 100%;background: #AAA none repeat scroll 0% 0%;"><input type="checkbox" name="map" value="map"> &nbsp; Map View
                                </label>
                            </div>
                            <div class="main-heading" style="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 6px;">
                                <label class="mh-label" style="width: 100%;background: rgb(170, 170, 170) none repeat scroll 0% 0%;
                                        padding: 8px 12px;"><input type="checkbox" name="prov" value="prov" id="dis"> &nbsp; Drivers</label>
                                <div>
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" value="0" > Add Drivers</label>
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" class="child" value="1" > Edit Details</label>
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" value="2"> Add Banking Details</label> 
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" value="3"> View Histroy</label>
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" value="7"> Approve</label>
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" value="8"> Decline</label>
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" value="9"> Delete</label>
                                   <!--  <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" value="5">View Calender</label> -->
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="provi[]" value="6"> View Documents</label>    
                                        
                                </div>
                            </div>

                             <div class="main-heading" style="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 6px;">
                               
                                         <label class="mh-label" style="width: 100%;background: rgb(170, 170, 170) none repeat scroll 0% 0%;
                                        padding: 8px 12px;"><input type="checkbox" name="req" value="req"> &nbsp; Requests</label>
                                <div>

                                    <label style="margin: 10px 20px;"><input type="checkbox" name="reqi[]" value="1"> View Map </label>
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="reqi[]" value="2"> Transfer Amount</label> 
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="reqi[]" value="3"> Cancel Request</label> 
                                               
                                </div>
                            </div>
                           
                            <div class="main-heading" style="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 6px;">
                               
                                <label class="mh-label" style="width: 100%;background: rgb(170, 170, 170) none repeat scroll 0% 0%;
                                        padding: 8px 12px;"><input type="checkbox" name="usr" value="usr"> &nbsp; Users</label>
                                <div>

                                    <label style="margin: 10px 20px;"><input type="checkbox" name="usri[]" value="1"> Send Sms </label>
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="usri[]" value="2"> Edit Users</label> 
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="usri[]" value="3"> View History</label> 
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="usri[]" value="4"> Coupon Details</label>
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="usri[]" value="5"> Delete</label>  
                                               
                                </div>
                            </div>
                            <div class ="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 6px">
                                <label class="mh-label" style="width: 100%;background: rgb(170, 170, 170) none repeat scroll 0% 0%;
                                        padding: 8px 12px;"><input type="checkbox" name="rev" value="rev"> &nbsp; Review
                                </label>

                                <div>
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="revi" value="1"> Delete</label>  
                                               
                                </div>

                            </div>
                             
                             <div class="main-heading" style="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 6px;">
                                <label class="mh-label" style="width: 100%;background: rgb(170, 170, 170) none repeat scroll 0% 0%;
                                        padding: 8px 12px;"><input type="checkbox" name="rev" value="rev"> &nbsp; Payment Details</label>
                                <div>

                                    <label style="margin: 10px 20px;"><input type="checkbox" name="revi" value="1"> Delete</label>
                                              
                                </div>
                            </div>

                            <div class ="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 0px">
                                <label style="margin: 0px;padding: 10px 20px;width: 100%;background: #AAA none repeat scroll 0% 0%;"><input type="checkbox" name="sett" value="sett"> &nbsp; Settings
                                </label>
                            </div>
                            <div class ="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 0px">
                                <label style="margin: 0px;padding: 10px 20px;width: 100%;background: #AAA none repeat scroll 0% 0%;"><input type="checkbox" name="info" value="info"> &nbsp; Information
                                </label>
                            </div>
                        
                            
                            <div class="main-heading" style="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 6px;">
                                <label class="mh-label" style="width: 100%;background: rgb(170, 170, 170) none repeat scroll 0% 0%;
                                        padding: 8px 12px;"><input type="checkbox" name="typ" value="typ"> &nbsp; Types</label>
                                <div>

                                    <label style="margin: 10px 20px;"><input type="checkbox" name="typi" value="1"> Edit</label>
                                              
                                </div>
                            </div>

                           
                             <div class="main-heading" style="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 6px;">
                                <label class="mh-label" style="width: 100%;background: rgb(170, 170, 170) none repeat scroll 0% 0%;
                                        padding: 8px 12px;"><input type="checkbox" name="doc" value="doc"> &nbsp; Documents</label>
                                <div>

                                    <label style="margin: 10px 20px;"><input type="checkbox" name="doci[]" value="1"> Edit</label>
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="doci[]" value="2"> Delete</label> 
                                                
                                </div>
                            </div>

                             <div class="main-heading" style="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 6px;">
                                <label class="mh-label" style="width: 100%;background: rgb(170, 170, 170) none repeat scroll 0% 0%;
                                        padding: 8px 12px;"><input type="checkbox" name="promo" value="promo"> &nbsp; Promo Codes</label>
                                <div>
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="promoi[]" value="3"> Add Promo Code</label>
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="promoi[]" value="1"> Edit Promo Code</label>
                                    <label style="margin: 10px 20px;"><input type="checkbox" name="promoi[]" value="2"> Deactivate</label> 
                                                
                                </div>
                            </div>
                            <div class ="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 0px">
                                <label style="margin: 0px;padding: 10px 20px;width: 100%;background: #AAA none repeat scroll 0% 0%;"><input type="checkbox" name="customize" value="customize"> &nbsp; Customize
                                </label>
                            </div>
                             <div class="main-heading" style="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 6px;">
                                <label class="mh-label" style="width: 100%;background: rgb(170, 170, 170) none repeat scroll 0% 0%;
                                        padding: 8px 12px;"><input type="checkbox" name="payment" value="payment"> &nbsp; Payment Details</label>
                                <div>

                                    <label style="margin: 10px 20px;"><input type="checkbox" name="paymenti" value="1"> View Map</label>
                                              
                                </div>
                            </div>

                            <div class ="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 0px">
                                <label style="margin: 0px;padding: 10px 20px;width: 100%;background: #AAA none repeat scroll 0% 0%;"><input type="checkbox" name="role" value="role"> &nbsp; Role
                                </label>
                            </div>

                             <div class ="main-heading" style ="margin: 20px 0px;border: 1px solid #aaa;padding-bottom: 0px">
                                <label style="margin: 0px;padding: 10px 20px;width: 100%;background: #AAA none repeat scroll 0% 0%;"><input type="checkbox" name="manual_assign" value="manual_assign"> &nbsp; Manual Assign
                                </label>
                            </div>

                          

                        </div>

                            <!-- <div class=".checkbox-inline">
                                <label style="margin: 10px 20px;"><input type="checkbox" name="typ" value="typ"> Types</label>
                                <label style="margin: 10px 20px;"><input type="checkbox" name="doc" value="doc"> Documents</label>
                                <label style="margin: 10px 20px;"><input type="checkbox" name="promo" value="promo"> Promo Codes</label>
                                <label style="margin: 10px 20px;"><input type="checkbox" name="customize" value="customize"> Customize</label>
                                <label style="margin: 10px 20px;"><input type="checkbox" name="payment" value="payment"> Payment Details</label>
                              
                            </div> -->
                        </div>
                       <!--   <div class="form-group">

                            <label>Actions Button</label>
                            <br/>
                            <label style="margin: 10px 20px;"><input type="radio" name="action" value="enable"> Enable</label>
                            <label style="margin: 10px 20px;"><input type="radio" name="action" value="disable" checked="checked"> Disable</label>
                            

                        </div> -->
                        
                 
                        
                        <div class="box-footer">
                                  
                                      
                                       <button type="submit"  id="submit_check" class="btn btn-flat btn-block btn-success" value="Add Role">Add</button>

                                        
                                </div>

                                </form>
                                </div>
                            
                                </div>

@stop