<?php

	error_reporting(E_ALL ^ E_DEPRECATED);
    $mysql_host = "127.0.0.1";
    #$mysql_database = "order_mgmt";
    #$mysql_user = "root";
    #$mysql_password = "";
    $mysql_database = "u437315520_order";
    $mysql_user = "u437315520_order";
    $mysql_password = "&otv93afEf8#";

    $connection = mysql_connect($mysql_host, $mysql_user, $mysql_password) or die ("Cannot connect to database server");
    mysql_select_db($mysql_database, $connection);

    function begin(){
    	mysql_query("BEGIN");
    }

    function commit(){
    	mysql_query("COMMIT");
    }

    function rollback(){
    	mysql_query("ROLLBACK");
    }
?>