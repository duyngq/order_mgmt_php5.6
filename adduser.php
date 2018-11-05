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
?>
<html>
<style>
form {
    border: 3px solid #f1f1f1;
}

button {
    background-color: #4CAF50;
    color: white;
    padding: 14px 20px;
    margin: 8px 0;
    border: none;
    cursor: pointer;
}
.container {
    padding: 16px;
}
.cancelbtn {
    width: auto;
    padding: 10px 18px;
    background-color: #f44336;
}

/* Change styles for span and cancel button on extra small screens */
@media screen and (max-width: 300px) {
    span.psw {
       display: block;
       float: none;
    }
    .cancelbtn {
       width: 100%;
    }
}
</style>
<script>
function validatePassword() {
    var pass1 = document.getElementById("password").value;
    var pass2 = document.getElementById("confirm-password").value;
    var ok = true;
    if (pass1 != pass2) {
        //alert("Passwords Do not match");
        document.getElementById("password").style.borderColor = "#E34234";
        document.getElementById("confirm-password").style.borderColor = "#E34234";
        document.getElementById("passString").innerHTML ="Password not match!!!";
        document.getElementById("passString").style.color="red";
        ok = false;
    }
    return ok;
}
</script>
<body>
<table width='300' border='0' align='center' cellpadding='0' cellspacing='1' bgcolor='#CCCCCC'>
    <tr>
        <?php
        // check if user is logged or not 
        if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn']) {
            // if logged, show change pass form
            ?>
            <div class="container">
                <form name='form1' method='post' action="adduser_.php" onsubmit="return validatePassword();">
                    <td>
                        <table width='100%' border='0' cellpadding='3' cellspacing='1' bgcolor='#FFFFFF'>
                            <tr>
                                <td colspan='3'><h2 align="center">Add new user</h2></td>
                            </tr>
                            <tr>
                                <td colspan='3'><label id="passString"></label></td>
                            </tr>
                            <tr>
                                <td colspan='3'><?php
                                if ( isset($_GET['st'])) {
                                    if ($_GET['st'] == 0 ){
                                        echo "<label style='color:green'>Add successully</label>";
                                    } else if ($_GET['st'] == 1 ){
                                       echo "<label style='color:red'>Check username/password</label>";
                                    } else if ($_GET['st'] == 2 ){
                                       echo "<label style='color:red'>User existed</label>";
                                    }
                                }
                                ?></td>
                            </tr>
                            <tr>
                                <td width='78'><label><b>Username</b></label></td>
                                <td width='6'>:</td>
                                <td width='294'><input name='username' type='text' id='username' placeholder="Enter new name" required></td>
                            </tr>
                            <tr>
                                <td><label><b>Password</b></label></td>
                                <td>:</td>
                                <td><input name='password' type='password' id='password' placeholder="Enter Password" required></td>
                            </tr>
                            <tr>
                                <td><label><b>Confirm Password</b></label></td>
                                <td>:</td>
                                <td><input name='confirm-password' type='password' id='confirm-password' placeholder="Re-enter Password" required></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td><button type='submit'>Add user</button></td>
                            </tr>
                        </table>
                    </td>
                </form>
            </div>
        <?php
            } else {
                header("location:login.php");
            }
        ?>
    </tr>
</table>
</body>
</html>