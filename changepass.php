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

global $connection;
function changePass() {    
    ob_start();
    // Define $myusername and $mypassword
    $username = $_SESSION["username"];
    $oldpassword = $_POST['old-password'];
    $password = $_POST['password'];
    $cpassword = $_POST['confirm-password'];
    if($password != $cpassword) {
        header("location:edituser.php?st=0");
    }
    
    // To protect MySQL injection (more detail about MySQL injection)
    $username = stripslashes($username);
    $password = stripslashes($password);
    $username = mysqli_real_escape_string($username);
    $password = mysqli_real_escape_string($password);
    $cpassword = mysqli_real_escape_string($cpassword);
    $oldpassword = mysqli_real_escape_string($oldpassword);

	$sql = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($connection, $sql);

    // Mysql_num_row is counting table row
	if($result) {
		$count = $result->num_rows;
	} else {
        header("location:edituser.php?st=0");
	}

    session_regenerate_id();
    if ($count == 1) {
        //get current date
        date_default_timezone_set('Asia/Bangkok');
        $currentDate = date('d/m/Y H:i');
        
        while ($user=mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $dbpassword = $user['password'];
            echo $dbpassword;
            echo "\n\n\n\n\n";
            echo password_hash($oldpassword, PASSWORD_BCRYPT);
            if ( password_hash($oldpassword, PASSWORD_BCRYPT) != $dbpassword ) {
//                header("location:edituser.php?st=0");
echo "test";
            }
        }
        
		//regenerate password, Extended DES
		$hashed_password=password_hash($password, PASSWORD_BCRYPT);
		
        //update to DB
        $updatePassword = "UPDATE users SET date_last_entered='$currentDate', password='$hashed_password' WHERE username='$username'";
        $updateResult = mysqli_query($connection, $updatePassword);
        if (!$updateResult) {
            echo "Update unsuccessfully. Please try again!!!";
        } else {
            #   header("location:edituser.php?st=1");
        }
    } else {
        header("location:edituser.php?st=0");
    }
    mysqli_close($connection);
    ob_end_flush();
}

changePass();
?>