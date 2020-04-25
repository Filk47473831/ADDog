<?php ob_start();
if(session_status() == PHP_SESSION_NONE) { session_start(); }
require("AD.php");
$AD = new AD;
$AD->connect();
$AD->bind();
$authid = substr($AD->getKey(),0,12);
$authkey = "rDyu6ghCZ33hQDDXJuzNnL8k5PcjB3YAyiSrmaY2FJ2BH";
$authkey = password_hash($authkey, PASSWORD_BCRYPT, array('cost' => 13));
$data = "";
$action = "checkWork";
$AD->dataTransfer($authid,$authkey,$data,$action);
