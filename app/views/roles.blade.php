@extends('layout')

@section('content')

<a id="addroles" href="{{ URL::Route('AddingRoles') }}"><input type="button" class="btn btn-info btn-flat btn-block" value="Add Roles"></a>


<br>


                   {{--  <div class="box box-danger">

                       <form method="get" action="{{ URL::Route('/admin/searchdoc') }}">
                                <div class="box-header">
                                    <h3 class="box-title">Filter</h3>
                                </div>
                                <div class="box-body row">

                                <div class="col-md-6 col-sm-12">

                                <select id="searchdrop" class="form-control" name="type">
                                    <option value="docid" id="docid">ID</option>
                                    <option value="docname" id="docname">Name</option>
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

                    </div> --}}



                <div class="box box-info tbl-box">
                    
                <table class="table table-bordered">
                                <tbody>
                                        <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Edit</th>
                                                <th>Delete</th>

                                        </tr>

                            <?php foreach ($roles as $type) { ?>
                            <tr>
                                <td><?= $type->id ?></td>
                                <td><?= $type->name ?>
                                 
                                </td>
                                <td>  <a id="edit" <?php if($type->id != 1){ ?> href="{{ URL::Route('EditRoles', $type->id) }}" <?php } ?> > <?php if($type->id != 1){ ?> <input type="button" class="btn btn-primary" value="Edit"> <?php }else{ echo "N/A"; } ?></a></td>

                                <td>  <a id="delete"  <?php if($type->id != 1){ ?> href="{{ URL::Route('DeleteRoles', $type->id) }}"  <?php } ?> > <?php if($type->id != 1){ ?>  <input type="button" class="btn btn-danger" value="Delete"> <?php }else{ echo "N/A"; } ?> </a></td>
                              
                            </tr>
                            <?php } ?>
                    </tbody>
                </table>

              


@stop