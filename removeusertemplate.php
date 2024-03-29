<?php require("header.php"); ?>
<?php if(!$_SESSION['admin']) { header("Location: index"); } ?>
  <main>
    <div class="container-fluid">
      <h3 class="mt-4">Remove User Template</h3>
      <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
        <li class="breadcrumb-item active">Remove User Template</li>
      </ol>
      <div class="col-sm-12 col-md-10 col-lg-8 col-xl-6">
        <div class="card shadow-lg border-0 rounded-lg mt-2">
          <div class="card-body">
            <?php
            if(isset($_POST['inputUserTemplate'])) {
              $AD->removeFromUserTemplatesFile($_POST['inputUserTemplate']);

              echo '<p>User Template Removed Successfully</p>
              <a href="removeusertemplate"><button class="btn btn-success">Back</button></a>';
              
            } else {
            ?>

              <form action="removeusertemplate" method="POST">
                <div class="form-group">
                    <label class="small mb-1" for="inputUserTemplate">Select User Template</label>
                    <select name="inputUserTemplate" class="form-control"><?php echo $AD->displayUserTemplates(); ?></select>
                </div>
                <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                  <input type="submit" class="btn btn-danger" href="#" value="Remove User Template">
                </div>
              </form>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </main>
  <?php require("footer.php"); ?>
