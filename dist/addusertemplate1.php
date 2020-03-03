<?php require("header.php"); ?>
<?php if(!$_SESSION['admin']) { header("Location: index.php"); } ?>
  <main>
    <div class="container-fluid">
      <h3 class="mt-4">Add User Template</h3>
      <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Add User Template</li>
      </ol>
      <div class="col-sm-12 col-md-10 col-lg-8 col-xl-6">
        <div class="card shadow-lg border-0 rounded-lg mt-2">
          <div class="card-body">
            <?php

            $AD->connect();
            $AD->bind();

            ?>
              <form action="addusertemplate.php" method="POST">
                <p><small>You must have at least one user template before you can add new users.</small></p>
                <div class="form-group">
                  <label class="small mb-1" for="inputUserTemplateName">Template Name</label>
                  <input required name="inputUserTemplateName" class="form-control" id="inputUserTemplateName" type="text" placeholder="e.g. Staff" value="<?php if(isset($_POST['inputUserTemplateName'])) { echo $_POST['inputUserTemplateName']; } ?>"/>
                </div>
                <div class="form-group">
                    <label class="small mb-1" for="inputUser">Create Template From User</label>
                    <input required class="form-control" id="inputUser" type="text" placeholder="e.g. John Smith" value="<?php if(isset($_POST['inputUserTemplateName'])) { echo $_POST['inputUser']; } ?>"/>
                    <input name="inputUser" type="hidden" id="inputUser-id">
                </div>
                <div class="form-group">
                  <label class="small mb-1" for="inputHomeDirectory">Home Directory (must include trailing backslash)</label>
                  <input required name="inputHomeDirectory" class="form-control" id="inputHomeDirectory" type="text" placeholder="e.g. \\AS-DC\Staff$\" value="<?php if(isset($_POST['inputUserTemplateName'])) { echo $_POST['inputHomeDirectory']; } ?>"/>
                </div>
                <div class="form-group">
                  <label class="small mb-1" for="inputHomeDrive">Home Drive Letter</label>
                  <input required name="inputHomeDrive" class="form-control" id="inputHomeDrive" type="text" placeholder="e.g. U" maxlength="1" value="<?php if(isset($_POST['inputUserTemplateName'])) { echo $_POST['inputHomeDrive']; } ?>"/>
                </div>
                <div class="form-group">
                  <label class="small mb-1" for="inputProfilePath">Profile Path (must include trailing backslash)</label>
                  <input name="inputProfilePath" class="form-control" id="inputProfilePath" type="text" placeholder="e.g. \\AS-DC\Profiles$\" value="<?php if(isset($_POST['inputUserTemplateName'])) { echo $_POST['inputProfilePath']; } ?>"/>
                </div>
                <div class="form-group">
                  <label class="small mb-1" for="inputScriptPath">Logon Script</label>
                  <input name="inputScriptPath" class="form-control" id="inputScriptPath" type="text" placeholder="e.g. Staff.bat" value="<?php if(isset($_POST['inputUserTemplateName'])) { echo $_POST['inputScriptPath']; } ?>"/>
                </div>
                <div class="form-group">
                  <label class="small mb-1" for="OUTree">User OU</label>
                  <div id="OUTree">
                    <?php $AD->showOUTree(); ?>
                  </div>
                  <p class="mt-2" id="selectedOU"></p>
                </div>
                <input required hidden name="inputUserOU" id="inputUserOU" value="">
                <div class="form-group">
                  <label class="small mb-1" for="inputGroupDN">Member Group DN's (1 Per Line)</label>
                  <textarea name="inputGroupDN" class="form-control" id="inputGroupDN" type="text" rows="7" placeholder="e.g. CN=Staff,OU=Groups,OU=Arunside,DC=ASDOMAIN,DC=local"><?php if(isset($_POST['inputUserTemplateName'])) { echo $_POST['inputGroupDN']; } ?></textarea>
                </div>
                <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                  <input type="submit" class="btn btn-success" href="#" value="Add User Template">&nbsp;&nbsp;
                </div>
                <?php

                $data = $AD->searchAD();

                if(isset($_POST['inputUserTemplateName'])) {
                    $userTemplate = array(
                        "userTemplateName" => $_POST['inputUserTemplateName'],
                        "homeDirectory" => $_POST['inputHomeDirectory'],
                        "homeDrive" => $_POST['inputHomeDrive'],
                        "profilePath" => $_POST['inputProfilePath'],
                        "scriptPath" => $_POST['inputScriptPath'],
                        "groupDN" => explode("\n", str_replace("\r", "", $_POST['inputGroupDN'])),
                        "userOU" => $_POST['inputUserOU']
                    );
                    $AD->addToUserTemplatesFile($userTemplate);
                    header("Location: addusertemplatecomplete.php");
                  }

                ?>
              </form>
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
        .append( "<div>" + item.label + "</div>" )
        .appendTo( ul );
    };
  } );

function getUserData(chosenUser){
  if (window.XMLHttpRequest) {
    xmlhttp = new XMLHttpRequest();
  } else {
    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onload = function() {
    if (this.status == 200) {
      var response = JSON.parse(this.responseText);
      if(response !== null) {
        document.getElementById("inputHomeDirectory").value = response.homedirectory;
        document.getElementById("inputHomeDrive").value = response.homedrive;
        document.getElementById("inputScriptPath").value = response.scriptpath;
        document.getElementById("inputProfilePath").value = response.profilepath;
        document.getElementById("inputUserOU").value = response.ou;
        document.getElementById("selectedOU").innerText = response.ou;
        document.getElementById("inputGroupDN").value = response.groups;
      }
    }
  }
  xmlhttp.open("POST", "control/controller.php", true);
  xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xmlhttp.send("getUserData=" + chosenUser);
}

document.getElementById("inputUser").addEventListener("change", function() {
  getUserData(document.getElementById("inputUser").value);
});

$(function () { $('#OUTree').jstree(); });
$('#OUTree').on('changed.jstree', function (e, data) {
    var i, j, r = [];
    for(i = 0, j = data.selected.length; i < j; i++) {
      r.push(data.instance.get_node(data.selected[i]).li_attr.value);
    }
    $('#selectedOU').html(r.join(', '));
    $('#inputUserOU').attr('value', r.join(', '));
  });
</script>
<?php require("footer.php"); ?>
