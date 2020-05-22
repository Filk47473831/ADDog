<?php require("header.php"); ?>
<?php header("Location: /"); ?>
<main>
    <div class="container-fluid">
        <h3 class="mt-4">Bulk Add Users</h3>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
            <li class="breadcrumb-item active">Bulk Add Users</li>
        </ol>
            <div class="col-sm-12 col-md-10 col-lg-8 col-xl-6">
            <div class="card shadow-lg border-0 rounded-lg mt-2">
                <div class="card-body">
                  <?php

                  $AD->connect();
                  $AD->bind();

                   ?>
                  <form action="addbulkuserscomplete" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="small mb-1" for="inputUserTemplate">Select User Template</label>
                        <select name="inputUserTemplate" id="inputUserTemplate" class="form-control"><?php echo $AD->displayUserTemplates(); ?></select>
                    </div>
                    <div class="form-group">
                      <label class="small mb-1" for="OUTree">User OU</label>
                      <div id="OUTree">
                        <?php $AD->showOUTree(); ?>
                      </div>
                      <input required style="border:0px" required class="form-control mt-3" name="inputUserOU" id="inputUserOU" value="" placeholder="Select target OU for new users">
                    </div>
                    <div class="form-group">
                      <label class="small mb-1" for="bulkUsersFile">Users CSV</label>
                      <input name="csv" accept=".txt,.csv" type="file" class="small form-control-file" id="bulkUsersFile">
                      <p class="small mt-2">One user per row. Each row must contain comma separated values for first name, last name, username and desired password. In that order. Max 100 users.</p>
                    </div>
                    <div class="form-group">
                      <textarea required name="bulkUsersInput" class="form-control" id="bulkUsersInput" type="text" rows="10" placeholder="e.g. Chris,Groves,cgroves,Password1234"></textarea>
                    </div>
                    <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                      <input type="submit" class="btn btn-success" href="#" value="Add Bulk Users">
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
        document.getElementById("inputUserOU").value = response[1];
      }
    }
  }
  xmlhttp.open("POST", "control/controller", true);
  xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xmlhttp.send("chosenUserTemplate=" + chosenTemplate);
}

document.getElementById("inputUserTemplate").addEventListener("change", function() {
  if(document.getElementById("inputUserTemplate").value !== "null") {
    getTemplateData(document.getElementById("inputUserTemplate").value);
  }
});

$(function () { $('#OUTree').jstree(); });
$('#OUTree').on('changed.jstree', function (e, data) {
    var i, j, r = [];
    for(i = 0, j = data.selected.length; i < j; i++) {
      r.push(data.instance.get_node(data.selected[i]).li_attr.value);
    }
    document.getElementById("inputUserOU").value = r.join(', ');
  });

document.getElementById("bulkUsersFile").addEventListener("change", function(){
  var file = document.getElementById("bulkUsersFile").files[0];
  if(file) {
    if(file.type === "text/plain" || file.name.endsWith('.csv')) {
      var reader = new FileReader();
      reader.onload = function(progressEvent){
        var lines = this.result.split('\n');
        var res = "";
        for(var line = 0; line < lines.length; line++){
          res += lines[line] + "\n";
        }
        document.getElementById("bulkUsersInput").value = res;
      };
      reader.readAsText(file);
    }
  }
});
</script>
<?php require("footer.php"); ?>
