<?php
error_reporting(E_ALL ^ E_DEPRECATED);
session_start ();
// If already logged in, refer to index page
//if (isset ( $_SESSION ['loggedIn'] ) &&  $_SESSION ['loggedIn']) {
//    header ( "location:index.php" );
//}
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
<body>
<table width='300' border='0' align='center' cellpadding='0' cellspacing='1' bgcolor='#CCCCCC'>
    <tr>
        <?php
        // check if user is logged or not 
        if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn']) {
            //if yes, show log out form
            ?>
        <form name='form1' method='post' action="logout.php">
            <td>
                <table width='100%' border='0' cellpadding='3' cellspacing='1' bgcolor='#FFFFFF'>
                    <tr>
                        <td align='center' colspan='3'><strong>You are logged in. </strong></td>
                    </tr>
                    <tr>
                        <td align='center'><input type='submit' name='Submit' value='Logout'></td>
                    </tr>
                </table>
                <?php
            } else {
                // if not logged show login form
                ?>
				<div class="container">
                <form name='form1' method='post' action="checkpass.php">
                    <td>
                        <table width='100%' border='0' cellpadding='3' cellspacing='1' bgcolor='#FFFFFF'>
                            <tr>
                                <td colspan='3'><h2 align="center">Login </h2></td>
                            </tr>
                            <tr>
                                <td width='78'><label><b>Username</b></label></td>
                                <td width='6'>:</td>
                                <td width='294'><input name='username' type='text' id='username' placeholder="Enter Username" required></td>
                            </tr>
                            <tr>
                                <td><label><b>Password</b></label></td>
                                <td>:</td>
                                <td><input name='password' type='password' id='password' placeholder="Enter Password" required></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td><button type='submit'>Login</td>
                            </tr>
                            <?php
                            if (isset($_SESSION['loggedIn']) && !$_SESSION['loggedIn']) {
                                echo '<font color="red"> Incorrect username/password. Please, try again.</font>';
                            }
                            ?>
                        </table>
                    </td>
                </form>
				</div>
                </tr>
                </table>
                <?php
            }
            ?>
</body>
</html>