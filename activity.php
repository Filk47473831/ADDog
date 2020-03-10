<?php require("header.php"); ?>
<main>
  <div class="container-fluid">
    <h3 class="mt-4">Activity Log</h3>
    <ol class="breadcrumb mb-4">
      <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
      <li class="breadcrumb-item active">Activity Log</li>
    </ol>
    <div class="col-12">
    <div class="card shadow-lg border-0 rounded-lg mt-2">
        <div style="overflow:scroll" class="card-body">
                        <table width="100%" class="table table-striped table-borderless table-hover small" id="dataTable-activityLog">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Action</th>
                                    <th>User(s)</th>
                                    <th>Actioned By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $activities = $AD->readActivityLogFile();
                                    if($activities !== null) {
                                      foreach($activities as $activity) {
                                        $activity = explode(",",$activity);
                                        echo '<tr class="odd gradeX">
                                                <td>' . $activity[0] . '</td>
                                                <td>' . $activity[1] . '</td>
                                                <td>' . $activity[2] . '</td>
                                                <td>' . $activity[3] . '</td>
                                              </tr>';
                                      }
                                    }
                                 ?>
                            </tbody>
                        </table>
                      <?php if(isset($_SESSION['admin'])) { if($_SESSION['admin']) { ?><button onclick="clearAllLog()" class="btn btn-warning btn-sm">Clear Logs</button><?php } } ?>
                    </div>
                </div>
        </div>
    </div>
</main>
<script>

function clearAllLog(){
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
  xmlhttp.send("clearAllLog");
}

  $('#dataTable-activityLog').dataTable( {
    "order": [[ 0, "desc" ]],
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
</script>
<?php require("footer.php"); ?>
