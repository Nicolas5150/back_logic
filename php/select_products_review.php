<?php
  // Used to select all products from inventory.
  require('connection_info.php');

  // Option to send back as json
  attemptProductReview();

  // Option to send back as php echoed.
  // $data = attemptProductsAll();
  // $productData = $data['productData'];
  // foreach ($productData as $individualProduct) {
  // echo "sku: {$individualProduct['username']}<br />"
  //    . "rating: {$individualProduct['review']}<br /><br />";
  //  }


  function attemptProductReview () {
    $status = gatherSku();
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage'], $status['productData']));
    }
    else {
      // Pull out only the account information to be validated.
      $formFields = $status['$formFields'];
    }

    $status = obtainProductReview($formFields[0][2]);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage'], $status['productData']));
    }

    $status['statusCode'] = '200';
    $status['statusMessage'] ='Product data found';
    return (returnMessage($status['statusCode'], $status['statusMessage'], $status['productData']));
  }

  function gatherSku () {
    $reviewIssues = array();
    $status['statusCode'] = '200';
    $status['productData'] = null;
    $status['statusMessage'] = null;

    $formFields = array (
      array('sku', FILTER_SANITIZE_STRING)
    );

    for ($i = 0; $i < count($formFields); $i++ ) {
      // echo $_POST[$formFields[$i][0]];
      // echo '</br>';
      if (isset($_POST[$formFields[$i][0]]) && !empty($_POST[$formFields[$i][0]])) {
        array_push($formFields[$i], $_POST[$formFields[$i][0]]);
      }

      else {
        array_push($reviewIssues, $formFields[$i][0]);
        $status['statusMessage'] = "Error with form field(s)";
        $status['statusCode'] = '400';
      }
    }

    $status['productData'] = $reviewIssues;
    $status['$formFields'] = $formFields;
    return $status;
  }

  function obtainProductReview ($sku) {
    $conn = connectToDatabase();
    $status['productData'] = null;
    $status['statusCode'] = '200';
    $status['statusMessage'] = 'Product data found';

    $table = "review_".$sku;
    // Obtain any data from the products database.
    $sql = "SELECT * FROM $table";
    $result = $conn->query($sql);
    if($result === FALSE)
    {
      $status['statusCode'] = '406';
      $status['statusMessage'] = 'No product can be found';
      return $status;
    }
    $count = mysqli_num_rows($result);
    if ($count <= 0) {
      $status['statusCode'] = '406';
      $status['statusMessage'] = 'No reviews can be found';
      return $status;
    }

    // Data was found.
    else {
      while ($indReviewData = mysqli_fetch_assoc($result)) {
          $reviewData[] = $indReviewData;
      }

      $status['productData'] = $reviewData;
    }

    $conn->close();
    return $status;
  }

  // When finished or broken out of code, send back response to request.
  // https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
  function returnMessage ($statusCode, $statusMessage, $productData) {
    // var_dump($statusCode);
    // var_dump($statusMessage);

    $data = array(
    "status" => $statusCode,
    "statusMessage" => $statusMessage,
    'productData' => $productData
    );

    // Option to send back as json
    echo json_encode($data);

    // Option to send back as php echoed.
    //return $data;
  }
?>
