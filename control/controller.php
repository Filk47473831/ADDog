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
  $username = $_POST['updateAuthorisedAdmins'];
  $distinguishedNames = explode(", ", $_POST['distinguishednames']);
  $distinguishedNames = array_unique($distinguishedNames);
  $distinguishedNames = array_filter($distinguishedNames);
  $authList[$username]['username'] = $username;
  $authList[$username]['distinguishednames'] = $distinguishedNames;
  $AD->writeAuthFile($authList);
}

if(isset($_POST['clearAuthorisedAdmins'])) {
  $authList = null;
  $AD->writeAuthFile($authList);
}

if(isset($_POST['clearAllLog'])) {
  $AD->writeActivityLogFile("CLEAR_ALL_LOG");
}

if(isset($_POST['resetPrintQueue'])) {
  echo $AD->resetPrintQueue();
  $AD->writeActivityLogFile(gmdate("d-m-y h:i:sa") . ",Print Queue Reset,-," . $_SESSION['username']);
}

 ?>
