<?php include "header.php"; ?>
<!DOCTYPE html>
<html>
  <head>
    <title>Wikiplaces - Data Incubator - Andre Ribeiro</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <script type="text/javascript"
      src="https://maps.googleapis.com/maps/api/js?v=3.20&key=AIzaSyBbEXrSJEVY9_x4hRxfAEEWw-_4KniRSz8&libraries=places">
    </script>
    <link rel="stylesheet" type="text/css" href="style.css">
    <script type="text/javascript">
var map;


<?php
if(isset($_GET["lat"]))
{
 $lat = $_GET["lat"];
 $lon = $_GET["lon"];
 $query = strtolower($_GET["query"]);

 if (isset($_GET["dist"]))
 {
  $distance = $_GET["dist"];
 }
 else
 {
  $distance = 10.0;
 }


 

 $string = exec("python query.py $lat $lon $query $distance");


 $data = json_decode($string, true);

 $icon = "images/$query.png";

 if (file_exists($icon))
 {
   echo "var image = \"$icon\";\n";
 }

 
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
    if ($a['score'] == $b['score']) {
        return 0;
    }
    return ($a['score'] > $b['score']) ? -1 : 1;
  }
  uasort($data, 'cmp');

  foreach ($data as $key => $entry)
  {
   $name_pos = "pos_$count";
   $lat   = $entry['lat'];
   $lon   = $entry['lon'];
   $url   = $entry['url'];
   $score = $entry['score'];

   echo "var location = new google.maps.LatLng($lat,$lon);\n";
   echo "var title = '$key\\nClick to go to Wikipedia'\n;";
   echo "var name = '$key'\n;";
   echo "var score = $score\n;";
   echo "var object = {'location' : location, 'name' : name, 'title' : title, 'url' : '$url'};\n";
   echo "places.push(object);\n";

 
   $count++;
  }
  ?>

  var maxresults = 20;

  if (places.length < maxresults)
  {
   maxresults = places.length;
  }

  for (var i = 0; i < maxresults; i++) {
   var place = places[i];

   var attribution = {
    iosDeepLinkId : 'sei lah', 
    source: 'wikiplaces', 
    webUrl: place.url};

   urls.push(place.url);

   attributions.push(attribution);

   //throw new Error(place.url);

   var marker = new google.maps.Marker({
     map: map,
     <?php
      if (file_exists($icon))
      {
       echo "icon: image,\n";
      }
     ?>
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



   placesList.innerHTML += '<li>' + '<a href="' + place.url + '">' + place.name + '</a></li>';

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
  var distance = document.getElementById('distance-input')

  var searchBox = new google.maps.places.SearchBox(
    /** @type {HTMLInputElement} */(input));

  // [START region_getplaces]
  // Listen for the event fired when the user selects an item from the
  // pick list. Retrieve the matching places for that item.
  google.maps.event.addListener(searchBox, 'places_changed', function() {
    var places = searchBox.getPlaces();
    //echo places;
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
     var lat  = '?lat='.concat(places[0].geometry.location.A);
     var lon  = '\&lon='.concat(places[0].geometry.location.F);
     var dist = '\&dist='.concat(distance.value);
     var key  = '\&query='.concat(activity.value);
     //throw new Error(activity.value);
     //location.href = 'index.php';
     location.search = ((lat.concat(lon)).concat(key)).concat(dist);

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
<!--<div class="style3"></div><div class="style_2"><span class="style3"><a href="http://aribeiro.net.br" title="Andre Ribeiro"><strong>Andre Ribeiro</strong></a></span></div>-->

<?php print_header(); ?>

    <input id="pac-input" class="controls" type="text" placeholder="Type a location">
    <input id="activity-input" class="controls" type="text" placeholder="Type a keyword">
    <input id="distance-input" class="controls" type="text" placeholder="Type a distance (km)">
 <?php
}

?>


 <div id="map-canvas"></div>
   <?php
    
    $string2 = preg_replace('(\')', '&#39', $string);
    if(isset($_GET["lat"])) { ?>
    <div id="results">
 <form action="visualization.php" method="post" id="visualization">
 <input type="hidden" name="data" value='<?php echo $string2;?>'>
      <!--<h2>Results (<a href="visualization.php?lat=<?php echo $_GET['lat'];?>&lon=<?php echo $_GET['lon'];?>&query=<?php echo $query;?>&dist=<?php echo $distance;?>">Details</a>)</h2>-->
      <h2>Results (<a href="javascript:{}" onclick="document.getElementById('visualization').submit(); return false;">Details</a>)</form></h2>
      <ul id="places"></ul>
      <!--<button id="more">More results</button>-->
    </div>
  <?php } ?>
  </body>
</html>

