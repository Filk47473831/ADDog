<?php
require("..\AD.php");
$AD = new AD;

if(isset($_POST['getUserData'])) {
  $AD->connect();
  $AD->bind();
  $data = $AD->searchAD();
  echo json_encode($AD->getUserData($data,$_POST['getUserData']));
}

if(isset($_POST['chosenUserTemplate'])) {
  $AD->connect();
  $AD->bind();
  $data = $AD->chooseUserTemplate($_POST['chosenUserTemplate'],null);
  echo json_encode($data);
}

if(isset($_POST['targetSearchOU'])) {
  $AD->connect();
  $AD->bind();
  $data = $AD->getTargetOUCount($_POST['targetSearchOU']);
  echo $data;
}

 ?>
