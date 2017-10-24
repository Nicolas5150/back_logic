<?php
  session_start();
  require('connection_info.php');

  attemptValidation();

  function attemptValidation () {
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

    // Check password against the oassword stored for the user.
    $status = accountPasswordMatch($formFields);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    // session for a signed in user
    $_SESSION["userActLevel"] == "user";
    $_SESSION['username'] = $formFields[0][2];

    // Case where a super user or admin has logged in
    if ($formFields[0][2] == "Super") {
      $_SESSION["userActLevel"] = "super";
      $_SESSION['username'] = $formFields[0][2];
    }
    else if ($formFields[0][2] == "Admin") {
      $_SESSION["userActLevel"] = "admin";
      $_SESSION['username'] = $formFields[0][2];
    }


    $status['statusCode'] = '200';
    $status['statusMessage'] ='Username and password match';
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
    );

    // Gather all data from form and push to the formFields array.
    for ($i = 0; $i < count($formFields); $i++ ) {
      if (isset($_POST[$formFields[$i][0]]) && !empty($_POST[$formFields[$i][0]])) {
        array_push($formFields[$i], $_POST[$formFields[$i][0]]);
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

  // With all data finally cleaned and passed of all tests, check against the database.
  function accountPasswordMatch ($formFields) {
    $conn = connectToDatabase();

    // Obtain the latest password hashed in that users row pathway for comparison.
    $sql = "SELECT password FROM users WHERE username='".$formFields[0][2]."' ";
    $result = $conn->query($sql);
    $row = mysqli_fetch_array($result);

    if (password_verify($formFields[1][2], $row['password'])) {
      $status['statusCode'] = '200';
      $status['statusMessage'] = "Passwords match";
    }

    else {
      $status['statusCode'] = '406';
      $status['statusMessage'] = 'Username or password does not match';
    }

    $conn->close();
    return $status;
  }

  // When finished or broken out of code, send back response to request.
  // https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
  function returnMessage ($statusCode, $statusMessage) {
    // var_dump($statusCode);
    // var_dump($statusMessage);

    $data = array(
    "status" => $statusCode,
    "statusMessage" => $statusMessage
    );

    echo json_encode($data);
  }

?>
