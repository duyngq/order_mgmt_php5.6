<?php

error_reporting(E_ALL ^ E_DEPRECATED);
session_start();
if (isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]) {
    session_regenerate_id();
    session_unset();
    header('Location:index.php');
    session_destroy();
}
session_destroy();
?>