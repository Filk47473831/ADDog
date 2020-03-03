<?php require("header.php"); ?>
  <main>
    <div class="container-fluid">
      <h3 class="mt-4">Add User</h3>
      <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Add User</li>
      </ol>
      <div class="col-sm-12 col-md-10 col-lg-8 col-xl-6">
        <div class="card shadow-lg border-0 rounded-lg mt-2">
          <div class="card-body">

            <?php

            $AD->connect();
            $AD->bind();

            if(isset($_POST['inputFirstName'])) {

              $addAccount = "";
              $testFirstName = $AD->testFirstName($_POST['inputFirstName']);
              $testLastName = $AD->testLastName($_POST['inputLastName']);
              $testUsername = $AD->testUsername($_POST['inputUsername']);
              $testPassword = $AD->testPassword($_POST['inputPassword'],$_POST['inputPasswordConf']);

              if(($testFirstName == "") && ($testLastName == "") && ($testUsername == "") && ($testPassword == "")) {
                $userTemplate = $_POST['inputUserTemplate'];
                $info = array();
                $info["cn"] = $_POST['inputFirstName'] . " " . $_POST['inputLastName'];
                $info['givenName'] = $_POST['inputFirstName'];
                $info["sn"] = $_POST['inputLastName'];
                $info["sAMAccountName"] = $_POST['inputUsername'];
                $info["UserPrincipalName"] = $_POST['inputUsername'] . "@" . $settings->Domain;
                $password = $_POST['inputPassword'];
                $addAccount = $AD->addUser($userTemplate,$info,$password);
                $AD->writeActivityLogFile(date("d-m-y h:i:s") . ",User Added," . $info['givenName'] . " " . $info["sn"] . "," . $_SESSION['username']);
                if($addAccount === null) { header("Location: addusercomplete.php"); }
              }
            }

            ?>

              <form action="adduser.php" method="POST" class="needs-validation" novalidate>
                <div class="form-group">
                    <label class="small mb-1" for="inputUserTemplate">Select User Template</label>
                    <select name="inputUserTemplate" class="form-control"><?php echo $AD->displayUserTemplates(); ?></select>
                </div>
                <div class="form-group">
                  <label class="small mb-1" for="inputFirstName">First Name</label>
                  <input required name="inputFirstName" class="<?php if(isset($_POST['inputFirstName']) && $testFirstName !== "") { echo "is-invalid"; } ?> form-control" id="inputFirstName" type="text" placeholder="John" value="<?php if(isset($_POST['inputFirstName'])) { echo $_POST['inputFirstName']; } ?>"/>
                  <div class="invalid-feedback"><?php if(isset($_POST['inputFirstName'])) { echo $testFirstName; } ?></div>
                </div>
                <div class="form-group">
                  <label class="small mb-1" for="inputLastName">Last Name</label>
                  <input name="inputLastName" class="<?php if(isset($_POST['inputFirstName']) && $testLastName !== "") { echo "is-invalid"; } ?> form-control" id="inputLastName" type="text" placeholder="Smith" value="<?php if(isset($_POST['inputFirstName'])) { echo $_POST['inputLastName']; } ?>"/>
                  <div class="invalid-feedback"><?php if(isset($_POST['inputFirstName'])) { echo $testLastName; } ?></div>
                </div>
                <div class="form-group">
                  <label class="small mb-1" for="inputUsername">Username</label>
                  <input required name="inputUsername" class="<?php if(isset($_POST['inputFirstName']) && $testUsername !== "") { echo "is-invalid"; } ?> form-control" id="inputUsername" type="text" placeholder="jsmith" value="<?php if(isset($_POST['inputFirstName'])) { echo $_POST['inputUsername']; } ?>"/>
                  <div class="invalid-feedback"><?php if(isset($_POST['inputFirstName'])) { echo $testUsername; } ?></div>
                </div>
                <div class="form-group">
                  <label class="small mb-1" for="inputPassword">Password</label>
                  <input required name="inputPassword" class="<?php if(isset($_POST['inputFirstName']) && $testPassword !== "") { echo "is-invalid"; } ?> form-control" id="inputPassword" type="password" placeholder="Enter Password" value="<?php if(isset($_POST['inputFirstName'])) { echo $_POST['inputPassword']; } ?>"/>
                </div>
                <div class="form-group">
                  <input required name="inputPasswordConf" class="<?php if(isset($_POST['inputFirstName']) && $testPassword !== "") { echo "is-invalid"; } ?> form-control" id="inputPasswordConf" type="password" placeholder="Confirm Password" value="<?php if(isset($_POST['inputFirstName'])) { echo $_POST['inputPasswordConf']; } ?>"/>
                  <div class="invalid-feedback"><?php if(isset($_POST['inputFirstName'])) { echo $testPassword; } ?></div>
                </div>
                <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                  <input type="submit" class="btn btn-success <?php if(isset($_POST['inputFirstName']) && $addAccount !== "") { echo "is-invalid"; } ?>" href="#" value="Add User">&nbsp;&nbsp;
                  <div class="invalid-feedback">
                    <?php if(isset($_POST['inputFirstName'])) { echo $addAccount; } ?>
                  </div>
                </div>
              </form>
          </div>
        </div>
      </div>
    </div>
  </main>
  <?php require("footer.php"); ?>
