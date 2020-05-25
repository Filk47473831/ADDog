<?php require("header.php"); ?>
<main>
    <div class="container-fluid">
        <h3 class="mt-4">Bulk Add Users</h3>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
            <li class="breadcrumb-item active">Bulk Add Users</li>
        </ol>
            <div id="addBulkUsersDiv" class="col-sm-12 col-md-10 col-lg-8 col-xl-6">
            <div class="card shadow-lg border-0 rounded-lg mt-2">
                <div id="addBulkUsersForm" class="card-body">
                  <?php

                  $AD->connect();
                  $AD->bind();

                   ?>
                    <div class="form-group">
                        <label class="small mb-1" for="inputUserTemplate">Select User Template</label>
                        <select id="inputUserTemplate" class="form-control"><?php echo $AD->displayUserTemplates(); ?></select>
                    </div>
                    <div class="form-group">
                      <label class="small mb-1" for="OUTree">User OU</label>
                      <div id="OUTree">
                        <?php $AD->showOUTree(); ?>
                      </div>
                      <input required style="border:0px" required class="form-control mt-3" id="inputUserOU" value="" placeholder="Select target OU for new users">
                    </div>
                    <div class="form-group">
                      <label class="small mb-1" for="bulkUsersFile">Users CSV</label>
                      <input name="csv" accept=".txt,.csv" type="file" class="small form-control-file" id="bulkUsersFile">
                      <p class="small mt-2">One user per row. Each row must contain comma separated values for first name, last name, username and desired password. In that order. Max 100 users.</p>
                    </div>
                    <div class="form-group">
                      <textarea required class="form-control" id="bulkUsersInput" type="text" rows="10" placeholder="e.g. Chris,Groves,cgroves,Password1234"></textarea>
                    </div>
                    <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                      <input id="addBulkUsersBtn" onclick="addBulkUsers()" type="button" class="btn btn-success" href="#" value="Add Bulk Users">&nbsp;&nbsp;
                      <div id="invalid-feedback" class="invalid-feedback">Invalid Users Found</div>
                    </div>
                </div>
            </div>
            </div>
    </div>
</main>
<script>
var users = "";
var user = "";
var userTemplate = "";
var userOU = "";

async function addBulkUsers(){

  var error = false;
  userTemplate = document.getElementById("inputUserTemplate").value;
  userOU = document.getElementById("inputUserOU").value;

  users = document.getElementById("bulkUsersInput").value;
  users = users.split("\n");

  if(users.length < 100) {
      for(i = 0; i < users.length; i++) {
        if(users[i] == "") {
          error = "Missing Line";
        } else {

          if(users[i].indexOf(",") !== -1) {
            user = users[i].split(",");
            if(user.length == 4) {
            for(j = 0; j < users[i].length; j++) {
              if(user[j] == "") {
                error = "Missing Fields";
              }
            }
          } else { error = "Missing Fields"; }
          }

        }
      }
  } else {
      error = "Too Many Users (Max 100)";
    }

if(error == false) {

    document.getElementById("addBulkUsersDiv").setAttribute("class", "col-12");
    var output =
    `<table width="100%" class="table table-striped table-borderless table-hover small" id="dataTable-bulkUsers">
            <thead>
                <tr>
                    <th scope="col" class="d-none d-sm-table-cell">Name</th>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>`;

            for(i = 0; i < users.length; i++) {

            user = users[i].split(",");

    output +=
            `<tr id="${i}-row" class="odd gradeX">
                    <td scope="col" class="d-none d-sm-table-cell">${user[0]} ${user[1]}</td>
                    <td>${user[2]}</td>
                    <td>${user[3]}</td>
                    <td id="${i}-status">...</td>
                  </tr>`;

                }

  output +=
            `</tbody>
    </table>`;

    document.getElementById("addBulkUsersForm").innerHTML = output;

      for(i = 0; i < users.length; i++) {
        addUser(i);
      }

    document.getElementById("addBulkUsersForm").innerHTML += `<a href="addbulkusers"><button class="mt-5 btn btn-primary">Back</button></a>`;

    drawTable();

    } else {
      document.getElementById("addBulkUsersBtn").classList.add("is-invalid");
      document.getElementById("bulkUsersInput").classList.add("is-invalid");
      document.getElementById("invalid-feedback").innerText = error;
    }

}

function drawTable() {
  $('#dataTable-bulkUsers').dataTable( {
    "pageLength": 100,
    "sPaginationType": "listbox",
    dom: 'Bfrtip',
    buttons: {
          buttons: [
              { extend: 'copy', className: 'btn btn-primary btn-sm' },
              { extend: 'csv', className: 'btn btn-primary btn-sm' },
              { extend: 'excel', className: 'btn btn-primary btn-sm' },
              { extend: 'pdf', className: 'btn btn-primary btn-sm' },
              { extend: 'print', className: 'btn btn-primary btn-sm' }
          ]
      }
  } );
}

function addUser(i) {

  var result = null;

  if (window.XMLHttpRequest) {
    xmlhttp = new XMLHttpRequest();
  } else {
    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onload = function() {
    if (this.status == 200) {
      result = this.responseText;

      var status = i + "-status";
      var row = i + "-row";

      if(result !== null) {
        document.getElementById(status).innerText = result;
        if(result !== "Added Successfully") {
          document.getElementById(row).style.backgroundColor = "#edd8d8";
        } else {
          document.getElementById(row).style.backgroundColor = "#ddedd8";
        }
      }


    }
  }
  xmlhttp.open("POST", "control/controller", true);
  xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xmlhttp.send("addUser=" + users[i] + "&inputUserTemplate=" + userTemplate + "&inputUserOU=" + userOU);

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
        document.getElementById("inputUserOU").value = response[1];
      }
    }
  }
  xmlhttp.open("POST", "control/controller", true);
  xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xmlhttp.send("chosenUserTemplate=" + chosenTemplate);
}

if(document.getElementById("inputUserTemplate").value !== "No Available Templates") {
  getTemplateData(document.getElementById("inputUserTemplate").value);
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
          if(lines.length - line > 1) {
            res += lines[line] + "\n";
          } else {
            res += lines[line]
          }
        }
        document.getElementById("bulkUsersInput").value = res;
      };
      reader.readAsText(file);
    }
  }
});
</script>
<?php require("footer.php"); ?>
