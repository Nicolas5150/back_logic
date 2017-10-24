window.onload = function () {
  // Register the user.
  $( "#form-account-register-btn" ).click(function() {
      $.ajax({
        type: 'POST',
        url: 'account_register.php',
        data: $('#form-account-register-data').serialize(),
        success: function (returnedData) {

          // Raw returned JSON
          console.log(returnedData);
          // How to get a specific value from the returned data object
          var data = jQuery.parseJSON(returnedData);
          console.log(data.status);

          // Code to send the user to a new page when need be.
          // similar behavior as an HTTP redirect
          // window.location.replace("http://stackoverflow.com");
        }
      });
  });

  $( "#form-account-validate-btn" ).click(function() {
      $.ajax({
        type: 'POST',
        url: 'account_validate.php',
        data: $('#form-account-validate-data').serialize(),
        success: function (returnedData) {

          // Raw returned JSON
          console.log(returnedData);
          // How to get a specific value from the returned data object
          var data = jQuery.parseJSON(returnedData);
          console.log(data.status);

          // Code to send the user to a new page when need be.
          // similar behavior as an HTTP redirect
          // window.location.replace("http://stackoverflow.com");
        }
      });
  });

  // Delete the user.
  $( "#form-account-delete-btn" ).click(function() {
      $.ajax({
        type: 'POST',
        url: 'account_delete.php',
        data: $('#form-account-delete-data').serialize(),
        success: function (returnedData) {

          // Raw returned JSON
          console.log(returnedData);
          // How to get a specific value from the returned data object
          var data = jQuery.parseJSON(returnedData);
          console.log(data.status);

          // Code to send the user to a new page when need be.
          // similar behavior as an HTTP redirect
          // window.location.replace("http://stackoverflow.com");
        }
      });
  });

  // Review product as a user.
  $( "#form-review-add-btn" ).click(function() {
     // var formElement = document.forms['form-review-add-data'].elements['username'].value;
     // console.log(formElement);
      $.ajax({
        type: 'POST',
        url: 'product_review.php',
        data: $('#form-review-add-data').serialize(),
        success: function (returnedData) {

          // Raw returned JSON
          console.log(returnedData);
          // How to get a specific value from the returned data object
          var data = jQuery.parseJSON(returnedData);
          console.log(data.status);

          // Code to send the user to a new page when need be.
          // similar behavior as an HTTP redirect
          // window.location.replace("http://stackoverflow.com");
        }
      });
  });

  // Edit review as an upper level acct.
  $( "#form-review-edit-btn" ).click(function() {
    var formElement = document.forms['form-review-edit-data'].elements['username'].value;
    console.log(formElement);
      $.ajax({
        type: 'POST',
        url: 'admin_review_edit.php',
        data: $('#form-review-edit-data').serialize(),
        success: function (returnedData) {

          // Raw returned JSON
          console.log(returnedData);
          // How to get a specific value from the returned data object
          var data = jQuery.parseJSON(returnedData);
          console.log(data.status);

          // Code to send the user to a new page when need be.
          // similar behavior as an HTTP redirect
          // window.location.replace("http://stackoverflow.com");
        }
      });
  });

  // Delete review as an upper level acct.
  $( "#form-review-delete-btn" ).click(function() {
    var formElement = document.forms['form-review-delete-data'].elements['username'].value;
    console.log(formElement);
      $.ajax({
        type: 'POST',
        url: 'admin_review_delete.php',
        data: $('#form-review-delete-data').serialize(),
        success: function (returnedData) {

          // Raw returned JSON
          console.log(returnedData);
          // How to get a specific value from the returned data object
          var data = jQuery.parseJSON(returnedData);
          console.log(data.status);

          // Code to send the user to a new page when need be.
          // similar behavior as an HTTP redirect
          // window.location.replace("http://stackoverflow.com");
        }
      });
  });

  // Add product product as an upper level account.
  // http://codejaxy.com/q/1064709/javascript-jquery-ajax-forms-how-to-upload-image-and-text-using-ajax-and-php
  $("form#form-product-add-data").submit(function(e){
    e.preventDefault();
     var formData = new FormData(this);
      $.ajax({
        type: 'POST',
        url: 'admin_products_add.php',
        processData: false,
        contentType: false,
        data: formData,
        success: function (returnedData) {

          // Raw returned JSON
          console.log(returnedData);
          // How to get a specific value from the returned data object
          var data = jQuery.parseJSON(returnedData);
          console.log(data.status);

          // Code to send the user to a new page when need be.
          // similar behavior as an HTTP redirect
          // window.location.replace("http://stackoverflow.com");
        }
      });
  });


    // Add product product as an upper level account.
    // http://codejaxy.com/q/1064709/javascript-jquery-ajax-forms-how-to-upload-image-and-text-using-ajax-and-php
    $("form#form-product-edit-data").submit(function(e){
      e.preventDefault();
       var formData = new FormData(this);
        $.ajax({
          type: 'POST',
          url: 'admin_products_edit.php',
          processData: false,
          contentType: false,
          data: formData,
          success: function (returnedData) {

            // Raw returned JSON
            console.log(returnedData);
            // How to get a specific value from the returned data object
            var data = jQuery.parseJSON(returnedData);
            console.log(data.status);

            // Code to send the user to a new page when need be.
            // similar behavior as an HTTP redirect
            // window.location.replace("http://stackoverflow.com");
          }
        });
    });

  // Delete product as an upper level acct.
  $( "#form-product-delete-btn" ).click(function() {
      $.ajax({
        type: 'POST',
        url: 'admin_products_delete.php',
        data: $('#form-product-delete-data').serialize(),
        success: function (returnedData) {

          // Raw returned JSON
          console.log(returnedData);
          // How to get a specific value from the returned data object
          var data = jQuery.parseJSON(returnedData);
          console.log(data.status);

          // Code to send the user to a new page when need be.
          // similar behavior as an HTTP redirect
          // window.location.replace("http://stackoverflow.com");
        }
      });
  });

  // Select all products from the database.
  $( "#select-products-all-btn" ).click(function() {
      $.ajax({
        type: 'POST',
        url: 'select_products_all.php',
        success: function (returnedData) {

          // Raw returned JSON
          console.log(returnedData);
          // How to get a specific value from the returned data object
          var data = jQuery.parseJSON(returnedData);
          console.log(data.status);

          // Code to send the user to a new page when need be.
          // similar behavior as an HTTP redirect
          // window.location.replace("http://stackoverflow.com");
        }
      });
  });

  // Select all reviews for said product from the database.
  $( "#from-products-review-btn" ).click(function() {
      $.ajax({
        type: 'POST',
        data: $('#form-products-review-data').serialize(),
        url: 'select_products_review.php',
        success: function (returnedData) {

          // Raw returned JSON
          console.log(returnedData);
          // How to get a specific value from the returned data object
          var data = jQuery.parseJSON(returnedData);
          console.log(data.status);

          // Code to send the user to a new page when need be.
          // similar behavior as an HTTP redirect
          // window.location.replace("http://stackoverflow.com");
        }
      });
  });
}
