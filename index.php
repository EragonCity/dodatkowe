<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Dodatkowe</title>
</head>
<body>
    <?php
    //stripping unecessary data, removing backslashes, changing html special haracters to text
    function test_input($data)
    {
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      return $data;
    }
    //form validation
    $loginErr = $passwordErr = $emailErr = $nickErr = "";
    $login = $password = $email = $nick = "";
    //check if form request method is post
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      //check whethever fields are empty
      if (empty($_POST["login"])) {
        $loginErr = "Login required";
      } else {
        $login = test_input($_POST["login"]);
        //check if login starst with an letter and contains letter, digits and underscores
        if (!preg_match("/^[a-z](?=.*\d)(?=.*[a-z])(?=.*[_])/i", $login)) {
          $loginErr =
            "Login should start with a letter, and should contain letters, numbers and an underscore";
        }
      }
      if (empty($_POST["password"])) {
        $passwordErr = "Password required";
      } else {
        //password strencght validation
        $password = test_input($_POST["password"]);
        $uppercase = preg_match("@[A-Z]@", $password);
        $lowercase = preg_match("@[a-z]@", $password);
        $number = preg_match("@[0-9]@", $password);
        if (!$uppercase || !$lowercase || !$number || strlen($password) <= 6) {
          $passwordErr =
            "Password should be at least 7 characters long and should have at least one uppercase, one lowercase letter and one number";
        }
      }
      if (empty($_POST["email"])) {
        $emailErr = "Email required";
      } else {
        $email = test_input($_POST["email"]);
        //check format of an e-mail
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $emailErr = "Invalid email";
        }
      }
      if (empty($_POST["nick"])) {
        $nickErr = "Nick required";
      } else {
        $nick = test_input($_POST["nick"]);
        //check if nick starst with an letter
        if (!preg_match("/^[a-z]/i", $nick)) {
          $nickErr = "Nick should start with letter";
        }
      }
    }
    ?>
    <form action="<?php echo htmlspecialchars(
      $_SERVER["PHP_SELF"]
    ); ?>" method="post">
    Login: <input type="text" name="login" id="login" value="<?php echo $login; ?>"><span class="error">*<?php echo $loginErr; ?></span><br><br>
    Hasło: <input type="password" name="password" id="pwd"><span class="error">*<?php echo $passwordErr; ?></span><br><br>
    Email: <input type="text" name="email" id="email" value="<?php echo $email; ?>"><span class="error">*<?php echo $emailErr; ?></span><br><br>
    Nick: <input type="text" name="nick" id="nick" value="<?php echo $nick; ?>"><span class="error">*<?php echo $nickErr; ?></span><br><br>
    <input type="submit" value="Zaloguj/Zarejestruj">
    </form>
    
</body>
</html>