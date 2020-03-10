<?php require("header.php"); ?>
  <main>
      <div class="container-fluid">
          <h3 class="mt-4">Remove User</h3>
          <ol class="breadcrumb mb-4">
              <li class="breadcrumb-item"><a href="index">Dashboard</a></li>
              <li class="breadcrumb-item active">Remove User</li>
          </ol>
              <div class="col-sm-12 col-md-10 col-lg-8 col-xl-6">
              <div class="card shadow-lg border-0 rounded-lg mt-2">
                  <div class="card-body">

          <?php

          $AD->connect();
          $AD->bind();

          $data = $AD->searchAD();

          print_r($data);

          if(isset($_POST['inputUser'])) {
                  $AD->removeUser($_POST['inputUser']);
                  $name = explode(",",$_POST['inputUser']);
                  $AD->writeActivityLogFile(gmdate("d-m-y h:i:sa") . ",Removed User," . substr($name[0], 3) . "," . $_SESSION['username']);

                  echo '<p>User Removed Successfully</p>
                  <a href="removeuser"><button class="btn btn-success">Back</button></a>';

          } else {

          ?>

                      <form action="removeuser" method="POST">
                          <div class="form-group">
                              <label class="small mb-1" for="inputUser">Select User</label>
                              <input required class="form-control" id="inputUser" type="text" placeholder="e.g. John Smith" value="<?php if(isset($_POST['inputUserTemplateName'])) { echo $_POST['inputUser']; } ?>"/>
                              <input readonly style="margin-top:3px;border:0px;font-size:0.8rem" class="form-control" name="inputUser" id="inputUser-id">
                          </div>
                          <div class="form-group d-flex align-items-center justify-content-between mt-4 mb-0">
                              <input type="submit" class="btn btn-danger" href="#" value="Remove User">
                          </div>

                      </form>
                      <?php } ?>
                  </div>
              </div>
              </div>
      </div>
  </main>
  <script>
  $( function() {
    var users = <?php $AD->updateUsersJSON($data); ?>;

    $("#inputUser").autocomplete({
      minLength: 0,
      source: function(request, response) {
          var results = $.ui.autocomplete.filter(users, request.term);
          response(results.slice(0, 12));
      },
      focus: function( event, ui ) {
        $( "#inputUser" ).val( ui.item.label );
        return false;
      },
      select: function( event, ui ) {
        $( "#inputUser" ).val( ui.item.label );
        $( "#inputUser-id" ).val( ui.item.value );
        return false;
      }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {
      return $( "<li>" )
        .append( "<div class='mt-1'>" + item.label + "</div>" )
        .appendTo( ul );
    };
  } );
  </script>
<?php require("footer.php"); ?>
