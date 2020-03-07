<?php require("header.php"); ?>
<?php if(!$_SESSION['admin']) { header("Location: index"); } ?>
  <main>
    <div class="container-fluid">
      <h3 class="mt-4">Add User Template</h3>
      <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
        <li class="breadcrumb-item active">Add User Template</li>
      </ol>
      <div class="col-sm-12 col-md-10 col-lg-8 col-xl-6">
        <div class="card shadow-lg border-0 rounded-lg mt-2 mb-5">
          <div class="card-body">
            <?php

            $AD->connect();
            $AD->bind();

            ?>
              <form action="addusertemplate" method="POST">
                <div class="form-group">
                  <label class="small mb-1" for="inputUserTemplateName">Template Name</label>
                  <input required name="inputUserTemplateName" class="form-control" id="inputUserTemplateName" type="text" placeholder="e.g. Staff" value="<?php if(isset($_POST['inputUserTemplateName'])) { echo $_POST['inputUserTemplateName']; } ?>"/>
                </div>
                <div class="form-group">
                    <label class="small mb-1" for="inputUser">Create Template From User</label>
                    <input class="form-control" id="inputUser" type="text" placeholder="e.g. John Smith" value="<?php if(isset($_POST['inputUserTemplateName'])) {

                      $name = explode(",",$_POST['inputUser']);
                      $name = $name[0];
                      $name = substr($name, 3);

                      echo $name; } ?>"/>
                    <input name="inputUser" type="hidden" id="inputUser-id">
                </div>
                <div class="form-group">
                  <label class="small mb-1" for="inputEmailAddress">Email Address</label>
                  <input name="inputEmailAddress" class="form-control" id="inputEmailAddress" type="text" placeholder="e.g. %USERNAME%@arunside.school" value="<?php if(isset($_POST['inputUserTemplateName'])) { echo $_POST['inputEmailAddress']; } ?>"/>
                </div>
                <div class="form-group">
                  <label class="small mb-1" for="inputHomeDirectory">Home Directory</label>
                  <input required name="inputHomeDirectory" class="form-control" id="inputHomeDirectory" type="text" placeholder="e.g. \\AS-DC\Staff$\%USERNAME%" value="<?php if(isset($_POST['inputUserTemplateName'])) { echo $_POST['inputHomeDirectory']; } ?>"/>
                </div>
                <div class="form-group">
                  <label class="small mb-1" for="inputHomeDrive">Home Drive Letter</label>
                  <input required name="inputHomeDrive" class="form-control" id="inputHomeDrive" type="text" placeholder="e.g. U" maxlength="1" value="<?php if(isset($_POST['inputUserTemplateName'])) { echo $_POST['inputHomeDrive']; } ?>"/>
                </div>
                <div class="form-group">
                  <label class="small mb-1" for="inputProfilePath">Profile Path</label>
                  <input name="inputProfilePath" class="form-control" id="inputProfilePath" type="text" placeholder="e.g. \\AS-DC\Profiles$\%USERNAME%" value="<?php if(isset($_POST['inputUserTemplateName'])) { echo $_POST['inputProfilePath']; } ?>"/>
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
                  <input style="border:0px" required class="form-control mt-3" name="inputUserOU" id="inputUserOU" value="" placeholder="Select target OU for new users">
                </div>
                <div class="form-group">
                  <label class="small mb-1" for="inputGroupDN">Member Group Name's (1 Per Line)</label>
                  <textarea name="inputGroupDN" class="form-control" id="inputGroupDN" type="text" rows="7" placeholder="e.g. Staff"><?php if(isset($_POST['inputUserTemplateName'])) { echo $_POST['inputGroupDN']; } ?></textarea>
                </div>
                <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                  <input type="submit" class="btn btn-success" href="#" value="Add User Template">&nbsp;&nbsp;
                </div>
                <?php

                $data = $AD->searchAD();

                if(isset($_POST['inputUserTemplateName'])) {
                  $availableGroups = $AD->searchForGroupsAD();
                  $chosenGroups = explode("\n", str_replace("\r", "", $_POST['inputGroupDN']));
                  $finalGroups = [];
                  foreach($availableGroups as $availableGroup) {
                    if(in_array($availableGroup['cn'][0],$chosenGroups)) {
                      $finalGroups[] = $availableGroup['distinguishedname'][0];
                    }
                  }
                  $userTemplate = array(
                      "userTemplateName" => $_POST['inputUserTemplateName'],
                      "homeDirectory" => $_POST['inputHomeDirectory'],
                      "homeDrive" => $_POST['inputHomeDrive'],
                      "profilePath" => $_POST['inputProfilePath'],
                      "scriptPath" => $_POST['inputScriptPath'],
                      "groupDN" => $finalGroups,
                      "userOU" => $_POST['inputUserOU']
                  );
                    $AD->addToUserTemplatesFile($userTemplate);
                    header("Location: addusertemplatecomplete");
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
        .append( "<div class='mt-1'>" + item.label + "</div>" )
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
        document.getElementById("inputHomeDirectory").value = response.homedirectory + "%USERNAME%";
        document.getElementById("inputHomeDrive").value = response.homedrive;
        document.getElementById("inputScriptPath").value = response.scriptpath;
        document.getElementById("inputProfilePath").value = response.profilepath + "%USERNAME%";
        document.getElementById("inputUserOU").value = response.ou;
        document.getElementById("inputGroupDN").value = response.groups;
      }
    }
  }
  xmlhttp.open("POST", "control/controller", true);
  xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xmlhttp.send("getUserData=" + chosenUser);
}

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
        document.getElementById("inputHomeDirectory").value = response[0].homeDirectory;
        document.getElementById("inputHomeDrive").value = response[0].homeDrive;
        document.getElementById("inputScriptPath").value = response[0].scriptPath;
        document.getElementById("inputProfilePath").value = response[0].profilePath;
        document.getElementById("inputUserOU").value = response[1];
        document.getElementById("inputGroupDN").value = response[2];
      }
    }
  }
  xmlhttp.open("POST", "control/controller", true);
  xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xmlhttp.send("chosenUserTemplate=" + chosenTemplate);
}

document.getElementById("inputUserTemplateName").addEventListener("focusout", function() {
  getTemplateData(document.getElementById("inputUserTemplateName").value);
});

document.getElementById("inputUser").addEventListener("focusout", function() {
  getUserData(document.getElementById("inputUser").value);
});

$(function () { $('#OUTree').jstree(); });
$('#OUTree').on('changed.jstree', function (e, data) {
    var i, j, r = [];
    for(i = 0, j = data.selected.length; i < j; i++) {
      r.push(data.instance.get_node(data.selected[i]).li_attr.value);
    }
    document.getElementById("inputUserOU").value = r.join(', ');
  });
</script>
<?php require("footer.php"); ?>
