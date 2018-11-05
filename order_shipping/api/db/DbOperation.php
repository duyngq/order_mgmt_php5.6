<?php
error_reporting ( E_ALL );
ini_set ( 'display_errors', 1 );

/**
 * Class DbOperation
 * represent to all actions related to DB to make sure we can centralize all rest api to index.php
 */
class DbOperation {
	private $con;

	function __construct () {
		require_once dirname ( __FILE__ ) . '/DbConnect.php';
		$this->con = DbConnect::getInstance ();
		DbConnect::setCharsetEncoding ();
	}

	// Method to check username and password
	public function checkUser ( $name, $pass ) {
		$username = stripslashes ( $name );
		$password = stripslashes ( $pass );
		//TODO: check this why we need this one
		//		$username = mysql_real_escape_string ( $username );
		//		$password = mysql_real_escape_string ( $password ); // validate with plain text input user

		$getUser = "SELECT * FROM users WHERE username=:name";// and password='$password'";
		$stmt = $this->con->prepare ( $getUser );
		$stmt->bindParam ( "name", $username );
		$stmt->execute ();
		if ( $stmt->rowCount () == 1 ) {
			//move to the page user wants later
			//get ID to update last date access
			while ( $user = $stmt->fetchObject ( User::class ) ) {
				$dbPW = $user->password;
				$loginStatus = password_verify ( $password, $dbPW );
				if ( $loginStatus ) { // check hashed coded password with input one if match or not
					session_regenerate_id ();
					$_SESSION[ 'user_id' ] = $user->id;
					$_SESSION[ 'date_last_entered' ] = $user->date_last_entered;
					return $user;
				}
			}

		}
		return null;
	}

	/*
	 * Get all roles for specific user
	 */
	public function getUserRoles ( $userId ) {
		$roleQuery = $this->con->query ( "select id from roles where id=(SELECT ur.role_id FROM users u join userroles ur on u.id=ur.id where u.id=$userId)" );
		$roleQuery->setFetchMode ( PDO::FETCH_OBJ );
		return $roleQuery->fetchAll ();
	}

	/**
	 * Update the time of last success login
	 * @param $userId
	 * @return bool
	 */
	public function updateLastLogin ( $userId ) {
		// Update last successful login
		//get current date
		date_default_timezone_set ( 'Asia/Bangkok' );
		$currentDate = date ( 'd/m/Y H:i' );

		//update to DB
		$updateDateEntered = "UPDATE users SET date_last_entered='$currentDate' WHERE id=?";
		if ( $this->con->prepare ( $updateDateEntered )->execute ( [ $userId ] ) ) {
			return true;
		} else {
			// Ending a session in 30 minutes from the starting time.
			//            $_SESSION['expire'] = time() + (30 * 60);
			return false;
		}
	}

	/**
	 * Get all orders and related customer (sender/receiver)
	 */
	public function getAllOrders () {
		$ordersQuery = $this->con->query ( "select o.*, r.cust_name as r_name, r.phone as r_phone, r.address as r_address, s.cust_name as s_name, s.phone as s_phone, s.address as s_address from orders o join recvcustomers r join sendcustomers s on o.send_cust_id = s.id and o.recv_cust_id = r.id" );
		$orders = $ordersQuery->fetchAll ( PDO::FETCH_OBJ );
		return $orders;
	}

    /**
     * get selected order details base on order id
     *
     * @param $orderId
     * @return array
     */
    public function getSelectedOrder($orderId) {
        $orderQuery ="select o.*, r.cust_name as r_name, r.phone as r_phone, r.address as r_address, s.cust_name as s_name, s.phone as s_phone, s.address as s_address from orders o join recvcustomers r join sendcustomers s on o.send_cust_id = s.id and o.recv_cust_id = r.id where o.id=".$orderId;
        $ordersQuery = $this->con->query ( $orderQuery );
        $orders = $ordersQuery->fetchAll ( PDO::FETCH_OBJ );
        foreach ($orders as $order) {
            $orderDetailsQuery = $this->con->query ( "select * from orderdetails where order_id=".$order->id );
            $order->orderDetails = $orderDetailsQuery->fetchAll ( PDO::FETCH_OBJ );
        }
        return $orders;
    }

    /**
     * Update order
     *
     * @param $order
     */
    public function updateOrder($order) {
        $this->con->beginTransaction ();
        try {
            $deleteOrderDetails = $this->con->prepare ( "DELETE FROM orderdetails WHERE order_id =:id" );
            $deleteOrderDetails->bindParam ( "id", $order->id );
            $deleteOrderDetails->execute ();

            // update shipping
            $updateOrder = $this->con->prepare ( "UPDATE orders SET user_id=:userId, date=:date, status=:status, code=:code, product_desc=:desc, weight=:weight, total=:total, file_name=:fileName WHERE id=:id" );
            $updateOrder->bindParam ( ":id", $order->id );
            $updateOrder->bindParam ( ":date", $order->date );
            $updateOrder->bindParam ( ":userId", $order->userId );
            $updateOrder->bindParam ( ":status", $order->status);
            $updateOrder->bindParam ( ":code", $order->code );
            $updateOrder->bindParam ( ":desc", $order->productDesc );
            $updateOrder->bindParam ( ":weight", $order->weight );
            $updateOrder->bindParam ( ":total", $order->amount );
            $updateOrder->bindParam ( ":fileName", $order->fileNames );
            $updateOrder->execute ();

            // insert order details
            if ( count ( $order->productDetails ) > 0 ) {
                $sql = "insert into orderdetails (order_id, p_desc, weight, unit, price, total) values";
                $orderDetails = "";
                foreach ( $order->productDetails as $product ) {
                    $orderDetails .= "($order->id, '$product->desc', $product->weight, $product->unit, $product->price, $product->total),";
                }
                $stmt = $this->con->prepare ( $sql . substr ( $orderDetails, 0, -1 ) );
                $stmt->execute ();
            }

            $this->con->commit ();
        } catch ( PDOException $e ) {
            $this->con->rollBack ();
            echo '{"error":{"text":' . $e->getMessage () . '}}';
        }
    }

	/**
	 * Add order and update order details except customer datas since they will be update separately
	 * @param $order
	 * @throws Exception
	 */
	public function addOrder ( $order ) {
		$this->con->beginTransaction ();
		try {
			//Query 1: Attempt to insert order
			$sql = "INSERT INTO orders(send_cust_id, recv_cust_id, user_id, status, date, code, product_desc, weight, total, file_name) VALUES (?,?,?,?,?,?,?,?,?,?)"; //VALUES (:sendId, :recvIid, :userId, :status, :date, :code, :productDesc, :weight, :total, :file_name)";
			$stmt = $this->con->prepare ( $sql );
			$stmt->execute ( array ( $order->sendId, $order->recvId, $order->userId, 0, $order->date, 'test', $order->productDesc, $order->weight, $order->amount, $order->fileNames ) );
			//			$stmt->bindParam ("sendId", $order->sendId);
			//			$stmt->bindParam ("recvId", $order->recvId);
			//			$stmt->bindParam ("userId", $order->userId);
			//			$stmt->bindParam ("status", 0, );
			//			$stmt->bindParam ("date", $order->date);
			//			$stmt->bindParam ("code", "test");
			//			$stmt->bindParam ("productDesc", $order->sendId);
			//			$stmt->bindParam ("weight", $order->sendId);
			//			$stmt->bindParam ("total", $order->sendId);
			//			$stmt->bindParam ("fileNames", $order->sendId);

			$orderId = $this->con->lastInsertId ();

			//Query 2: Attempt to update the order details
			if ( count ( $order->productDetails ) > 0 ) {
				$sql = "insert into orderdetails (order_id, p_desc, weight, unit, price, total) values";
				$orderDetails = "";
				foreach ( $order->productDetails as $product ) {
					$orderDetails .= "($orderId, '$product->desc', $product->weight, $product->unit, $product->price, $product->total),";
				}
				$stmt = $this->con->prepare ( $sql . substr ( $orderDetails, 0, -1 ) );
				$stmt->execute ();
			}

			//We've got this far without an exception, so commit the changes.
			$this->con->commit ();
			return $orderId;
		} catch ( Exception $e ) {
			//Our catch block will handle any exceptions that are thrown.
			//Rollback the transaction.
			$this->con->rollBack ();
			throw $e;
		}
	}

	/**
	 * Delete selected order
	 */
	public function deleteOrder ( $orderId ) {
		$this->con->beginTransaction ();
		$delOrderDetailsQuery = "DELETE FROM orderdetails WHERE order_id =:id";
		$delCommentsQuery = "DELETE FROM comments WHERE order_id =:id";
		$delOrdersQuery = "DELETE FROM orders WHERE id=:id";
		try {
			$stmt = $this->con->prepare ( $delOrderDetailsQuery );
			$stmt->bindParam ( "id", $orderId );
			$stmt->execute ();

			$stmt = $this->con->prepare ( $delCommentsQuery );
			$stmt->bindParam ( "id", $orderId );
			$stmt->execute ();

			$stmt = $this->con->prepare ( $delOrdersQuery );
			$stmt->bindParam ( "id", $orderId );
			$stmt->execute ();
			$this->con->commit ();
		} catch ( PDOException $e ) {
			$this->con->rollBack ();
			throw $e;
		}
	}

	/**
	 * get all data for a specific table name
	 *
	 * @param $table
	 */
	public function getData ( $table ) {
		$dataQuery = $this->con->query ( "select * from " . $table );
		$data = $dataQuery->fetchAll ( PDO::FETCH_OBJ );
		return $data;
	}

	/**
	 * Delete data table by id --> TODO: with customer, may be remove the related records from order
	 *
	 * @param $table
	 * @param $id
	 */
	public function deleteById ( $table, $id ) {
		$this->con->beginTransaction ();
		try {
			$stmt = $this->con->prepare ( "DELETE FROM " . $table . " WHERE id =:id" );
			$stmt->bindParam ( "id", $id );
			$stmt->execute ();

			$this->con->commit ();
		} catch ( PDOException $e ) {
			$this->con->rollBack ();
			throw $e;
		}
	}

	/**
	 * Update customer with input value
	 *
	 * @param $table
	 * @param $customer
	 */
	public function updateCustomerById ( $table, $customer ) {
		$this->con->beginTransaction ();
		$updateCustomer = "UPDATE " . $table . " SET cust_name=:name, phone=:phone, address=:address WHERE id=:id";
		try {
			$stmt = $this->con->prepare ( $updateCustomer );
			$stmt->bindParam ( "id", $customer->id );
			$stmt->bindParam ( "name", $customer->name );
			$stmt->bindParam ( "phone", $customer->phone );
			$stmt->bindParam ( "address", $customer->address );
			$stmt->execute ();
			$this->con->commit ();
		} catch ( PDOException $e ) {
			$this->con->rollBack ();
			echo '{"error":{"text":' . $e->getMessage () . '}}';
		}
	}

	/**
	 * Add customer with input value
	 *
	 * @param $table
	 * @param $customer
	 */
	public function addCustomer ( $table, $customer ) {
		$this->con->beginTransaction ();
		$addCustomer = "INSERT INTO " . $table . " (cust_name, phone, address) VALUES(:name, :phone, :address)";
		try {
			$stmt = $this->con->prepare ( $addCustomer );
			$stmt->bindParam ( ":name", $customer->cust_name );
			$stmt->bindParam ( ":phone", $customer->phone );
			$stmt->bindParam ( ":address", $customer->address );
			$stmt->execute ();
			$custId = $this->con->lastInsertId ();
			$this->con->commit ();
			return $custId;
		} catch ( PDOException $e ) {
			$this->con->rollBack ();
			echo '{"error":{"text":' . $e->getMessage () . '}}';
		}
	}


	/////////////////////////
	/// Shipping section
	/////////////////////////
	///
    /**
     * get all shippings with shipping details
     */
	function getAllShippings() {
        $ordersQuery = $this->con->query ( "select sh.* from shippings sh" );
        $orders = $ordersQuery->fetchAll ( PDO::FETCH_OBJ );
        foreach ($orders as $order) {
            $shippingDetailsQuery = $this->con->query ( "select order_id from shippingdetails where shipping_id=".$order->id );
            $shippingDetails = $shippingDetailsQuery->fetchAll ( PDO::FETCH_OBJ );
            $orderIds = array();
            $i = 0;
            foreach ($shippingDetails as $shippingDetail) {
                $orderIds[$i] =  $shippingDetail->order_id;
                $i++;
            }
            $order->orderDetails =  $orderIds;
        }
        return $orders;
    }

    /**
	 * Add new shipping
     *
	 * @param $shipping
	 */
	function addShipping ( $shipping ) {
		$this->con->beginTransaction ();
		$addShipping = "INSERT INTO shippings (date, mawb, hawb, pieces, weight, amount) VALUES(:date, :mawb, :hawb, :pieces, :weight, :amount)";
		try {
			$stmt = $this->con->prepare ( $addShipping );
			$stmt->bindParam ( ":date", $shipping->date );
			$stmt->bindParam ( ":mawb", $shipping->mawb );
			$stmt->bindParam ( ":hawb", $shipping->hawb );
			$stmt->bindParam ( ":pieces", $shipping->pieces );
			$stmt->bindParam ( ":weight", $shipping->weight );
			$stmt->bindParam ( ":amount", $shipping->amount );
			$stmt->execute ();
			$shippingId = $this->con->lastInsertId ();

			//Query 2: Attempt to update the order details
			if ( count ( $shipping->orderDetails ) > 0 ) {
				$this -> addShippingDetails ( $shipping, $shippingId );
//				$sql = "insert into shippingdetails (shipping_id, order_id) values";
//				$orderDetails = "";
//				foreach ( $shipping -> orderDetails as $orderId ) {
//					$orderDetails .= "($shippingId, $orderId),";
//				}
//				$stmt = $this -> con -> prepare ( $sql . substr ( $orderDetails, 0, -1 ) );
//				$stmt -> execute ();
			}
			$this->con->commit ();
			return $shippingId;
		} catch ( PDOException $e ) {
			$this->con->rollBack ();
			echo '{"error":{"text":' . $e->getMessage () . '}}';
		}
	}

	function addShippingDetails ( $shipping, $shippingId ) {
		$sql = "insert into shippingdetails (shipping_id, order_id) values";
		$shippingDetails = "";
		foreach ( $shipping->orderDetails as $orderId ) {
			$shippingDetails .= "($shippingId, $orderId),";
		}
		$stmt = $this->con->prepare ( $sql . substr ( $shippingDetails, 0, -1 ) );
		$stmt->execute ();
	}

	/**
	 * Update shipping
	 *
	 * idea:
	 * 1 - remove all related order before re-adding since doesn't have any clue to know which one will be update
	 * 2 - update shipping and then re-insert shipping details
	 *
	 * @param $shipping
	 */
	function updateShipping ( $shipping ) {
		$this->con->beginTransaction ();
		try {
			$deleteShippingDetails = $this->con->prepare ( "DELETE FROM shippingdetails WHERE shipping_id =:id" );
			$deleteShippingDetails->bindParam ( "id", $shipping->id );
			$deleteShippingDetails->execute ();

			// update shipping
			$updateShipping = $this->con->prepare ( "UPDATE shippings SET date=:date, mawb=:mawb, hawb=:hawb, pieces=:pieces, weight=:weight, amount=:amount WHERE id=:id" );
			$updateShipping->bindParam ( ":id", $shipping->id );
			$updateShipping->bindParam ( ":date", $shipping->date );
			$updateShipping->bindParam ( ":mawb", $shipping->mawb );
			$updateShipping->bindParam ( ":hawb", $shipping->hawb );
			$updateShipping->bindParam ( ":pieces", $shipping->pieces );
			$updateShipping->bindParam ( ":weight", $shipping->weight );
			$updateShipping->bindParam ( ":amount", $shipping->amount );
			$updateShipping->execute ();

			// insert shipping details
			$this->addShippingDetails ( $shipping, $shipping->id );

			$this->con->commit ();
		} catch ( PDOException $e ) {
			$this->con->rollBack ();
			echo '{"error":{"text":' . $e->getMessage () . '}}';
		}
	}

	/**
	 * Delete shipping and shipping details as well
	 *
	 * @param $shippingId
	 */
	function deleteShipping ( $shippingId ) {
		$this->con->beginTransaction ();
		try {
            // TODO: extract to generic function
            // delete shipping details first
            $stmt = $this->con->prepare ( "DELETE FROM shippingdetails WHERE shipping_id=:id" );
            $stmt->bindParam ( "id", $shippingId );
            $stmt->execute ();

		    $stmt = $this->con->prepare ( "DELETE FROM shippings WHERE id =:id" ); // Do that since PHP doesn't allow nested transaction
            $stmt->bindParam ( "id", $shippingId );
            $stmt->execute ();
//			$this->deleteById ( "shippings", $shippingId );
			$this->con->commit ();
		} catch ( PDOException $e ) {
			$this->con->rollBack ();
			throw $e;
		}

	}

	//Method to register a new student
	public function createStudent ( $name, $username, $pass ) {
		if ( !$this->isStudentExists ( $username ) ) {
			$password = md5 ( $pass );
			$apikey = $this->generateApiKey ();
			$stmt = $this->con->prepare ( "INSERT INTO students(name, username, password, api_key) values(?, ?, ?, ?)" );
			$stmt->bind_param ( "ssss", $name, $username, $password, $apikey );
			$result = $stmt->execute ();
			$stmt->close ();
			if ( $result ) {
				return 0;
			} else {
				return 1;
			}
		} else {
			return 2;
		}
	}

	//Method to let a student log in

	private function isStudentExists ( $username ) {
		$stmt = $this->con->prepare ( "SELECT id from students WHERE username = ?" );
		$stmt->bind_param ( "s", $username );
		$stmt->execute ();
		$stmt->store_result ();
		$num_rows = $stmt->num_rows;
		$stmt->close ();
		return $num_rows > 0;
	}


	//method to register a new facultly

	private function generateApiKey () {
		return md5 ( uniqid ( rand (), true ) );
	}

	//method to let a faculty log in

	public function studentLogin ( $username, $pass ) {
		$password = md5 ( $pass );
		$stmt = $this->con->prepare ( "SELECT * FROM students WHERE username=? and password=?" );
		$stmt->bind_param ( "ss", $username, $password );
		$stmt->execute ();
		$stmt->store_result ();
		$num_rows = $stmt->num_rows;
		$stmt->close ();
		return $num_rows > 0;
	}

	//Method to create a new assignment

	public function createFaculty ( $name, $username, $pass, $subject ) {
		if ( !$this->isFacultyExists ( $username ) ) {
			$password = md5 ( $pass );
			$apikey = $this->generateApiKey ();
			$stmt = $this->con->prepare ( "INSERT INTO faculties(name, username, password, subject, api_key) values(?, ?, ?, ?, ?)" );
			$stmt->bind_param ( "sssss", $name, $username, $password, $subject, $apikey );
			$result = $stmt->execute ();
			$stmt->close ();
			if ( $result ) {
				return 0;
			} else {
				return 1;
			}
		} else {
			return 2;
		}
	}

	//Method to update assignment status

	private function isFacultyExists ( $username ) {
		$stmt = $this->con->prepare ( "SELECT id from faculties WHERE username = ?" );
		$stmt->bind_param ( "s", $username );
		$stmt->execute ();
		$stmt->store_result ();
		$num_rows = $stmt->num_rows;
		$stmt->close ();
		return $num_rows > 0;
	}

	//Method to get all the assignments of a particular student

	public function facultyLogin ( $username, $pass ) {
		$password = md5 ( $pass );
		$stmt = $this->con->prepare ( "SELECT * FROM faculties WHERE username=? and password =?" );
		$stmt->bind_param ( "ss", $username, $password );
		$stmt->execute ();
		$stmt->store_result ();
		$num_rows = $stmt->num_rows;
		$stmt->close ();
		return $num_rows > 0;
	}

	//Method to get student details

	public function createAssignment ( $name, $detail, $facultyid, $studentid ) {
		$stmt = $this->con->prepare ( "INSERT INTO assignments (name,details,faculties_id,students_id) VALUES (?,?,?,?)" );
		$stmt->bind_param ( "ssii", $name, $detail, $facultyid, $studentid );
		$result = $stmt->execute ();
		$stmt->close ();
		if ( $result ) {
			return true;
		}
		return false;
	}

	//Method to fetch all students from database

	public function updateAssignment ( $id ) {
		$stmt = $this->con->prepare ( "UPDATE assignments SET completed = 1 WHERE id=?" );
		$stmt->bind_param ( "i", $id );
		$result = $stmt->execute ();
		$stmt->close ();
		if ( $result ) {
			return true;
		}
		return false;
	}

	//Method to get faculy details by username

	public function getAssignments ( $studentid ) {
		$stmt = $this->con->prepare ( "SELECT * FROM assignments WHERE students_id=?" );
		$stmt->bind_param ( "i", $studentid );
		$stmt->execute ();
		$assignments = $stmt->get_result ();
		$stmt->close ();
		return $assignments;
	}

	//Method to get faculty name by id

	public function getStudent ( $username ) {
		$stmt = $this->con->prepare ( "SELECT * FROM students WHERE username=?" );
		$stmt->bind_param ( "s", $username );
		$stmt->execute ();
		$student = $stmt->get_result ()->fetch_assoc ();
		$stmt->close ();
		return $student;
	}

	//Method to check the student username already exist or not

	public function getAllStudents () {
		$stmt = $this->con->prepare ( "SELECT * FROM students" );
		$stmt->execute ();
		$students = $stmt->get_result ();
		$stmt->close ();
		return $students;
	}

	//Method to check the faculty username already exist or not

	public function getFaculty ( $username ) {
		$stmt = $this->con->prepare ( "SELECT * FROM faculties WHERE username=?" );
		$stmt->bind_param ( "s", $username );
		$stmt->execute ();
		$faculty = $stmt->get_result ()->fetch_assoc ();
		$stmt->close ();
		return $faculty;
	}

	//Checking the student is valid or not by api key

	public function getFacultyName ( $id ) {
		$stmt = $this->con->prepare ( "SELECT name FROM faculties WHERE id=?" );
		$stmt->bind_param ( "i", $id );
		$stmt->execute ();
		$faculty = $stmt->get_result ()->fetch_assoc ();
		$stmt->close ();
		return $faculty[ 'name' ];
	}

	//Checking the faculty is valid or not by api key

	public function isValidStudent ( $api_key ) {
		$stmt = $this->con->prepare ( "SELECT id from students WHERE api_key = ?" );
		$stmt->bind_param ( "s", $api_key );
		$stmt->execute ();
		$stmt->store_result ();
		$num_rows = $stmt->num_rows;
		$stmt->close ();
		return $num_rows > 0;
	}

	//Method to generate a unique api key every time

	public function isValidFaculty ( $api_key ) {
		$stmt = $this->con->prepare ( "SELECT id from faculties WHERE api_key=?" );
		$stmt->bind_param ( "s", $api_key );
		$stmt->execute ();
		$stmt->store_result ();
		$num_rows = $stmt->num_rows;
		$stmt->close ();
		return $num_rows > 0;
	}
}

