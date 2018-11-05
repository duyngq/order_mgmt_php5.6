<?php
error_reporting ( E_ALL ^ E_DEPRECATED );
//if (!isset($_SESSION['expire'])) {
//	header ( "location:login.php" );
//}
//
//$now = time(); // Checking the time now when home page starts.
//if ($now > $_SESSION['expire']) {
//	session_destroy();
//	header ( "location:login.php" );
//}
//$_SESSION['expire'] = $now + (30 * 60);
session_start ();
if (! isset ( $_SESSION ['loggedIn'] ) || (isset ( $_SESSION ['loggedIn'] ) && ! $_SESSION ['loggedIn'])) {
	header ( "location:login.php" );
}

include_once 'dbconn.php';
// add customer to DB and redirect to index.php
if (isset ( $_POST ["submit"] )) {
	$submit = $_POST ["submit"];
}
//if (isset($_SESSION['sender'])) {
//	unset ($_SESSION['sender']);
//}
if (isset ( $submit )) {
	unset ( $_POST ["submit"] );

	$custName = $_POST ["custName"];
	$custAddr = $_POST ["custAddr"];
	// TODO: need to validate phone format here.
	$custPhone = $_POST ["custPhone"];

	$recvName = $_POST ["recvName"];
	$recvAddr = $_POST ["recvAddr"];
	// TODO: need to validate phone format here.
	$recvPhone = $_POST ["recvPhone"];

//	$orderDate = $_POST ["orderDate"];
    $orderDate = $_POST["year"]."-".$_POST["month"]."-".$_POST["day"];

	$productDesc = $_POST ["product_desc"];
	$additionalFee = "";

	//parse additional fee with the last line is total, the next of last line is fee (delimiter as :), and the rest is each fee (delimiter as comma)
	/*for ( $i = 0; $i <= 5; $i++) { //skip title as 0
		${"proDesc".$i} = $_POST["proDesc".$i];
		${"totalWeight".$i} = $_POST["weight".$i];
		validateNumber ( ${"totalWeight".$i}, "Package weight" );
		${"pricePerWeight".$i} = $_POST["price".$i];;
		validateNumber ( ${"totalWeight".$i}, "Price per package weight" );
	}*/
	$products = array ();
    $noOfProducts = 11;
    for($i = 0; $i < $noOfProducts; $i ++) {
        $product = array ();
        $product [0] = $_POST ["proDesc" . $i];

        $product [1] = $_POST ["weight" . $i];
        validateNumber ( $_POST ["weight" . $i], "Package weight of " . $_POST ["proDesc" . $i] );
        $product [2] = $_POST ["price" . $i];
        validateNumber ( $_POST ["price" . $i], "Price per package of " . $_POST ["proDesc" . $i] );

        $product [3] = $_POST ["unit" . $i];
        validateNumber ( $_POST ["unit" . $i], "Unit of " . $_POST ["proDesc" . $i] );
        $product [4] = $_POST ["unitPrice" . $i];
        validateNumber ( $_POST ["unitPrice" . $i], "Price per unit of " . $_POST ["proDesc" . $i] );
        $products [$i] = $product;
    }

	// Validate param for product details
    $addFee = $_POST ["add_fee"];
    validateNumber ( $addFee, "Additional fee" );

    $weightSum = $_POST ["weight_sum"];
    validateNumber ( $addFee, "Weight" );

	$total = $_POST ["prm_sum"];
	validateNumber ( $total, "Total amount of all products" );

	// remove new line and comma got from list uploaded files
	$fileName = str_replace(array("\r\n", "\n\r", "\r", "\n"), "", substr($_POST['uploaded'], 0, -1));

	// add customer
	$userId = $_SESSION ['user_id'];
	$username = $_SESSION ['username'];

	begin();
	// Check existing send customer first
	$checkCustomerQuery = "select id, cust_name from sendcustomers where phone='" . $custPhone . "'";
	$checkCustomerResult = mysql_query ( $checkCustomerQuery, $connection ) or die ( mysql_error () . "Can not retrieve database" );

	$checkReceiverQuery = "select id, cust_name from recvcustomers where phone='" . $recvPhone . "'";
    $checkReceiverResult = mysql_query ( $checkReceiverQuery, $connection ) or die ( mysql_error () . "Can not retrieve database" );

	$custId = null;
	$recvCustId = null;
	if ($checkCustomerResult && $checkReceiverResult) {
		if (mysql_num_rows ( $checkCustomerResult ) == 0) {
			// Need to add this as new customer
			$addCustomerQuery = "insert into sendcustomers (cust_name, phone, address) values ('$custName', '$custPhone', '$custAddr')";
			$addCustomerResult = mysql_query ( $addCustomerQuery, $connection ) or die ( mysql_error () . "Can not retrieve to database" );
			$custId = mysql_insert_id ();
			if (!$addCustomerResult) {
				echo "<script>alert('Add send customer failed');</script>";
				clearAll ( $connection, $submit );
				exit ();
			}
		}

		if (mysql_num_rows ( $checkReceiverResult ) == 0) {
            // Need to add this as new receiver customer
            $addReceiverQuery = "insert into recvcustomers (cust_name, phone, address) values ('$recvName', '$recvPhone', '$recvAddr')";
            $addReceiverResult = mysql_query ( $addReceiverQuery, $connection ) or die ( mysql_error () . "Can not retrieve to database" );
            $recvCustId = mysql_insert_id ();
            if (!$addReceiverResult) {
                echo "<script>alert('Add send customer failed');</script>";
                clearAll ( $connection, $submit );
                exit ();
            }
        }

        // Then we have all customers (existing and non-existing), add new order
        if (is_null($custId)) {
        	while ( $customer = mysql_fetch_array ( $checkCustomerResult ) ) {
        		$custId = $customer ["id"];
        	}
        }

        if (is_null($recvCustId)) {
        	while ( $customer = mysql_fetch_array ( $checkReceiverResult ) ) {
        		$recvCustId = $customer ["id"];
        	}
        }
        $orderId = addNewOrder ( $custId, $recvCustId, $userId, $orderDate,
            "", 0, 0,//$proDesc0, $totalWeight0, $pricePerWeight0,
            "", 0, 0,//$proDesc1, $totalWeight1, $pricePerWeight1,
            "", 0, 0,//$proDesc2, $totalWeight2, $pricePerWeight2,
            "", 0, 0,//$proDesc3, $totalWeight3, $pricePerWeight3,
            "", 0, 0,//$proDesc4, $totalWeight4, $pricePerWeight4,
            "", 0, 0,//$proDesc5, $totalWeight5, $pricePerWeight5,
            $addFee, $productDesc, $additionalFee, $weightSum, $total, $fileName, $connection, $submit ); // with new design, move all shipment fee to order details table
        addOrderDetails($orderId, $products, $connection, $submit);
	} else {
		rollback();
		echo "<script>alert('Unable to add new order');</script>";
		echo "<script>location.href = 'addorder.php';</script>";
	}
	commit();
	mysql_close ( $connection );
	ob_end_flush ();
	unset ( $submit );
}

function addNewOrder($custId, $recvCustId, $userId, $orderDate, $proDesc0, $totalWeight, $pricePerWeight, $proDesc1, $totalWeight1, $pricePerWeight1, $proDesc2, $totalWeight2, $pricePerWeight2, $proDesc3, $totalWeight3, $pricePerWeight3, $proDesc4, $totalWeight4, $pricePerWeight4, $proDesc5, $totalWeight5, $pricePerWeight5, $addFee, $productDesc, $additionalFee, $weightSum, $total, $fileName, $connection, $submit) {
	//convert input date to format d/m/Y to parse to timestamp for cal current week number of month
	$dates = explode ("-",$orderDate);
    $ordDate = strtotime($dates[0]."/".$dates[1]."/".$dates[2]);
	$code = date("n", $ordDate).weekOfMonth($ordDate);
//	$orderDate = $dates[2]."-".$dates[1]."-".$dates[0];

	$addNewOrder = "insert into orders(send_cust_id, recv_cust_id, user_id, status, date,
	   desc_0, total_weight, price_per_weight,
	   desc_1, total_weight_1, price_per_weight_1,
	   desc_2, total_weight_2, price_per_weight_2,
	   desc_3, total_weight_3, price_per_weight_3,
	   desc_4, total_weight_4, price_per_weight_4,
	   desc_5, total_weight_5, price_per_weight_5, code, fee, product_desc, additional_fee, weight, total, file_name, new_type)
	   values ($custId, $recvCustId, $userId, 0, '$orderDate', '$proDesc0', $totalWeight, $pricePerWeight, '$proDesc1', $totalWeight1, $pricePerWeight1, '$proDesc2', $totalWeight2, $pricePerWeight2, '$proDesc3', $totalWeight3, $pricePerWeight3, '$proDesc4', $totalWeight4, $pricePerWeight4, '$proDesc5', $totalWeight5, $pricePerWeight5, '$code', $addFee, '$productDesc', '$additionalFee', $weightSum, $total, '$fileName', 1)";
	$addNewOrderResult = mysql_query ( $addNewOrder, $connection ) or die ( mysql_error () . "Can not retrieve to database" );
	$orderId = mysql_insert_id ();
	if (!$addNewOrderResult) {
		echo "<script>alert('Unable to add new order');</script>";
		clearAll ( $connection, $submit );
		exit ();
	}
	return $orderId;
}

function addOrderDetails($orderId, $products, $connection, $submit) {
	$addOrderDetails="insert into orderdetails (order_id, p_desc, weight, price_weight, unit, price_unit) values";
	foreach ($products as $product) {
		$addOrderDetails.="($orderId, '$product[0]', $product[1], $product[2], $product[3], $product[4]),";
	}
	$addOrderDetailsResult = mysql_query ( substr($addOrderDetails, 0, -1), $connection ) or die ( mysql_error () . "Can not retrieve to database" );
	if ($addOrderDetailsResult) {
		echo "<script>alert('Add new order succeed');</script>";
//		echo "<script>location.href = 'index.php';</script>";
	} else {
		echo "<script>alert('Unable to add new order');</script>";
		clearAll ( $connection, $submit );
		exit ();
	}
}

function clearAll($connection, $submit) {
	mysql_close ( $connection );
	ob_end_flush ();
	unset ( $submit );
}

// get the week number of the month for input date
function weekOfMonth($date) {
    //Get the first day of the month.
    $firstOfMonth = strtotime(date("Y-m-01", $date));
    //Apply above formula.
    $weekNumber = intval(date("W", $date)) - intval(date("W", $firstOfMonth)) + 1;
    switch ($weekNumber) {
    case 1:
        return "a";
    case 2:
        return "b";
    case 3:
        return "c";
    case 4:
        return "d";
    case 5:
        return "e";
    }
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
<title>Add New Order</title>
<style type="text/css">
tr {
	background-color: transparent;
	color: #000;
	text-decoration: none;
}

p.hidden {
	border-style: hidden;
}

.rTable { display: table; width: 100%; }
.rTableRow {display: table-row;}
.rTableHeading { display: table-header-group; background-color: #ddd;}
.rTableCell, .rTableHead { display: table-cell; padding: 3px 10px; border: 1px solid #999999; }
.rTableHeading { display: table-header-group; background-color: #ddd; font-weight: bold; }
.rTableFoot { display: table-footer-group; font-weight: bold; background-color: #ddd; }
.rTableBody { display: table-row-group; }

.href-right{
    text-align: right;
}
.link {
    display:inline;
}
</style>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="js/validate.js"></script>
<script type="text/javascript" src="js/util.js"></script>
<script type="text/javascript" src="js/upload.js"></script>
<script>
  $( function() {
    $( "#datepicker" ).datepicker({
    	dateFormat: "dd/mm/yy"
    });
  } );
</script>
</head>

<body>
	<form name="addOrder" onsubmit="return validation()" method="post">
		<center>
			<table width="1024px" border="1">
				<tr>
					<td><div align="right">
							<p>Welcome, Saigonair Cargo!  <a href="index.php">Home</a> <a href="logout.php">Log out</a>
							</p>
							<p align="left">
								<strong>ADD NEW SHIPMENT </strong>
							</p>
							<p align="left">Date: <?php date_default_timezone_set('Asia/Bangkok'); echo date('d/m/Y');?> - Time: <?php echo date('H:i'); ?> </p>
							<table width="100%" border="0" bordercolor="#F0F0F0">
							    <tr>
                                    <td>- Sender</td>
                                </tr>
								<tr>
								<td><blockquote>Sender Phone Number:</blockquote></td>
								<td><input name="custPhone" type="text" id="custPhone" size="60" list="custPhoneList" required />
									<!-- drop down list that can be search/add new text -->
									<script type="text/javascript">
									   $("input[name=custPhone]").focusout(function(){
										   var ptamzzNamespace = <?php if (isset($_SESSION['sender'])) {echo json_encode($_SESSION['sender']);}?>;
										   var custPhone = $(this).val();
										   $('input[name="custName"]').val(ptamzzNamespace[custPhone]['cust_name']);
										   $('input[name="custAddr"]').val(ptamzzNamespace[custPhone]['address']);
									   });
									</script>
									<datalist id="custPhoneList">
									<?php
									   if (isset($_SESSION['sender'])) {
										   foreach ($_SESSION['sender'] as $sender) {
										   	   echo "<option data-id=\"".$sender['phone']."\" value=\"".$sender['phone']."\"></option>";
										   }
									   }
									?>
									</datalist>
								</td>
								</tr>
								<tr>
									<td><blockquote>Sender Name:</blockquote></td>
									<td><input list="custName" name="custName" id="custName" size="60" /></td>
								</tr>
								<td><blockquote>Sender Address:</blockquote></td>
								<td><input name="custAddr" type="text" id="custAddr" size="60" required /></td>
								</tr>
								<tr>
                                    <td>- Receiver</td>
                                </tr>
								<tr>
                                <td><blockquote>Receiver Phone Number:</blockquote></td>
                                <td><input name="recvPhone" type="text" id="recvPhone" size="60" list="recvPhoneList" required />
                                    <!-- drop down list that can be search/add new text -->
                                    <script type="text/javascript">
                                       $("input[name=recvPhone]").focusout(function(){
                                           var recvs = <?php if (isset($_SESSION['recv'])) {echo json_encode($_SESSION['recv']);}?>;
                                           var recvPhone = $(this).val();
                                           $('input[name="recvName"]').val(recvs[recvPhone]['cust_name']);
                                           $('input[name="recvAddr"]').val(recvs[recvPhone]['address']);
                                       });
                                    </script>
                                    <datalist id="recvPhoneList">
                                    <?php
                                    if (isset($_SESSION['recv'])) {
                                       foreach ($_SESSION['recv'] as $receiver) {
                                           echo "<option data-id=\"".$receiver['phone']."\" value=\"".$receiver['phone']."\"></option>";
                                       }
                                    }
                                    ?>
                                    </datalist>
                                    </td>
                                </tr>
                                <tr>
                                    <td><blockquote>Receiver Name:</blockquote></td>
                                    <td><input list="recvName" name="recvName" id="recvName" size="60" /></td>
                                </tr>
                                <td><blockquote>Receiver Address:</blockquote></td>
                                <td><input name="recvAddr" type="text" id="recvAddr" size="60" required /></td>
                                </tr>
								<tr>
									<td>- Date:</td>
                                    <td>
									<select name="day" id="day">
										<option value="01">01</option>
                                        <option value="02">02</option>
                                        <option value="03">03</option>
                                        <option value="04">04</option>
                                        <option value="05">05</option>
                                        <option value="06">06</option>
                                        <option value="07">07</option>
                                        <option value="08">08</option>
                                        <option value="09">09</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                        <option value="13">13</option>
                                        <option value="14">14</option>
                                        <option value="15">15</option>
                                        <option value="16">16</option>
                                        <option value="17">17</option>
                                        <option value="18">18</option>
                                        <option value="19">19</option>
                                        <option value="20">20</option>
                                        <option value="21">21</option>
                                        <option value="22">22</option>
                                        <option value="23">23</option>
                                        <option value="24">24</option>
                                        <option value="25">25</option>
                                        <option value="26">26</option>
                                        <option value="27">27</option>
                                        <option value="28">28</option>
                                        <option value="29">29</option>
                                        <option value="30">30</option>
                                        <option value="31">31</option>
                                    </select>
                                    <select name="month" id="month">
                                        <option value="01">01</option>
                                        <option value="02">02</option>
                                        <option value="03">03</option>
                                        <option value="04">04</option>
                                        <option value="05">05</option>
                                        <option value="06">06</option>
                                        <option value="07">07</option>
                                        <option value="08">08</option>
                                        <option value="09">09</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                    </select>
									<select name="year" id="year">
									    <option value="2016">2016</option>
                                        <option value="2017">2017</option>
                                        <option value="2018">2018</option>
                                        <option value="2019">2019</option>
                                        <option value="2020">2020</option>
                                        <option value="2021">2021</option>
                                        <option value="2022">2022</option>
                                        <option value="2023">2023</option>
                                        <option value="2024">2024</option>
                                        <option value="2025">2025</option>
                                        <option value="2026">2026</option>
                                        <option value="2027">2027</option>
                                        <option value="2028">2028</option>
                                        <option value="2029">2029</option>
                                        <option value="2030">2030</option>
									</select>
										<!-- input name="orderDate" type="text"
                                        size="20" value="<?php echo date_create_from_format("d/m/Y", date("Y/m/d")); ?>" readonly="true" />
                                        <input name="orderDate" type="text"
                                        size="20" value="<?php echo date_create_from_format("d/m/Y", date("Y/m/d")); ?>" readonly="true" /-->
                                        <script type="text/javascript">
//                                        validateDate();
//                                         validate_date();
                                        selectDate();
                                        </script>
                                        </td>
									<td><input name="orderTest" type="text"
                                        size="60" value="<?php echo date("d M/d/Y")." ".date_default_timezone_get()." ";
                                        $dt = new DateTime();
echo $dt->format('Y-m-d H:i:s'); ?>" readonly="true" /></td>
								</tr>
								<tr>
									<td>- Products:</td>
								</tr>
								<tr>
									<td colspan="2">
										<table width="1024px" border="0" id='productTbl'>
											<tr>
												<th>Description</th>
												<th>Shipment Fee</th>
											</tr>
											<tr>
												<td width=40%><p id="product_desc_para"><textarea name="product_desc" id="product_desc" cols="65" rows="20"
										style="border: 1px solid black" placeholder="Click and write product description"></textarea></p>
										<form name="fileUpload" id="fileUpload" method="post" enctype="multipart/form-data">
		                                    <input name="file[]" type="file" multiple="multiple" id="uploadFile"/>
		                                    <!--<br />
										    <iframe id="upload_frame" name="upload_frame" frameborder="0" border="0" src="" scrolling="no" scrollbar="no" > </iframe>
										    <br />
		                                    -->
		                                    <div id="uploadedFiles"></div>
		                                    <input name="uploaded" id="uploaded" type="hidden"/>
		                                </form>
		                                <!-- progress></progress-->
                                <script>
                                    // Add events
                                    $(':file').on('change', prepareUpload);
//                                    $("#upload").on( 'click', uploadFiles);
                                    $("#uploadedFiles").on('click', 'a', removeUploadedFile); // To do ajax call for dynamic component. Refer http://api.jquery.com/on/ delegated events part
                                </script>
										</td>
												<td width=60% style="vertical-align: top;" id="feeTableRow">
									           <div class="rTable" id="feeTable">
			                                       <div class="rTable">
			                                            <div class="rTableRow">
			                                                <div class="rTableHead"><strong>Description</strong></div>
			                                                <div class="rTableHead"><strong>Weight(lbs)</strong></div>
			                                                <div class="rTableHead"><strong>Price</strong></div>
			                                                <div class="rTableHead"><strong>Unit</strong></div>
                                                            <div class="rTableHead"><strong>Price</strong></div>
			                                                <div class="rTableHead"><strong>Total</strong></div>
			                                            </div>
			                                            <div class="rTableRow">
			                                                <div class="rTableCell"><input name="proDesc0" type="text" id="proDesc0" class="proDesc0" size="30" style="border:0"/></div>
			                                                <div class="rTableCell"><input name="weight0" type="text" id="weight0" class="weight0" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight0', 'price0', 'unit0', 'unitPrice0', 'total0');calTotal('feeTable');calTotalWeight('feeTable');"/></div>
			                                                <div class="rTableCell"><input name="price0" type="text" id="price0" class="price0" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight0', 'price0', 'unit0', 'unitPrice0', 'total0');calTotal('feeTable');" /></div>
			                                                <div class="rTableCell"><input name="unit0" type="text" id="unit0" class="unit0" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight0', 'price0', 'unit0', 'unitPrice0', 'total0');calTotal('feeTable');calTotalUnit('feeTable');"/></div>
                                                            <div class="rTableCell"><input name="unitPrice0" type="text" id="unitPrice0" class="unitPrice0" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight0', 'price0', 'unit0', 'unitPrice0', 'total0');calTotal('feeTable');" /></div>
			                                                <div class="rTableCell"><input name="total0" type="text" id="total0" class="total0" value="0" size="10" style="border:0" readonly="readonly" /></div>
			                                            </div>
			                                            <div class="rTableRow">
			                                                <div class="rTableCell"><input name="proDesc1" type="text" id="proDesc1" class="proDesc1" size="30" style="border:0"/></div>
			                                                <div class="rTableCell"><input name="weight1" type="text" id="weight1" class="weight1" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight1', 'price1', 'unit1', 'unitPrice1', 'total1');calTotal('feeTable');calTotalWeight('feeTable');"/></div>
			                                                <div class="rTableCell"><input name="price1" type="text" id="price1" class="price1" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight1', 'price1', 'unit1', 'unitPrice1', 'total1');calTotal('feeTable');" /></div>
			                                                <div class="rTableCell"><input name="unit1" type="text" id="unit1" class="unit1" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight1', 'price1', 'unit1', 'unitPrice1', 'total1');calTotal('feeTable');calTotalUnit('feeTable');"/></div>
                                                            <div class="rTableCell"><input name="unitPrice1" type="text" id="unitPrice1" class="unitPrice1" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight1', 'price1', 'unit1', 'unitPrice1', 'total1');calTotal('feeTable');" /></div>
			                                                <div class="rTableCell"><input name="total1" type="text" id="total1" class="total1" value="0" size="10" style="border:0" readonly="readonly" /></div>
			                                            </div>
			                                            <div class="rTableRow">
			                                                <div class="rTableCell"><input name="proDesc2" type="text" id="proDesc2" class="proDesc2" size="30" style="border:0"/></div>
			                                                <div class="rTableCell"><input name="weight2" type="text" id="weight2" class="weight2" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight2', 'price2', 'unit2', 'unitPrice2', 'total2');calTotal('feeTable');calTotalWeight('feeTable');"/></div>
			                                                <div class="rTableCell"><input name="price2" type="text" id="price2" class="price2" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight2', 'price2', 'unit2', 'unitPrice2', 'total2');calTotal('feeTable');" /></div>
			                                                <div class="rTableCell"><input name="unit2" type="text" id="unit2" class="unit2" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight2', 'price2', 'unit2', 'unitPrice2', 'total2');calTotal('feeTable');calTotalUnit('feeTable');"/></div>
                                                            <div class="rTableCell"><input name="unitPrice2" type="text" id="unitPrice2" class="unitPrice2" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight2', 'price2', 'unit2', 'unitPrice2', 'total2');calTotal('feeTable');" /></div>
			                                                <div class="rTableCell"><input name="total2" type="text" id="total2" class="total2" value="0" size="10" style="border:0" readonly="readonly" /></div>
			                                            </div>
			                                            <div class="rTableRow">
			                                                <div class="rTableCell"><input name="proDesc3" type="text" id="proDesc3" class="proDesc3" size="30" style="border:0"/></div>
			                                                <div class="rTableCell"><input name="weight3" type="text" id="weight3" class="weight3" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight3', 'price3', 'unit3', 'unitPrice3', 'total3');calTotal('feeTable');calTotalWeight('feeTable');"/></div>
			                                                <div class="rTableCell"><input name="price3" type="text" id="price3" class="price3" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight3', 'price3', 'unit3', 'unitPrice3', 'total3');calTotal('feeTable');" /></div>
			                                                <div class="rTableCell"><input name="unit3" type="text" id="unit3" class="unit3" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight3', 'price3', 'unit3', 'unitPrice3', 'total3');calTotal('feeTable');calTotalUnit('feeTable');"/></div>
                                                            <div class="rTableCell"><input name="unitPrice3" type="text" id="unitPrice3" class="unitPrice3" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight3', 'price3', 'unit3', 'unitPrice3', 'total3');calTotal('feeTable');" /></div>
			                                                <div class="rTableCell"><input name="total3" type="text" id="total3" class="total3" value="0" size="10" style="border:0" readonly="readonly" /></div>
			                                            </div>
			                                            <div class="rTableRow">
			                                                <div class="rTableCell"><input name="proDesc4" type="text" id="proDesc4" class="proDesc4" size="30" style="border:0"/></div>
			                                                <div class="rTableCell"><input name="weight4" type="text" id="weight4" class="weight4" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight4', 'price4', 'unit4', 'unitPrice4', 'total4');calTotal('feeTable');calTotalWeight('feeTable');"/></div>
			                                                <div class="rTableCell"><input name="price4" type="text" id="price4" class="price4" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight4', 'price4', 'unit4', 'unitPrice4', 'total4');calTotal('feeTable');" /></div>
			                                                <div class="rTableCell"><input name="unit4" type="text" id="unit4" class="unit4" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight4', 'price4', 'unit4', 'unitPrice4', 'total4');calTotal('feeTable');calTotalUnit('feeTable');"/></div>
                                                            <div class="rTableCell"><input name="unitPrice4" type="text" id="unitPrice4" class="unitPrice4" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight4', 'price4', 'unit4', 'unitPrice4', 'total4');calTotal('feeTable');" /></div>
			                                                <div class="rTableCell"><input name="total4" type="text" id="total4" class="total4" value="0" size="10" style="border:0" readonly="readonly" /></div>
			                                            </div>
			                                            <div class="rTableRow">
			                                                <div class="rTableCell"><input name="proDesc5" type="text" id="proDesc5" class="proDesc5" size="30" style="border:0"/></div>
			                                                <div class="rTableCell"><input name="weight5" type="text" id="weight5" class="weight5" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight5', 'price5', 'unit5', 'unitPrice5', 'total5');calTotal('feeTable');calTotalWeight('feeTable');"/></div>
			                                                <div class="rTableCell"><input name="price5" type="text" id="price5" class="price5" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight5', 'price5', 'unit5', 'unitPrice5', 'total5');calTotal('feeTable');" /></div>
			                                                <div class="rTableCell"><input name="unit5" type="text" id="unit5" class="unit5" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight5', 'price5', 'unit5', 'unitPrice5', 'total5');calTotal('feeTable');calTotalUnit('feeTable');"/></div>
                                                            <div class="rTableCell"><input name="unitPrice5" type="text" id="unitPrice5" class="unitPrice5" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight5', 'price5', 'unit5', 'unitPrice5', 'total5');calTotal('feeTable');" /></div>
			                                                <div class="rTableCell"><input name="total5" type="text" id="total5" class="total5" value="0" size="10" style="border:0" readonly="readonly" /></div>
			                                            </div>
			                                            <div class="rTableRow">
                                                            <div class="rTableCell"><input name="proDesc6" type="text" id="proDesc6" class="proDesc6" size="30" style="border:0"/></div>
                                                            <div class="rTableCell"><input name="weight6" type="text" id="weight6" class="weight6" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight6', 'price6', 'unit6', 'unitPrice6', 'total6');calTotal('feeTable');calTotalWeight('feeTable');"/></div>
                                                            <div class="rTableCell"><input name="price6" type="text" id="price6" class="price6" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight6', 'price6', 'unit6', 'unitPrice6', 'total6');calTotal('feeTable');" /></div>
                                                            <div class="rTableCell"><input name="unit6" type="text" id="unit6" class="unit6" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight6', 'price6', 'unit6', 'unitPrice6', 'total6');calTotal('feeTable');calTotalUnit('feeTable');"/></div>
                                                            <div class="rTableCell"><input name="unitPrice6" type="text" id="unitPrice6" class="unitPrice6" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight6', 'price6', 'unit6', 'unitPrice6', 'total6');calTotal('feeTable');" /></div>
                                                            <div class="rTableCell"><input name="total6" type="text" id="total6" class="total6" value="0" size="10" style="border:0" readonly="readonly" /></div>
                                                        </div>
                                                        <div class="rTableRow">
                                                            <div class="rTableCell"><input name="proDesc7" type="text" id="proDesc7" class="proDesc7" size="30" style="border:0"/></div>
                                                            <div class="rTableCell"><input name="weight7" type="text" id="weight7" class="weight7" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight7', 'price7', 'unit7', 'unitPrice7', 'total7');calTotal('feeTable');calTotalWeight('feeTable');"/></div>
                                                            <div class="rTableCell"><input name="price7" type="text" id="price7" class="price7" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight7', 'price7', 'unit7', 'unitPrice7', 'total7');calTotal('feeTable');" /></div>
                                                            <div class="rTableCell"><input name="unit7" type="text" id="unit7" class="unit7" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight7', 'price7', 'unit7', 'unitPrice7', 'total7');calTotal('feeTable');calTotalUnit('feeTable');"/></div>
                                                            <div class="rTableCell"><input name="unitPrice7" type="text" id="unitPrice7" class="unitPrice7" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight7', 'price7', 'unit7', 'unitPrice7', 'total7');calTotal('feeTable');" /></div>
                                                            <div class="rTableCell"><input name="total7" type="text" id="total7" class="total7" value="0" size="10" style="border:0" readonly="readonly" /></div>
                                                        </div>
                                                        <div class="rTableRow">
                                                            <div class="rTableCell"><input name="proDesc8" type="text" id="proDesc8" class="proDesc8" size="30" style="border:0"/></div>
                                                            <div class="rTableCell"><input name="weight8" type="text" id="weight8" class="weight8" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight8', 'price8', 'unit8', 'unitPrice8', 'total8');calTotal('feeTable');calTotalWeight('feeTable');"/></div>
                                                            <div class="rTableCell"><input name="price8" type="text" id="price8" class="price8" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight8', 'price8', 'unit8', 'unitPrice8', 'total8');calTotal('feeTable');" /></div>
                                                            <div class="rTableCell"><input name="unit8" type="text" id="unit8" class="unit8" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight8', 'price8', 'unit8', 'unitPrice8', 'total8');calTotal('feeTable');calTotalUnit('feeTable');"/></div>
                                                            <div class="rTableCell"><input name="unitPrice8" type="text" id="unitPrice8" class="unitPrice8" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight8', 'price8', 'unit8', 'unitPrice8', 'total8');calTotal('feeTable');" /></div>
                                                            <div class="rTableCell"><input name="total8" type="text" id="total8" class="total8" value="0" size="10" style="border:0" readonly="readonly" /></div>
                                                        </div>
                                                        <div class="rTableRow">
                                                            <div class="rTableCell"><input name="proDesc9" type="text" id="proDesc9" class="proDesc9" size="30" style="border:0"/></div>
                                                            <div class="rTableCell"><input name="weight9" type="text" id="weight9" class="weight9" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight9', 'price9', 'unit9', 'unitPrice9', 'total9');calTotal('feeTable');calTotalWeight('feeTable');"/></div>
                                                            <div class="rTableCell"><input name="price9" type="text" id="price9" class="price9" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight9', 'price9', 'unit9', 'unitPrice9', 'total9');calTotal('feeTable');" /></div>
                                                            <div class="rTableCell"><input name="unit9" type="text" id="unit9" class="unit9" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight9', 'price9', 'unit9', 'unitPrice9', 'total9');calTotal('feeTable');calTotalUnit('feeTable');"/></div>
                                                            <div class="rTableCell"><input name="unitPrice9" type="text" id="unitPrice9" class="unitPrice9" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight9', 'price9', 'unit9', 'unitPrice9', 'total9');calTotal('feeTable');" /></div>
                                                            <div class="rTableCell"><input name="total9" type="text" id="total9" class="total9" value="0" size="10" style="border:0" readonly="readonly" /></div>
                                                        </div>
                                                        <div class="rTableRow">
                                                            <div class="rTableCell"><input name="proDesc10" type="text" id="proDesc10" class="proDesc10" size="30" style="border:0"/></div>
                                                            <div class="rTableCell"><input name="weight10" type="text" id="weight10" class="weight10" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight10', 'price10', 'unit10', 'unitPrice10', 'total10');calTotal('feeTable');calTotalWeight('feeTable');"/></div>
                                                            <div class="rTableCell"><input name="price10" type="text" id="price10" class="price10" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight10', 'price10', 'unit10', 'unitPrice10', 'total10');calTotal('feeTable');" /></div>
                                                            <div class="rTableCell"><input name="unit10" type="text" id="unit10" class="unit10" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight10', 'price10', 'unit10', 'unitPrice10', 'total10');calTotal('feeTable');calTotalUnit('feeTable');"/></div>
                                                            <div class="rTableCell"><input name="unitPrice10" type="text" id="unitPrice10" class="unitPrice10" value="0" size="5" style="border:0" onchange="calFeeAmount('feeTable', 'weight10', 'price10', 'unit10', 'unitPrice10', 'total10');calTotal('feeTable');" /></div>
                                                            <div class="rTableCell"><input name="total10" type="text" id="total10" class="total10" value="0" size="10" style="border:0" readonly="readonly" /></div>
                                                        </div>
			                                            <br/><br/>
			                                            <!-- Additional fee and total -->
			                                            <div class="rTableRow">
			                                                Payment in VN's fee
			                                                 <div class="rTableCell" style="border:0"></div>
			                                                <div class="rTableCell" style="border:0"></div>
			                                                <div class="rTableCell" style="border:0"></div>
			                                                <div class="rTableCell" style="border:0"></div>
			                                                <div class="rTableCell"><input name="add_fee" type="text" id="add_fee" class="add_fee" value="0" size="5" style="border:0" onchange="calTotal('feeTable');"/></div>
			                                            </div>
			                                            <div class="rTableRow">
			                                                <div class="rTableCell" style="border:0">Total (*)</div>
			                                                <div class="rTableCell" style="border:0"><input name="weight_sum" type="text" id="weight_sum" class="weight_sum" value="0" size="5" style="border:0" readonly="readonly"/></div>
			                                                <div class="rTableCell" style="border:0"></div>
			                                                <div class="rTableCell" style="border:0"><input name="unit_sum" type="text" id="unit_sum" class="unit_sum" value="0" size="5" style="border:0" readonly="readonly"/></div>
                                                            <div class="rTableCell" style="border:0"></div>
			                                                <div class="rTableCell" style="border:0"><input name="prm_sum" type="text" id="prm_sum" class="prm_sum" value="0" size="10" style="border:0" readonly="readonly"/></div>
			                                            </div>
			                                        </div>
								                </div>
										        </td>
											</tr>
										</table>
									</td>
								</tr>
								<div id="dialog-form" title="Product description">
                                       <textarea name="product_desc_dlg" id="product_desc_dlg" style="border: 1px solid black; width: 100%; height: 95%" placeholder="Click and write product description"></textarea>
                                </div>
                                <script>
                                  $( function() {
                                      //$('.rTable').clone().appendTo('#feeTable');
                                      //$('#feeTable').find('input').attr('readonly', 'readonly');
                                      var productDescDlg = $( "#dialog-form" ).dialog({
                                            autoOpen: false,
                                            height: 800,
                                            width: 800,
                                            modal: true,
                                            buttons: {
                                              Close: function() {
                                    	  	    productDescDlg.dialog( "close" );
                                                $( "#product_desc" ).val($( "#product_desc_dlg" ).val());
                                              }
                                            },
                                            close: function() {
                                                $( "#product_desc" ).val($( "#product_desc_dlg" ).val());
                                            }
                                          });

                                          $( "#product_desc" ).on( "click", function() {
                                        	productDescDlg.dialog( "open" );
                                            $( "#product_desc_dlg" ).val($( "#product_desc" ).val());
                                          });
                                  } );
                                </script>
							</table>
							<p>&nbsp;</p>
							<div align="center">
<!--								<form id="form1" name="form1" method="post"-->
<!--									style="text-align: center">-->
									<input type="submit" name="submit" id="submit" value="Add" />
							</div>
							<p align="left">&nbsp;</p>
						</div></td>
				</tr>
			</table>

		</center>
	</form>
</BODY>
</HTML>