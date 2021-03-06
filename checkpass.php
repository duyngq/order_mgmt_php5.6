<?php
error_reporting(E_ALL ^ E_DEPRECATED);
session_start();
include_once 'dbconn.php';

// need to declare global var here; if not, inside function, mysqli $connection will be reset; not sure why
// refer link https://bobobobo.wordpress.com/2008/09/13/why-dont-my-php-globals-work-inside-of-classes/
global $connection;
function check_pass() {
    global $connection;
    ob_start();
    // Define $myusername and $mypassword
    $username = $_POST['username'];
    $password = $_POST['password'];

    // To protect MySQL injection (more detail about MySQL injection)
    $username = stripslashes($username);
    $password = stripslashes($password);
    $username = mysqli_real_escape_string($connection, $username);
    // $inputPassword = password_hash(mysql_real_escape_string($password), PASSWORD_DEFAULT);
    $password = mysqli_real_escape_string($connection, $password); // validate with plain text input user

    $sql = "SELECT * FROM users WHERE username='$username'";// and password='$password'";
    // $result = mysql_query($sql);
    $result= mysqli_query($connection,$sql) or die(mysqli_error());
    
    // Mysql_num_row is counting table row
	if($result) {
        $count=$result->num_rows;
		// $count = mysql_num_rows($result);
	} else {
		loginFailed();
	}

    session_regenerate_id();
    if ($count == 1) {
        //move to the page user wants later
        //get ID to update last date access
        $id;
        while ($user=mysqli_fetch_array($result,MYSQLI_ASSOC)) {
        	$dbPW = $user['password'];
        	if (password_verify($password, $dbPW)) { // check hashed coded password with input one if match or not
	            $id = $user['id'];
	            $_SESSION['user_id'] = $id;
	            $_SESSION['date_last_entered'] = $user['date_last_entered'];
        	} else {
		        loginFailed();
        	}
        }
        // Register $myusername, $mypassword and redirect to file "login_success.php"
        $_SESSION["username"] = $username;
        $_SESSION["loggedIn"] = true;
        
        $roleQuery = "select id from roles where id=(SELECT ur.role_id FROM users u join userroles ur on u.id=ur.id where u.id=$id)";
        // $roleResult = mysql_query($roleQuery);
        $roleResult = mysqli_query($connection, $roleQuery);
        if ($roleResult && $roleResult->num_rows == 1) {//mysql_num_rows($roleResult) == 1) {
	        while ($role=mysqli_fetch_array($roleResult,MYSQLI_ASSOC)) {
	            $_SESSION['role_id'] = $role['id'];
	        }
        }
        //get current date
        date_default_timezone_set('Asia/Bangkok');
        $currentDate = date('d/m/Y H:i');
        
        //update to DB
        $updateDateEntered = "UPDATE users SET date_last_entered='$currentDate' WHERE id='$id'";
        $updateResult = mysqli_query($connection, $updateDateEntered);
        if (!$updateResult) {
            echo "Can not access the DB. Maybe the system has some problems, Please try again!!!";
        } else {
            // Ending a session in 30 minutes from the starting time.
            // $_SESSION['expire'] = time() + (30 * 60);
            header("location:index.php");
        }
    } else {
        loginFailed();
    }
    mysqli_close($connection);
    ob_end_flush();
}

function loginFailed() {
	$_SESSION["username"] = 'Guess';
	$_SESSION["loggedIn"] = false;
	header("location:login.php");
	exit;
}

// function hash_equals($str1, $str2){
// 	if(strlen($str1) != strlen($str2)) {
// 		return false;
// 	} else {
// 		$res = $str1 ^ $str2;
// 		$ret = 0;
// 		for($i = strlen($res) - 1; $i >= 0; $i--) {
// 			$ret |= ord($res[$i]);
// 		}
// 		return !$ret;
//     }
// }
check_pass();
?>
