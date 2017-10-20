<?php
  // Used to add products to the inventory.
  require('connection_info.php');

  attemptDelete();

  function attemptDelete () {

    if (!isset($_POST['sku']) || empty($_POST['sku'])) {
      $status['statusCode'] = '400';
      $status['statusMessage'] = 'sku not provided';
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    else {
      $conn = connectToDatabase();
      $sku = $_POST['sku'];
    }

    $status = skuExists($sku, $conn);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    $status = deleteProduct($sku, $conn);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    $status = deleteProductReview($sku, $conn);
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage']));
    }

    // All requirements pass, all product data is now deleted.
    $status['statusCode'] = '200';
    $status['statusMessage'] = 'All product data deleted sucessfully';
    return (returnMessage($status['statusCode'], $status['statusMessage']));
  }

  // Returns 200 if sku is in db.
  function skuExists ($sku, $conn) {
    $result = mysqli_query($conn, "SELECT * FROM products WHERE sku='".$sku."' ");
    $count = mysqli_num_rows($result);
      if ($count > 0) {
        $status['statusCode'] = '200';
        $status['statusMessage'] = 'Sku found';
        return $status;
      }

      $status['statusCode'] = '400';
      $status['statusMessage'] = 'Sku not found';
      return $status;
  }

  // Returns 200 if product is deleted.
  function deleteProduct ($sku, $conn) {
    $sql = "DELETE FROM products WHERE sku=$sku";
    if ($conn->query($sql) === TRUE) {
      $status['statusCode'] = '200';
      $status['statusMessage'] = 'Product deleted successfully';
    }

    else {
      $status['statusCode'] = '400';
      $status['statusMessage'] = $conn->error;
    }

    return $status;
  }

  // Returns 200 if product review table is deleted.
  function deleteProductReview ($sku, $conn) {
    $sql = "DROP TABLE review_".$sku;
    if ($conn->query($sql) === TRUE) {
      $status['statusCode'] = '200';
      $status['statusMessage'] = 'Product review deleted successfully';
    }

    else {
      $status['statusCode'] = '400';
      $status['statusMessage'] = $conn->error;
    }

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
