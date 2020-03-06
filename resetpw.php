<?php require("header.php"); ?>
<main>
    <div class="container-fluid">
        <h3 class="mt-4">Reset User Password</h3>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
            <li class="breadcrumb-item active">Reset User Password</li>
        </ol>
            <div class="col-sm-12 col-md-10 col-lg-8 col-xl-6">
            <div class="card shadow-lg border-0 rounded-lg mt-2">
                <div class="card-body">

        <?php

        $AD->connect();
        $AD->bind();

        $data = $AD->searchAD();

        if(isset($_POST['inputPassword'])) {
            $testPassword = $AD->testPassword($_POST['inputPassword'],$_POST['inputConfPassword']);
            $name = explode(",",$_POST['inputUser']);
            if($testPassword == "") {
                if(isset($_POST['promptNextLogin'])) { $promptNextLogin = "on"; } else { $promptNextLogin = null; }
                $testPassword = $AD->resetPassword($_POST['inputUser'],$_POST['inputPassword'],$promptNextLogin);
                $AD->writeActivityLogFile(gmdate("d-m-y h:i:sa") . ",Password Reset," . substr($name[0], 3) . "," . $_SESSION['username']);
                if($testPassword == "") {
                  $name = explode(",",$_POST['inputUser']);
                  $name = $name[0];
                  $name = substr($name, 3);
                  echo '<p>Password Reset Successfully</p><p>' . $name . '\'s new password is: ' . $_POST['inputPassword'] . '</p>
                      <a href="resetpw"><button class="btn btn-success">Back</button></a>';
                } else {
                  echo '<p>' . $testPassword . '</p><a href="resetpw"><button class="btn btn-success">Back</button></a>';
                }
            } else {
              echo '<p>' . $testPassword . '</p><a href="resetpw"><button class="btn btn-success">Back</button></a>';
            }
        } else {

        ?>

                    <form action="resetpw" method="POST">
                        <div class="form-group">
                            <label class="small mb-1" for="inputUser">Select User</label>
                            <input required class="form-control" id="inputUser" type="text" placeholder="e.g. John Smith" value="<?php if(isset($_POST['inputUserTemplateName'])) { echo $_POST['inputUser']; } ?>"/>
                            <input name="inputUser" type="hidden" id="inputUser-id">
                        </div>
                        <div class="form-group">
                            <label class="small mb-1" for="inputPassword">New Password</label>
                            <input required name="inputPassword" class="<?php if(isset($_POST['inputPassword']) && $testPassword !== "") { echo "is-invalid"; } ?> form-control" id="inputPassword" type="password" placeholder="New Password" value="<?php if(isset($_POST['inputPassword'])) { echo $_POST['inputPassword']; } ?>"/>
                        </div>
                        <div class="form-group">
                            <label class="small mb-1" for="inputConfPassword">Confirm Password</label>
                            <input required name="inputConfPassword" class="<?php if(isset($_POST['inputPassword']) && $testPassword !== "") { echo "is-invalid"; } ?> form-control" id="inputConfPassword" type="password" placeholder="Confirm Password" value="<?php if(isset($_POST['inputPassword'])) { echo $_POST['inputConfPassword']; } ?>"/>
                            <div class="invalid-feedback"><?php if(isset($_POST['inputPassword'])) { echo $testPassword; } ?></div>
                        </div>
                        <div class="form-check">
                          <input name="promptNextLogin" type="checkbox" class="form-check-input" id="promptNextLogin">
                          <label class="form-check-label small" for="promptNextLogin">Prompt user to change password on next login</label>
                        </div>
                        <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                            <input type="submit" class="btn btn-primary" href="#" value="Reset Password">
                            <div class="invalid-feedback">
                              <?php if(isset($_POST['inputPassword'])) { echo $testPassword; } ?>
                            </div>
                        </div>
                    </form>

                  <?php } ?>

                </div>
            </div>
            </div>
    </div>
</main>
<script>
$( function() {
  var users = <?php $AD->updateUsersJSON($data); ?>;

  $("#inputUser").autocomplete({
    minLength: 0,
    source: users,
    focus: function( event, ui ) {
      $( "#inputUser" ).val( ui.item.label );
      return false;
    },
    select: function( event, ui ) {
      $( "#inputUser" ).val( ui.item.label );
      $( "#inputUser-id" ).val( ui.item.value );
      return false;
    }
  })
  .autocomplete( "instance" )._renderItem = function( ul, item ) {
    return $( "<li>" )
      .append( "<div class='mt-1'>" + item.label + "</div>" )
      .appendTo( ul );
  };
} );
</script>
<?php require("footer.php"); ?>
