<?php require("header.php"); ?>
<main>
    <div class="container-fluid unselectable">
        <h3 class="mt-4">Dashboard</h3>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active"><i class="fas fa-users"></i>&nbsp;Users</li>
        </ol>
        <div class="row">
            <div class="col-xl-3 col-md-6 dashboard-item">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">Reset User Password</div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="resetpw">Go</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 dashboard-item">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">Add User</div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="adduser">Go</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 dashboard-item">
                <div class="card bg-warning text-white mb-4">
                    <div class="card-body">Enable User</div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="enableuser">Go</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 dashboard-item">
                <div class="card bg-danger text-white mb-4">
                    <div class="card-body">Disable User</div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="disableuser">Go</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item active"><i class="fas fa-print"></i>&nbsp;Printing</li>
        </ol>
        <div class="row">
            <div class="col-xl-3 col-md-6 dashboard-item">
                <div class="card bg-info text-white mb-4">
                    <div class="card-body">Reset Print Queue</div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a class="small text-white stretched-link" href="resetprintqueue">Go</a>
                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require("footer.php"); ?>
