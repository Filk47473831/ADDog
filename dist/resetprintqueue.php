<?php require("header.php"); ?>
<main>
    <div class="container-fluid">
        <h3 class="mt-4">Reset Print Queue</h3>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Reset Print Queue</li>
        </ol>
            <div class="col-sm-12 col-md-10 col-lg-8 col-xl-6">
            <div class="card shadow-lg border-0 rounded-lg mt-2">
                <div class="card-body">
                  <?php
                  if(isset($_POST['resetPrintQueue'])) {
                    echo $AD->resetPrintQueue();
                    $AD->writeActivityLogFile(gmdate("d-m-y h:i:sa") . ",Print Queue Reset,-," . $_SESSION['username']);
                  ?>
                  <p>Print Queue Reset Successfully</p>
                  <a href="resetprintqueue.php"><button class="btn btn-success">Back</button></a>
                <?php } else { ?>
                  <form action="resetprintqueue.php" method="POST">
                    <input hidden name="resetPrintQueue">
                    <p>Clear all currently pending jobs in the printer queue.</p>
                      <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                          <input type="submit" class="btn btn-primary" href="#" value="Reset Print Queue">
                      </div>
                  </form>
                <?php } ?>
                </div>
            </div>
            </div>
    </div>
</main>
<?php require("footer.php"); ?>
