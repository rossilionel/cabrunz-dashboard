@extends('layout')

@section('content')
  

  <style>
   
  .map-canvas {
  height: 400px;
  margin: 0px;
  padding: 0px
  }
  .controls {
  margin-top: 16px;
  border: 1px solid transparent;
  border-radius: 2px 0 0 2px;
  box-sizing: border-box;
  -moz-box-sizing: border-box;
  height: 32px;
  outline: none;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
  }

  .pac-input {
  background-color: #fff;
  font-family: Roboto;
  font-size: 15px;
  font-weight: 300;
  margin-left: 12px;
  padding: 0 11px 0 13px;
  text-overflow: ellipsis;
  width: 300px;
  margin-top: 11px;
  }

  .pac-input:focus {
  border-color: #4d90fe;
  }

  .pac-container {
  font-family: Roboto;
  }

.type-selector {
  color: #fff;
  background-color: #4d90fe;
  padding: 5px 11px 0px 11px;
}

.type-selector label {
  font-family: Roboto;
  font-size: 13px;
  font-weight: 300;
}

    </style> 


<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&signed_in=true&libraries=places"></script>
<script>
// This example adds a search box to a map, using the Google Place Autocomplete
// feature. People can enter geographical searches. The search box will return a
// pick list containing a mix of places and predicted search terms.

function initialize() {

  var markers = [];
  var map = new google.maps.Map(document.getElementById('map-canvas'), {
    mapTypeId: google.maps.MapTypeId.ROADMAP
  });
  
  var defaultBounds = new google.maps.LatLngBounds(
      new google.maps.LatLng(-33.8902, 151.1759),
      new google.maps.LatLng(-33.8474, 151.2631));
  map.fitBounds(defaultBounds);

  // Create the search box and link it to the UI element.
  var input = /** @type {HTMLInputElement} */(
      document.getElementById('pac-input'));
  map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);

  var searchBox = new google.maps.places.SearchBox(
    /** @type {HTMLInputElement} */(input));

  // [START region_getplaces]
  // Listen for the event fired when the user selects an item from the
  // pick list. Retrieve the matching places for that item.
  google.maps.event.addListener(searchBox, 'places_changed', function() {
    var places = searchBox.getPlaces();

    if (places.length == 0) {
      return;
    }
    for (var i = 0, marker; marker = markers[i]; i++) {
      marker.setMap(null);
    }

    // For each place, get the icon, place name, and location.
    markers = [];
    var bounds = new google.maps.LatLngBounds();
    for (var i = 0, place; place = places[i]; i++) {
      var image = {
        url: place.icon,
        size: new google.maps.Size(71, 71),
        origin: new google.maps.Point(0, 0),
        anchor: new google.maps.Point(17, 34),
        scaledSize: new google.maps.Size(25, 25)
      };

      // Create a marker for each place.
      var marker = new google.maps.Marker({
        map: map,
        icon: image,
        title: place.name,
        position: place.geometry.location
      });

      markers.push(marker);

      bounds.extend(place.geometry.location);
    }

    map.fitBounds(bounds);
  });
  // [END region_getplaces]

  // Bias the SearchBox results towards places that are within the bounds of the
  // current map's viewport.
  google.maps.event.addListener(map, 'bounds_changed', function() {
    var bounds = map.getBounds();
    searchBox.setBounds(bounds);
  });
}

google.maps.event.addDomListener(window, 'load', initialize);

</script>

<script>
// This example adds a search box to a map, using the Google Place Autocomplete
// feature. People can enter geographical searches. The search box will return a
// pick list containing a mix of places and predicted search terms.

function initialize() {

  var markers1 = [];
  var map1 = new google.maps.Map(document.getElementById('map-canvas1'), {
    mapTypeId: google.maps.MapTypeId.ROADMAP
  });
  
  var defaultBounds1 = new google.maps.LatLngBounds(
      new google.maps.LatLng(-33.8902, 151.1759),
      new google.maps.LatLng(-33.8474, 151.2631));
  map1.fitBounds(defaultBounds1);

  // Create the search box and link it to the UI element.
  var input1 = /** @type {HTMLInputElement} */(
      document.getElementById('pac-input1'));
  
  map1.controls[google.maps.ControlPosition.TOP_LEFT].push(input1);

  var searchBox1 = new google.maps.places.SearchBox(
    /** @type {HTMLInputElement} */(input1));

  // [START region_getplaces]
  // Listen for the event fired when the user selects an item from the
  // pick list. Retrieve the matching places for that item.
  google.maps.event.addListener(searchBox1, 'places_changed', function() {
    GetLocation();
    var places1 = searchBox1.getPlaces();

    if (places1.length == 0) {
      return;
    }
    for (var i = 0, marker1; marker1 = markers1[i]; i++) {
      marker1.setMap(null);
    }

    // For each place, get the icon, place name, and location.
    markers1 = [];
    var bounds1 = new google.maps.LatLngBounds();
    for (var i = 0, place1; place1 = places1[i]; i++) {
      var image1 = {
        url: place1.icon,
        size: new google.maps.Size(71, 71),
        origin: new google.maps.Point(0, 0),
        anchor: new google.maps.Point(17, 34),
        scaledSize: new google.maps.Size(25, 25)
      };

      // Create a marker for each place.
      var marker1 = new google.maps.Marker({
        map: map1,
        icon: image1,
        title: place1.name,
        position: place1.geometry.location
      });

      markers1.push(marker1);

      bounds1.extend(place1.geometry.location);
    }

    map1.fitBounds(bounds1);
  });
  // [END region_getplaces]

  // Bias the SearchBox results towards places that are within the bounds of the
  // current map's viewport.
  google.maps.event.addListener(map1, 'bounds_changed', function() {
    var bounds1 = map1.getBounds();
    searchBox1.setBounds(bounds1);
  });
}

google.maps.event.addDomListener(window, 'load', initialize);

function GetLocation() {
            var geocoder = new google.maps.Geocoder();
            var address = document.getElementById("pac-input1").value;
            geocoder.geocode({ 'address': address }, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    var latitude = results[0].geometry.location.lat();
                    var longitude = results[0].geometry.location.lng();
                   
                    //$("#d_latitude").val(latitude);
                    document.getElementById("d_latitude").value = latitude;
                    document.getElementById("d_longitude").value= longitude;
                    //$("#d_longitude").val(longitude);
                } else {
                    alert("Request failed.")
                }
            });
        };

</script>

    <style>
      #target {
        width: 345px;
      }
    </style>
  <div class="container" style="width: 100%;">
      <div class="row">
        <div class="col-md-12">
         
            <div class="form-group">
              <label>Select User</label>
              <select name ="owner_id" id ="owner_id" class="form-control">
                                @foreach($owner as $o)
                                <option id ="{{$o->id}}" value ="{{$o->id}}">{{$o->first_name}} {{$o->last_name}}</option>
                                @endforeach

              </select>
              </div>

              <div class="form-group">
              <label>Select Type</label>
              <select name ="type" id ="type" class="form-control">
                             <option id ="1" value='1'>Regular</option>
                                <option id="2" value='2'>Luxury</option>

                                                               

              </select>
              </div>
              <div class="form-group">
              <label>Select Payment Mode</label>
                <select name ="payment_mode" id ="payment_mode" class="form-control">
                            
                                <option id ="0" value='0'>Card</option>
                                <option id="1" value='1'>Cash</option>
                               

              </select>
              </div>
              <div class="form-group"> 
              <label>Instruction</label> 
              <input type="text" id="instruction" name="instruction" style="width:100%;">

              <input type="hidden" id="d_latitude"/>
              <input type="hidden" id="d_longitude"/>
            </div>
         
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <input id="pac-input" class="controls pac-input" type="text" placeholder="Search Box" value="">
          <div class="map-container">
            <div id="map-canvas" class="map-canvas"></div>
          </div>
        </div>
        <div class="col-md-6">
          <input id="pac-input1" class="controls pac-input" type="text" placeholder="Search Box" value="">
          <div class="map-container">
            <div id="map-canvas1" class="map-canvas"></div>
          </div>
        </div>
      </div>

      <div class="form-group" style="margin-top: 30px;"> 
        <input type="button" class="btn btn-success" value="Manual" onclick="manual_assign()" style="width: 100%;">
      </div>
      
    </div>  

<script>
  function manual_assign(){
    owner_id =$("#owner_id").val();
  
    if(!owner_id){
      alert("select user");
      return;
    }
    payment_mode = $('#payment_mode').val();
    if(!payment_mode){
      alert("Select payment mode");
      return;
    }
    source_address =$('#pac-input').val();
    if(!source_address){
      alert("Select source address");
      return;
    }
    destination_address=$('#pac-input1').val();
    if(!destination_address){
      alert("Select destination address");
      return;
    }
    d_longitude =$("#d_longitude").val();
    d_latitude=$("#d_latitude").val();
    
    
    type =$("#type").val();
  
    if(!type){
      alert("Select type");
      return;
    }
    instruction=$("#instruction").val();
  
  $.ajax({
           url: "{{ URL::Route('AdminManualAssign') }}",
          type: "GET",
            data:{
        'source_address':source_address,
        'destination_address':destination_address,
        'd_longitude':d_longitude,
        'd_latitude':d_latitude,
        'payment_mode':payment_mode,
        'type':type,
        'owner_id':owner_id
     },
            success: function (data) {
            
             if(data['success'] === false){
                alert(data['error']);
             }else{
              alert("Booked successfully");
             }
            },
            cache: false
        });
  
}
</script>
@stop 



