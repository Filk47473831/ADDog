<?php
require("..\AD.php");
$AD = new AD;

if(isset($_POST['addUser'])) {
  $AD->connect();
  $AD->bind();
  $user = explode(",", $_POST['addUser']);

  $user['inputFirstName'] = $user[0];
  $user['inputLastName'] = $user[1];
  $user['inputUsername'] = $user[2];
  $user['inputPassword'] = $user[3];

  if(($user['inputFirstName'] !== "") && ($user['inputLastName'] !== "") && ($user['inputUsername'] !== "") && ($user['inputPassword'] !== "")) {

                    $addAccount = "";
                    $testFirstName = $AD->testFirstName($user['inputFirstName']);
                    $testLastName = $AD->testLastName($user['inputLastName']);
                    $testUsername = $AD->testUsername($user['inputUsername']);
                    $testPassword = $AD->testPassword($user['inputPassword'],$user['inputPassword']);

                    if(($testFirstName == "") && ($testLastName == "") && ($testUsername == "") && ($testPassword == "")) {

                      $userTemplate = $_POST['inputUserTemplate'];
                      $info = array();
                      $info["cn"] = $user['inputFirstName'] . " " . $user['inputLastName'];
                      $info['givenName'] = $user['inputFirstName'];
                      $info["sn"] = $user['inputLastName'];
                      $info["sAMAccountName"] = $user['inputUsername'];
                      $info["UserPrincipalName"] = $user['inputUsername'] . "@" . $settings->Domain;
                      $password = $user['inputPassword'];
                      $addAccount = $AD->addUser($userTemplate,$info,$password,$_POST['inputUserOU'],null);

                      if($addAccount == "") {

                          echo 'Added Successfully';

                        } else {

                          echo $addAccount;

                        }

                      } else {

                        echo 'Account cannot be added - ' . $testFirstName . " - " . $testLastName . " - " . $testUsername . " - " . $testPassword;

                      }
            } else {

              echo 'Account cannot be added - Missing Info';

            }
}

if(isset($_POST['resetPw'])) {
  $AD->connect();
  $AD->bind();

  $username = $AD->getDnFromUsername($_POST['resetPw']);
  $testPassword = $AD->testPassword($_POST['password'],$_POST['password']);

  if($testPassword == "") {

        $passwordReset = $AD->resetPassword($username,$_POST['password'],$_POST['promptNextLogin']);
        $name = explode(",",$username);
        if($name !== "") {
          $AD->writeActivityLogFile(gmdate("d-m-y h:i:sa") . ",Password Reset," . substr($name[0], 3) . "," . $_SESSION['username']);
        }
      //echo $passwordReset;
      echo $promptNextLogin;
  } else {
    echo $testPassword;
  }

}

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

if(isset($_POST['targetGetUsersFromOU'])) {
  $AD->connect();
  $AD->bind();
  $searchOU[] = $_POST['targetGetUsersFromOU'];
  $data = $AD->searchTargetOU($searchOU);
  $data = $AD->displayUsernames($data);
  echo json_encode($data);
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
