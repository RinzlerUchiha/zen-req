<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="assets/image/d.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous" />
    <link rel="stylesheet" type="text/css" href="assets/css/login.css">
</head>
  <body>
    <div class="container">
    <div id="alertPlaceholder"></div>
  <div class="wrapper">
    <div class="title"><span><b>Sign In</b></span></div>
    <form action="" method="post" id="loginForm" class="sign-in-form">
      <div align="center">
        <!-- <img src="assets/image/sc.png"> -->
      </div>
      <div class="col-12">
        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text" id="basic-addon1"><i class="fas fa-user"></i></span>
          </div>
          <input name="username" type="text" value="" class="input form-control" id="username" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" />
        </div>
      </div>
      <div class="col-12">
        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text" id="basic-addon1"><i class="fas fa-lock"></i></span>
          </div>
          <input name="password" type="password" value="" class="input form-control" id="password" placeholder="password" required="true" aria-label="password" aria-describedby="basic-addon1" />
          <div class="input-group-append">
            <span class="input-group-text" onclick="password_show_hide();">
              <i class="fas fa-eye" id="show_eye"></i>
              <i class="fas fa-eye-slash d-none" id="hide_eye"></i>
            </span>
          </div>
        </div>
      </div>
      <div class="col-12">
        <div class="input-group mb-3">
          <input type="submit" name="submit" value="Login" class="btn btn-sm btn-block login" />
        </div>
      </div>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('#loginForm').on('submit', function(event) {
        event.preventDefault();
        
        $.ajax({
            url: 'signIn',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                console.log('Server response:', response); // Debugging line

                try {
                    let result = typeof response === 'string' ? JSON.parse(response) : response;

                    if (result.success) {
                        showAlert(result.message, 'success');
                        setTimeout(function() {
                            window.location.href = '/Portal/dashboard';
                        }, 2000); // Redirect after 2 seconds
                    } else {
                        showAlert('Login failed: ' + result.message, 'danger');
                    }
                } catch (e) {
                    showAlert('An error occurred while processing the response.', 'danger');
                    console.error('Error parsing JSON:', e);
                }
            },
            error: function(xhr, status, error) {
                showAlert('An error occurred: ' + error, 'danger');
                console.error('AJAX error:', xhr, status, error);
            }
        });
    });
});

function showAlert(message, type) {
    let alertBox = $(`
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `);

    $('#alertPlaceholder').html(alertBox);

    // Ensure the alert can be dismissed automatically after 3 seconds
    setTimeout(function() {
        alertBox.alert('close');
    }, 1000); // Auto-dismiss after 3 seconds
}


function password_show_hide() {
  var x = document.getElementById("password");
  var show_eye = document.getElementById("show_eye");
  var hide_eye = document.getElementById("hide_eye");
  hide_eye.classList.remove("d-none");
  if (x.type === "password") {
    x.type = "text";
    show_eye.style.display = "none";
    hide_eye.style.display = "block";
  } else {
    x.type = "password";
    show_eye.style.display = "block";
    hide_eye.style.display = "none";
  }
}
</script>

<link rel="stylesheet" type="text/css" href="src/plugins/sweetalert2/sweetalert2.css"/>
<script src="src/plugins/sweetalert2/sweet-alert.init.js"></script>
<script src="app.js"></script>

  </body>
</html>