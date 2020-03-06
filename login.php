<?php ob_start();
if (session_status() == PHP_SESSION_NONE) { session_start(); }
require("AD.php");
$AD = new AD;
$settings = $AD->readSettingsFile();
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
                                      <?php

                                        if(isset($_POST['inputUsername'])) {
                                          $AD->connect();
                                          if($login = $AD->login()) {
                                            if($login == 1) {
                                              header("Location: index");
                                            }
                                            if($login === -1) {
                                              header("Location: settings");
                                            }
                                          }
                                        }

                                         ?>
                                        <form action="login" method="POST">
                                            <div class="form-group">
                                              <label class="small mb-1" for="inputUsername">Username</label><input name="inputUsername" class="<?php if(isset($_POST['inputUsername'])) { echo "is-invalid"; } ?> form-control py-4" id="inputUsername" type="text" value="<?php if(isset($_POST['inputUsername'])) { echo $_POST['inputUsername']; } ?>"/>
                                            </div>
                                            <div class="form-group">
                                              <label class="small mb-1" for="inputPassword">Password</label><input name="inputPassword" class="<?php if(isset($_POST['inputUsername'])) { echo "is-invalid"; } ?> form-control py-4" id="inputPassword" type="password" value="<?php if(isset($_POST['inputPassword'])) { echo $_POST['inputPassword']; } ?>"/>
                                              <div class="invalid-feedback">Login Failed</div>
                                            </div>

                                            <div class="form-group float-right mb-0"><input type="submit" class="btn btn-primary" href="#" value="Login"></div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-center">
                                        <div class="small"><?php if(isset($settings->LoginMessage)) { echo $settings->LoginMessage; } else { echo "Please login with your network credentials"; }?></div>
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
