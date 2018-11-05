<?php
	error_reporting(E_ALL ^ E_DEPRECATED);
    $mysql_host = "127.0.0.1";
    #$mysql_database = "order_mgmt";
    #$mysql_user = "root";
    #$mysql_password = "";
    $mysql_database = "u437315520_order";
    $mysql_user = "root";
    $mysql_password = "";

    //$connection = mysql_connect($mysql_host, $mysql_user, $mysql_password) or die ("Cannot connect to database server");
    //mysql_select_db($mysql_database, $connection);
    //global $connection;
    $connection = mysqli_connect($mysql_host, $mysql_user, $mysql_password);// or die("Could not connect server to retrieve data");
    if (mysqli_connect_errno())  {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    mysqli_select_db($connection, $mysql_database);


    function begin(){
        global $connection;
    	mysqli_begin_transaction($connection);
    }

    function commit(){
        global $connection;
    	mysqli_commit($connection);
    }

    function rollback(){
        global $connection;
    	mysqli_rollback($connection);
    }
?>
