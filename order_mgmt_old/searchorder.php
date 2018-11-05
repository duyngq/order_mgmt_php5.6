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

// Load and save all customers to be able to selectable when input
function loadAllCustomers() {
    getSenders();
    getReceivers();
}

function getSenders() {
    // get all customers' name
    $getCustomersNameQuery = "SELECT * FROM sendcustomers";
    $getCustomersNameResult = mysql_query($getCustomersNameQuery);
    // Mysql_num_row is counting table row
    if($getCustomersNameResult) {
        while ($customer=mysql_fetch_array($getCustomersNameResult)) {
            $senderArray[$customer['phone']] = array("cust_name" => $customer['cust_name'], "address" => $customer['address'], "phone" => $customer['phone']);
        }
        if (isset($senderArray)) {
            $_SESSION['sender'] = $senderArray;
        }
    }
}

function getReceivers() {
    // get all customers' name
    $getRecvNameQuery = "SELECT * FROM recvcustomers";
    $getRecvNameResult = mysql_query($getRecvNameQuery);
    // Mysql_num_row is counting table row
    if($getRecvNameResult) {
        while ($recv = mysql_fetch_array($getRecvNameResult)) {
            $receiverArray[$recv['phone']] = array("cust_name" => $recv['cust_name'], "address" => $recv['address'], "phone" => $recv['phone']);
        }
        if (isset($receiverArray)) {
            $_SESSION['recv'] = $receiverArray;
        }
    }
}
loadAllCustomers();
?>
<DOCTYPE html PUBLIC"-//W3C//DTDXHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>SEARCH ORDERS</title>
<style type="text/css">
tr {
    background-color: transparent;
    color: #000;
    text-decoration: none;
}

p.hidden {
    border-style: hidden;
}
</style>
<script type="text/javascript" src="js/validate.js"></script>
<script type="text/javascript" src="js/util.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" language="javascript" src="js/jquery.js"></script>
<script type="text/javascript" language="javascript" src="js/jquery.dataTables.js"></script>
<script type="text/javascript" language="javascript" >
$(document).ready(function() {
	$("#submit").click(function(){
		$("#searchResult tbody").remove();
		var dataString = 'orderNo='+ $("#orderNo").val() + '&senderPhone=' + $('#senderPhone').val() + '&receiverPhone=' + $('#receiverPhone').val()+ '&fromDate=' + $('#fromDate').val()+ '&toDate=' + $('#toDate').val();// + '&email1='+ email + '&password1='+ password + '&contact1='+ contact;
		$.ajax({
	        url: 'search.php',                  //the script to call to get data
	        type: "POST",
	        data: dataString,                        //you can insert url argumnets here to pass to api.php
	           //for example "id=5&parent=6"
	        dataType: 'json',                //data format
	        success: function(data) {        //on recieve of reply
	        	// Loop through Object
	        	if (data.length <= 0) {
	        		$(".searchResult-error").html("");
                    $("#searchResult").append('<tbody class="employee-grid-error"><tr><th colspan="12">No data found in the server</th></tr></tbody>');
                    $("#searchResult_processing").css("display","none");
                    return;
	        	}
	        	var totalWeight = 0, totalAmount = 0;
		            for (var key in data) {
			            if (data.hasOwnProperty(key)) {
				            var weight = data[key]['weight'];
				            var total = data[key]['total'];
				            if (data[key]['weight'] != null) {
				            	totalWeight+=parseFloat(data[key]['weight']);
				            } else {
					            weight = 0;
				            }

				            if (data[key]['total'] != null) {
				            	totalAmount+=parseFloat(data[key]['total']);
				            } else {
					            total = 0;
				            }
// 			                var order = data['order'];              //get id
// 			                var sender = data['sender'];           //get name
// 			                var receiver = data['receiver'];           //get name
			                //--------------------------------------------------------------------
			                // 3) Update html content
			                //--------------------------------------------------------------------
		                    $("#searchResult").append('<tbody><tr>');
		                    var newRow =
		                    	"<tbody><tr onMouseOver=\"ChangeColor(this, true);\" onMouseOut=\"ChangeColor(this, false);\" onClick=\"DoNav('orderdetails.php?tr=" + data[key]['order_id'] + "')\">"
		                    	+"<td>"+data[key]['id']+"</td>"
		                    	+"<td>"+data[key]['date']+"</td>"
		                    	+"<td>"+data[key]['sender_name']+"</td>"
		                    	+"<td>"+data[key]['sender_phone']+"</td>"
		                    	+"<td>"+data[key]['sender_address']+"</td>"
		                    	+"<td>"+data[key]['recv_name']+"</td>"
		                        +"<td>"+data[key]['recv_phone']+"</td>"
		                        +"<td>"+data[key]['recv_address']+"</td>"
		                        +"<td>"+weight+"</td>"
		                        +"<td>"+total+"</td>"
		                    	+"</tr></tbody>" ;
		                    $("#searchResult").append($(newRow));
			                //recommend reading up on jquery selectors they are awesome
			                // http://api.jquery.com/category/selectors/
				        }
		            }
		            var totalRow="<tbody><tr><td colspan=8>Total (*): </td><td>"+totalWeight+"</td><td>"+totalAmount+"</td></tr></tbody>";
		            $("#searchResult").append($(totalRow));
	            },
	       error: function() {        //on recieve of reply
	                //--------------------------------------------------------------------
	                // 3) Update html content
	                //--------------------------------------------------------------------
	                $(".searchResult-error").html("");
	                $("#searchResult").append('<tbody class="employee-grid-error"><tr><th colspan="12">No data found in the server</th></tr></tbody>');
	                $("#searchResult_processing").css("display","none");
	                //recommend reading up on jquery selectors they are awesome
	                // http://api.jquery.com/category/selectors/
	            }
	    });
	});
});

$( function() {
    $( ".datepicker" ).datepicker({
        dateFormat: "dd/mm/yy"
    });
  } );
</script>
</head>

<body>
    <form name="searchOrder" onsubmit="return validation()" method="post">
        <center>
            <table width="1024px" border="1" id="searchOrder">
                <tr>
                    <td><div align="right">
                            <p>Welcome, Saigonair Cargo! <a href="index.php">Home</a> <a href="logout.php">Log out</a>
                            </p>
                            <p align="left">
                                <strong>SEARCH ORDER </strong>
                            </p>
                            <p align="left">Today's Date: <?php date_default_timezone_set('Asia/Bangkok'); echo date('d/m/Y');?> - Time: <?php echo date('H:i'); ?> </p>
                            <table width="100%" border="0" bordercolor="#F0F0F0">
                                <tr>
	                                <td>Order Number:</td>
	                                <td><input name="orderNo" type="text" id="orderNo" size="60" onkeydown="if (event.keyCode == 13) return false;"/></td>
                                </tr>
                                <tr>
                                    <td>Sender Phone Number:</td>
                                    <td><input name="senderPhone" type="text" id="senderPhone" size="60" list="custPhoneList"/></td>
                                    <datalist id="custPhoneList">
                                    <?php
                                       if (isset($_SESSION['sender'])) {
                                           foreach ($_SESSION['sender'] as $sender) {
                                               echo "<option data-id=\"".$sender['phone']."\" value=\"".$sender['phone']."\"></option>";
                                           }
                                       }
                                    ?>
                                    </datalist>
                                </tr>
                                <tr>
                                    <td>Receiver Phone Number:</td>
                                    <td><input name="receiverPhone" type="text" id="receiverPhone" size="60" list="recvPhoneList"/></td>
                                    <datalist id="recvPhoneList">
                                    <?php
                                    if (isset($_SESSION['recv'])) {
                                       foreach ($_SESSION['recv'] as $receiver) {
                                           echo "<option data-id=\"".$receiver['phone']."\" value=\"".$receiver['phone']."\"></option>";
                                       }
                                    }
                                    ?>
                                    </datalist>
                                </tr>
                                <tr>
                                    <td>Date</td>
                                </tr>
                                <tr>
                                    <td><blockquote>From</blockquote></td>
                                    <td><input name="fromDate" type="date" id="fromDate" class="datePicker" size="30" /></td>
                                    <td>To</td>
                                    <td><input name="toDate" type="date" id="toDate" class="datepicker" size="30" /></td>
                                </tr>
                            </table>
                            <p align="center"><input type="button" name="submit" id="submit" value="Search" /></p>
                        </div></td>
                </tr>
            </table>
            <table width="1024px" border="1" id="searchResult">
                    <thead>
                        <tr>
                            <th>Order ID </th>
                            <th>Order date </th>
                            <th>Sender Name </th>
                            <th>Sender Phone</th>
                            <th>Sender Address</th>
                            <th>Receiver Name </th>
                            <th>Receiver Phone</th>
                            <th>Receiver Address</th>
                            <th>Total weight</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
            </table>
        </center>
    </form>
</BODY>
</HTML>