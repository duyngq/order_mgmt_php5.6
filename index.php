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

session_start(); /// initialize session
if (!isset($_SESSION['loggedIn']) || (isset($_SESSION['loggedIn']) && !$_SESSION['loggedIn'])) {
    header("location:login.php");
}
?>
<html>
<style>
.dropdown {
    position: relative;
}

.dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    #background-color: #f9f9f9;
    #min-width: 160px;
    #box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
}

.dropdown-content a {
    padding: 12px 16px;
    text-decoration: none;
    display: block;
}

.dropdown-content a:hover {background-color: #f1f1f1}

.dropdown:hover .dropdown-content {
    display: block;
}

.dropdown:hover .dropbtn {
    background-color: #3e8e41;
}
</style>
    <head><title>Orders</title>
        <meta http-equiv="Content-Type" CONTENT="text/html; charset=utf-8" />
        <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
        <style type="text/css">
            tr {
                background-color: transparent;
                color: #000;
                text-decoration: none;
            }
            .tab {
                font-family: verdana,sans-serif;
                font-size: 14px;
                width: 120px;
                white-space: nowrap;
                text-align: center;
                border-style: solid;
                border-color: black;
                border-left-width: 1px;
                border-right-width: 1px;
                border-top-width: 1px;
                border-bottom-width: 0px;
                padding-top: 5px;
                padding-bottom: 5px;
                cursor: pointer;
            }
            .tabhold {
                background-color: white;
                color: black;
            }
            .tabfocus {
                background-color: blue;
                color: white;
            }
            .tabcontent {
                border-width: 1px;
                padding-top: 15px;
                padding-left: 10px;
                padding-right: 10px;
            }
            table.zebra-style {
                font-family:"Lucida Sans Unicode", "Lucida Grande", Sans-Serif;
                text-align:left;
                border:1px solid #ccc;
                margin-bottom:25px;
/*              width:70% */
            }
            table.zebra-style th {
                color: #444;
                font-size: 13px;
                font-weight: normal;
                padding: 10px 8px;
            }
            table.zebra-style td {
                color: #777;
                padding: 8px;
                font-size:13px;
            }
            table.zebra-style tr.odd {
                background:#f2f2f2;
            }
            .table-action {
                text-align: center;
            }
            .table-action-hide {
                opacity: 1;
            }
            .table-action a, .table-action-hide a {
                display: inline-block;
                margin-right: 5px;
                color: #666;
            }
            .table-action a:hover, .table-action-hide a:hover {
                color: #333;
            }
            .table-action a:last-child, .table-action-hide a:last-child {
                margin-right: 0;
            }
            .href-right{
                text-align: right;
            }
            .link {
                float: right;
                display:inline;
            }

        </style>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
        <script type="text/javascript">
            function ChangeColor(tableRow, highLight) {
                if (highLight) {
                    tableRow.style.backgroundColor = '#dcfac9';
                    tableRow.style.cursor='pointer';
                } else {
                    tableRow.style.backgroundColor = 'white';
                    tableRow.style.cursor='default';
                }
            }

            function DoNav(theUrl) {
                document.location.href = theUrl;
            }
            function ManageTabPanelDisplay() {
                //
                // Between the parenthesis, list the id's of the div's that
                //     will be affected when tabs are clicked. List in any
                //     order. Put the id's in single quotes (apostrophes)
                //     and separate them with a comma -- all one line.
                //
                var idlist = new Array('tab1focus','tab2focus','tab1ready','tab2ready','content1','content2');

                // No other customizations are necessary.
                if(arguments.length < 1) { return; }
                for(var i = 0; i < idlist.length; i++) {
                    var block = false;
                    for(var ii = 0; ii < arguments.length; ii++) {
                        if(idlist[i] == arguments[ii]) {
                            block = true;
                            break;
                        }
                    }
                    if(block) {
                        document.getElementById(idlist[i]).style.display = "block";
                    } else {
                        document.getElementById(idlist[i]).style.display = "none";
                    }
                }
            }
            /* When the user clicks on the button,
            toggle between hiding and showing the dropdown content */
            function myFunction() {
                document.getElementById("myDropdown").classList.toggle("show");
            }

            // Close the dropdown if the user clicks outside of it
            window.onclick = function(event) {
              if (!event.target.matches('.dropbtn')) {

                var dropdowns = document.getElementsByClassName("dropdown-content");
                var i;
                for (i = 0; i < dropdowns.length; i++) {
                  var openDropdown = dropdowns[i];
                  if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                  }
                }
              }
            }
        </script>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                // Delete row in a table
                jQuery('.delete-row').click(function(){
                  var c = confirm("Continue delete?");
                  if(c) {
                	var $row = jQuery(this).closest('tr'),
                	orderId = $row.find("td:nth-child(1)").text();
//                     $row.fadeOut(function(){
                        //get selected data here, invoke delete and remove also
                        var dataString = 'orderid='+ orderId;
						$.ajax({
	        				url: 'deleteorder.php',                  //the script to call to get data
	        				type: "POST",
	        				data: {'orderId': orderId},
	        				success: function(data){
		        				if(data.toLowerCase() == "yes") {
		        					$row.fadeOut().remove();
		        				} else {
			        				alert("can't delete the row")
		        				}
	        	             }
						});
//                     });
                    return false;
                  }
                });

                // Show aciton upon row hover
                jQuery('.table tr').hover(function(){
                  jQuery(this).find('.table-action-hide').animate({opacity: 1});
                },function(){
                  jQuery(this).find('.table-action-hide').animate({opacity: 0});
                });
            });
        </script>
    </head>
    <body>
    <center>
        <table border="1" width="1024px" RULES=NONE FRAME=BOX>
            <tr>
                <td colspan="4"><div align="right">
                        <p>Welcome, Saigonair Cargo!  <a href="logout.php">Log out</a>
                <?php
                    if ( isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1) {
                    	echo "<a href=\"edituser.php\">Change pass</a><a href=\"adduser.php\">Add user</a></p>";
                    }
                ?>
                        <p align="left"><strong>ORDER</strong></p>
                        <p align="left">Date: <?php date_default_timezone_set('Asia/Bangkok');
echo date('d/m/Y'); ?> - Time: <?php echo date('H:i'); ?> </p>
                        <p align="left">
                            <label>
                                <input type="submit" name="Submit" value="Add Shipment" onClick="window.location.href='addorder.php'"/>
                            </label>
                            <label>
                                <input type="submit" name="Search" value="Search Shipment" onClick="window.location.href='searchorder.php'"/>
                            </label>
                        </p>
                        <p align="left">&nbsp;</p>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div id="tab1focus" class="tab tabfocus" style="display:block;">
                        Processing Order
                    </div>
                    <div id="tab1ready" class="tab tabhold" style="display:none;">
                        <!-- Between the parenthesis, provide a list of ids that are to
                             be visible when this tab is clicked. The ids are between
                             single quotes (apostrophes) and separated with a comma. -->
                        <span onclick="ManageTabPanelDisplay('tab1focus','tab2ready','content1')">Processing Order</span>
                    </div>
                </td><td width="20"> </td><td>
                    <div id="tab2focus" class="tab tabfocus" style="display:none;">
                        Shipped Order
                    </div>
                    <div id="tab2ready" class="tab tabhold" style="display:block;">
                        <!-- Between the parenthesis, provide a list of ids that are to
                             be visible when this tab is clicked. The ids are between
                             single quotes (apostrophes) and separated with a comma. -->
                        <span onclick="ManageTabPanelDisplay('tab1ready','tab2focus','content2')">Shipped Order</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <div id="content1" class="tabcontent" style="display:block;">
                        <table width="100%" border="1" align="right"  class="table">
                            <tr>
                            	<td><strong>Order ID </strong></td>
                                <td><strong>Order date </strong></td>
                                <td><strong>Sender Name </strong></td>
                                <td><strong>Sender Phone</strong></td>
                                <td><strong>Sender Address</strong></td>
                                <td><strong>Receiver Name </strong></td>
                                <td><strong>Receiver Phone</strong></td>
                                <td><strong>Receiver Address</strong></td>
                                <td><strong>Total Weight</strong></td>
                                <td><strong>Total Amount</strong></td>
                            </tr>
                            <?php
                            include_once 'dbconn.php';
                            global $connection;
                            ob_start();
                            //show all customers and their info
                            if ( isset($_SESSION['user_id']) && ($_SESSION['user_id'] == 1 ||$_SESSION['user_id'] == 5 || $_SESSION['username'] == 'khoa')) { // apply full role with user khoa - id = 5
                                $getAllOrdersQuery = "SELECT * FROM orders";// ORDER BY date DESC";
                            } else {
                            	$getAllOrdersQuery = "SELECT * FROM orders WHERE user_id=".$_SESSION['user_id'];// ORDER BY date DESC";
                            }

                            $getAllOrdersQuery.=" ORDER BY id";
                            $allOrdersResult = mysqli_query($connection, $getAllOrdersQuery) or die(mysqli_error() . "Can not retrieve Orders data");
                            $shippedList = array();
                            $shippingList = array();
                            while ($order = mysqli_fetch_array($allOrdersResult)) {
                            	$getSendCustomerQuery = "SELECT * FROM sendcustomers WHERE id=" . $order["send_cust_id"];
                            	$sendCustomerResult = mysqli_query($connection, $getSendCustomerQuery) or die(mysqli_error() . "Can not retrieve Send Customers data");

                            	$getReceiverCustomerQuery = "SELECT * FROM recvcustomers WHERE id=" . $order["recv_cust_id"];
                            	$receiverCustomerResult = mysqli_query($connection, $getReceiverCustomerQuery) or die(mysqli_error() . "Can not retrieve Receiver Customers data");
                                $orderDate = $order['date'];
					            $dates = explode ("-",$order['date']);
					            if (count($dates) > 1) {
					               $orderDate= $dates[2]."/".$dates[1]."/".$dates[0];
					            }
                                if ($order['status'] == 1) {
                                    array_push($shippedList, $orderDate);
                                    array_push($shippedList, $order['user_id']); // temp move to user id.it should be user name
                                    array_push($shippedList, $order['id']);
                                    while ($sender = mysqli_fetch_array($sendCustomerResult)) {
	                                    array_push($shippedList, $sender['cust_name']);
	                                    array_push($shippedList, $sender['phone']);
	                                    array_push($shippedList, $sender['address']);
                                    }
                                    while ($receiver = mysqli_fetch_array($receiverCustomerResult)) {
                                    	array_push($shippedList, $receiver['cust_name']);
                                    	array_push($shippedList, $receiver['phone']);
                                    	array_push($shippedList, $receiver['address']);
                                    }
                                    array_push($shippedList, $order['weight']);
                                    array_push($shippedList, $order['total']);
                                } else {
                                    array_push($shippingList, $orderDate);
                                    array_push($shippingList, $order['user_id']);
                                    array_push($shippingList, $order['id']);
                                	while ($sender = mysqli_fetch_array($sendCustomerResult)) {
	                                    array_push($shippingList, $sender['cust_name']);
	                                    array_push($shippingList, $sender['phone']);
	                                    array_push($shippingList, $sender['address']);
                                    }
                                    while ($receiver = mysqli_fetch_array($receiverCustomerResult)) {
                                    	array_push($shippingList, $receiver['cust_name']);
                                    	array_push($shippingList, $receiver['phone']);
                                    	array_push($shippingList, $receiver['address']);
                                    }
                                    array_push($shippingList, $order['weight']);
                                    array_push($shippingList, $order['total']);
                                }
                            }
                            $totalWeight = 0;
                            $totalAmount = 0;
                            for ($index = 0; $index < (count($shippingList) / 11); $index++) {
                                $shippingIndex = $index * 11;
                                $totalWeight += $shippingList[$shippingIndex + 9];
                                $totalAmount += $shippingList[$shippingIndex + 10];
                                ?>
                                <tr onMouseOver="ChangeColor(this, true);" onMouseOut="ChangeColor(this, false);" onClick="DoNav('orderdetails.php?tr=<?php
                            $custId = base64_encode($shippingList[$shippingIndex + 2]);
                            $pos1 = rand(0, 25);
                            $pos2 = rand(26, 51);
                            $a_z = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
                            $randomLetter1 = $a_z[$pos1];
                            $randomLetter2 = $a_z[$pos2];
                            echo substr_replace($custId, $randomLetter1 . $randomLetter2, 1, 0);
                                ?>')">
                                	<td><?php //print sender
                                     echo $shippingList[$shippingIndex + 2]?></td>
                                    <td><?php
                                    echo $shippingList[$shippingIndex];
//                                     $date_last_entered = $shippedList[$shippedIndex];
//                                     $date_time = explode(" ", $date_last_entered);
//                                     echo trim($date_time[0]);
                                    ?></td>
                                    <td><?php //print sender
                                     echo $shippingList[$shippingIndex + 3]?></td>
                                    <td><?php echo $shippingList[$shippingIndex + 4] ?></td>
                                    <td><?php echo $shippingList[$shippingIndex + 5] ?></td>
                                    <td><?php //print receiver
                                      echo $shippingList[$shippingIndex + 6]?></td>
                                    <td><?php echo $shippingList[$shippingIndex + 7] ?></td>
                                    <td><?php echo $shippingList[$shippingIndex + 8] ?></td>
                                    <td><?php echo $shippingList[$shippingIndex + 9] ?></td>
                                    <td><?php echo $shippingList[$shippingIndex + 10] ?>
                                        <span class="link"><!-- a href="#" class="href-right table-action-hide">
                                                <i class="fa fa-pencil"></i>
                                            </a-->
                                            <a class="href-right delete-row table-action-hide">
                                                <i class="fa fa-trash-o href-right"></i>
                                            </a></span>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                            <tr border="0">
                            	<td colspan=8>Total (*): </td>
                            	<td><?php echo $totalWeight; ?></td>
                            	<td><?php echo $totalAmount; ?></td>
                            </tr>
                        </table>
                    </div>
                    <div id="content2" class="tabcontent" style="display:none;">
                        <table width="100%" border="1" align="right">
                            <tr>
                            	<td><strong>Order ID </strong></td>
                                <td><strong>Order date </strong></td>
                                <td><strong>Sender Name </strong></td>
                                <td><strong>Sender Phone</strong></td>
                                <td><strong>Sender Address</strong></td>
                                <td><strong>Receiver Name </strong></td>
                                <td><strong>Receiver Phone</strong></td>
                                <td><strong>Receiver Address</strong></td>
                                <td><strong>Total Weight</strong></td>
                                <td><strong>Total Amount</strong></td>
                            </tr>

                            <?php
                            $totalShippedWeight = 0;
                            $totalShippedAmount = 0;
                            for ($index = 0; $index < (count($shippedList) / 11); $index++) {
                                $shippedIndex = $index * 11;
                                $totalShippedWeight += $shippedList[$shippedIndex + 9];
                                $totalShippedAmount += $shippedList[$shippedIndex + 10];
                                ?>
                                <tr onMouseOver="ChangeColor(this, true);" onMouseOut="ChangeColor(this, false);" onClick="DoNav('orderdetails.php?tr=<?php
                            $orderId = base64_encode($shippedList[$shippedIndex + 2]);
                            $pos1 = rand(0, 25);
                            $pos2 = rand(26, 51);
                            $a_z = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
                            $randomLetter1 = $a_z[$pos1];
                            $randomLetter2 = $a_z[$pos2];
                            echo substr_replace($orderId, $randomLetter1 . $randomLetter2, 1, 0);
                            //echo $orderIdEncrypted;
                                ?>')">
                                	<td><?php //print sender
                                     echo $shippedList[$shippedIndex + 2]?></td>
                                    <td><?php
                                    	echo $shippedList[$shippedIndex];
//                                     $date_last_entered = $shippedList[$shippedIndex];
//                                     $date_time = explode(" ", $date_last_entered);
//                                     echo trim($date_time[0]);
                                    ?></td>
                                    <td><?php echo $shippedList[$shippedIndex + 3] ?></td>
                                    <td><?php echo $shippedList[$shippedIndex + 4] ?></td>
                                    <td><?php echo $shippedList[$shippedIndex + 5] ?></td>
                                    <td><?php echo $shippedList[$shippedIndex + 6] ?></td>
                                    <td><?php echo $shippedList[$shippedIndex + 7] ?></td>
                                    <td><?php echo $shippedList[$shippedIndex + 8] ?></td>
                                    <td><?php echo $shippedList[$shippedIndex + 9] ?>
                                    <td><?php echo $shippedList[$shippedIndex + 10] ?>
                                        <span class="link"><!-- a href="#" class="href-right table-action-hide">
                                                <i class="fa fa-pencil"></i>
                                            </a-->
                                            <a class="href-right delete-row table-action-hide">
                                                <i class="fa fa-trash-o href-right"></i>
                                            </a></span>
                                    </td>
                                </tr>
                                <?php
                            }
                            mysqli_close($connection);
                            ob_end_flush();
                            ?>
                            <tr border="0">
                            	<td colspan=8>Total (*): </td>
                                    <td><?php echo $totalShippedWeight; ?></td>
                                    <td><?php echo $totalShippedAmount;?></td>
                              </tr>
                        </table>
                    </div>
                </td></tr>
        </table>
    </center>
</body>
</html>