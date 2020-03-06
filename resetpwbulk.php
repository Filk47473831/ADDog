<?php require("header.php"); ?>
<main>
    <div class="container-fluid">
        <h3 class="mt-4">Bulk Reset User Passwords</h3>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
            <li class="breadcrumb-item active">Bulk Reset User Passwords</li>
        </ol>
            <div class="col-sm-12 col-md-10 col-lg-8 col-xl-6">
            <div class="card shadow-lg border-0 rounded-lg mt-2">
                <div class="card-body">

        <?php

        $AD->connect();
        $AD->bind();

        if(isset($_POST['inputUserOU'])) {
          $testPassword = $AD->testPassword($_POST['inputPassword'],$_POST['inputConfPassword']);
          if($testPassword == "") {
            $searchOU = [$_POST['inputUserOU']];
            $data = $AD->searchTargetOU($searchOU);
              foreach($data as $user) {
                $AD->resetPassword($user['dn'],$_POST['inputPassword'],$_POST['promptNextLogin']);
              }
              $AD->writeActivityLogFile(gmdate("d-m-y h:i:sa") . ",Bulk Password Reset,-," . $_SESSION['username']);
              header("Location: resetpwbulkcomplete");
          }
        }

        ?>

                    <form action="resetpwbulk" method="POST">
                        <div class="form-group">
                          <label class="small mb-1" for="OUTree">Target OU</label>
                          <div id="OUTree">
                            <?php $AD->showOUTree(); ?>
                          </div>
                          <input style="border:0px" required class="form-control mt-2" name="inputUserOU" id="inputUserOU" value="" placeholder="Select Target OU from Tree">
                          <p style="margin-left:12px" class="small" id="targetCount"></p>
                        </div>

                        <div class="form-group">
                            <label class="small mb-1" for="inputPassword">New Password</label>
                            <input name="inputPassword" class="<?php if(isset($_POST['inputPassword']) && $testPassword !== "") { echo "is-invalid"; } ?> form-control" id="inputPassword" type="password" placeholder="New Password" value="<?php if(isset($_POST['inputPassword'])) { echo $_POST['inputPassword']; } ?>"/>
                        </div>
                        <div class="form-group">
                            <label class="small mb-1" for="inputConfPassword">Confirm Password</label>
                            <input name="inputConfPassword" class="<?php if(isset($_POST['inputPassword']) && $testPassword !== "") { echo "is-invalid"; } ?> form-control" id="inputConfPassword" type="password" placeholder="Confirm Password" value="<?php if(isset($_POST['inputPassword'])) { echo $_POST['inputConfPassword']; } ?>"/>
                            <div class="invalid-feedback"><?php if(isset($_POST['inputPassword'])) { echo $testPassword; } ?></div>
                        </div>
                        <div class="form-check">
                          <input name="promptNextLogin" type="checkbox" class="form-check-input" id="promptNextLogin">
                          <label class="form-check-label small unselectable" for="promptNextLogin">Prompt users to change password on next login</label>
                        </div>
                        <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                            <input type="submit" class="btn btn-primary" href="#" value="Reset Passwords">
                        </div>
                    </form>
                </div>
            </div>
            </div>
    </div>
</main>
<script>
function getTargetOUCount(targetSearchOU){
  if (window.XMLHttpRequest) {
    xmlhttp = new XMLHttpRequest();
  } else {
    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onload = function() {
    if (this.status == 200) {
       document.getElementById("targetCount").innerText = this.responseText + " user(s) will have their passwords reset.";
    }
  }
  xmlhttp.open("POST", "control/controller", true);
  xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xmlhttp.send("targetSearchOU=" + targetSearchOU);
}

$(function () { $('#OUTree').jstree(); });
$('#OUTree').on('changed.jstree', function (e, data) {
    var i, j, r = [];
    for(i = 0, j = data.selected.length; i < j; i++) {
      r.push(data.instance.get_node(data.selected[i]).li_attr.value);
    }
    $('#inputUserOU').html(r.join(', '));
    $('#inputUserOU').attr('value', r.join(', '));
    getTargetOUCount(r);
  });
</script>
<?php require("footer.php"); ?>
