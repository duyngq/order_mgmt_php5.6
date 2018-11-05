<?php
error_reporting(E_ALL ^ E_DEPRECATED);
session_start();
include_once 'dbconn.php';

function deleteOrder() {
    ob_start();

    $orderId = $_POST['orderId'];
    validateNumber($orderId, "Wrong input");

	$resultOD = deleteOrderWith("DELETE FROM orderdetails WHERE order_id = $orderId;");
	$resultComment = deleteOrderWith("DELETE FROM comments WHERE order_id = $orderId;");
	$resultO = deleteOrderWith("DELETE FROM orders WHERE id = $orderId;");

	if (isset ( $resultOD ) && isset ( $resultO ) && isset ( $resultComment ) && $resultO && $resultOD && $resultComment) {
		echo "YES";
	} else {
		echo "NO";
	}

    mysql_close();
    ob_end_flush();
}

function deleteOrderWith($query) {
	return mysql_query($query)  or die ( mysql_error () . "Can not retrieve database" );
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
deleteOrder();
?>