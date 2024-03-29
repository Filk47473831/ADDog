<?php require("header.php"); ?>
<main>
    <div class="container-fluid">
        <h3 class="mt-4">Bulk Remove Users</h3>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
            <li class="breadcrumb-item active">Bulk Remove Users</li>
        </ol>
            <div class="col-sm-12 col-md-10 col-lg-8 col-xl-6">
            <div class="card shadow-lg border-0 rounded-lg mt-2">
                <div class="card-body">

        <?php

        $AD->connect();
        $AD->bind();

        if(isset($_POST['inputUserOU'])) {
            $searchOU = [$_POST['inputUserOU']];
            $data = $AD->searchTargetOU($searchOU);
              foreach($data as $user) {
                $AD->removeUser($user['dn']);
                $name = explode(",",$user['dn']);
                $AD->writeActivityLogFile(gmdate("d-m-y h:i:sa") . ",Removed User," . substr($name[0], 3) . "," . $_SESSION['username']);
              }

              echo '<p>Bulk User Removal Completed</p>
              <a href="removebulkusers"><button class="btn btn-success">Back</button></a>';
        } else {

        ?>

                    <form action="removebulkusers" method="POST">
                        <div class="form-group">
                          <label class="small mb-1" for="OUTree">Target OU</label>
                          <div id="OUTree">
                            <?php $AD->showOUTree(); ?>
                          </div>
                          <input style="border:0px" required class="form-control mt-2" name="inputUserOU" id="inputUserOU" value="" placeholder="Select Target OU from Tree">
                          <p style="margin-left:12px" class="small" id="targetCount"></p>
                        </div>

                        <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                            <input type="submit" class="btn btn-danger" href="#" value="Remove Users">
                        </div>
                    </form>
                    
                  <?php } ?>
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
       document.getElementById("targetCount").innerText = this.responseText + " user(s) will be removed.";
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
    document.getElementById("inputUserOU").value = r.join(', ');
    getTargetOUCount(r);
  });
</script>
<?php require("footer.php"); ?>
