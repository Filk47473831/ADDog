<?php
require("AD.php");
$AD = new AD;
$settings = $AD->readSettingsFile();
$AD->getKey();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>AD Dog</title>
        <link href="css/styles.css" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css?family=Montserrat&display=swap" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js" crossorigin="anonymous"></script>
    </head>
    <body class="bg-primary">
        <div id="layoutAuthentication">
            <div id="layoutAuthentication_content">
                <main>
                    <div class="container">
                        <div class="row justify-content-center login-panel">
                            <div class="col-lg-5">
                                <div class="card shadow-lg border-0 rounded-lg mt-2">
                                    <div class="card-header d-flex justify-content-center">
                                      <img style="max-width: 140px; max-height: 110px" src="assets/img/dog.png">
                                    </div>
                                    <div class="card-body">
                                      <form action="settings" method="POST">
                                        <div class="form-group">
                                          <label class="small mb-1" for="inputDC">Domain Controller</label>
                                          <input required name="inputDC" class="form-control" id="inputDC" type="text" placeholder="e.g. A-DC" value="<?php if(isset($_POST['inputDC'])) { echo $_POST['inputDC']; } else { echo $settings->Server; } ?>"/>
                                        </div>
                                        <div class="form-group">
                                          <label class="small mb-1" for="inputDC">Domain FQDN</label>
                                          <input required name="inputDomain" class="form-control" id="inputDomain" type="text" placeholder="e.g. ASDOMAIN.local" value="<?php if(isset($_POST['inputDC'])) { echo $_POST['inputDomain']; } else { echo $settings->Domain; } ?>"/>
                                        </div>
                                        <div class="form-group">
                                          <label class="small mb-1" for="inputOU">Search OU</label>
                                          <input required name="inputOU" class="form-control" id="inputOU" type="text" placeholder="e.g. OU=Arunside,DC=ASDOMAIN,DC=LOCAL" value="<?php if(isset($_POST['inputDC'])) { echo $_POST['inputOU']; } else { if(isset($settings->SearchOU[0])) { echo $settings->SearchOU[0]; } } ?>"/>
                                        </div>
                                        <div class="form-group">
                                          <label class="small mb-1" for="inputUsername">Admin Account</label>
                                          <input required name="inputUsername" class="form-control" id="inputUsername" type="text" placeholder="e.g. Administrator" value="<?php if(isset($_POST['inputDC'])) { echo $_POST['inputUsername']; } else { echo $settings->Username; } ?>"/>
                                        </div>
                                        <div class="form-group">
                                          <input required name="inputPassword" class="form-control" id="inputPassword" type="password" placeholder="Confirm Password" value=""/>
                                        </div>
                                        <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                                          <input type="submit" class="btn btn-primary" href="#" value="Submit">
                                        </div>
                                        <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">

                                          <?php

                                          if(isset($_POST['inputDC'])) {

                                              if($_POST['inputDC'] !== "" && $_POST['inputDC'] !== "" && $_POST['inputUsername'] !== "" && $_POST['inputPassword'] !== "" && $_POST['inputOU'] !== "") {
                                              $settings = $AD->writeSettingsFile($_POST['inputDC'],$_POST['inputDomain'],$_POST['inputUsername'],$_POST['inputPassword'],$_POST['inputOU']);
                                              $admins = [strtolower($_POST['inputUsername'])];
                                              $AD->writeAdminsFile($admins);
                                              header("Location: login");
                                            } else {
                                              echo "Fields Missing";
                                            }
                                          }

                                          ?>

                                        </div>
                                      </form>
                                    </div>
                                    <div class="card-footer text-center">
                                        <div class="small">Please update your settings</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <div id="layoutAuthentication_footer">
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid">
                        <div class="text-center small">
                            <div class="text-muted">Copyright &copy; Chris Groves 2020</div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
    </body>
</html>
