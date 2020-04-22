<?php ob_start();
if(session_status() == PHP_SESSION_NONE) { session_start(); }
require("AD.php");
$AD = new AD;

$AD->connect();
$AD->bind();

$authid = $AD->getKey();
$key = "rDyu6ghCZ33hQDDXJuzNnL8k5PcjB3YAyiSrmaY2FJ2BHQeX3XsQYUsgteybdMebBZc25PhAh6J6V66bttyn7LemFxB33iiW9gm9";

if(isset($_GET['data'])) {

  $data = $_GET['data'];
  if(isset($_GET['result'])) { $data .= "&result=" . $_GET['result']; }
      if(isset($_GET['error'])) {
        $data .= "&error=" . urlencode($_GET['error']);
      }

} else { $data = ""; }

$url = 'https://mybalance.io/devtest/addog/addog.php';
$payload = 'authid=' . $authid . '&key=' . $key . '&data=' . $data;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec($ch);

$response = json_decode($response);

if($response[0] === $authid) {

  echo $response[0];
  echo $response[1];

  $data = json_decode($response[2]);

  $workid = $data[0];

  echo $workid;

    switch ($response[1]) {

      case "dataSubmitted":

        echo "Data Sent Successfully: " . $payload;

      break;

      case "resetPassword":

      $data = json_decode($data[1]->data);

      $testPassword = $AD->testPassword($data->password,$data->password);
      $name = explode(",",$data->user);
      if($testPassword == "") {
          $testPassword = $AD->resetPassword($data->user,$data->password,$data->promptnextlogin);
          if($name !== "") {
            $AD->writeActivityLogFile(gmdate("d-m-y h:i:sa") . ",Password Reset," . substr($name[0], 3) . ",Remote Management");
          }
          if($testPassword == "") {
            $data = "data=" . $workid . "&result=success";
            header("Location: request?" . $data);
          } else {
            $data = "data=" . $workid . "&result=fail&error=" . $testPassword;
            header("Location: request?" . $data);
          }
      } else {
        $data = "data=" . $workid . "&result=fail&error=" . $testPassword;
        header("Location: request?" . $data);
      }

      break;

    }

}

print_r(curl_getinfo($ch));
echo curl_errno($ch) . '-' . curl_error($ch);

curl_close($ch);
