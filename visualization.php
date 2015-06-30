<?php include "header.php"; ?>
<!DOCTYPE html>
<html>
  <head>
    <title>Perform a query and improve this website. Hit enter and you'll see!</title>
    <link href="c3.css" rel="stylesheet" type="text/css">

<!-- Load d3.js and c3.js -->
<script src="d3.min.js" charset="utf-8"></script>
<script src="c3.min.js"></script>
</head>
<body>

<div id="chart"></div>



<?php
if(isset($_GET["lat"]))
{
 $lat = $_GET["lat"];
 $lon = $_GET["lon"];
 $query = strtolower($_GET["query"]);
 $distance = $_GET["dist"];

 $string = exec("python query.py $lat $lon $query $distance");

 $data = json_decode($string, true);


?>
 <script>
 var visits = new Array();
 var titles = new Array();
 visits.push('Visits');
 <?php
 foreach ($data as $key => $entry)
 {
  ?>
  visits.push(<?php echo $entry['visits']; ?>);
  titles.push('<?php echo $key; ?>');
  <?php
 }
 ?>


var chart = c3.generate({
    data: {
        columns: [
            visits
            
        ],
        type: 'bar'
    },
    legend : {
    },
    axis : {
     x : {
      show: false,
      type : 'category',
      categories: titles
     }
    }, 
    bar: {
        width: {
            ratio: 0.5 // this makes bar width 50% of length between ticks
        }
        // or
        //width: 100 // this makes bar width 100px
    }
});

 </script>


 <?php

 
}

?>

  </body>
</html>


