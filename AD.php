<?php ob_start(); ?>
<?php if(session_status() == PHP_SESSION_NONE) { session_start(); }

Class AD {

public $username;
public $ds = '';
public $settings = '';

public function __construct() {
    $this->username = strtolower($_SESSION['username']);
    $this->admin = $_SESSION['admin'];
}

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

            if($this->checkAdminLevel($this->username)) { $searchOU = $settings->SearchOU; } else {
              $searchOU = $this->getAuthorisedOU($this->username);
            }

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
                if(array_key_exists(strtolower($result["samaccountname"][0]),$authUsers) || in_array(strtolower($result["samaccountname"][0]),$adminUsers)) { } else { $data[] = $result; }
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

            if($this->checkAdminLevel($this->username)) { $searchOU = $settings->SearchOU; } else {
              $searchOU = $this->getAuthorisedOU($this->username);
            }

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
                if(array_key_exists(strtolower($result["samaccountname"][0]),$authUsers) || in_array(strtolower($result["samaccountname"][0]),$adminUsers)) { } else { $data[] = $result; }
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
              array_shift($results);
              foreach($results as $result) {
                $authUsers = $this->readAuthFile();
                $adminUsers = $this->readAdminsFile();
                if(array_key_exists(strtolower($result["samaccountname"][0]),$authUsers) || in_array(strtolower($result["samaccountname"][0]),$adminUsers)) { } else { $data[] = $result; }
                }
              }
              $data['count'] = $count;
              return $data;
        }

        function searchForGroupsAD() {
            global $ds;
            global $settings;
            $data = [];
            $count = 0;
            $searchOU = $settings->SearchOU;
            foreach($searchOU as $dn) {
              $search = "(objectClass=group)";
              ldap_set_option($ds, LDAP_OPT_SIZELIMIT, 10000);
              $sr = ldap_search($ds, $dn, $search);
              $results = ldap_get_entries($ds, $sr);
              $count = $results["count"] + $count;
              array_shift($results);
              foreach($results as $result) {
                  $data[] = $result;
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
            for ($i = 0; $i < $data["count"]; $i++) {
              if(isset($data[$i])) {
                if($i !== 0) { $users .= ","; }
                  $users .= '{ "value": "' . $data[$i]["distinguishedname"][0] . '", "label": "' . $data[$i]["cn"][0] . '" }';
                }
              }
            $users .= "]";
          echo $users;
        }

        function displayUserTemplates() {
          $userTemplates = $this->readUserTemplatesFile();
          $response = "";
          if(is_array($userTemplates)) {
            if(count($userTemplates) > 0) {
              foreach($userTemplates as $userTemplate){
                $authorisedUsers = explode(",", $userTemplate['authorisedUsers']);
                if(in_array($this->username, $authorisedUsers) || $this->admin) {
                  $response .= "<option value='" . $userTemplate['name'] . "'>" . $userTemplate['name'] . "</option>";
                }
              }
              if($response == "") { $response .= "<option value='null'>No Available Templates</option>"; }
            } else { $response .= "<option value='null'>No Available Templates</option>"; }
          } else { $response .= "<option value='null'>No Available Templates</option>"; }
          echo $response;
        }

        function addUser($userTemplate,$user,$password,$userOU,$groups) {
            global $ds;
            $user['objectclass'] = "User";
            $user['UserAccountControl'] = "66080";
            if($userTemplate !== null) {
              $user['displayName'] = $user['givenName'] . " " . $user['sn'];
              $group = $this->chooseUserTemplate($userTemplate,$user);
              $user = array_merge($user, $group[0]);
              if($userOU !== null) {
                $dn = "cn=" . $user['givenName'] . " " . $user['sn'] . "," . $userOU;
              } else {
                $dn = "cn=" . $user['givenName'] . " " . $user['sn'] . "," . $group[1];
              }
            } else {
              $dn = "cn=" . $user['givenName'] . " " . $user['sn'] . "," . $userOU;
              $user['displayName'] = $user['givenName'] . " " . $user['sn'];
              $user['mail'] = str_replace("%USERNAME%",$user['sAMAccountName'],$user['mail']);
              $user['proxyAddresses'] = "SMTP:" . $user['mail'];
              $user['homeDirectory'] = str_replace("%USERNAME%",$user['sAMAccountName'],$user['homeDirectory']);
              $user['profilePath'] = str_replace("%USERNAME%",$user['sAMAccountName'],$user['profilePath']);
            }
            $user = array_filter($user);
            if(ldap_add($ds,$dn,$user) === false) {
              $error = ldap_error($ds);
              $errno = ldap_errno($ds);
              $response = "Account cannot be added - " . $error . " (" . $errno . ")";
              return $response;
            } else {
              $this->resetPassword($dn,$password,null);
              if($userTemplate !== null) {
                $groups = $group[2];
              }
              foreach($groups as $group){
                $this->addUsersToGroup($dn,$group);
              }
            }
        }

         function addUsersToGroup($dn,$group) {
             global $ds;
             $groupInfo['member'] = $dn;
             ldap_mod_add($ds,$group,$groupInfo);
         }

        function enableUser($user) {
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

        function removeUser($user) {
            global $ds;
            if(ldap_delete($ds,$user) === false) {
                $error = ldap_error($ds);
                $errno = ldap_errno($ds);
                $response = "User cannot be removed - " . $error . " (" . $errno . ")";
                } else {
                $response = "User Removed Successfully";
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
          }
          if (strlen($firstName) === 0) {
            $message = "You must enter a first name.";
          }
          if (strlen($firstName) > 44) {
            $message = "First name must be 44 characters or less.";
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
          }
          if (strlen($lastName) === 0) {
            $message = "You must enter a last name.";
          }
          if (strlen($lastName) > 44) {
            $message = "Last name must be 44 characters or less.";
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
          }
          if (strlen($username) === 0) {
            $message = "You must enter a username.";
          }
          if (strlen($username) > 20) {
            $message = "Username must be 20 characters or less.";
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
            }

            if (strlen($password) < $settings->PasswordMinLength) {
              $message = "Password must be at least " . $settings->PasswordMinLength . " character(s) long.";
            }
            if($message === "") {
              return "";
            } else {
              return $message;
            }
        }

        function testUserOU($userOU) {
            global $settings;
            $message = "";
            if (strlen($userOU) < 3) {
              $message = "You must specify a user OU.";
            }
            return $message;
        }

        function readUserTemplatesFile() {
          $userTemplates = [];
          $userTemplatesFile = fopen(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "usertemplates.data", "r") or die("Unable to open user templates.");
          if(filesize(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "usertemplates.data") > 0) {
          $userTemplates = fread($userTemplatesFile,filesize(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "usertemplates.data"));
          $userTemplates = $this->decryptData($userTemplates);
          $userTemplates = json_decode($userTemplates, TRUE);
          fclose($userTemplatesFile);
          }
          return $userTemplates;
        }

        function chooseUserTemplate($userTemplate,$user) {
          global $ds;
          $userTemplates = $this->readUserTemplatesFile();
          $response = array();
          if($user !== null) { $response[0]['mail'] = str_replace("%USERNAME%",$user['sAMAccountName'],$userTemplates[$userTemplate]['mail']); } else {
            $response[0]['mail'] = $userTemplates[$userTemplate]['mail'];
            $response[0]['proxyAddresses'] = "SMTP:" . $response[0]['mail'];
          }
          if($user !== null) { $response[0]['homeDirectory'] = str_replace("%USERNAME%",$user['sAMAccountName'],$userTemplates[$userTemplate]['homeDirectory']); } else {
            $response[0]['homeDirectory'] = $userTemplates[$userTemplate]['homeDirectory'];
          }
          $response[0]['homeDrive'] = $userTemplates[$userTemplate]['homeDrive'];
          if($user !== null) { $response[0]['profilePath'] = str_replace("%USERNAME%",$user['sAMAccountName'],$userTemplates[$userTemplate]['profilePath']); } else {
            $response[0]['profilePath'] = $userTemplates[$userTemplate]['profilePath'];
          }
          $response[0]['scriptPath'] = $userTemplates[$userTemplate]['scriptPath'];
          $response[1] = $userTemplates[$userTemplate]['userOU'];
          $response[2] = $userTemplates[$userTemplate]['groupDN'];
          $response[3] = $userTemplates[$userTemplate]['upnSuffix'];
          $response[4] = $userTemplates[$userTemplate]['usernameFormat'];
          return $response;
         }

        function addToUserTemplatesFile($userTemplate) {
          $userTemplates = [];
          $userTemplates = $this->readUserTemplatesFile();
          $userTemplates[$userTemplate['userTemplateName']]['name'] = $userTemplate['userTemplateName'];
          $userTemplates[$userTemplate['userTemplateName']]['mail'] = $userTemplate['mail'];
          $userTemplates[$userTemplate['userTemplateName']]['homeDirectory'] = $userTemplate['homeDirectory'];
          $userTemplates[$userTemplate['userTemplateName']]['homeDrive'] = $userTemplate['homeDrive'];
          $userTemplates[$userTemplate['userTemplateName']]['profilePath'] = $userTemplate['profilePath'];
          $userTemplates[$userTemplate['userTemplateName']]['scriptPath'] = $userTemplate['scriptPath'];
          $userTemplates[$userTemplate['userTemplateName']]['groupDN'] = $userTemplate['groupDN'];
          $userTemplates[$userTemplate['userTemplateName']]['upnSuffix'] = $userTemplate['upnSuffix'];
          $userTemplates[$userTemplate['userTemplateName']]['userOU'] = $userTemplate['userOU'];
          $userTemplates[$userTemplate['userTemplateName']]['usernameFormat'] = $userTemplate['usernameFormat'];
          $userTemplates[$userTemplate['userTemplateName']]['authorisedUsers'] = $userTemplate['authorisedUsers'];
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

        function writeSettingsFile($dc,$domain,$username,$password,$searchOU,$passwordMinLength = 0,$loginMessage = "Please login with your network credentials",$printServer = "") {
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
          $settings->PrintServer = $printServer;
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
          if($entry === "CLEAR_ALL_LOG") { $activities = null; } else {
            $activities = $this->readActivityLogFile();
            if(empty($activities)) {
              $activities = array();
              array_push($activities,$entry);
              $activities = implode("\r\n",$activities);
            } else {
            array_unshift($activities, $entry);
            $activities = implode("\r\n",$activities);
            }
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

        function createPSExec($script) {
          $settings = $this->readSettingsFile();
          $filename = substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . 'exec.ps1';
          $text = '$password = "' . $settings->Password . '" | ConvertTo-SecureString -asPlainText -Force; $cred = New-Object System.Management.Automation.PSCredential("' . $settings->Username . '",$password); Invoke-Command -ComputerName ' . $settings->PrintServer . ' -File "' . substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . $script . '.ps1" -Credential $cred';
          file_put_contents($filename, $text);
          shell_exec('PowerShell.exe -ExecutionPolicy Bypass -File "' . substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . 'exec.ps1"');
          unlink(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . 'exec.ps1');
        }

        function createPSScript($name,$script) {
          $settings = $this->readSettingsFile();
          $filename = substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . $name . '.ps1';
          $text = 'Stop-Service -Name spooler -Force
                  Start-Sleep -s 3
                  Get-Process PrintIsolationHost | Stop-Process -Force
                  Get-Process mmc | Stop-Process -Force
                  Start-Sleep -s 3
                  Remove-Item -Path "$env:SystemRoot\System32\spool\PRINTERS\*.*" -Force
                  Start-Sleep -s 3
                  Start-Service -Name spooler';
          file_put_contents($filename, $script);
          return $text;
        }

        function resetPrintQueue() {
            $settings = $this->readSettingsFile();
            $this->createPSScript('spool','Stop-Service -Name spooler -Force
                    Get-Process PrintIsolationHost | Stop-Process -Force
                    Remove-Item -Path "$env:SystemRoot\System32\spool\PRINTERS\*" -Recurse -Force
                    Start-Service -Name spooler');
            $this->createPSExec("spool");
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
          $auth = "";
          if(filesize(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "auth.data") > 0) {
          $auth = file_get_contents(substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . "auth.data", true);
          $auth = json_decode($auth, true);
          }
          return $auth;
        }

        function writeAuthFile($authList) {
          $authFile = substr($_SERVER['DOCUMENT_ROOT'], 0, -3) . 'auth.data';
          $authList = json_encode($authList);
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
          if(is_array($authList)) {
            if(array_key_exists($username, $authList)) {
              return true;
            } else {
              return false;
            }
          } else { return false; }
        }

        function getAuthorisedOU($username) {
          $authList = $this->readAuthFile();
          if(isset($authList[$username])) {
            return $authList[$username]['distinguishednames'];
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
              $result = ["mail"=>"","homedirectory"=>"","homedrive"=>"","profilepath"=>"","scriptpath"=>"","ou"=>"","groups"=>"","upnSuffix"=>""];
              if(in_array("mail", $user)) {
                $result['mail'] = $user['mail'][0];
              }
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
              if(in_array("upnSuffix", $user)) {
                $result['upnSuffix'] = $user['upnSuffix'][0];
              }
              if(in_array("distinguishedname", $user)) {
                $ou = explode(",",$user['distinguishedname'][0]);
                array_shift($ou);
                $result['ou'] = implode(",",$ou);
              }
              if(in_array("memberof", $user)) {
              $chosenGroups = $user['memberof'];
              array_shift($chosenGroups);
              $availableGroups = $this->searchForGroupsAD();
              $finalGroups = [];
              foreach($availableGroups as $availableGroup) {
                if(in_array($availableGroup['distinguishedname'][0],$chosenGroups)) {
                  $finalGroups[] = $availableGroup['cn'][0];
                }
              }
              $result['groups'] = $finalGroups;
              $result['groups'] = implode("\r\n",$result['groups']);
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

          if($this->checkAdminLevel(strtolower($_SESSION['username']))) { $searchOU = $settings->SearchOU; } else {
            $searchOU = $this->getAuthorisedOU(strtolower($_SESSION['username']));
          }

            foreach($searchOU as $dn) {

              $filter="(objectClass=organizationalunit)";
              $justthese = array("ou");
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
