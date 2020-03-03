<?php
require("..\AD.php");
$AD = new AD;

if(isset($_POST['getUserData'])) {
  $AD->connect();
  $AD->bind();
  $data = $AD->searchAD();
  echo json_encode($AD->getUserData($data,$_POST['getUserData']));
}

 ?>
