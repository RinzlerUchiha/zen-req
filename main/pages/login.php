<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <link rel="icon" href="/zen/assets/img/coffi.png" type="image/png">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" />

  <style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700;800&display=swap");

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      font-family: "Facebook Letter Faces", sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background-color: #f4f4f4;
    }

    .container {
      display: flex;
      width: 100%;
      max-width: 900px;
      margin-bottom: 200px;
    }

    .logo-section,
    .form-section {
      padding: 50px;
    }

    .logo-section {
      color: #64402f;
      justify-content: center;
      align-items: center;
      width: 40%;
      text-align: center;
    }

    .logo-section h1 {
      font-size: 3rem;
    }

    .form-section {
      width: 60%;
      text-align: center;
      align-content: center;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .input-field {
      max-width: 380px;
      width: 100%;
      background-color: #f0f0f0;
      margin: 10px auto;
      height: 55px;
      border-radius: 55px;
      display: grid;
      grid-template-columns: 15% 70% 15%;
      padding: 0 0.4rem;
      position: relative;
    }

    .input-field i {
      text-align: center;
      line-height: 55px;
      color: #acacac;
      font-size: 1.1rem;
      cursor: pointer;
    }

    .input-field input {
      background: none;
      outline: none;
      border: none;
      font-weight: 600;
      font-size: 1.1rem;
      color: #333;
      padding: 10px;
    }

    .btn {
      width: 150px;
      background-color: #5995fd;
      height: 49px;
      border-radius: 49px;
      color: #fff;
      text-transform: uppercase;
      font-weight: 600;
      margin: 10px 0;
      cursor: pointer;
      transition: 0.5s;
    }

    .btn:hover {
      background-color: #4d84e2;
    }

    @media (max-width: 768px) {
      .container {
        flex-direction: column;
      }

      .logo-section,
      .form-section {
        width: 100%;
        padding: 30px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="logo-section">
      <img src="https://teamtngc.com/zen/assets/img/coffi.png" />
      <h2>Zenhub</h2>
      <p>Your Daily Dose of Energy & Inspiration.</p>
    </div>

    <div class="form-section">
      <form action="" method="post" id="loginForm" class="sign-in-form">
        <div class="input-field">
          <i class="fas fa-user"></i>
          <input type="text" name="username" id="username" placeholder="Username" required />
          <span></span>
        </div>

        <div class="input-field">
          <i class="fas fa-lock"></i>
          <input type="password" name="password" id="password" placeholder="Password" required />
          <i class="fas fa-eye" id="togglePassword"></i>
        </div>

        <input type="submit" name="submit" value="Login" class="btn" />
        <div id="alertPlaceholder"></div>
      </form>
    </div>
  </div>

  <!-- JS Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
    // Toggle password visibility
    $(document).ready(function () {
      $('#togglePassword').on('click', function () {
        const password = $('#password');
        const type = password.attr('type') === 'password' ? 'text' : 'password';
        password.attr('type', type);
        $(this).toggleClass('fa-eye fa-eye-slash');
      });

      $('#loginForm').on('submit', function (event) {
        event.preventDefault();
        $.ajax({
          url: 'signIn',
          method: 'POST',
          data: $(this).serialize(),
          success: function (response) {
            try {
              let result = typeof response === 'string' ? JSON.parse(response) : response;
              if (result.success) {
                showAlert(result.message, 'success');
                setTimeout(() => window.location.href = '/zen/dashboard', 2000);
              } else {
                showAlert('Login failed: ' + result.message, 'danger');
              }
            } catch (e) {
              showAlert('Invalid response from server.', 'danger');
              console.error(e);
            }
          },
          error: function (xhr, status, error) {
            showAlert('Error: ' + error, 'danger');
          }
        });
      });

      function showAlert(message, type) {
        let alertBox = $(`
          <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
          </div>
        `);
        $('#alertPlaceholder').html(alertBox);
        setTimeout(() => alertBox.alert('close'), 3000);
      }
    });
  </script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
