<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <style>
      html, body, #map-canvas {
        height: 100%;
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

      #pac-input {
        background-color: #fff;
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
        margin-left: 12px;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        width: 400px;
      }

      #pac-input:focus {
        border-color: #4d90fe;
      }

      .pac-container {
        font-family: Roboto;
      }

      #type-selector {
        color: #fff;
        background-color: #4d90fe;
        padding: 5px 11px 0px 11px;
      }

      #type-selector label {
        font-family: Roboto;
        font-size: 13px;
        font-weight: 300;
      }
    </style>
    <script type="text/javascript"
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBbEXrSJEVY9_x4hRxfAEEWw-_4KniRSz8&libraries=places">
    </script>
    <script type="text/javascript">
var map;


<?php
if(isset($_GET["query"]))
{
 ?>
 function initialize() {
   var mapOptions = {
     zoom: 8
   };
   map = new google.maps.Map(document.getElementById('map-canvas'),
       mapOptions);
   var pos = new google.maps.LatLng(40.807833,-73.962126);
 <?php
  $query = $_GET["query"];
  //$string = file_get_contents("$query.json");
  $string = exec("python query.py $query");
  #echo 1;
  #system('echo 1');
  $data = json_decode($string, true);

  echo "var image = '$query.png'\n";

  $count = 0;
  foreach ($data as $key => $entry)
  {
   $name_pos = "pos_$count";
   $lat = $entry['lat'];
   $lon = $entry['lon'];
   echo "var $name_pos = new google.maps.LatLng($lat,$lon);\n";

   $name_mark = "mark_$count";
   echo "var $name_mark = new google.maps.Marker({\n";
   echo "   map: map,\n";
   echo "   position: $name_pos,\n";
   if($query == 'climbing' or $query == 'hiking')
   {
    echo "   icon: image,\n";
   }
   echo "   title: '$key\\nClick to go to Wikipedia'\n";
   echo "});\n";

   $url = $entry['url'];

   echo "google.maps.event.addListener($name_mark, 'click', function() {\n";
   echo "window.location.href = '$url'\n";
   echo "});\n";
 
   $count++;
  }
  ?>
  //var pos_gunks = new google.maps.LatLng(41.7038888889,-74.3447222222);

      var infowindow = new google.maps.InfoWindow({
        map: map,
        position: pos,
        content: 'Columbia University'
      });
      map.setCenter(pos);

  // Try HTML5 geolocation
  /*if(navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      var pos = new google.maps.LatLng(position.coords.latitude,
                                       position.coords.longitude);

      var infowindow = new google.maps.InfoWindow({
        map: map,
        position: pos,
        content: 'Location found using HTML5.'
      });

      map.setCenter(pos);
    }, function() {
      handleNoGeolocation(true);
    });
  } else {
    // Browser doesn't support Geolocation
    handleNoGeolocation(false);
  }*/
 }
 google.maps.event.addDomListener(window, 'load', initialize);
    </script>
  </head>
  <body>
 <?php
}
else
{
 $query = "climbing";
 ?>
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
    //document.write(places[0].position)
    /*$.ajax({
                type: "GET",
                url: "clear.php" ,
                data: { h: "michael" },
                success : function() { 

                    // here is the code that will run on client side after running clear.php on server

                    // function below reloads current page
                    location.reload();

                }
            });*/

     location.href = 'index.php';
     location.search = '?query=hiking';

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
  </head>
  <body>
    <input id="pac-input" class="controls" type="text" placeholder="Search Box">
 <?php
}

?>


 <div id="map-canvas"></div>
  </body>
</html>

