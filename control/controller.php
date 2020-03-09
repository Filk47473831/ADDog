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
  $availableGroups = $AD->searchForGroupsAD();
  $chosenGroups = $data[2];
  if($chosenGroups === null) { $chosenGroups = []; }
  $finalGroups = [];
  foreach($availableGroups as $availableGroup) {
    if(in_array($availableGroup['distinguishedname'][0],$chosenGroups)) {
      $finalGroups[] = $availableGroup['cn'][0];
    }
  }
  $data[2] = $finalGroups;
  $data[2] = implode("\r\n",$data[2]);
  echo json_encode($data);
}

if(isset($_POST['targetSearchOU'])) {
  $AD->connect();
  $AD->bind();
  $data = $AD->getTargetOUCount($_POST['targetSearchOU']);
  echo $data;
}

if(isset($_POST['updateAuthorisedAdmins'])) {
  $authList = $AD->readAuthFile();
  $distinguishedNames = explode("\r\n", $_POST['distinguishednames']);
  if(is_array($distinguishedNames)) { $distinguishedNames = json_decode($distinguishedNames); }
  $authList[$_POST['updateAuthorisedAdmins']]['username'] = $_POST['updateAuthorisedAdmins'];
  $authList[$_POST['updateAuthorisedAdmins']]['distinguishednames'] = $_POST['distinguishednames'];
  $AD->writeAuthFile($authList);
}

if(isset($_POST['clearAuthorisedAdmins'])) {
  $authList = null;
  $AD->writeAuthFile($authList);
}

if(isset($_POST['clearAllLog'])) {
  $AD->writeActivityLogFile("CLEAR_ALL_LOG");
}

 ?>
