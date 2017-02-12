@extends('layout')

@section('content')

<style>
.main-heading .mh-label .icheckbox_minimal{
    margin-right: 5px;
}
</style>

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
            <form method="post" action="{{ URL::Route('AdminAdminsAdd') }}">
          
            <div class="form-group">
                          <label>Email</label><input class="form-control" type="text" name="username" placeholder="Add admin email">
                          </div>
                          
                       <div class="form-group">
                          <label>Password</label>
                          <input type="password" class="form-control" name="password" placeholder="Add admin password">
                        </div>
                        <div class ="form-group">
                            <label>Roles</label>
                            <select name ="roles" id ="roles" class="form-control">
                                @foreach($roles as $r)
                                <option id ="{{$r->id}}" value ="{{$r->id}}">{{$r->name}}</option>
                                @endforeach

                            </select>
                        </div>
                        
                       
                        
                        <div class="box-footer">
                                  
                                        <button type="submit" id="btnsearch" class="btn btn-flat btn-block btn-success">Add Admin</button>

                                        
                                </div>

                                </form>
                                </div>
                                </div>
                                </div>
                    

@stop