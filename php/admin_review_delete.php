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
    $status = deleteReview($formFields[0][2], $formFields[1][2]);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    $status['statusCode'] = '200';
    $status['statusMessage'] = 'Review deleted successfully';
    return (returnMessage($status['statusCode'], $status['statusMessage']));
  }

  function gatherReviewInfo () {
    $reviewIssues = array();
    $status['statusCode'] = '200';

    $formFields = array (
      array('username', FILTER_SANITIZE_STRING),
      array('sku', FILTER_SANITIZE_STRING),
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

  // Delete users review.
  function deleteReview($username, $sku) {
    $conn = connectToDatabase();
    $table = 'review_'.$sku;

    $sql = "DELETE FROM $table WHERE username='$username'";
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

  // When finished or broken out of code, send back response to request.
  // https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
  function returnMessage ($statusCode, $statusMessage) {
    var_dump($statusCode);
    var_dump($statusMessage);
  }
?>
