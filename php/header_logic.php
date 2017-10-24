<?php
  session_start();
  // The account is not logged in so therefore set the userActLevel to guest
  if (!isset($_SESSION['userActLevel']) || empty($_SESSION['userActLevel'])) {
    $_SESSION['userActLevel'] = "guest";
  }

  // Components for a signed in user
  else if ($_SESSION["userActLevel"] == "user") {

  }

  // Components for a signed in user
  else if ($_SESSION["userActLevel"] == "super") {

  }

  // Components for a signed in user
  else if ($_SESSION["userActLevel"] == "admin") {

  }

  var_dump($_SESSION);
?>
