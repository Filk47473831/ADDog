<?php require("header.php"); ?>
<main>
    <div class="container-fluid">
        <h3 class="mt-4">Bulk Reset User Passwords</h3>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
            <li class="breadcrumb-item active">Bulk Reset User Passwords</li>
        </ol>
            <div id="resetPwBulkDiv" class="col-sm-12 col-md-10 col-lg-8 col-xl-6">
            <div class="card shadow-lg border-0 rounded-lg mt-2">
                <div id="resetPwBulkForm" class="card-body">

        <?php

        $AD->connect();
        $AD->bind();

        $settings = $AD->readSettingsFile();

        ?>

                        <div hidden id="passwordLength"><?php echo $settings->PasswordMinLength; ?></div>
                        <div class="form-group">
                            <label class="small mb-1" for="inputPasswordFormat">Select Password Format</label>
                            <select disabled id="inputPasswordFormat" class="form-control">
                              <option value="1">Random Simple</option>
                              <option value="2">Random Complex</option>
                              <option value="3">3 Random Words</option>
                              <option selected value="4">Custom</option>
                            </select>
                        </div>
                        <div id="customerPasswordFormGroup" class="form-group">
                            <label class="small mb-1" for="inputCustomPassword">Custom Password</label>
                            <input class="form-control" id="inputCustomPassword" type="text" placeholder="Custom Password" value=""/>
                        </div>
                        <div class="form-group">
                          <label class="small mb-1" for="OUTree">Target OU</label>
                          <div id="OUTree">
                            <?php $AD->showOUTree(); ?>
                          </div>
                          <input style="border:0px" required class="form-control mt-2" name="inputUserOU" id="inputUserOU" value="" placeholder="Select Target OU from Tree">
                          <p style="margin-left:12px" class="small" id="targetCount"></p>
                        </div>
                        <div class="form-check">
                          <input type="checkbox" class="form-check-input" id="promptNextLogin">
                          <label class="form-check-label small unselectable" for="promptNextLogin">Prompt user to change password on next login (this may not allow the user to login if connecting remotely). This setting will not work if your Administrator has configured the user password never to expire.</label>
                        </div>
                        <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                            <input id="resetPwBulkBtn" onclick="bulkPwReset()" type="button" class="btn btn-primary" href="#" value="Reset Passwords">&nbsp;&nbsp;
                            <div id="invalid-feedback" class="invalid-feedback">Invalid Users Found</div>
                        </div>
                </div>
            </div>
            </div>
    </div>
</main>
<script>

var users = "";
var promptNextLogin = false;

// document.getElementById("inputPasswordFormat").addEventListener("change", function(){
//   if(document.getElementById("inputPasswordFormat").value == "4"){
//     document.getElementById("customerPasswordFormGroup").removeAttribute("hidden");
//   }
// })

async function bulkPwReset() {
  var error = false;
  var targetSearchOU = document.getElementById("inputUserOU").value;
  var password = document.getElementById("inputCustomPassword").value;
  promptNextLogin = document.getElementById("promptNextLogin").checked;

  if(password.length < document.getElementById("passwordLength").innerText) { error = "Password must be at least " + document.getElementById("passwordLength").innerText + " character(s) long."; }
  if(targetSearchOU == "") { error = "Please select a target OU"; }

  if(!error) {

  var output = `<table width="100%" class="table table-striped table-borderless table-hover small" id="dataTable-bulkUsers">
          <thead>
              <tr>
                  <th>Username</th>
                  <th>Password</th>
                  <th>Status</th>
              </tr>
          </thead>
          <tbody>`;

  if (window.XMLHttpRequest) {
    xmlhttp = new XMLHttpRequest();
  } else {
    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onload = function() {
    if (this.status == 200) {
      if(this.responseText == "") { error = true; throw new Error(); }

      users = JSON.parse(this.responseText);

       for(var i = 0; i < users.length; i++) {
            if(users[i] != null) {

              output +=
                      `<tr id="${i}-row" class="odd gradeX">
                              <td>${users[i]}</td>
                              <td>${password}</td>
                              <td id="${i}-status">...</td>
                            </tr>`;

           }
         }
       output += `</tbody>
       </table>`;

       document.getElementById("resetPwBulkDiv").setAttribute("class", "col-12");
       document.getElementById("resetPwBulkForm").innerHTML = output;

       for(i = 0; i < users.length; i++) {
         resetPw(i, password);
       }

       drawTable();

     document.getElementById("resetPwBulkForm").innerHTML += `<a href="resetpwbulk"><button class="mt-5 btn btn-primary">Back</button></a>`;

    }
  }
  xmlhttp.open("POST", "control/controller", true);
  xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xmlhttp.send("targetGetUsersFromOU=" + targetSearchOU);
} else {
  document.getElementById("resetPwBulkBtn").classList.add("is-invalid");
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

function resetPw(i, password) {

  var result = null;
  console.log(promptNextLogin);
  if(promptNextLogin == true) { promptNextLogin = "on"; }

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
        if(result !== "") {
          document.getElementById(status).innerText = result;
          document.getElementById(row).style.backgroundColor = "#edd8d8";
        } else {
          document.getElementById(status).innerText = "Reset Successfully";
          document.getElementById(row).style.backgroundColor = "#ddedd8";
        }
      }


    }
  }
  xmlhttp.open("POST", "control/controller", true);
  xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xmlhttp.send("resetPw=" + users[i] + "&password=" + password + "&promptNextLogin=" + promptNextLogin);

}

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
