<?php include "header.php"; ?>
<!DOCTYPE html>
<html>
  <head>
    <title>Perform a query and improve this website. Hit enter and you'll see!</title>
    <link href="c3.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="site.css">

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

  function cmp($a, $b) {
    if ($a['score'] == $b['score']) {
        return 0;
    }
    return ($a['score'] > $b['score']) ? -1 : 1;
  }

 uasort($data, 'cmp');


?>
 <script>
 var visits = new Array();
 var titles = new Array();
 var counts = new Array();
 visits.push('Visits');
 counts.push('Keyword Count');
 <?php
 foreach ($data as $key => $entry)
 {
  ?>
  visits.push(<?php echo $entry['visits']; ?>);
  counts.push(<?php echo $entry['count']; ?>);
  titles.push('<?php echo $key; ?>');
  <?php
 }
 ?>


var chart = c3.generate({
    data: {
        columns: [
            counts, visits
        ],
        axes: {'Visits' : 'y2'},
        type: 'bar'
    },
    legend : {
    },
    axis : {
     x : {
      show: false,
      type : 'category',
      categories: titles
     }, 
     y2 : {
      show : true
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

 <div id="groups_small">
	<div id="group_index" class="menu_network">
		<h2>Title</h2>
		<ul>
<li><span class="tip">Title</span></li>
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
		<h2>Keyword Count</h2>
		<ul>
<li><span class="tip">Keyword Count</span></li>
 <?php
 foreach ($data as $key => $entry)
 {?>
<li><span class="tip"><?php echo $entry['count'];?></span></li>
 <?php } ?>

      </ul>
         </div>

        <div id="group_group" class="menu_network">
		<h2>Visits</h2>
		<ul>
<li><span class="tip">Visits</span></li>
 <?php
 foreach ($data as $key => $entry)
 {?>
<li><span class="tip"><?php echo $entry['visits'];?></span></li>
 <?php } ?>

      </ul>
         </div>



 <?php

 
}

?>

  </body>
</html>


