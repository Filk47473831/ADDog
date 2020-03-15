<?php require("header.php"); ?>
<?php
if(!$_SESSION['admin']) { header("Location: index"); }
$settings = $AD->readSettingsFile();
?>
  <main>
    <div class="container-fluid">
      <h3 class="mt-4">Settings</h3>
      <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
        <li class="breadcrumb-item active">Settings</li>
      </ol>
      <div class="col-sm-12 col-md-10 col-lg-8 col-xl-6">
      <div class="card shadow-lg border-0 rounded-lg mb-2">
          <div class="card-body">
              <p>Current Version: 1.1</p>
              <?php

              $AD->connect();
              $AD->bind();

              if(isset($_POST['updateApp'])) {
                shell_exec("..\git\bin\git.exe -c http.sslVerify=false reset --hard 2>&1");
                $outputs = shell_exec("..\git\bin\git.exe -c http.sslVerify=false pull https://b47ce1f940d20badca903c57add8be34ab2f6abc@github.com/Filk47473831/ADDog.git 2>&1");
                $outputs = substr($outputs, 89);
                echo "Check Complete";
                echo "<p class='small'>$outputs</p>";
              }

              ?>
              <form action="editsettings" method="POST">
                  <input type="submit" name="updateApp" class="btn btn-success" href="#" value="Check for updates">
              </form>
          </div>
        </div>
      </div>
      <div class="col-sm-12 col-md-10 col-lg-8 col-xl-6">
      <div class="card shadow-lg border-0 rounded-lg mt-3 mb-5">
          <div class="card-body">
                <form action="editsettings" method="POST">
                  <div class="form-group">
                    <label class="small mb-1" for="inputDC">Domain Controller</label>
                    <input required name="inputDC" class="form-control" id="inputDC" type="text" placeholder="e.g. A-DC" value="<?php if(isset($_POST['inputDC'])) { echo $_POST['inputDC']; } else { echo $settings->Server; } ?>"/>
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="inputDC">Domain FQDN</label>
                    <input required name="inputDomain" class="form-control" id="inputDomain" type="text" placeholder="e.g. ASDOMAIN.local" value="<?php if(isset($_POST['inputDC'])) { echo $_POST['inputDomain']; } else { echo $settings->Domain; } ?>"/>
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="inputBaseDN">Base DN</label>
                    <input required name="inputBaseDN" class="form-control" id="inputBaseDN" type="text" placeholder="e.g. DC=ASDOMAIN,DC=local" value="<?php if(isset($_POST['inputDC'])) { echo $_POST['inputBaseDN']; } else { echo implode("\r\n",$settings->SearchOU); } ?>">
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="OUTree">Authorised Users</label>

                        <?php

                        $authList = $AD->readAuthFile();

                        if($authList !== null) {
                          echo '<div id="OUTree">
                                  <ul>';
                          foreach ($authList as $authUser) {
                              echo '<li>' . $authUser['username'];
                              echo '<ul>';
                              $distinguishedNames = $authUser['distinguishednames'];
                              foreach($distinguishedNames as $distinguishedname){
                                echo '<li>' . $distinguishedname . '</li>';
                              }
                              echo '</ul></li>';
                          }
                            echo '</ul></div>';
                        } else {
                          echo '<p>No Authorised Admins</p>';
                        }

                        ?>

                  </div>
                  <div class="form-group">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addAuthorisedAdminModal">
                      Add Authorised User
                    </button>
                    <?php if($authList !== null) { ?><button onclick="clearAuthorisedAdmins()" type="button" class="btn btn-danger btn-sm">
                      Clear All Authorised Users
                    </button><?php } ?>
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="inputPWMinLength">Password Minimum Length</label>
                    <input name="inputPWMinLength" class="form-control" id="inputPWMinLength" type="number" min="0" placeholder="e.g. 10" value="<?php if(isset($_POST['inputDC'])) { echo $_POST['inputPWMinLength']; } else { echo $settings->PasswordMinLength; } ?>"/>
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="inputLoginMessage">Custom Login Message</label>
                    <input name="inputLoginMessage" class="form-control" id="inputLoginMessage" type="text" placeholder="e.g. Please login with your network credentials" value="<?php if(isset($_POST['inputDC'])) { echo $_POST['inputLoginMessage']; } else { echo $settings->LoginMessage; } ?>"/>
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="inputPrintServer">Print Server</label>
                    <input name="inputPrintServer" class="form-control" id="inputPrintServer" type="text" placeholder="e.g. A-DC.ASDOMAIN.local" value="<?php if(isset($_POST['inputDC'])) { echo $_POST['inputPrintServer']; } else { echo $settings->PrintServer; } ?>"/>
                  </div>
                  <div class="form-group">
                    <label class="small mb-1" for="inputUsername">Admin Account</label>
                    <input required name="inputUsername" class="form-control" id="inputUsername" type="text" placeholder="e.g. Administrator" value="<?php if(isset($_POST['inputDC'])) { echo $_POST['inputUsername']; } else { echo $settings->Username; } ?>"/>
                  </div>
                  <div class="form-group">
                    <input required name="inputPassword" class="form-control" id="inputPassword" type="password" placeholder="Confirm Password" value=""/>
                  </div>
                  <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                    <input type="submit" class="btn btn-primary" href="#" value="Save">
                  </div>
                  <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">

                    <?php

                    if(isset($_POST['inputDC'])) {
                        if($_POST['inputDC'] !== "" && $_POST['inputDC'] !== "" && $_POST['inputUsername'] !== "" && $_POST['inputPassword'] !== "" && $_POST['inputBaseDN'] !== "") {
                        $settings = $AD->writeSettingsFile($_POST['inputDC'],$_POST['inputDomain'],$_POST['inputUsername'],$_POST['inputPassword'],$_POST['inputBaseDN'],$_POST['inputPWMinLength'],$_POST['inputLoginMessage'],$_POST['inputPrintServer']);
                        echo "<p style='color:green'><b>Settings Updated</b></p>";
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

  <div id="addAuthorisedAdminModal" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Authorised User</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="small mb-1" for="inputAuthorisedAdminUsername">Username</label>
          <input class="form-control" id="inputAuthorisedAdminUsername" type="text" placeholder="e.g. jsmith" value=""/>
        </div>
        <div class="form-group">
          <label class="small mb-1" for="authUserOUTree">User Search OU</label>
          <div id="authUserOUTree">
            <?php $AD->showOUTree(); ?>
          </div>
          <textarea readonly class="mt-3 mb-2 form-control" id="inputAuthorisedAdminSearchOUs" type="text" rows="5" placeholder="Select Search OU's"></textarea>
          <button type="button" class="btn btn-warning btn-sm" onclick="document.getElementById('inputAuthorisedAdminSearchOUs').value = ''">
            Clear
          </button>
        </div>
        <!-- <div class="form-group">
          <label class="small mb-1" for="inputAuthorisedAdminSearchOUs">User Search OUs (1 DN Per Line)</label>
          <textarea class="form-control" id="inputAuthorisedAdminSearchOUs" type="text" rows="5" placeholder="e.g. OU=Users,OU=Arunside,DC=ASDOMAIN,DC=local"></textarea>
        </div> -->
      </div>
      <div class="modal-footer">
        <button id="updateAuthorisedAdminsSaveBtn" onclick="updateAuthorisedAdmins()" type="button" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-danger" onclick="hideResetModal()">Cancel</button>
      </div>
    </div>
  </div>
</div>
<script>
function updateAuthorisedAdmins(){
  var username = document.getElementById("inputAuthorisedAdminUsername").value;
  var distinguishednames = document.getElementById("inputAuthorisedAdminSearchOUs").value;

  if(username !== "" && distinguishednames !== "") {

    document.getElementById("updateAuthorisedAdminsSaveBtn").style = "width:56px";
    document.getElementById("updateAuthorisedAdminsSaveBtn").innerHTML = '<i style="font-size:1.2rem" class="fas fa-circle-notch fa-spin"></i>';

    if (window.XMLHttpRequest) {
      xmlhttp = new XMLHttpRequest();
    } else {
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onload = function() {
      if (this.status == 200) {
        location.reload();
      }
    }
    xmlhttp.open("POST", "control/controller", true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send("updateAuthorisedAdmins=" + username + "&distinguishednames=" + distinguishednames);

  }
}

function clearAuthorisedAdmins(){
  if (window.XMLHttpRequest) {
    xmlhttp = new XMLHttpRequest();
  } else {
    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onload = function() {
    if (this.status == 200) {
      location.reload();
    }
  }
  xmlhttp.open("POST", "control/controller", true);
  xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xmlhttp.send("clearAuthorisedAdmins");
}

function hideResetModal() {
  $('#addAuthorisedAdminModal').modal('hide');
  document.getElementById("updateAuthorisedAdminsSaveBtn").style = "";
  document.getElementById("updateAuthorisedAdminsSaveBtn").innerHTML = 'Save';
  document.getElementById("inputAuthorisedAdminUsername").value = "";
  document.getElementById("inputAuthorisedAdminSearchOUs").value = "";
}

$(function () { $('#OUTree').jstree(); });

$(function () { $('#authUserOUTree').jstree(); });

$('#authUserOUTree').on('changed.jstree', function (e, data) {
    var i, j, r = [];
    for(i = 0, j = data.selected.length; i < j; i++) {
      r.push(data.instance.get_node(data.selected[i]).li_attr.value);
    }
    if(document.getElementById("inputAuthorisedAdminSearchOUs").value == "") { document.getElementById("inputAuthorisedAdminSearchOUs").value = r.join(', '); } else {
    document.getElementById("inputAuthorisedAdminSearchOUs").value = document.getElementById("inputAuthorisedAdminSearchOUs").value + ", " + r.join(', '); }
  });
</script>
  <?php require("footer.php"); ?>
