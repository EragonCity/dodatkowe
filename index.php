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
    //error reporting
    error_reporting(E_ALL);

    function myExceptionHandler($e)
    {
      error_log($e);
      http_response_code(500);
      if (filter_var(ini_get("display_errors"), FILTER_VALIDATE_BOOLEAN)) {
        echo $e;
      } else {
        echo "<h1>500 Internal Server Error</h1>
                  An internal server error has been occurred.<br>
                  Please try again later.";
      }
    }

    set_exception_handler("myExceptionHandler");

    set_error_handler(function ($level, $message, $file = "", $line = 0) {
      throw new ErrorException($message, 0, $level, $file, $line);
    });

    register_shutdown_function(function () {
      $error = error_get_last();
      if ($error !== null) {
        $e = new ErrorException(
          $error["message"],
          0,
          $error["type"],
          $error["file"],
          $error["line"]
        );
        myExceptionHandler($e);
      }
    });
    //stripping unecessary data, removing backslashes, changing html special haracters to text
    function test_input($data)
    {
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      return $data;
    }
    //console logging
    function console_log($output, $with_script_tags = true)
    {
      $js_code = "console.log(" . json_encode($output, JSON_HEX_TAG) . ");";
      if ($with_script_tags) {
        $js_code = "<script>" . $js_code . "</script>";
      }
      echo $js_code;
    }
    //form validation
    $loginErr = $passwordErr = $emailErr = $nickErr = "";
    $login = $password = $email = $nick = "";
    //check if form request method is post
    if ($_SERVER["REQUEST_METHOD"] = "POST") {
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
        $emailErr = "";
      } else {
        $email = test_input($_POST["email"]);
        //check format of an e-mail
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $emailErr = "Invalid email";
        }
      }
      if (empty($_POST["nick"])) {
        $nickErr = "";
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
    Hasło: <input type="password" name="password" id="pwd" value="<?php echo $password; ?>"><span class="error">*<?php echo $passwordErr; ?></span><br><br>
    Email: <input type="text" name="email" id="email" value="<?php echo $email; ?>"><span class="error"><?php echo $emailErr; ?></span><br><br>
    Nick: <input type="text" name="nick" id="nick" value="<?php echo $nick; ?>"><span class="error"><?php echo $nickErr; ?></span><br><br>
    <input type="submit" name="loguj" value="Zaloguj"><br><span style="color:blue">
    <?php
    //database connection
    $link = new mysqli("localhost", "root", "");
    if (!$link) {
      die("Not connected : " . mysqli_error($link));
    }
    // make login the current db
    // check whenever the database exists
    $sql = "USE login";
    if ($link->connect_error) {
      die("Connection failed: " . $link->connect_error);
    }
    if ($link->query($sql) === true) {
      echo "Connected to database <br>";
    } else {
      //error
      echo "Error connecting to database:" . $link->error . "<br>";
      $sql = "CREATE DATABASE login";
      //create database if does not exist
      if ($link->query($sql) === true) {
        echo "Database created successfully<br>";
      } else {
        die("Error creating database: " . $link->error . "<br>");
      }
    }

    // create users table
    $sql = "CREATE TABLE users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL UNIQUE,
        pwd VARCHAR(32) NOT NULL,
        email VARCHAR(50) UNIQUE,
        nick VARCHAR(50)
        )";
    if ($link->query($sql) === true) {
      echo "Table created successfully<br>";
    } else {
      echo "Error creating table: " . $link->error . "<br>";
    }
    //check if user is already in database
    //!console_log($login);
    //!console_log($password);
    //!console_log(md5($password));
    $sql = "SELECT * FROM users WHERE username='$login';";
    //!console_log($sql);
    $result = $link->query($sql);
    //some debbuging stuff
    /*
    if ($result->num_rows > 0) {
      // output data of each row
      while ($row = $result->fetch_assoc()) {
        echo "<br> id: " .
          $row["id"] .
          " - Name: " .
          $row["username"] .
          " " .
          $row["pwd"] .
          "<br>";
      }
    } else {
      echo "0 results";
    }*/
    if ($result->num_rows > 0) {
      echo "User found <br>";
      $sql = "SELECT * FROM users WHERE username='$login' AND pwd=MD5('$password');";
      //!console_log($sql);
      $result = $link->query($sql);
      if ($result->num_rows > 0) {
        echo "User logged in";
      } else {
        echo "Password is not right<br>";
      }
    } else {
      echo "Account not found <br>";
      echo "Do you want to create a new account?  ";
      echo '<input type="submit" name="register" value="Rejestruj">';
      if (($_SERVER["REQUEST_METHOD"] = "POST") and isset($_POST["register"])) {
        if (empty($password) or empty($password)) {
          die("<br> Password and login should not be blank <br>");
        }
        $sql = "INSERT INTO users VALUES (NULL, '$login', MD5('$password'), '$email', '$nick')";
        if (!$link->query($sql)) {
          if ($link->error === "Duplicate entry '$login' for key 'username'") {
            echo "<br>User already exists in the database";
          } else {
            echo "<br>Error description: " . $link->error;
          }
        }
      }
    }
    $link->close();
    ?></span>
    </form>
</body>
</html>