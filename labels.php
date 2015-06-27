<?php include "header.php"; ?>
<!DOCTYPE html>
<html>
  <head>
    <title>Perform a query and improve this website. Hit enter and you'll see!</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <script type="text/javascript"
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBbEXrSJEVY9_x4hRxfAEEWw-_4KniRSz8&libraries=places">
    </script>
    <script type="text/javascript">



<?php
if(isset($_GET["lat"]))
{
 echo '</script>';
 echo '<link rel="stylesheet" type="text/css" href="site.css">';
 echo '</head><body>';
 $lat = $_GET["lat"];
 $lon = $_GET["lon"];
 $query = strtolower($_GET["query"]);
 $distance = $_GET["dist"];

 $string = exec("python query.py $lat $lon $query $distance");

 $data = json_decode($string, true);

 ?>

 <p><h2>Note: The purpose of this page is to help me improve the ranking system for wikiplaces.biz.</h2></p>
 <p><h3>There are some entries that should simply not be associated with a given query, like Manhattan showing up for the query "museum". I want to use supervised learning for identifying these.</h3></p>
 <p><h3>I therefore need labels for training. Help me out by checking entries that should and should not be given as results to the query you just submitted. </h3></p>
 <p><h3>I also note that you don't need to fill out the whole thing. Thanks!</h3></p>

 <h1>Results of the query <?php echo $query;?></h1>


 <form action="update_db_query.php" method="post">

 <input type="hidden" name="query" value="<?php echo $query; ?>">

 <div id="groups_small">
	<div id="group_index" class="menu_network">
		<h2>Title</h2>
		<ul>
<li><span class="tip">TITLE</span></li>
 <?php
 foreach ($data as $key => $entry)
 {
  $url = 'http://en.wikipedia.org/wiki/'.$key;
?>
<li><span class="tip"><a href="<?php echo $url; ?>"><?php echo $key;?></a></span></li>
 <?php } ?>

  </ul>
 </div>

        <div id="group_group" class="menu_network">
		<h2>Positive</h2>
		<ul>
<li><span class="tip">TRUE</span></li>
 <?php
 foreach ($data as $key => $entry)
 {?>
<li><span class="tip"><input type="checkbox" class="radio" name="<?php echo $key; ?>" value="1"></span></li>
 <?php } ?>

      </ul>
         </div>

         <div id="group_s1" class="menu_network">
         	<h2>Group</h2>
         	<ul>
<li><span class="tip">FALSE</span></li>
 <?php
 foreach ($data as $key => $entry)
 {?>
<li><span class="tip"><input type="checkbox" class="radio" name="<?php echo $key; ?>" value="0"></span></li>
 <?php } ?>
      </ul>

      </div>
   </div><div style="clear: both"></div>

 <input type="submit"><br>
 <a href="labels.php">Go back</a>

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
    <link rel="stylesheet" type="text/css" href="style.css">
  </head>
  <body>
<!--<div class="style3"></div><div class="style_2"><span class="style3"><a href="http://aribeiro.net.br" title="Andre Ribeiro"><strong>Andre Ribeiro</strong></a></span></div>-->

<?php print_header(); ?>
<p><h2>Perform a query and improve this website. Just hit enter and you'll see!</h2></p>
    <input id="pac-input" class="controls" type="text" placeholder="Type a location">
    <input id="activity-input" class="controls" type="text" placeholder="Type a keyword">
    <input id="distance-input" class="controls" type="text" placeholder="Type a distance (km)">
 <?php
}

?>

 <div id="map-canvas"></div>
   <?php
    if(isset($_GET["lat"])) { ?>
  <?php } ?>
  </body>
</html>


