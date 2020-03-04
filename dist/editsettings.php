<?php require("header.php"); ?>
<?php
if(!$_SESSION['admin']) { header("Location: index.php"); }
$settings = $AD->readSettingsFile();
$authList = $AD->readAuthFile();
if($authList !== null) { $authList = implode("\n",$authList); }
?>
  <main>
    <div class="container-fluid">
      <h3 class="mt-4">Settings</h3>
      <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Settings</li>
      </ol>
      <div class="col-sm-12 col-md-10 col-lg-8 col-xl-6">
      <div class="card shadow-lg border-0 rounded-lg mb-2">
          <div class="card-body">
              <p>Current Version: 0.2</p>
              <?php

              if(isset($_POST['updateApp'])) {
                shell_exec("..\..\git\bin\git.exe -c http.sslVerify=false reset --hard 2>&1");
                $outputs = shell_exec("..\..\git\bin\git.exe -c http.sslVerify=false pull https://b47ce1f940d20badca903c57add8be34ab2f6abc@github.com/Filk47473831/ADDog.git 2>&1");
                $outputs = substr($outputs, 89);
                echo "ADDog Updated";
                echo "<p class='small'>$outputs</p>";
              }

              ?>
              <form action="editsettings.php" method="POST">
                  <input type="submit" name="updateApp" class="btn btn-success" href="#" value="Check for updates">
              </form>
          </div>
        </div>
      </div>
      <div class="col-sm-12 col-md-10 col-lg-8 col-xl-6">
      <div class="card shadow-lg border-0 rounded-lg mt-3 mb-5">
          <div class="card-body">
                <form action="editsettings.php" method="POST">
                  <div class="form-group">
                    <label class="small mb-1" for="inputDC">Domain Controller</label>
                    <input required name="inputDC" class="form-control" id="inputDC" type="text" placeholder="e.g. A-DC" value="<?php if(isset($_POST['inputDC'])) { echo $_POST['inputDC']; } else { echo $settings->Server; } ?>"/>
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="inputDC">Domain FQDN</label>
                    <input required name="inputDomain" class="form-control" id="inputDomain" type="text" placeholder="e.g. ASDOMAIN.local" value="<?php if(isset($_POST['inputDC'])) { echo $_POST['inputDomain']; } else { echo $settings->Domain; } ?>"/>
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="inputOU">User Search OUs (1 DN Per Line)</label>
                    <textarea required name="inputOU" class="form-control" id="inputOU" type="text" rows="5" placeholder="e.g. OU=The Gaff,DC=thegaff,DC=co,DC=uk"><?php if(isset($_POST['inputDC'])) { echo $_POST['inputOU']; } else { echo implode("\r\n",$settings->SearchOU); } ?></textarea>
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="inputAuthList">Authorised Admins (1 Username Per Line)</label>
                    <textarea name="inputAuthList" class="form-control" id="inputAuthList" type="text" rows="7"><?php if(isset($_POST['inputDC'])) { echo $_POST['inputAuthList']; } else { echo $authList; } ?></textarea>
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="inputPWMinLength">Password Minimum Length</label>
                    <input name="inputPWMinLength" class="form-control" id="inputPWMinLength" type="number" min="0" placeholder="10" value="<?php if(isset($_POST['inputDC'])) { echo $_POST['inputPWMinLength']; } else { echo $settings->PasswordMinLength; } ?>"/>
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="inputLoginMessage">Custom Login Message</label>
                    <input name="inputLoginMessage" class="form-control" id="inputLoginMessage" type="text" placeholder="e.g. Please login with your network credentials" value="<?php if(isset($_POST['inputDC'])) { echo $_POST['inputLoginMessage']; } else { echo $settings->LoginMessage; } ?>"/>
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="inputUsername">Admin Account</label>
                    <input required name="inputUsername" class="form-control" id="inputUsername" type="text" placeholder="e.g. Administrator" value="<?php if(isset($_POST['inputDC'])) { echo $_POST['inputUsername']; } else { echo $settings->Username; } ?>"/>
                  </div>
                  <div class="form-group">
                    <input required name="inputPassword" class="form-control" id="inputPassword" type="password" placeholder="Confirm Password" value=""/>
                  </div>
                  <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                    <input type="submit" class="btn btn-primary" href="#" value="Submit">
                  </div>
                  <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">

                    <?php

                    if(isset($_POST['inputDC'])) {
                        if($_POST['inputDC'] !== "" && $_POST['inputDC'] !== "" && $_POST['inputUsername'] !== "" && $_POST['inputPassword'] !== "" && $_POST['inputOU'] !== "") {
                        $settings = $AD->writeSettingsFile($_POST['inputDC'],$_POST['inputDomain'],$_POST['inputUsername'],$_POST['inputPassword'],$_POST['inputOU'],$_POST['inputPWMinLength'],$_POST['inputLoginMessage']);
                        $authList = [strtolower($_POST['inputAuthList'])];
                        $AD->writeAuthFile($authList);
                        echo "Settings Updated";
                      } else {
                        echo "Fields Missing";
                      }
                    }

                    ?>

                  </div>
                </form>
            </div>
          </div>
        </div>
      </div>
  </main>
  <?php require("footer.php"); ?>
