<?php
error_reporting(E_ALL ^ E_DEPRECATED);
session_start();
include_once 'dbconn.php';
global $connection;

function deleteOrder() {
    global $connection;

    ob_start();

    $orderId = $_POST['orderId'];
    validateNumber($orderId, "Wrong input");

    $getOrdersQuery = "SELECT file_name FROM orders WHERE id = $orderId";
    $ordersResult = mysqli_query($connection, $getOrdersQuery) or die(mysqli_error() . "Can not retrieve information from database");
    if ($ordersResult->num_rows < 1) {
        header("location:index.php");
        exit;
    }
    $custId;
    $recvId;
    while ($order = mysqli_fetch_array($ordersResult)) {
        $fileNames = explode(",", $order['file_name'] );
        foreach ($fileNames as $fileName) {
            $path =  dirname(__FILE__) . DIRECTORY_SEPARATOR .'uploads'.DIRECTORY_SEPARATOR .$_SESSION['user_id'].DIRECTORY_SEPARATOR .$fileName;
            // check that file exists and is readable
            if (file_exists($path) && is_readable($path)) {
                if (!unlink($path)) {
                    echo "NO";
                    return;
                }
            }
        }
    }
    $resultOD = deleteOrderWith("DELETE FROM orderdetails WHERE order_id = $orderId;");
    $resultComment = deleteOrderWith("DELETE FROM comments WHERE order_id = $orderId;");
    $resultO = deleteOrderWith("DELETE FROM orders WHERE id = $orderId;");

    if (isset ( $resultOD ) && isset ( $resultO ) && isset ( $resultComment ) && $resultO && $resultOD && $resultComment) {
        echo "YES";
    } else {
        echo "NO";
    }

    mysqli_close($connection);
    ob_end_flush();
}

function deleteOrderWith($query) {
    global $connection;
    return mysqli_query($connection, $query)  or die ( mysqli_error () . "Can not retrieve database" );
}

function validateNumber($validatedValue, $stringName) {
    if (! is_numeric ( $validatedValue )) {
        echo "<script>alert('$stringName should be a number!!!!')</script>";
        exit ();
    } else if ($validatedValue < 0) {
        echo "<script>alert('$stringName should be greater or equal 0!!!!')</script>";
        exit ();
    }
}
function isEmptyValue($value) {
    return is_null($value) || $value == null || $value == '';
}
deleteOrder();
?>