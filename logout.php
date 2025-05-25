<?php
session_start();
setcookie("remember_token", "", time() - 3600, "/");
setcookie("remember_email", "", time() - 3600, "/");
setcookie("remember_pass", "", time() - 3600, "/");
session_unset();
session_destroy();
header("Location: login.php");
exit();
