<?php
  // Used to add products to the inventory.
  require('connection_info.php');

  attemptComment();

  function attemptComment () {

    $status = gatherReviewInfo();
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }
    else {
      // Pull out only the account information to be validated.
      $formFields = $status['$formFields'];
    }

    // Validate data from form.
    $status = reviewValidate($formFields);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    // Validate username is in database.
    $status = usernameValidate($formFields[0][2]);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    // Send valid review to the corresponding product review table
    $status = submitToProductReviewDatabase($formFields);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }
    // Send valid review to the corresponding product review table
    $status = submitRatingToProductsDatabase($formFields);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    $status['statusCode'] = '200';
    $status['statusMessage'] = 'Review edited successfully';
    return (returnMessage($status['statusCode'], $status['statusMessage']));
  }

  function gatherReviewInfo () {
    $reviewIssues = array();
    $status['statusCode'] = '200';

    $formFields = array (
      array('username', FILTER_SANITIZE_STRING),
      array('sku', FILTER_SANITIZE_STRING),
      array('review', FILTER_SANITIZE_STRING),
      array('rating', FILTER_SANITIZE_STRING)
    );

    for ($i = 0; $i < count($formFields); $i++ ) {
      echo $_POST[$formFields[$i][0]];
      echo '</br>';
      if (isset($_POST[$formFields[$i][0]]) && !empty($_POST[$formFields[$i][0]])) {
        array_push($formFields[$i], $_POST[$formFields[$i][0]]);
      }

      else {
        array_push($reviewIssues, $formFields[$i][0]);
        $status['statusCode'] = '400';
      }
    }

    $status['statusMessage'] = $reviewIssues;
    $status['$formFields'] = $formFields;
    return $status;
  }

  function reviewValidate ($formFields) {
    // Validating all review information from the array.
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

  // Once all information is validated, then enter into the table for review.
  function submitToProductReviewDatabase($formFields) {
    $conn = connectToDatabase();
    $table = 'review_'.$formFields[1][2];

    $sql = "UPDATE $table SET
      username='{$formFields[0][2]}',
      review='{$formFields[2][2]}'
      WHERE username= '{$formFields[0][2]}'";

    // If query is valid return 200 update has been successful.
    if ($conn->query($sql) === TRUE) {
      $status['statusCode'] = '200';
      $status['statusMessage'] = "Review edited successfully";
    }
    else {
      $status['statusCode'] = '400';
      $status['statusMessage'] = $conn->error;
    }

    $conn->close();
    return $status;
  }

  function submitRatingToProductsDatabase ($formFields) {
    $conn = connectToDatabase();
    $sku = $formFields[1][2];
    $rating = $formFields[3][2];

    $sql = "SELECT rating FROM products WHERE sku='".$sku."' ";
    $result = $conn->query($sql);
    $row = mysqli_fetch_array($result);

    if ($row['rating'] == 'na') {
      $sql = "UPDATE products SET rating='$rating' WHERE sku='$sku'";
    }
    else {
      $newRating = (intval($row['rating']) + $rating) / 2;
      $sql = "UPDATE products SET rating='$newRating' WHERE sku='$sku'";
    }

    if ($conn->query($sql) === TRUE) {
      $status['statusCode'] = '200';
      $status['statusMessage'] = "New review created successfully";
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
