<?php
error_reporting(E_ALL ^ E_DEPRECATED);
//if (!isset($_SESSION['expire'])) {
//    header ( "location:login.php" );
//}
//
//$now = time(); // Checking the time now when home page starts.
//if ($now > $_SESSION['expire']) {
//    session_destroy();
//    header ( "location:login.php" );
//}
//$_SESSION['expire'] = $now + (30 * 60);

session_start();

if (! isset ( $_SESSION ['loggedIn'] ) || (isset ( $_SESSION ['loggedIn'] ) && ! $_SESSION ['loggedIn'])) {
    header ( "location:login.php" );
}
include_once 'dbconn.php';

//function addNewUser() {
	ob_start();
    // Define $myusername and $mypassword
    $username = $_POST['username'];
    $password = $_POST['password'];
    $cpassword = $_POST['confirm-password'];
    if($password != $cpassword) {
        header("location:adduser.php");
    }

    // To protect MySQL injection (more detail about MySQL injection)
    $username = stripslashes($username);
    $password = stripslashes($password);
    $username = mysql_real_escape_string($username);
    $password = password_hash(mysql_real_escape_string($password), PASSWORD_DEFAULT);

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = mysql_query($sql);

    // Mysql_num_row is counting table row
    if($result) {
        $count = mysql_num_rows($result);
    } else {
        header("location:adduser.php");
    }

    session_regenerate_id();
    if ($count == 0) {
    	$addNewUserQuery = "insert into users(username, password, enabled) values('$username', '$password', 1)";
	    $addNewUserResult = mysql_query($addNewUserQuery, $connection) or die(mysql_error() . "Can not store User to database");
	    if ($addNewUserResult) {
//            echo "<script>alert('Add user succeed');</script>";
            echo "<script>location.href = 'adduser.php?st=0';</script>";
	    }else {
//	        echo "<script>alert('Add user failed');</script>";
	        header("location:adduser.php?st=1");
	    }
    } else {
    	//return to adduser page with error code as 2 - user existed
        header("location:adduser.php?st=2");
    }
    mysql_close($connection);
    ob_end_flush();
    unset($submit);
//
//
//addNewUser();
?>