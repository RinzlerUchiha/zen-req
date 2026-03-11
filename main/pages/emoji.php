
  <link rel="stylesheet" type="text/css" href="css/jquery.emojipicker.css">
  <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.2.min.js"></script>
  <script type="text/javascript" src="js/jquery.emojipicker.js"></script>

  <link rel="stylesheet" type="text/css" href="css/jquery.emojipicker.tw.css">
  <script type="text/javascript" src="js/jquery.emojis.js"></script>

  <script type="text/javascript">
    $(document).ready(function(e) {

      $('#input-default').emojiPicker();

      // keyup event is fired
      $(".emojiable-option").on("keyup", function () {
        //console.log("emoji added, input val() is: " + $(this).val());
      });

    });
  </script>

  <style type="text/css">
    form {margin:200px 0 0 0; text-align:center;}
    input {width:400px; height:30px;}
    input,textarea,button {padding:5px 10px; font-family:"Helvetica Neue", "Helvetica", "Arial", sans-serif; font-size:24px; font-weight:300; outline:none; border:none;}
    #emojiPickerWrap {margin:10px 0 0 0;}
    .field { padding: 20px 0; }
    textarea { width: 400px; height: 200px; }
  </style>

</head>
<body>
  <form>
    <div class="field">
      <input type="text" id="input-default" class="emojiable-option" placeholder="Default">
    </div>
  </form>