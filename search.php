<?php
error_reporting ( E_ALL ^ E_DEPRECATED );
//if (!isset($_SESSION['expire'])) {
//  header ( "location:login.php" );
//}
//
//$now = time(); // Checking the time now when home page starts.
//if ($now > $_SESSION['expire']) {
//  session_destroy();
//  header ( "location:login.php" );
//}
//$_SESSION['expire'] = $now + (30 * 60);
session_start ();
if (! isset ( $_SESSION ['loggedIn'] ) || (isset ( $_SESSION ['loggedIn'] ) && ! $_SESSION ['loggedIn'])) {
    header ( "location:login.php" );
}

include_once 'dbconn.php';
global $connection;


function validateNumber($validatedValue, $stringName) {
    if (! is_numeric ( $validatedValue )) {
        echo "<script>alert('$stringName should be a number!!!!')</script>";
        exit ();
    } else if ($validatedValue < 0) {
        echo "<script>alert('$stringName should be greater or equal 0!!!!')</script>";
        exit ();
    }
}

// Load and save all customers to be able to selectable when input
function loadAllCustomer() {
    global $connection;
	$getCustQuery = "SELECT * FROM sendcustomers where id = $custId ";
    $custResult = mysqli_query($connection, $getCustQuery) or die(mysqli_error() . "Can not retrieve information from database");
    while ($cust = mysqli_fetch_array($custResult)) {
        $senderArray = array("id" => $cust['id'], "cust_name" => $cust['cust_name'], "address" => $cust['address'], "phone" => $cust['phone']);
    }
}
//loadAllCustomer();

function isValueSet($value) {
    if (isset($value) && !is_null($value) && !empty($value)) {
        return true;
    }
    return false;
}

// Add user_id to make sure order can show with correct user
function searchOrder($orderQueryString) {
	global $connection;
	$orderQueryString.=" ORDER BY id";
	if ( isset($_SESSION['user_id']) && ($_SESSION['user_id'] == 1 || $_SESSION['user_id'] == 5 || $_SESSION['username'] == 'khoa')) { // apply full role with user khoa - id = 5
		$orderQueryResult = mysqli_query($connection, $orderQueryString) or die ( mysqli_error () . "Can not retrieve database" );
	} else {
		$orderQueryResult = mysqli_query($connection, $orderQueryString." AND user_id = ".$_SESSION['user_id']) or die ( mysqli_error () . "Can not retrieve database" );
	}
	$result = array();
	$i = 1;
	while ($order = mysqli_fetch_array($orderQueryResult)) {
// 		$row['order'.$i] = mysqli_fetch_row($orderQueryResult);

		//Sender
		$senderQuery = "select * from sendcustomers where id=".$order['send_cust_id'];
		$senderQueryResult = mysqli_query($connection, $senderQuery) or die ( mysqli_error () . "Can not retrieve database" );
		$row['sender'] = mysqli_fetch_row($senderQueryResult);

		//Receiver
		$receiverQuery = "select * from recvcustomers where id=".$order['recv_cust_id'];
		$receiverQueryResult = mysqli_query($connection, $receiverQuery) or die ( mysqli_error () . "Can not retrieve database" );
		$row['receiver'] = mysqli_fetch_row($receiverQueryResult);

		$orderId = base64_encode($order['id']);
		$pos1 = rand(0, 25);
		$pos2 = rand(26, 51);
		$a_z = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$randomLetter1 = $a_z[$pos1];
		$randomLetter2 = $a_z[$pos2];
		$row['orderId'] = substr_replace($orderId, $randomLetter1 . $randomLetter2, 1, 0);

		$orderDate = $order['date'];
		if (isValueSet($orderDate)) {
			$dates = explode ("-",$orderDate);
	        $orderDate= $dates[2]."/".$dates[1]."/".$dates[0];
		}

		array_push($result, array(
			'id' => $order['id'],
			'date' => $orderDate,
			'sender_name' => $row['sender'][1],
			'sender_phone' => $row['sender'][2],
			'sender_address' => $row['sender'][3],
			'recv_name' => $row['receiver'][1],
			'recv_phone' => $row['receiver'][2],
			'recv_address' => $row['receiver'][3],
			'order_id' => $row['orderId'],
            'weight' => $order['weight'],
			'total' => $order['total']
		));
	}
	echo json_encode($result);
}
//    $sender = $_POST ["sender"];
//    $senderPhone = $_POST ["senderPhone"];
//
//    $receiver = $_POST ["receiver"];
//    $receiverPhone = $_POST ["receiverPhone"];

$orderId = $_POST ["orderNo"];
$senderPhone = $_POST ["senderPhone"];
$receiverPhone = $_POST ["receiverPhone"];
$fromDate = $_POST ["fromDate"];
$toDate = $_POST ["toDate"];

//begin();
if (isValueSet($orderId)) {
    searchOrder("select * from orders where id=".$orderId);
} else if (isValueSet($senderPhone)) {
	searchOrder("SELECT * FROM orders where send_cust_id = (select id from sendcustomers where phone like '%".$senderPhone."%')");
} else if (isValueSet($receiverPhone)) {
	searchOrder("SELECT * FROM orders where recv_cust_id = (select id from recvcustomers where phone like '%".$receiverPhone."%')");
} else if (isValueSet($fromDate) && isValueSet($toDate)) {
	$dates = explode ("/",$fromDate);
	if (count($dates) > 1) {
	   $fromDate= $dates[2]."-".$dates[1]."-".$dates[0];
	}
	$toDate = $_POST ["toDate"];
	$dates = explode ("/",$toDate);
	if (count($dates) > 1) {
	   $toDate= $dates[2]."-".$dates[1]."-".$dates[0];
	}
	searchOrder("select * from orders where date >= '".$fromDate."' and date <= '".$toDate."'");
} else {
    throw new Exception('Unable to find order');
}
?>