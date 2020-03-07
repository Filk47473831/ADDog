<?php require("header.php"); ?>
  <main>
    <div class="container-fluid">
      <h3 class="mt-4">Add User</h3>
      <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
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
              $testUserOU = $AD->testUserOU($_POST['inputUserOU']);

              if(($testFirstName == "") && ($testLastName == "") && ($testUsername == "") && ($testPassword == "") && ($testUserOU == "") && ($testUserOU == "")) {

                echo $testUserOU;

                $info = array();
                $info["cn"] = $_POST['inputFirstName'] . " " . $_POST['inputLastName'];
                $info['givenName'] = $_POST['inputFirstName'];
                $info["sn"] = $_POST['inputLastName'];
                $info["sAMAccountName"] = $_POST['inputUsername'];
                $info["UserPrincipalName"] = $_POST['inputUsername'] . "@" . $settings->Domain;
                $info['homeDirectory'] = $_POST['inputHomeDirectory'];
                $info['homeDrive'] = $_POST['inputHomeDrive'];
                $info['profilePath'] = $_POST['inputProfilePath'];
                $info['scriptPath'] = $_POST['inputScriptPath'];
                $availableGroups = $AD->searchForGroupsAD();
                $chosenGroups = explode("\n", str_replace("\r", "", $_POST['inputGroupDN']));
                $finalGroups = [];
                foreach($availableGroups as $availableGroup) {
                  if(in_array($availableGroup['cn'][0],$chosenGroups)) {
                    $finalGroups[] = $availableGroup['distinguishedname'][0];
                  }
                }
                $password = $_POST['inputPassword'];
                $addAccount = $AD->addUser(null,$info,$password,$_POST['inputUserOU'],$finalGroups);
                $AD->writeActivityLogFile(gmdate("d-m-y h:i:sa") . ",User Added," . $info['givenName'] . " " . $info["sn"] . "," . $_SESSION['username']);
                if($addAccount === null) { header("Location: addusercomplete"); }
              }
            }

            ?>

              <form action="adduser" method="POST" class="needs-validation" novalidate>
                <div class="form-group">
                    <label class="small mb-1" for="inputUserTemplate">Select User Template</label>
                    <select id="inputUserTemplate" name="inputUserTemplate" class="form-control"><?php echo $AD->displayUserTemplates(); ?></select>
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
                <p style="cursor:pointer" class="small" data-toggle="collapse" href="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                    &nbsp;<i class="far fa-plus-square"></i>&nbsp;Advanced Options
                </p>
                <div class="collapse" id="collapseExample">
                  <div class="form-group">
                    <label class="small mb-1" for="inputEmailAddress">Email Address</label>
                    <input required name="inputEmailAddress" class="form-control" id="inputEmailAddress" type="text" placeholder="e.g. jsmith@arunside.school" value="<?php if(isset($_POST['inputUserTemplateName'])) { echo $_POST['inputEmailAddress']; } ?>"/>
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="inputHomeDirectory">Home Directory</label>
                    <input required name="inputHomeDirectory" class="form-control" id="inputHomeDirectory" type="text" placeholder="e.g. \\AS-DC\Staff$\%USERNAME%" value="<?php if(isset($_POST['inputFirstName'])) { echo $_POST['inputHomeDirectory']; } ?>"/>
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="inputHomeDrive">Home Drive Letter</label>
                    <input required name="inputHomeDrive" class="form-control" id="inputHomeDrive" type="text" placeholder="e.g. U" maxlength="1" value="<?php if(isset($_POST['inputFirstName'])) { echo $_POST['inputHomeDrive']; } ?>"/>
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="inputProfilePath">Profile Path</label>
                    <input name="inputProfilePath" class="form-control" id="inputProfilePath" type="text" placeholder="e.g. \\AS-DC\Profiles$\%USERNAME%" value="<?php if(isset($_POST['inputFirstName'])) { echo $_POST['inputProfilePath']; } ?>"/>
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="inputScriptPath">Logon Script</label>
                    <input name="inputScriptPath" class="form-control" id="inputScriptPath" type="text" placeholder="e.g. Staff.bat" value="<?php if(isset($_POST['inputFirstName'])) { echo $_POST['inputScriptPath']; } ?>"/>
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="inputGroupDN">Member Group Name's (1 Per Line)</label>
                    <textarea name="inputGroupDN" class="form-control" id="inputGroupDN" type="text" rows="7" placeholder="e.g.&#x0a;Staff&#x0a;RD Users"><?php if(isset($_POST['inputFirstName'])) { echo $_POST['inputGroupDN']; } ?></textarea>
                  </div>
                </div>

                <div class="form-group">
                  <label class="small mb-1" for="OUTree">User OU</label>
                  <div id="OUTree">
                    <?php $AD->showOUTree(); ?>
                  </div>
                  <input required style="border:0px" required class="<?php if(isset($_POST['inputFirstName']) && $testUserOU !== "") { echo "is-invalid"; } ?> form-control mt-3" name="inputUserOU" id="inputUserOU" value="<?php if(isset($_POST['inputFirstName'])) { echo $_POST['inputUserOU']; } ?>" placeholder="Select target OU for new user">
                  <div class="invalid-feedback"><?php if(isset($_POST['inputFirstName'])) { echo $testUserOU; } ?></div>
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
  <script>
  function getTemplateData(chosenTemplate){
    if (window.XMLHttpRequest) {
      xmlhttp = new XMLHttpRequest();
    } else {
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onload = function() {
      if (this.status == 200) {
        var response = JSON.parse(this.responseText);
        if(response !== null) {
          document.getElementById("inputHomeDirectory").value = response[0].homeDirectory + "%USERNAME%";
          document.getElementById("inputHomeDrive").value = response[0].homeDrive;
          document.getElementById("inputScriptPath").value = response[0].scriptPath;
          document.getElementById("inputProfilePath").value = response[0].profilePath + "%USERNAME%";
          document.getElementById("inputUserOU").value = response[1];
          document.getElementById("inputGroupDN").value = response[2];
        }
      }
    }
    xmlhttp.open("POST", "control/controller", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send("chosenUserTemplate=" + chosenTemplate);
  }
  </script>
  <?php if(!isset($_POST['inputFirstName'])) { ?>
  <script>
    if(document.getElementById("inputUserTemplate").value !== "null") {
      getTemplateData(document.getElementById("inputUserTemplate").value);
    }
  </script>
  <?php } ?>
  <script>
  $(function () { $('#OUTree').jstree(); });
  $('#OUTree').on('changed.jstree', function (e, data) {
      var i, j, r = [];
      for(i = 0, j = data.selected.length; i < j; i++) {
        r.push(data.instance.get_node(data.selected[i]).li_attr.value);
      }
      document.getElementById("inputUserOU").value = r.join(', ');
    });

  document.getElementById("inputUserTemplate").addEventListener("change", function() {
    if(document.getElementById("inputUserTemplate").value !== "null") {
      getTemplateData(document.getElementById("inputUserTemplate").value);
    }
  });
</script>
  <?php require("footer.php"); ?>
