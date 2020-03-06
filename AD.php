<?php

Class AD {

public $ds = '';
public $settings = '';

        function connect() {
            global $ds;
            global $settings;
            $settings = $this->readSettingsFile();
            $server = "ldaps://" . $settings->Server . ":636";
            return $ds = ldap_connect($server) or die("Could not connect to server. Please check your <a href='settings'>Settings</a>.");
        }

        function bind() {
          global $ds;
          global $settings;
          $user = $settings->Username . "@" . $settings->Domain;
          $psw = $settings->Password;
          ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
          ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
          $r = ldap_bind($ds, $user, $psw) or die("Could not bind to AD. Please check your <a href='settings'>Settings</a>.");
        }

        function searchAD() {
            global $ds;
            global $settings;
            $data = [];
            $count = 0;
            $searchOU = $settings->SearchOU;
            foreach($searchOU as $dn) {
              $search = "(&(objectCategory=organizationalPerson)(objectClass=User)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))";
  	          ldap_set_option($ds, LDAP_OPT_SIZELIMIT, 10000);
              $sr = ldap_search($ds, $dn, $search);
              $results = ldap_get_entries($ds, $sr);
              $count = $results["count"] + $count;
              array_shift($results);
              foreach($results as $result) {
                $authUsers = $this->readAuthFile();
                $adminUsers = $this->readAdminsFile();
                $hiddenUsers = array_merge($authUsers, $adminUsers);
                if(in_array(strtolower($result["samaccountname"][0]),$hiddenUsers) == false) {
                  $data[] = $result;
                }
              }
            }
              $data['count'] = $count;
              return $data;
        }

        function searchDisabledAD() {
            global $ds;
            global $settings;
            $data = [];
            $count = 0;
            $searchOU = $settings->SearchOU;
            foreach($searchOU as $dn) {
              $search = "(&(objectCategory=organizationalPerson)(objectClass=User)(userAccountControl:1.2.840.113556.1.4.803:=2))";
  	          ldap_set_option($ds, LDAP_OPT_SIZELIMIT, 10000);
              $sr = ldap_search($ds, $dn, $search);
              $results = ldap_get_entries($ds, $sr);
              $count = $results["count"] + $count;
              array_shift($results);
              foreach($results as $result) {
                $authUsers = $this->readAuthFile();
                $adminUsers = $this->readAdminsFile();
                $hiddenUsers = array_merge($authUsers, $adminUsers);
                if(in_array(strtolower($result["samaccountname"][0]),$hiddenUsers) == false) {
                  $data[] = $result;
                }
              }
            }
              $data['count'] = $count;
              return $data;
        }

        function searchTargetOU($searchOU) {
            global $ds;
            global $settings;
            $data = [];
            $count = 0;
            foreach($searchOU as $dn) {
              $search = "(&(objectCategory=organizationalPerson)(objectClass=User)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))";
  	          ldap_set_option($ds, LDAP_OPT_SIZELIMIT, 10000);
              $sr = ldap_search($ds, $dn, $search);
              $results = ldap_get_entries($ds, $sr);
              $count = $results["count"] + $count;
              echo $count;
              array_shift($results);
              foreach($results as $result) {
                $authUsers = $this->readAuthFile();
                $adminUsers = $this->readAdminsFile();
                $hiddenUsers = array_merge($authUsers, $adminUsers);
                if(in_array(strtolower($result["samaccountname"][0]),$hiddenUsers) == false) {
                  $data[] = $result;
                }
              }
            }
              $data['count'] = $count;
              return $data;
        }

        function getTargetOUCount($searchOU) {
              global $ds;
              global $settings;
              $count = 0;
              $search = "(&(objectCategory=organizationalPerson)(objectClass=User)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))";
              ldap_set_option($ds, LDAP_OPT_SIZELIMIT, 10000);
              $sr = ldap_search($ds, $searchOU, $search);
              $results = ldap_get_entries($ds, $sr);
              $count = $results["count"] + $count;
              return $count;
        }

        function displayUsers($data) {
          if($data["count"] === 0) { header("Location: index"); } else {
            for ($i = 0; $i < $data["count"]; $i++) {
                echo "<option value='" . $data[$i]["distinguishedname"][0] . "'>" . $data[$i]["cn"][0] . "</option>";
            }
          }
        }

        function updateUsersJSON($data) {
            $users = "[";
            for ($i = 0; $i < $data["count"] - 1; $i++) {
              if($i !== 0) { $users .= ","; }
                $users .= '{ "value": "' . $data[$i]["distinguishedname"][0] . '", "label": "' . $data[$i]["cn"][0] . '" }';
              }
            $users .= "]";
          echo $users;
        }

        function displayUserTemplates() {
          $userTemplates = $this->readUserTemplatesFile();
          if(count($userTemplates) > 0) {
            foreach($userTemplates as $userTemplate){
              echo "<option value='" . $userTemplate['name'] . "'>" . $userTemplate['name'] . "</option>";
            }
          } else { header("Location: addusertemplate"); }
        }

        function addUser($userTemplate,$user,$password) {
            global $ds;
            $user['objectclass'] = "User";
            $user['UserAccountControl'] = "66080";
            $group = $this->chooseUserTemplate($userTemplate,$user);
            $dn = "cn=" . $user['givenName'] . " " . $user['sn'] . "," . $group[1];
            $user = array_merge($user, $group[0]);
            $user = array_filter($user);
            print_r($user);
            if(ldap_add($ds,$dn,$user) === false) {
              $error = ldap_error($ds);
              $errno = ldap_errno($ds);
              $response = "Account cannot be added - " . $error . " (" . $errno . ")";
              return $response;
            } else {
              $this->resetPassword($dn,$password,null);
              $groups = $group[2];
              foreach($groups as $group){
              $this->addUsersToGroup($dn,$group);
              }
            }
        }

        function chooseUserTemplate($userTemplate,$user) {
          global $ds;
          $userTemplates = $this->readUserTemplatesFile();
          $response = array();
          $response[0]['homeDirectory'] = $userTemplates[$userTemplate]['homeDirectory'] . $user['sAMAccountName'];
          $response[0]['homeDrive'] = $userTemplates[$userTemplate]['homeDrive'];
          if($userTemplates[$userTemplate]['profilePath'] !== "") {
          $response[0]['profilePath'] = $userTemplates[$userTemplate]['profilePath'] . $user['sAMAccountName'];
          }
          $response[0]['scriptPath'] = $userTemplates[$userTemplate]['scriptPath'];
          $response[1] = $userTemplates[$userTemplate]['userOU'];
          $response[2] = $userTemplates[$userTemplate]['groupDN'];
          return $response;
         }

         function addUsersToGroup($dn,$group) {
             global $ds;
             $groupInfo['member'] = $dn;
             ldap_mod_add($ds,$group,$groupInfo);
         }

        function enableuser($user) {
            global $ds;
            $entry["UserAccountControl"] = "66080";
            if(ldap_mod_replace($ds,$user,$entry) === false) {
                $error = ldap_error($ds);
                $errno = ldap_errno($ds);
                $response = "User cannot be enabled - " . $error . " (" . $errno . ")";
                } else {
                $response = "User Enabled Successfully";
            }
            return $response;
        }

        function disableUser($user) {
            global $ds;
            $entry["UserAccountControl"] = "514";
            if(ldap_mod_replace($ds,$user,$entry) === false) {
                $error = ldap_error($ds);
                $errno = ldap_errno($ds);
                $response = "User cannot be disabled - " . $error . " (" . $errno . ")";
                } else {
                $response = "User Disabled Successfully";
            }
            return $response;
        }

        function resetPassword($user,$password,$change) {
            global $ds;
            $encoded_password = $this->hashPassword($password);
            $entry = array();
            if($change === "on") {
              $entry["pwdlastset"] = 0;
            }
            $entry["unicodePwd"] = "$encoded_password";
            if(ldap_mod_replace($ds,$user,$entry) === false) {
                $error = ldap_error($ds);
                $errno = ldap_errno($ds);
                $response = "Password cannot be reset - " . $error . " (" . $errno . ")";
                return $response;
                }
        }

        function hashPassword($newpassword) {
            $newpassword = "\"" . $newpassword . "\"";
            $len = strlen($newpassword);
            $newpass = "";
            for ($i = 0; $i < $len; $i++) $newpass .= "{$newpassword{$i}}\000";
            return $newpass;
        }

        function testFirstName($firstName) {
          $message = "";
          $matches = preg_match('/[`\'\"~!@#$*()<>,:;{}\|]/',$firstName);
          if ($matches === 1) {
            $message = "First name must not contain symbols.";
            return $message;
          }
          if (strlen($firstName) === 0) {
            $message = "You must enter a first name.";
            return $message;
          }
          if (strlen($firstName) > 44) {
            $message = "First name must be 44 characters or less.";
            return $message;
          }
          if ($message === "") {
            return "";
          } else {
            return $message;
          }
        }

        function testLastName($lastName) {
          $message = "";
          $matches = preg_match('/[`\'\"~!@#$*()<>,:;{}\|]/',$lastName);
          if ($matches === 1) {
            $message = "Last name must not contain symbols.";
            return $message;
          }
          if (strlen($lastName) === 0) {
            $message = "You must enter a last name.";
            return $message;
          }
          if (strlen($lastName) > 44) {
            $message = "Last name must be 44 characters or less.";
            return $message;
          }
          if ($message === "") {
            return "";
          } else {
            return $message;
          }
        }

        function testUsername($username) {
          $message = "";
          $matches = preg_match('/[`\'\"~!@# $*()<>,:;{}\|]/',$username);
          if ($matches === 1) {
            $message = "Username must not contain symbols or spaces.";
            return $message;
          }
          if (strlen($username) === 0) {
            $message = "You must enter a username.";
            return $message;
          }
          if (strlen($username) > 20) {
            $message = "Username must be 20 characters or less.";
            return $message;
          }
          if ($message === "") {
            return "";
          } else {
            return $message;
          }
        }

        function testPassword($password,$passwordConf) {
            global $settings;
            $message = "";
            if ($password != $passwordConf) {
              $message = "Passwords do not match.";
              return $message;
            }

            if (strlen($password) < $settings->PasswordMinLength) {
              $message = "Password must be at least " . $settings->PasswordMinLength . " character(s) long.";
              return $message;
            }
            if($message === "") {
              return "";
            } else {
              return $message;
            }
        }

        function readUserTemplatesFile() {
          $userTemplates = "";
          $userTemplatesFile = fopen(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "usertemplates.data", "r") or die("Unable to open user templates.");
          if(filesize(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "usertemplates.data") > 0) {
          $userTemplates = fread($userTemplatesFile,filesize(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "usertemplates.data"));
          $userTemplates = $this->decryptData($userTemplates);
          $userTemplates = json_decode($userTemplates, TRUE);
          fclose($userTemplatesFile);
          }
          return $userTemplates;
        }

        function addToUserTemplatesFile($userTemplate) {
          $userTemplates = $this->readUserTemplatesFile();
          $userTemplates[$userTemplate['userTemplateName']]['name'] = $userTemplate['userTemplateName'];
          $userTemplates[$userTemplate['userTemplateName']]['homeDirectory'] = $userTemplate['homeDirectory'];
          $userTemplates[$userTemplate['userTemplateName']]['homeDrive'] = $userTemplate['homeDrive'];
          $userTemplates[$userTemplate['userTemplateName']]['profilePath'] = $userTemplate['profilePath'];
          $userTemplates[$userTemplate['userTemplateName']]['scriptPath'] = $userTemplate['scriptPath'];
          $userTemplates[$userTemplate['userTemplateName']]['groupDN'] = $userTemplate['groupDN'];
          $userTemplates[$userTemplate['userTemplateName']]['userOU'] = $userTemplate['userOU'];
          $userTemplates = json_encode($userTemplates);
          $userTemplates = $this->encryptData($userTemplates);
          $userTemplatesFile = fopen(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "usertemplates.data", "w") or die("Unable to open user templates.");
          fwrite($userTemplatesFile, $userTemplates);
          fclose($userTemplatesFile);
        }

        function removeFromUserTemplatesFile($userTemplate) {
          $userTemplates = $this->readUserTemplatesFile();
          unset($userTemplates[$userTemplate]);
          $userTemplates = json_encode($userTemplates);
          $userTemplates = $this->encryptData($userTemplates);
          $userTemplatesFile = fopen(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "usertemplates.data", "w") or die("Unable to open user templates.");
          fwrite($userTemplatesFile, $userTemplates);
          fclose($userTemplatesFile);
        }

        function readSettingsFile() {
          $settings = json_decode('{"Server":"","Domain":"","Username":"","Password":"","SearchOU":""}');
          $settingsFile = fopen(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "settings.data", "r") or die("Unable to open settings.");
          if(filesize(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "settings.data") > 0) {
          $settings = fread($settingsFile,filesize(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "settings.data"));
          $settings = $this->decryptData($settings);
          $settings = json_decode($settings);
          fclose($settingsFile);
          }
          return $settings;
        }

        function writeSettingsFile($dc,$domain,$username,$password,$searchOU,$passwordMinLength = 0,$loginMessage = "Please login with your network credentials") {
          if($loginMessage === "") { $loginMessage = "Please login with your network credentials"; }
          $settingsFile = fopen(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "settings.data", "w") or die("Unable to open settings.");
          $settings = new \stdClass;
          $searchOU = explode("\r\n",$searchOU);
          $settings->Server = $dc;
          $settings->Domain = $domain;
          $settings->Username = $username;
          $settings->Password = $password;
          $settings->SearchOU = $searchOU;
          $settings->PasswordMinLength = $passwordMinLength;
          $settings->LoginMessage = $loginMessage;
          $settings = json_encode($settings);
          $settings = $this->encryptData($settings);
          fwrite($settingsFile, $settings);
          fclose($settingsFile);
        }

        function readActivityLogFile() {
          $activityLogFile = fopen(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "activity.log", "r") or die("Unable to open log.");
          if(filesize(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "activity.log") > 0) {
          $activities = fread($activityLogFile,filesize(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "activity.log"));
          $activities = explode("\n",$activities);
          return $activities;
          fclose($activityLogFile);
          }
        }

        function writeActivityLogFile($entry) {
          $activities = $this->readActivityLogFile();
          if(empty($activities)) {
            $activities = array();
            array_push($activities,$entry);
            $activities = implode("\r\n",$activities);
          } else {
          array_unshift($activities, $entry);
          $activities = implode("\r\n",$activities);
          }
          $activityLogFile = fopen(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "activity.log", "w") or die("Unable to open log.");
          fwrite($activityLogFile, $activities);
          fclose($activityLogFile);
        }

        function encryptData($plaintext) {
          $key = $this->getKey();
          $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
          $iv = openssl_random_pseudo_bytes($ivlen);
          $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
          $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
          return $ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );
        }

        function decryptData($ciphertext) {
          $key = $this->getKey();
          $c = base64_decode($ciphertext);
          $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
          $iv = substr($c, 0, $ivlen);
          $hmac = substr($c, $ivlen, $sha2len=32);
          $ciphertext_raw = substr($c, $ivlen+$sha2len);
          $plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
          $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
          if (hash_equals($hmac, $calcmac))
          {
              return $plaintext;
          }
        }

        function getKey() {
          $filename = substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . 'set.data';
          if(file_exists($filename)) {
            return file_get_contents($filename, true);
          } else {
            $bytes = random_bytes(60);
            ob_start();
            var_dump(bin2hex($bytes));
            $key = ob_get_clean();
            $key = substr($key,13);
            $key = substr($key, 0, -2);
            file_put_contents($filename, $key);
            return $key;
          }
        }

        function resetPrintQueue() {
            shell_exec('whoami');
            shell_exec('sc \\\\%COMPUTERNAME% stop spooler');
            shell_exec('del %windir%\system32\spool\printers\*.* /q');
            shell_exec('sc \\\\%COMPUTERNAME% start spooler');
        }

        function login() {
            global $ds;
            global $settings;
            if($_POST['inputUsername'] !== "" && $_POST['inputPassword'] !== "") {
                if(ldap_bind($ds, strtolower($_POST['inputUsername']) . "@" . $settings->Domain, $_POST['inputPassword']) === false) {
                  $errno = ldap_errno($ds);
                  return $errno;
                } else {
                  if($this->checkAuthLevel(strtolower($_POST['inputUsername'])) || $this->checkAdminLevel(strtolower($_POST['inputUsername']))) {
                  $_SESSION['username'] = $_POST['inputUsername'];
                  $_SESSION['admin'] = $this->checkAdminLevel(strtolower($_POST['inputUsername']));
                  return true;
                } else { return false; }
                }
            } else { return false; }
        }

        function isLoggedIn() {
          if(isset($_SESSION['username'])) { if($_SESSION['username'] !== null) { return true; } else { return false; } } else { return false; }
        }

        function logout() {
          $_SESSION = null;
          setcookie('PHPSESSID', '', time() - 7000000, '/');
          if(session_status() !== PHP_SESSION_NONE) { session_destroy(); }
          header("Location: login");
        }

        function readAuthFile() {
          $auth = array();
          if(filesize(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "auth.data") > 0) {
          $authFile = fopen(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "auth.data", "r");
          $auth = fread($authFile,filesize(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "auth.data"));
          $auth = explode("\n",$auth);
          $auth = array_map('trim', $auth);
          $auth = array_map('strtolower', $auth);
          fclose($authFile);
          }
          return $auth;
        }

        function writeAuthFile($authList) {
          $authFile = substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . 'auth.data';
          file_put_contents($authFile, $authList);
        }

        function writeAdminsFile($admins) {
          $adminsFile = substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . 'admins.data';
          file_put_contents($adminsFile, $admins);
        }


        function readAdminsFile() {
          $admins = array();
          if(filesize(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "admins.data") > 0) {
          $adminsFile = fopen(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "admins.data", "r");
          $admins = fread($adminsFile,filesize(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "admins.data"));
          $admins = explode("\n",$admins);
          $admins = array_map('trim', $admins);
          $admins = array_map('strtolower', $admins);
          fclose($adminsFile);
          return $admins;
          }
        }

        function checkAuthLevel($username) {
          $authList = $this->readAuthFile();
          if(in_array($username, $authList)) {
            return true;
          } else {
            return false;
          }
        }

        function checkAdminLevel($username) {
          $admins = $this->readAdminsFile();
          if(in_array($username, $admins)) {
            return true;
          } else {
            return false;
          }
        }

        function endsWith($currentString, $target) {
            $length = strlen($target);
            if ($length == 0) {
                return true;
            }
            return (substr($currentString, -$length) === $target);
        }

        function getUserData($data,$chosenUser) {
          foreach($data as $user){
            if($user['cn'][0] === $chosenUser) {
              $result = ["homedirectory"=>"","homedrive"=>"","profilepath"=>"","scriptpath"=>"","ou"=>"","groups"=>""];
              if(in_array("homedirectory", $user)) {
                $homeDirectory = explode("\\",$user['homedirectory'][0]);
                array_pop($homeDirectory);
                $result['homedirectory'] = implode("\\",$homeDirectory) . "\\";
              }
              if(in_array("homedrive", $user)) {
                $result['homedrive'] = substr($user['homedrive'][0], 0, 1);
              }
              if(in_array("profilepath", $user)) {
                $profilePath = explode("\\",$user['profilepath'][0]);
                array_pop($profilePath);
                $result['profilepath'] = implode("\\",$profilePath) . "\\";
              }
              if(in_array("scriptpath", $user)) {
                $result['scriptpath'] = $user['scriptpath'][0];
              }
              if(in_array("distinguishedname", $user)) {
                $ou = explode(",",$user['distinguishedname'][0]);
                array_shift($ou);
                $result['ou'] = implode(",",$ou);
              }
              if(in_array("memberof", $user)) {
              $groups = $user['memberof'];
              array_shift($groups);
              $result['groups'] = implode("\r\n",$groups);
              }
              return $result;
            }
          }
        }

        function showOUTree() {
          global $ds;
          global $settings;

          echo '<ul>
                  <li class="jstree-open">' . $settings->Domain;

            foreach($settings->SearchOU as $dn) {

              $filter="(objectClass=organizationalunit)";
              $justthese = array("dn", "ou");
              $sr=ldap_search($ds, $dn, $filter, $justthese);
              $info = ldap_get_entries($ds, $sr);

              echo '<ul>
                     <li value="' . $dn . '">' . $dn . '
                      <ul>';

              for ($i=0; $i < $info["count"]; $i++) {
                  $name = explode(",",$info[$i]['dn']);
                  $name = $name[0];
                  $name = substr($name, 3);

                  echo '<li value="' . $info[$i]['dn'] . '">' . $name . '</li>';
              }

              echo '</ul>
                  </li>
                </ul>';

            }

            echo '</li>
              </ul>';

        }

    }
