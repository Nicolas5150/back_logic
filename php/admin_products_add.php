<?php
  // Used to add products to the inventory.
  require('connection_info.php');

  attemptAdd();

  function attemptAdd () {
    $status = gatherProductInfo();
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }
    else {
      // Pull out only the account information to be validated.
      $formFields = $status['$formFields'];
    }

    // Validate data from form.
    $status = productValidate($formFields);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    // Send valid data to the products table
    $status = productInfoToProductsDatabase($formFields);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    // Cereate relational table based on the product just added.
    // Send valid data to the products table
    $status = createRelatedReview($formFields[0][2]);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    $status['statusCode'] = '200';
    $status['statusMessage'] ='All accounts created';
    return (returnMessage($status['statusCode'], $status['statusMessage']));
  }


  // Create array that stores all the data from the form.
  function gatherProductInfo () {
    $accountIssues = array();
    $status['statusCode'] = '200';

    // Array that conains an array within each postion; structure is as followed:
    // 0 the column to be instered into in the db
    // 1 contains the filter to be applied to check the value against.
    // 2 gets pushed value from form - if passes sanitization, gets pushed to db.
    // https://www.w3schools.com/php/php_arrays_multi.asp
    $formFields = array (
      array('sku', FILTER_SANITIZE_STRING),
      array('name', FILTER_SANITIZE_STRING),
      array('flat_value', FILTER_SANITIZE_STRING),
      array('yard_value', FILTER_SANITIZE_STRING),
      array('stock', FILTER_SANITIZE_STRING),
      array('description', FILTER_SANITIZE_STRING),
      array('category', FILTER_SANITIZE_STRING),
      array('tag_one', FILTER_SANITIZE_STRING),
      array('tag_two', FILTER_SANITIZE_STRING),
      array('color', FILTER_SANITIZE_STRING),
      array('rating', FILTER_SANITIZE_STRING),
    );

    // Gather all data from form and push to the formFields array.
    for ($i = 0; $i < count($formFields); $i++ ) {
      // echo $_POST[$formFields[$i][0]];
      // echo '</br>';
      if (isset($_POST[$formFields[$i][0]]) && !empty($_POST[$formFields[$i][0]])) {
        array_push($formFields[$i], $_POST[$formFields[$i][0]]);
      }
      // Either flat value or yard value needs to be filled in
      else if ($i == 2 && (!isset($_POST[$formFields[2][0]]) || empty($_POST[$formFields[2][0]]))) {
        array_push($formFields[$i], 'na');
      }
      else if ($i == 3 && (!isset($_POST[$formFields[3][0]]) || empty($_POST[$formFields[3][0]]))) {
        array_push($formFields[$i], 'na');
      }
      else {
        array_push($accountIssues, $formFields[$i][0]);
        $status['statusCode'] = '400';
      }
    }

    // Only one value should be filled in not both.
    if (isset($_POST[$formFields[2][0]]) && !empty($_POST[$formFields[2][0]]) &&
      isset($_POST[$formFields[3][0]]) && !empty($_POST[$formFields[3][0]])) {
      array_push($accountIssues, 'Both yard and flat values set');
      $status['statusCode'] = '400';
    }

    else if (isset($_POST[$formFields[2][0]]) && empty($_POST[$formFields[2][0]]) &&
      isset($_POST[$formFields[3][0]]) && empty($_POST[$formFields[3][0]])) {
      array_push($accountIssues, 'Neither yard or flat values set');
      $status['statusCode'] = '400';
    }

    if ($_FILES["myimage"]["name"] == '') {
      array_push($accountIssues, 'File is not included');
      $status['statusCode'] = '400';
    }

    $status['statusMessage'] = $accountIssues;
    $status['$formFields'] = $formFields;
    return $status;
  }

  function productValidate ($formFields) {
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
  function productInfoToProductsDatabase ($formFields) {
    $conn = connectToDatabase();
    // Check for a duplicate in the database prior to adding.
    if (!productDuplicate($formFields[0][2], $conn)) {

      // Needed for tracking and moving picture to the database.
      // http://talkerscode.com/webtricks/upload%20image%20to%20database%20and%20server%20using%20HTML,PHP%20and%20MySQL.php
      $upload_image = $_FILES["myimage"]["name"];
      $folder = "/Applications/XAMPP/xamppfiles/htdocs/back_logic/img/";
      $imagePath = $folder.$upload_image;

      $sql = "INSERT INTO products (sku,
        name,
        image,
        flat_value,
        yard_value,
        stock,
        description,
        category,
        tag_one,
        tag_two,
        color,
        rating)
        VALUES ('".$formFields[0][2]."',
          '".$formFields[1][2]."',
          '".$imagePath."',
          '".$formFields[2][2]."',
          '".$formFields[3][2]."',
          '".$formFields[4][2]."',
          '".$formFields[5][2]."',
          '".$formFields[6][2]."',
          '".$formFields[7][2]."',
          '".$formFields[8][2]."',
          '".$formFields[9][2]."',
          '".$formFields[10][2]."')";

      // If query is valid return 200 and move the image to the img directory
      if ($conn->query($sql) === TRUE) {
        move_uploaded_file($_FILES["myimage"]["tmp_name"], "$folder".$_FILES["myimage"]["name"]);
        $status['statusCode'] = '200';
        $status['statusMessage'] = "New product created successfully";
      }
      else {
        $status['statusCode'] = '400';
        $status['statusMessage'] = $conn->error;
      }
      $conn->close();
    }

    else {
      $status['statusCode'] = '406';
      $status['statusMessage'] = 'product found already';
    }

    return $status;
  }

  // Returns true if a sku with the same value has been found already.
  function productDuplicate ($skuDuplicate, $conn) {
    $result = mysqli_query($conn, "SELECT * FROM products WHERE sku='".$skuDuplicate."' ");
    $count = mysqli_num_rows($result);
      if ($count > 0) {
        return true;
      }
      return false;
  }

  // Upon creating new user and cart, create a history for each individual user.
  function createRelatedReview ($sku) {
    $conn = connectToDatabase();

    // sql to create table
    $tableName = 'review_'.$sku;
    $sql = "CREATE TABLE $tableName (
      username VARCHAR(120) PRIMARY KEY,
      review VARCHAR(1000) NOT NULL
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
    // var_dump($statusCode);
    // var_dump($statusMessage);

    $data = array(
    "status" => $statusCode,
    "statusMessage" => $statusMessage
    );

    echo json_encode($data);
  }
?>
