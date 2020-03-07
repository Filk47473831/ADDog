<?php require("header.php"); ?>
<main>
    <div class="container-fluid">
        <h3 class="mt-4">Add Bulk Users</h3>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
            <li class="breadcrumb-item active">Add Bulk Users</li>
        </ol>
            <div class="col-12">
            <div class="card shadow-lg border-0 rounded-lg mt-2">
                <div class="card-body">
                    <?php

                      $AD->connect();
                      $AD->bind();

                          $csv = array();

                          if(isset($_POST['bulkUsersInput'])) {
                            $csv = explode("\r\n", trim($_POST['bulkUsersInput']));

                            echo '<table width="100%" class="table table-striped table-borderless table-hover small" id="dataTable-bulkUsers">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="d-none d-sm-table-cell">Name</th>
                                            <th>Username</th>
                                            <th>Password</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>';

                          foreach($csv as $row) {
                            $row = explode(",",$row);

                            $user['inputFirstName'] = $row[0];
                            $user['inputLastName'] = $row[1];
                            $user['inputUsername'] = $row[2];
                            $user['inputPassword'] = $row[3];

                            if(($user['inputFirstName'] !== "") && ($user['inputLastName'] !== "") && ($user['inputUsername'] !== "") && ($user['inputPassword'] !== "")) {

                                              $addAccount = "";
                                              $testFirstName = $AD->testFirstName($user['inputFirstName']);
                                              $testLastName = $AD->testLastName($user['inputLastName']);
                                              $testUsername = $AD->testUsername($user['inputUsername']);
                                              $testPassword = $AD->testPassword($user['inputPassword'],$user['inputPassword']);

                                              if(($testFirstName == "") && ($testLastName == "") && ($testUsername == "") && ($testPassword == "")) {

                                                $userTemplate = $_POST['inputUserTemplate'];
                                                $info = array();
                                                $info["cn"] = $user['inputFirstName'] . " " . $user['inputLastName'];
                                                $info['givenName'] = $user['inputFirstName'];
                                                $info["sn"] = $user['inputLastName'];
                                                $info["sAMAccountName"] = $user['inputUsername'];
                                                $info["UserPrincipalName"] = $user['inputUsername'] . "@" . $settings->Domain;
                                                $password = $user['inputPassword'];
                                                $addAccount = $AD->addUser($userTemplate,$info,$password,$_POST['inputUserOU'],null);

                                                if($addAccount == "") {

                                                    echo '<tr style="background-color:#ddedd8" class="odd gradeX">
                                                            <td scope="col" class="d-none d-sm-table-cell">' . $user['inputFirstName'] . " " . $user['inputLastName'] . '</td>
                                                            <td>' . $user['inputUsername'] . '</td>
                                                            <td>' . $user['inputPassword'] . '</td>
                                                            <td>Added Successfully</td>
                                                          </tr>';

                                                  } else {

                                                    echo '<tr style="background-color:#edd8d8" class="odd gradeX">
                                                          <td scope="col" class="d-none d-sm-table-cell">' . $user['inputFirstName'] . " " . $user['inputLastName'] . '</td>
                                                          <td>' . $user['inputUsername'] . '</td>
                                                          <td>' . $user['inputPassword'] . '</td>
                                                          <td>' . $addAccount . '</td>
                                                      </tr>';

                                                  }

                                                } else {

                                                  echo '<tr style="background-color:#edd8d8" class="odd gradeX">
                                                        <td scope="col" class="d-none d-sm-table-cell">' . $user['inputFirstName'] . " " . $user['inputLastName'] . '</td>
                                                        <td>' . $user['inputUsername'] . '</td>
                                                        <td>' . $user['inputPassword'] . '</td>
                                                        <td>Account cannot be added - ' . $testPassword . '</td>
                                                    </tr>';

                                                }
                                      } else {

                                        echo '<tr style="background-color:#edd8d8" class="odd gradeX">
                                              <td scope="col" class="d-none d-sm-table-cell">' . $user['inputFirstName'] . " " . $user['inputLastName'] . '</td>
                                              <td>' . $user['inputUsername'] . '</td>
                                              <td>' . $user['inputPassword'] . '</td>
                                              <td>Account cannot be added - Missing Info</td>
                                          </tr>';

                                      }

                          }

                                echo '</tbody>
                                    </table>';

                        }
                           ?>
                  <a href="addbulkusers"><button class="mt-5 btn btn-primary">Back</button></a>
                </div>
            </div>
            </div>
    </div>
</main>
<script>
$('#dataTable-bulkUsers').dataTable( {
  "pageLength": 100,
  dom: 'Bfrtip',
  buttons: {
        buttons: [
            { extend: 'copy', className: 'btn-sm' },
            { extend: 'csv', className: 'btn-sm' },
            { extend: 'excel', className: 'btn-sm' },
            { extend: 'pdf', className: 'btn-sm' },
            { extend: 'print', className: 'btn-sm' }
        ]
    }
} );
</script>
<?php require("footer.php"); ?>
