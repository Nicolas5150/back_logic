<?php
  // Used to select all products from inventory.
  require('connection_info.php');

  // Option to send back as json
  attemptProductsAll();

  // Option to send back as php echoed.
  // $data = attemptProductsAll();
  // $productData = $data['productData'];
  // foreach ($productData as $individualProduct) {
  // echo "sku: {$individualProduct['sku']}<br />"
  //    . "name: {$individualProduct['name']}<br />"
  //    . "image: {$individualProduct['image']}<br />"
  //    . "flat_value: {$individualProduct['flat_value']}<br />"
  //    . "yard_value: {$individualProduct['yard_value']}<br />"
  //    . "stock: {$individualProduct['stock']}<br />"
  //    . "description: {$individualProduct['description']}<br />"
  //    . "category: {$individualProduct['category']}<br />"
  //    . "tag_one: {$individualProduct['tag_one']}<br />"
  //    . "tag_two: {$individualProduct['tag_two']}<br />"
  //    . "color: {$individualProduct['color']}<br />"
  //    . "rating: {$individualProduct['rating']}<br /><br />";
  //  }


  function attemptProductsAll () {
    $status = obtainProducts();
    if ($status['statusCode'] !== '200') {
      return (returnMessage($status['statusCode'], $status['statusMessage'], $status['productData']));
    }

    $status['statusCode'] = '200';
    $status['statusMessage'] ='Product data found';
    return (returnMessage($status['statusCode'], $status['statusMessage'], $status['productData']));
  }

  function obtainProducts () {
    $conn = connectToDatabase();

    // Obtain any data from the products database.
    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);
    $count = mysqli_num_rows($result);
    if ($count <= 0) {
      $status['statusCode'] = '406';
      $status['statusMessage'] = 'No product data can be found';
      $status['productData'] = null;
      return $status;
    }

    // Data was found.
    else {
      while ($row_user = mysqli_fetch_assoc($result)) {
          $productData[] = $row_user;
      }

      $status['statusCode'] = '200';
      $status['statusMessage'] = 'Product data found';
      $status['productData'] = $productData;
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
