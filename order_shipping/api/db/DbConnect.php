<?php
error_reporting ( E_ALL );
ini_set ( 'display_errors', 1 );

//Class DbConnect
class DbConnect {
	//Variable to store database link
	protected static $instance;

	//Class constructor
	function __construct () {
	}

	//This method will connect to the database

	public static function setCharsetEncoding () {
		if ( self::$instance == null ) {
			self::getInstance ();
		}
		self::$instance->exec (
			"SET NAMES 'utf8';
			SET character_set_connection=utf8;
			SET character_set_client=utf8;
			SET character_set_results=utf8" );
	}

	public static function getInstance () {
		if ( empty( self::$instance ) ) {
			$db_info = array (
				"db_host" => "127.0.0.1",
				"db_port" => "3306",
				"db_user" => "u437315520_ship",
				"db_pass" => "Mrlonely66.",
				"db_name" => "u437315520_ship",
				"db_charset" => "UTF-8" );
			try {
//				include_once dirname ( __FILE__ ) . '/DbConstants.php';
				self::$instance = new PDO( "mysql:host=" . $db_info[ 'db_host' ] . ';port=' . $db_info[ 'db_port' ] . ';dbname=' . $db_info[ 'db_name' ], $db_info[ 'db_user' ], $db_info[ 'db_pass' ] );
//				self::$instance = new PDO( "mysql:host=" . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME, DB_USERNAME, DB_PASSWORD );
//				self::$instance->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT );
				self::$instance->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
				self::$instance->query ( 'SET NAMES utf8' );
				self::$instance->query ( 'SET CHARACTER SET utf8' );
			} catch ( PDOException $error ) {
				echo $error->getMessage ();
			}
		}
		return self::$instance;
	}

}