<?php
  require('connection_info.php');

  attemptRegister();

  function attemptRegister () {
    // If any function fails, return out and send current error code back to front.
    // Create an a multi dimension array to store all the form data of the user.
    $status = gatherAccountInfo();
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }
    else {
      // Pull out only the account information to be validated.
      $formFields = $status['$formFields'];
    }

    // Validate data from form.
    $status = accountValidate($formFields);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    // Send valid data to the users table
    $status = accountToUsersDatabase($formFields);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    // Create relational table that is tied the users username _cart.
    $status = createRelatedCart($formFields[0][2]);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    // Create relational table that is tied the users username _history.
    $status = createRelatedHistory($formFields[0][2]);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    $status['statusCode'] = '200';
    $status['statusMessage'] ='All accounts created';
    return (returnMessage($status['statusCode'], $status['statusMessage']));
  }

  // Create array that stores all the data from the form.
  function gatherAccountInfo () {
    $accountIssues = array();
    $status['statusCode'] = '200';

    // Array that conains an array within each postion; structure is as followed:
    // 0 the column to be instered into in the db
    // 1 contains the filter to be applied to check the value against.
    // 2 gets pushed value from form - if passes sanitization, gets pushed to db.
    // https://www.w3schools.com/php/php_arrays_multi.asp
    $formFields = array (
      array('username', FILTER_SANITIZE_STRING),
      array('password', FILTER_SANITIZE_STRING),
      array('firstname', FILTER_SANITIZE_STRING),
      array('lastname', FILTER_SANITIZE_STRING),
      array('phone', FILTER_SANITIZE_STRING),
      array('email', FILTER_SANITIZE_EMAIL),
      array('street', FILTER_SANITIZE_STRING),
      array('unit', FILTER_SANITIZE_STRING),
      array('zipcode', FILTER_SANITIZE_NUMBER_INT),
      array('state', FILTER_SANITIZE_STRING),
    );

    // Gather all data from form and push to the formFields array.
    for ($i = 0; $i < count($formFields); $i++ ) {
      if (isset($_POST[$formFields[$i][0]]) && !empty($_POST[$formFields[$i][0]])) {
        array_push($formFields[$i], $_POST[$formFields[$i][0]]);
      }
      // Unit does not need to be a required field.
      else if ($i == 7 && (!isset($_POST[$formFields[7][0]]) || empty($_POST[$formFields[7][0]]))) {
        array_push($formFields[$i], 'na');
      }
      else {
        array_push($accountIssues, $formFields[$i][0]);
        $status['statusCode'] = '400';
      }
    }

    $status['statusMessage'] = $accountIssues;
    $status['$formFields'] = $formFields;
    return $status;
  }

  function accountValidate ($formFields) {
    // Validating all registration information from the array.
    $accountIssues = array();
    $status['statusCode'] = '200';
    for ($i = 0; $i < count($formFields); $i++) {
      if (!sanatizeString($formFields[$i][2], $formFields[$i][1])) {
        $status['statusCode'] = '406';
        array_push($accountIssues, $formFields[$i][0]);
      }
    }

    $status['statusMessage'] = $accountIssues;
    return $status;
  }

  // Function to validate all data for the account to register.
  // http://php.net/manual/en/filter.filters.sanitize.php
  function sanatizeString($stringToCheck, $sanitizationFilter) {
    $stringChecked = filter_var($stringToCheck, $sanitizationFilter);

    // If the strings are same length, no values were removed.
    if (strlen($stringToCheck) ===  strlen($stringChecked)) {
      return true;
    }
    return false;
  }

  // With all data finally cleaned and passed of all tests, add to the database.
  function accountToUsersDatabase ($formFields) {
    $conn = connectToDatabase();
    // Check for a duplicate in the database prior to adding.
    if (!accountDuplicate($formFields[0][2], $conn)) {
      $hashed_password = password_hash($formFields[1][2], PASSWORD_DEFAULT);
      // if(password_verify($password, $hashed_password)) { let user in }
      //var_dump($hashed_password);

      $sql = "INSERT INTO users (username,
        password,
        firstname,
        lastname,
        phone,
        email,
        street,
        unit,
        zipcode,
        state)
        VALUES ('".$formFields[0][2]."',
          '".$hashed_password."',
          '".$formFields[2][2]."',
          '".$formFields[3][2]."',
          '".$formFields[4][2]."',
          '".$formFields[5][2]."',
          '".$formFields[6][2]."',
          '".$formFields[7][2]."',
          '".$formFields[8][2]."',
          '".$formFields[9][2]."')";

      if ($conn->query($sql) === TRUE) {
        $status['statusCode'] = '200';
        $status['statusMessage'] = "New record created successfully";
      }
      else {
        $status['statusCode'] = '400';
        $status['statusMessage'] = $conn->error;
      }
      $conn->close();
    }

    else {
      $status['statusCode'] = '406';
      $status['statusMessage'] = 'account found already';
    }

    return $status;
  }

  // Returns true if an account with the same username has been found already.
  function accountDuplicate ($usernameDuplicate, $conn) {
    $result = mysqli_query($conn, "SELECT * FROM users WHERE username='".$usernameDuplicate."' ");
    $count = mysqli_num_rows($result);
      if ($count > 0) {
        return true;
      }
      return false;
  }

  // Upon creating new user, create a cart for each individual user.
  function createRelatedCart($username) {
    $conn = connectToDatabase();

    // sql to create table
    $tableName = 'cart_'.$username;
    $sql = "CREATE TABLE $tableName (
      sku VARCHAR(120) PRIMARY KEY,
      yard_amount VARCHAR(120),
      yard_value VARCHAR(120),
      flat_value VARCHAR(120)
    )";

    if ($conn->query($sql) === TRUE) {
      $status['statusCode'] = '200';
      $status['statusMessage'] = "Cart created successfully";
    }
    else {
      $status['statusCode'] = '400';
      $status['statusMessage'] = $conn->error;
    }

    $conn->close();
    return $status;
  }

  // Upon creating new user and cart, create a history for each individual user.
  function createRelatedHistory($username) {
    $conn = connectToDatabase();

    // sql to create table
    $tableName = 'history_'.$username;
    $sql = "CREATE TABLE $tableName (
      order_number VARCHAR(120) PRIMARY KEY,
      sku VARCHAR(120) NOT NULL,
      yard_amount VARCHAR(120),
      yard_value VARCHAR(120),
      flat_value VARCHAR(120)
    )";

    if ($conn->query($sql) === TRUE) {
      $status['statusCode'] = '200';
      $status['statusMessage'] = "History created successfully";
    }
    else {
      $status['statusCode'] = '400';
      $status['statusMessage'] = $conn->error;
    }

    $conn->close();
    return $status;
  }

  // When finished or broken out of code, send back response to request.
  // https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
  function returnMessage ($statusCode, $statusMessage) {
    var_dump($statusCode);
    var_dump($statusMessage);
  }

?>
