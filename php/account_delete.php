<?php
  require('connection_info.php');

  attemptDeletion();

  function attemptDeletion () {
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
    $status = usernameValidate($formFields[0][2]);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    // Delete user account from users table
    $status = deleteAccountFromUsersDatabase($formFields[0][2]);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    // Delete the related cart history.
    $status = deleteRelatedCart($formFields[0][2]);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    // Delete the related purchase history.
    $status = deleteRelatedHistory($formFields[0][2]);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    // Deletes all reviews from the user on products.
    $status = deleteReviewsOnProducts($formFields[0][2]);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    $status['statusCode'] = '200';
    $status['statusMessage'] ='All user account data deleted';
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
      array('username', FILTER_SANITIZE_STRING)
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

  function accountValidate($formFields) {
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
  function deleteAccountFromUsersDatabase($username) {
    $conn = connectToDatabase();

    $sql = "DELETE FROM users WHERE username='$username'";
    if ($conn->query($sql) === TRUE) {
      $status['statusCode'] = '200';
      $status['statusMessage'] = 'Review deleted successfully';
    }

    else {
      $status['statusCode'] = '400';
      $status['statusMessage'] = $conn->error;
    }

    $conn->close();
    return $status;
  }

  // Check that the username exists.
  function usernameValidate($username) {
    $conn = connectToDatabase();

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username='".$username."' ");
    $count = mysqli_num_rows($result);
    $conn->close();
      if ($count > 0) {
        $status['statusCode'] = '200';
        $status['statusMessage'] = 'Username found';
        return $status;
      }

      $status['statusCode'] = '406';
      $status['statusMessage'] = 'Username not found';
      return $status;
  }


    // Delete users cart.
    function deleteRelatedCart($username) {
      $conn = connectToDatabase();

      $sql = "DROP TABLE cart_".$username;
      if ($conn->query($sql) === TRUE) {
        $status['statusCode'] = '200';
        $status['statusMessage'] = 'Review deleted successfully';
      }

      else {
        $status['statusCode'] = '400';
        $status['statusMessage'] = $conn->error;
      }

      $conn->close();
      return $status;
    }

    // Delete history cart.
    function deleteRelatedHistory($username) {
      $conn = connectToDatabase();

      $sql = "DROP TABLE history_".$username;
      if ($conn->query($sql) === TRUE) {
        $status['statusCode'] = '200';
        $status['statusMessage'] = 'Review deleted successfully';
      }

      else {
        $status['statusCode'] = '400';
        $status['statusMessage'] = $conn->error;
      }

      $conn->close();
      return $status;
    }

    function deleteReviewsOnProducts($username) {
      $conn = connectToDatabase();
      $status['statusCode'] = '200';
      $status['statusMessage'] = 'No reviews deleted';

      $result = mysqli_query($conn, "SELECT sku FROM products");
      if ($result) {
        while ($arr = $result->fetch_assoc()) {
          $table = 'review_'.$arr['sku'];

          $sql = "DELETE FROM $table WHERE username='$username'";
          if ($conn->query($sql) === TRUE) {
            $status['statusCode'] = '200';
            $status['statusMessage'] = 'Review deleted successfully';
          }
        }
      }


      else {
        $status['statusCode'] = '400';
        $status['statusMessage'] = 'Error on slecting sku from products';
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
