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

      #results {
        font-family: Arial, Helvetica, sans-serif;
        position: absolute;
        right: 5px;
        top: 50%;
        margin-top: -195px;
        height: 380px;
        width: 200px;
        padding: 5px;
        z-index: 5;
        border: 1px solid #999;
        background: #fff;
      }
      h2 {
        font-size: 22px;
        margin: 0 0 5px 0;
      }
      ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
        height: 321px;
        width: 200px;
        overflow-y: scroll;
      }
      li {
        background-color: #f1f1f1;
        padding: 10px;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
      }
      li:nth-child(odd) {
        background-color: #fcfcfc;
      }
      #more {
        width: 100%;
        margin: 5px 0 0 0;
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
      #activity-input {
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
      #activity-input:focus {
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
if(isset($_GET["lat"]))
{
 $lat = $_GET["lat"];
 $lon = $_GET["lon"];
 $query = $_GET["query"];
 $string = exec("python query.py $lat $lon $query");
 $data = json_decode($string, true);
 echo "var image = '$query.png'\n";
 
 ?>
 function initialize() {
   var mapOptions = {
     zoom: 8
   };
   map = new google.maps.Map(document.getElementById('map-canvas'),
       mapOptions);

   placesList = document.getElementById('places');

   createMarkers(map);

 }
 function createMarkers(map) {
  var markerBounds = new google.maps.LatLngBounds();
  var lat = <?php echo $lat; ?>;
  var lon = <?php echo $lon; ?>;
  var pos = new google.maps.LatLng(lat,lon);
  markerBounds.extend(pos);

  var places = new Array();
  var attributions = new Array();
  var markers = new Array();
  var urls = new Array();

 <?php
  $count = 0;
  function cmp($a, $b) {
    if ($a['count'] == $b['count']) {
        return 0;
    }
    return ($a['count'] > $b['count']) ? -1 : 1;
  }
  uasort($data, 'cmp');

  foreach ($data as $key => $entry)
  {
   $name_pos = "pos_$count";
   $lat   = $entry['lat'];
   $lon   = $entry['lon'];
   $url   = $entry['url'];
   $count = $entry['count'];

   echo "var location = new google.maps.LatLng($lat,$lon);\n";
   echo "var title = '$key\\nClick to go to Wikipedia'\n;";
   echo "var name = '$key'\n;";
   echo "var count = $count\n;";
   echo "var object = {'location' : location, 'name' : name, 'title' : title, 'url' : '$url'};\n";
   //echo "var object = {'location' : location, 'title' : title};\n";
   echo "places.push(object);\n";
   //echo "places.push(item);\n";

   /*$name_mark = "mark_$count";
   echo "var $name_mark = new google.maps.Marker({\n";
   echo "   map: map,\n";
   echo "   position: $name_pos,\n";
   if($query == 'climbing' or $query == 'hiking')
   {
    echo "   icon: image,\n";
   }
   echo "   title: '$key\\nClick to go to Wikipedia'\n";
   echo "});\n";

   echo "markerBounds.extend($name_pos);";


   echo "google.maps.event.addListener($name_mark, 'click', function() {\n";
   echo "window.location.href = '$url'\n";
   echo "});\n";*/
 
   $count++;
  }
  ?>

  //for (var i = 0, place; place = places[i]; i++) {
  //for (var i = 0; i < places.length; i++) {
  for (var i = 0; i < 10; i++) {
   var place = places[i];
   /*var image = {
    url: place.icon,
     size: new google.maps.Size(71, 71),
     origin: new google.maps.Point(0, 0),
     anchor: new google.maps.Point(17, 34),
     scaledSize: new google.maps.Size(25, 25)
   };*/


   var attribution = {
    iosDeepLinkId : 'sei lah', 
    source: 'wikiplaces', 
    webUrl: place.url};

   urls.push(place.url);

   attributions.push(attribution);

   //throw new Error(place.url);

   var marker = new google.maps.Marker({
     map: map,
     //icon: image,
     title: place.title,
     position: place.location,
     attribution : { iosDeepLinkId: 'sei lah',
                     source: 'wikiplaces',
                     webUrl: String(i)}
   });

   markers.push(marker);

   google.maps.event.addListener(marker, 'click', (function(marker, i) 
   {
    return function() {
     window.location.href = places[i].url;
    }
   })(marker, i));



   placesList.innerHTML += '<li>' + place.name + '</li>';

   markerBounds.extend(place.location);
  }
  map.fitBounds(markerBounds);
  map.setCenter(pos);
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
      //new google.maps.LatLng(-33.8902, 151.1759),
      //new google.maps.LatLng(-33.8474, 151.2631));
      new google.maps.LatLng(40.8902, -73.1759),
      new google.maps.LatLng(40.8474, -73.2631));
  map.fitBounds(defaultBounds);

  // Create the search box and link it to the UI element.
  var input = /** @type {HTMLInputElement} */(
      document.getElementById('pac-input'));
  map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
  var activity = document.getElementById('activity-input')

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
     var lat = '?lat='.concat(places[0].geometry.location.A);
     var lon = '\&lon='.concat(places[0].geometry.location.F);
     var key = '\&query='.concat(activity.value);
     //throw new Error(activity.value);
     //location.href = 'index.php';
     location.search = (lat.concat(lon)).concat(key);

    if (places.length == 0) {
      return;
    }

    // For each place, get the icon, place name, and location.
    markers = [];
    var bounds = new google.maps.LatLngBounds();
    for (var i = 0, place; place = places[i]; i++) {

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
    <input id="pac-input" class="controls" type="text" placeholder="Type a location">
    <input id="activity-input" class="controls" type="text" placeholder="Type a keyword">
 <?php
}

?>


 <div id="map-canvas"></div>
   <?php
    if(isset($_GET["lat"])) { ?>
    <div id="results">
      <h2>Results</h2>
      <ul id="places"></ul>
      <button id="more">More results</button>
    </div>
  <?php } ?>
  </body>
</html>

