<?php ob_start(); ?>
<?php if(session_status() == PHP_SESSION_NONE) { session_start(); } ?>
<?php
require("AD.php");
$AD = new AD;
$settings = $AD->readSettingsFile();
if(!$AD->isLoggedIn()) { header("Location: login"); }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        <meta name="description" content="AD Dog Active Directory Commander" />
        <meta name="author" content="Chris Groves" />
        <title>AD Dog</title>
        <link href="css/styles.css" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css?family=Montserrat&display=swap" rel="stylesheet" crossorigin="anonymous" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet"  crossorigin="anonymous" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" rel="stylesheet" crossorigin="anonymous" />
        <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.flash.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/plug-ins/1.10.20/pagination/select.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.print.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/gh/xcash/bootstrap-autocomplete@v2.3.0/dist/latest/bootstrap-autocomplete.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark unselectable">
            <a class="navbar-brand" href="/"><img style="max-width: 50px" src="img/dog_white.png"> <small>AD Dog</small></a>
            <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>
            <ul class="navbar-nav ml-auto mr-0 mr-md-3 my-2 my-md-0">
                <li class="nav-item dropdown">
                    <div class="nav-link dropdown-toggle" id="userDropdown" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-user fa-fw"></i></div>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <?php if(isset($_SESSION['admin'])) { if($_SESSION['admin']) { ?>
                        <a class="dropdown-item" href="editsettings">Settings</a><?php } } ?><a class="dropdown-item" href="activity">Activity Log</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout">Logout</a>
                    </div>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav" class="unselectable">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading"></div>
                            <a class="nav-link" href="/"><div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard</a>
                            <div id="usersNavItem" class="cursor-pointer nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts"><div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                                Users
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div></div>
                            <div class="collapse cursor-pointer" id="collapseLayouts" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                  <div id="individualUserNavItem" class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#individualUserNavItems" aria-expanded="false" aria-controls="individualUserNavItems">
                                      Individual User
                                      <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div></div>
                                  <div class="collapse" id="individualUserNavItems" aria-labelledby="headingOne" data-parent="#collapseLayouts">
                                      <nav class="sb-sidenav-menu-nested nav">
                                        <a class="nav-link" href="resetpw">Reset Password</a>
                                        <a class="nav-link" href="adduser">Add User</a>
                                        <a class="nav-link" href="removeuser">Remove User</a>
                                        <a class="nav-link" href="enableuser">Enable User</a>
                                        <a class="nav-link" href="disableuser">Disable User</a>
                                      </nav>
                                  </div>
                                  <div id="bulkManageNavItem" class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#bulkManageNavItems" aria-expanded="false" aria-controls="bulkManageNavItems">
                                      Bulk Manage
                                      <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div></div>
                                  <div class="collapse" id="bulkManageNavItems" aria-labelledby="headingOne" data-parent="#collapseLayouts">
                                      <nav class="sb-sidenav-menu-nested nav">
                                        <a class="nav-link" href="resetpwbulk">Reset Passwords</a>
                                        <a class="nav-link" href="addbulkusers">Add Users</a>
                                        <a class="nav-link" href="removebulkusers">Remove Users</a>
                                      </nav>
                                  </div>
                                  <?php if(isset($_SESSION['admin'])) { if($_SESSION['admin']) { ?>
                                  <div id="templatesNavItem" class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#templatesNavItems" aria-expanded="false" aria-controls="templatesNavItems">
                                      Templates
                                      <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div></div>
                                  <div class="collapse" id="templatesNavItems" aria-labelledby="headingOne" data-parent="#collapseLayouts">
                                      <nav class="sb-sidenav-menu-nested nav">
                                        <a class="nav-link" href="addusertemplate">Add User Template</a>
                                        <a class="nav-link" href="removeusertemplate">Remove User Template</a>
                                      </nav>
                                  </div>
                                  <?php } } ?>
                                </nav>
                            </div>
                            <div id="printingNavItem" class="cursor-pointer nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages" aria-expanded="false" aria-controls="collapsePages"><div class="sb-nav-link-icon"><i class="fas fa-print"></i></div>
                                Printing
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div></div>
                            <div class="collapse" id="collapsePages" aria-labelledby="headingTwo" data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav"><a class="nav-link" href="resetprintqueue">Reset Print Queue</a></nav>
                            </div>
                            <div class="sb-sidenav-menu-heading"></div>
                            <?php if(isset($_SESSION['admin'])) { if($_SESSION['admin']) { ?>
                            <a class="nav-link" href="editsettings"><div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
                                Settings</a><?php } } ?>
                            <a class="nav-link" href="activity"><div class="sb-nav-link-icon"><i class="far fa-file-alt"></i></div>
                                Activity Log</a>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        <?php if(isset($_SESSION['username'])) { echo $_SESSION['username']; } if(isset($_SESSION['admin'])) { if($_SESSION['admin']) { echo " - (admin)"; } } ?>
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
