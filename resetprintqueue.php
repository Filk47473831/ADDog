<?php require("header.php");
$settings = $AD->readSettingsFile(); ?>
<main>
    <div class="container-fluid">
        <h3 class="mt-4">Reset Print Queue</h3>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
            <li class="breadcrumb-item active">Reset Print Queue</li>
        </ol>
            <div class="col-sm-12 col-md-10 col-lg-8 col-xl-6">
            <div class="card shadow-lg border-0 rounded-lg mt-2">
                <div id="printResetForm" class="card-body">
                    <input hidden name="resetPrintQueue">
                    <p>Clear all currently pending jobs in the printer queue.</p>
                      <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                          <button id="resetPrintQueueBtn" onclick="resetPrintQueue()" type="button" class="btn btn-primary">Reset Print Queue</button>
                      </div>
                </div>
            </div>
            </div>
    </div>
</main>
<script>
function resetPrintQueue(){

  document.getElementById("resetPrintQueueBtn").innerHTML = 'Reset Print Queue &nbsp;<i style="margin-top:2px" class="fas fa-circle-notch fa-spin"></i>';

  if (window.XMLHttpRequest) {
    xmlhttp = new XMLHttpRequest();
  } else {
    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onload = function() {
    if (this.status == 200) {
      document.getElementById("printResetForm").innerHTML = '<p>Print Queue Reset Successfully</p><a href="resetprintqueue"><button class="btn btn-success">Back</button></a>';
    }
  }
  xmlhttp.open("POST", "control/controller", true);
  xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xmlhttp.send("resetPrintQueue");
}
</script>
<?php require("footer.php"); ?>
