<?php

 $mongo = new MongoClient("mongodb://104.236.201.75");
 $db = $mongo->selectDB("wikiplaces");
 $col = $db->pages;





 $query = $_POST["query"];

 unset($_POST["query"]);

 /*$qticket = array('ticket' => $data['ticket']);
 $options = array('upsert' => 1);
 $col->update($qticket,$data,$options);*/

 echo '<h2>Thanks for helping out! <a href="http://wikiplaces.biz/labels.php">Additional help</a> is also welcome!</h2>';


 foreach ($_POST as $key => $value)
 {
  $label = array("museum" => (int) $value);

  $title = preg_replace('(_)', ' ', $key);

  /*$newdata = array('$set' => array("labels" => $label));*/
  $newdata = array('$push' => array("labels.$query" => (int) $value));
  $col->update(array("title" => $title), $newdata);

 }
?>
