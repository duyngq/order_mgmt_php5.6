<?php
error_reporting ( E_ALL ^ E_DEPRECATED );
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
session_start (); // / initialize session
if (! isset ( $_SESSION ['loggedIn'] ) || (isset ( $_SESSION ['loggedIn'] ) && ! $_SESSION ['loggedIn'])) {
	header ( "location:login.php" );
}
//header('Content-Type: text/plain');
include_once 'dbconn.php';

//TODO: need to move this page to structure as header, content, footer
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Order details</title>
<style type="text/css">
tr {
	background-color: transparent;
	color: #000;
	text-decoration: none;
}

p.hidden {
	border-style: hidden;
}
.print-order {
                opacity: 0;
            }

.rTable { display: table; width: 100%; border:0;}
.rTableRow {display: table-row;}
.rTableHeading { display: table-header-group; background-color: #ddd;}
.rTableCell, .rTableHead { display: table-cell; padding: 3px 10px; border: 0px solid #999999; }
.rTableHeading { display: table-header-group; background-color: #ddd; font-weight: bold; }
.rTableFoot { display: table-footer-group; font-weight: bold; background-color: #ddd; }
.rTableBody { display: table-row-group; }
</style>
<script type="text/javascript" src="js/validate.js"></script>
<script type="text/javascript" src="js/util.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" />
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<link rel='stylesheet' type='text/css' href='css/style.css' />
<link rel='stylesheet' type='text/css' href='css/print.css' media="print" />
<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet" />
<script>
  $( function() {
    $( "#datepicker" ).datepicker({
        dateFormat: "dd/mm/yy"
    });
  } );
  $(function() {
      // Delete row in a table
      jQuery('.print-order').click(function(){
    	  window.print();
      });
      jQuery('#page-wrap').hover(function(){
          jQuery(this).find('.print-order').animate({opacity: 1});
        },function(){
          jQuery(this).find('.print-order').animate({opacity: 0});
        });
  });


</script>
</head>

<body>
	<div id="page-wrap">
	    <div align="right">
            <a class="fa fa-print print-order" aria-hidden="true" href="#"></a>
        </div>
		<textarea id="header"><?php
		  if ( isset($_SESSION['user_id']) && ($_SESSION['user_id'] == 7 || $_SESSION['username'] == 'trietle')) { // apply full role with user khoa - id = 5
		  	echo "SF Express";
		  } else if ( isset($_SESSION['user_id']) && ($_SESSION['user_id'] == 5 || $_SESSION['username'] == 'khoa')) { // apply full role with user khoa - id = 5
            echo "SAIGONAIR CARGO";
          } else{
		  	echo "SAO PHI CARGO";
		  }
		?></textarea>
		<div id="identity">
		<?php
		if ( isset($_SESSION['user_id']) && ($_SESSION['user_id'] == 7 || $_SESSION['username'] == 'trietle')) { // apply full role with user khoa - id = 5
		?>
		<div style="width:800px;">
            <p align="center" style="font-size:20px"><?php
                echo "2955 Senter Rd, Ste 60, San Jose, CA 95111";
            ?></br></p>
            <p align="center" style="font-size:20px"><?php
                echo "<strong>Mr. Triet: </strong>408-898 9898";
            ?></p>
            <?php } else {

            ?>
		  <div style="width:800px;">
		      <div style="width:400px; float:left;">
			<p align="center" style="font-size:20px"><?php
			if ( isset($_SESSION['user_id']) && ($_SESSION['user_id'] == 6 || $_SESSION['username'] == 'vinhle')) { // apply full role with user khoa - id = 5
				echo "<strong>Mr. Vinh: </strong>408-797-7777";
            } else {
//            	echo "1229 Jacklin Rd, Milpitas, CA 95036";
                echo "1759 S Main St #116, Milpitas CA 95035";
            }
			?></br></p>
			<p align="center" style="font-size:20px"><?php
			 if ( isset($_SESSION['user_id']) && ($_SESSION['user_id'] == 6 || $_SESSION['username'] == 'vinhle')) { // apply full role with user khoa - id = 5
                echo "";
            } else {
                echo "<strong>Mr. Phap: </strong>408-781-8812";
            }

			?></p>
			</br>
			</br>
            </div>
            <div style="width:400px; float:right;">
               <p align="center" style="font-size:20px"></p>
      			<p align="center" style="font-size:20px"><?php
      				echo "<strong>Mr.Khoa: </strong>0934-934-952";

      			?></p>
			</div>
			</div>
			</br></br></br></br></br>
			<?php
            }
				$orderId = $_GET ['tr'];
				$orderId1 = substr ( $orderId, 0, 1 );
				$orderId2 = substr ( $orderId, 3 );
				$orderId = base64_decode ( $orderId1 . $orderId2 );
				if ( isset($_SESSION['user_id']) && ($_SESSION['user_id'] == 1 || $_SESSION['user_id'] == 5 || $_SESSION['username'] == 'khoa')) { // apply full role with user khoa - id = 5
					$getOrdersQuery = "SELECT * FROM orders where id = $orderId ";
				} else {
					$getOrdersQuery = "SELECT * FROM orders where id = $orderId AND user_id = ".$_SESSION['user_id'];
				}
				$ordersResult = mysql_query($getOrdersQuery) or die(mysql_error() . "Can not retrieve information from database");
				$custId;
				$recvId;
				while ($order = mysql_fetch_array($ordersResult)) {
					$custId = $order['send_cust_id'];
					$recvId = $order['recv_cust_id'];
					$_SESSION['orderType'] = $order['new_type'];
					$orderArray = array("id" => $order['id'], "send_cust_id" => $order['send_cust_id'],
			                  "user_id" => $order['user_id'], "status" => $order['status'], "date" => $order['date'], "total_weight_0" => $order['total_weight'],
			                  "desc_0" => $order['desc_0'], "price_per_weight_0" => $order['price_per_weight'], "total_weight_1" => $order['total_weight_1'],
			                  "desc_1" => $order['desc_1'], "price_per_weight_1" => $order['price_per_weight_1'], "total_weight_2" => $order['total_weight_2'],
                              "desc_2" => $order['desc_2'], "price_per_weight_2" => $order['price_per_weight_2'], "total_weight_3" => $order['total_weight_3'],
                              "desc_3" => $order['desc_3'], "price_per_weight_3" => $order['price_per_weight_3'], "total_weight_4" => $order['total_weight_4'],
                              "desc_4" => $order['desc_4'], "price_per_weight_4" => $order['price_per_weight_4'], "total_weight_5" => $order['total_weight_5'],
                              "desc_5" => $order['desc_5'], "price_per_weight_5" => $order['price_per_weight_5'], "fee" => $order['fee'], "total" => $order['total'], "recv_cust_id" => $order['recv_cust_id'],
			                  "product_desc" => $order['product_desc'], "additional_fee" => $order['additional_fee'], "code" => $order['code']);
				}

				//Get customer info
				$getCustQuery = "SELECT * FROM sendcustomers where id = $custId ";
				$custResult = mysql_query($getCustQuery) or die(mysql_error() . "Can not retrieve information from database");
				while ($cust = mysql_fetch_array($custResult)) {
					$senderArray = array("id" => $cust['id'], "cust_name" => $cust['cust_name'], "address" => $cust['address'], "phone" => $cust['phone']);
				}

				//Get receiver info
				$getRecvQuery = "SELECT * FROM recvcustomers where id = $recvId ";
				$recvResult = mysql_query($getRecvQuery) or die(mysql_error() . "Can not retrieve information from database");
				while ($recv = mysql_fetch_array($recvResult)) {
					$recvArray = array("id" => $recv['id'], "cust_name" => $recv['cust_name'], "address" => $recv['address'], "phone" => $recv['phone']);
				}
			?>
			<div style="width:800px; float:center;">
			<p align="center" style="font-size:20px">
			</br>
				RECEIPT</br>SP: <?php echo $orderArray['id'];//echo $orderArray['code']."  ".$orderArray['id'];?></p>
				</div>
			<div style="clear: both"></div>
			<div id="customer" align="center">
				<table id="meta">
					<tr>
                        <td class="meta-head">
							<p><strong>
								Sender (Người gửi)</strong></br>
							</p> Name: <?php echo $senderArray['cust_name'];?></br>
							<p>Address: <?php echo $senderArray['address'];?></p>
							<p>Phone: <?php echo $senderArray['phone'];?></p>
						</td>
						<td class="meta-head">
							<p> <strong>
								Receiver (Người nhận)</strong></br>
							</p> Name: <?php echo $recvArray['cust_name'];?></br>
							<p>Address: <?php echo $recvArray['address'];?></p>
							<p>Phone: <?php echo $recvArray['phone'];?></p>
						</td>
					</tr>
				</table>
			</div>
		</div>
        </br></br>
		<div align="center">
			<table id="meta">
				<tr>
					<td contenteditable='true'>
						<p id="underline"><strong>DESCRIPTION OF CONTENTS: (TÊN HÀNG HÓA):</strong></p>
						<?php
    						// For loop here over product list to show:
    						// + <quantity> <unit> <productName>

    						// hien phu thu
    						// PHU THU: <quantity> <unit> <productName> X <price> = <total>
    						// TOTAL: <weight> X <pricePerWeight> = total1 + <total> = <final total>
    						$order   = array("\r\n", "\n", "\r");
                            $replace = '<br />';
                            $productDes = str_replace($order, $replace, $orderArray['product_desc']);

							echo $productDes;
                            if (count(split($replace, $productDes)) < 10) {
                            	echo "<br/><br/><br/><br/><br/><br/><br/><br/><br/>";
                            }
							echo "<br/>";

// 							$orderFormat = "%-30s %-10s %-10s %-10s %s";
// 							echo str_replace(" ", "&nbsp;",sprintf($orderFormat,"Description","weight","price","unit","price"));
//							echo str_pad("Description",20,"&nbsp;").str_pad("weight",5,"&nbsp;").str_pad("price",5,"&nbsp;").str_pad("unit",5,"&nbsp;").str_pad("price",5,"&nbsp;")."total";
							echo "<br/>";
						?>
							<div class="rTable">
								<div class="rTableRow">
									<div class="rTableHead" style="width:70%"><strong>Description</strong></div>
									<div class="rTableHead" style="width:5%"><strong>Weight(lbs)</strong></div>
									<div class="rTableHead" style="width:5%"><strong>Price</strong></div>
									<div class="rTableHead" style="width:5%"><strong>Unit</strong></div>
									<div class="rTableHead" style="width:5%"><strong>Price</strong></div>
									<div class="rTableHead" style="width:10%"><strong>Total</strong></div>
								</div>
						<?php
							function isEmptyValue($value) {
								return is_null($value) || $value == null || $value == '' || $value == 0;
							}
							if ($_SESSION['orderType'] == 1) {
							     $getOrderDetailsQuery = "select * from orderdetails where order_id=".$orderId;
							     $orderDetailsResult = mysql_query($getOrderDetailsQuery) or die(mysql_error() . "Can not retrieve information from database");
							     $noOfProducts = 0;
							     $oldProducts = array ();
							     while ($orderDetails = mysql_fetch_array($orderDetailsResult)) {
							     	$product = array ();
							     	$product [0] = $orderDetails['id'];
							     	$product [1] = $orderDetails['order_id'];
							     	$product [2] = $orderDetails['p_desc'];
							     	$product [3] = $orderDetails['weight'];
							     	$product [4] = $orderDetails['price_weight'];
							     	$product [5] = $orderDetails['unit'];
							     	$product [6] = $orderDetails['price_unit'];
							     	$oldProducts [$noOfProducts] = $product;
							     	$noOfProducts++;
							     	if ( isEmptyValue($product [2]) && isEmptyValue($product [3]) && isEmptyValue($product [4]) && isEmptyValue($product [5]) && isEmptyValue($product [6])) {
							     		continue;
							     	}
						?>
								<div class="rTableRow">
									<div class="rTableCell" style="width:70%"><?php echo $orderDetails['p_desc'];?></div>
									<div class="rTableCell" style="width:5%"><?php echo $orderDetails['weight'];?></div>
									<div class="rTableCell" style="width:5%"><?php echo $orderDetails['price_weight'];?></div>
									<div class="rTableCell" style="width:5%"><?php echo $orderDetails['unit'];?></div>
									<div class="rTableCell" style="width:5%"><?php echo $orderDetails['price_unit'];?></div>
									<div class="rTableCell" style="width:10%"><?php echo ($orderDetails['weight'] * $orderDetails['price_weight']) + ($orderDetails['unit'] * $orderDetails['price_unit']);?></div>
								</div>
                                    <?php
							     }
						?>
						<br/><br/>
									<!-- Additional fee and total -->
		                            <div class="rTableRow">
		                              Payment in VN's fee
		                              <div class="rTableCell" style="border:0"></div>
		                              <div class="rTableCell" style="border:0"></div>
		                              <div class="rTableCell" style="border:0"></div>
		                              <div class="rTableCell" style="border:0"></div>
		                              <div class="rTableCell"><?php echo $orderArray['fee'];?></div>
		                            </div>
		                            <div class="rTableRow">
		                                 <div class="rTableCell" style="border:0">Total (*)</div>
		                                 <div class="rTableCell" style="border:0"><input name="weight_sum" type="text" id="weight_sum" class="weight_sum" value="<?php
		                                    $weightSum = 0;
                                            foreach ($oldProducts as $orderDetails) {
                                                $weightSum += $orderDetails[3];
                                            }
                                            echo $weightSum;
		                                 ?>" size="10" style="border:0" readonly="readonly"/></div>
		                                 <div class="rTableCell" style="border:0"></div>
		                                 <div class="rTableCell" style="border:0"><input name="unit_sum" type="text" id="unit_sum" class="unit_sum" value="<?php
                                            $unitSum = 0;
                                            foreach ($oldProducts as $orderDetails) {
                                                $unitSum += $orderDetails[5];
                                            }
                                            echo $unitSum;
                                         ?>" size="5" style="border:0" readonly="readonly"/></div>
		                                 <div class="rTableCell" style="border:0"></div>
		                                 <div class="rTableCell" style="border:0"><?php echo $orderArray['total'];?></div>
		                            </div>
							</div>
							<?php
							} else { //old format
								$weightSum = 0;
								for ($i = 0; $i <= 5; $i++) {
									$weightSum += $orderArray['total_weight_'.$i];
									if ( isEmptyValue($orderArray['desc_'.$i]) && isEmptyValue($orderArray['total_weight_'.$i]) && isEmptyValue($orderArray['price_per_weight_'.$i])) {
										continue;
									}
								?>
								<div class="rTableRow">
									<div class="rTableCell" style="width:70%"><?php echo $orderArray['desc_'.$i];?></div>
									<div class="rTableCell" style="width:5%"><?php echo $orderArray['total_weight_'.$i];?></div>
									<div class="rTableCell" style="width:5%"><?php echo $orderArray['price_per_weight_'.$i];?></div>
									<div class="rTableCell" style="width:5%">0</div>
									<div class="rTableCell" style="width:5%">0</div>
									<div class="rTableCell" style="width:10%"><?php echo ($orderArray['total_weight_'.$i] * $orderArray['price_per_weight_'.$i]);?></div>
									</div>
									<?php
								}?>
									<br/><br/>
										<!-- Additional fee and total -->
			                            <div class="rTableRow">
			                              Payment in VietNam
			                              <div class="rTableCell" style="border:0"></div>
			                              <div class="rTableCell" style="border:0"></div>
			                              <div class="rTableCell" style="border:0"></div>
			                              <div class="rTableCell" style="border:0"></div>
			                              <div class="rTableCell"><?php echo $orderArray['fee'];?></div>
			                            </div>
			                            <div class="rTableRow">
			                                 <div class="rTableCell" style="border:0">Total (*)</div>
			                                 <div class="rTableCell" style="border:0"><input name="weight_sum" type="text" id="weight_sum" class="weight_sum" value="<?php
	                                            echo $weightSum;
			                                 ?>" size="10" style="border:0" readonly="readonly"/></div>
			                                 <div class="rTableCell" style="border:0"></div>
			                                 <div class="rTableCell" style="border:0"><input name="unit_sum" type="text" id="unit_sum" class="unit_sum" value="0" size="5" style="border:0" readonly="readonly"/></div>
			                                 <div class="rTableCell" style="border:0"></div>
			                                 <div class="rTableCell" style="border:0"><?php echo $orderArray['total'];?></div>
			                            </div>
								</div>
								<?php
							}
							?>
                    </td>
				</tr>

			</table>
			<table id="tablenoborder" class="nothing">
				<tr>
					<td contenteditable='true'><strong>Weight:(lbs)</strong></td>
					<td><?php echo $weightSum;?></td>
					<td contenteditable='true'><strong>Weight:(kgs)</strong></td>
					<td><?php echo $weightSum*0.4535924;?></td>
					<td><strong>Date:</strong></td>
					<td><?php echo $orderArray["date"];?></td>
					<td><strong>No.oBox:</strong></td>
					<td contenteditable='true'>1</td>
				</tr>
			</table>
			<table id="tablenoborder" class="nothing">
				<tr>
					<td><strong>TOTAL:</strong></td>
					<td contenteditable='true'><p class="solid"><?php echo "$".$orderArray["total"];?></p></td>
				</tr>
			</table>
		</div>
		<div align="center">
			<table id="meta" class="nothing">
				<tr>
					<td>
						<ul>
							<li id="underline">Chúng tôi không chịu trách nhiệm của hàng hóa bị vỡ trong quá
								trình vận chuyển trên chuyến bay.</li>
							<li id="underline">Chúng tôi chỉ hoàn trả lại tổng giá trị hàng hóa 100% nếu như
								quý khách hàng mua bảo hiểm của công ty chúng tôi.</li>
						</ul>
					</td>
				</tr>
				<tr>
				    <td>
                       <strong id="underline">Customer: <?php echo str_repeat("&nbsp;", 100);?> </strong></br>
                       <strong id="underline">Employee: <?php echo str_repeat("&nbsp;", 100); ?></strong>
                    </td>
				</tr>
			</table>
		</div>

</body>
</html>
