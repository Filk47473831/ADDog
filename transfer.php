<?php ob_start();
if(session_status() == PHP_SESSION_NONE) { session_start(); }
require("AD.php");
$AD = new AD;
$AD->connect();
$AD->bind();
if($AD->remoteManagement()) {
  $authid = $AD->remoteManagement()->AuthID;
  $authkey = $AD->remoteManagement()->AuthKey;
  $authkey = password_hash($authkey, PASSWORD_BCRYPT, array('cost' => 13));
  $data = "";
  $action = "checkWork";
  $AD->dataTransfer($authid,$authkey,$data,$action);
}
