<?php

class User {
	private $id;
	private $username;
	private $password;
	private $enabled;
	private $lastLogin;

	/**
	 * @return mixed
	 */
	public function getId () {
		return $this->id;
	}

	/**
	 * @param mixed $id
	 */
	public function setId ( $id ) {
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getUsername () {
		return $this->username;
	}

	/**
	 * @param mixed $username
	 */
	public function setUsername ( $username ) {
		$this->username = $username;
	}

	/**
	 * @return mixed
	 */
	public function getPassword () {
		return $this->password;
	}

	/**
	 * @param mixed $password
	 */
	public function setPassword ( $password ) {
		$this->password = $password;
	}

	/**
	 * @return mixed
	 */
	public function getEnabled () {
		return $this->enabled;
	}

	/**
	 * @param mixed $enabled
	 */
	public function setEnabled ( $enabled ) {
		$this->enabled = $enabled;
	}

	/**
	 * @return mixed
	 */
	public function getLastLogin () {
		return $this->lastLogin;
	}

	/**
	 * @param mixed $lastLogin
	 */
	public function setLastLogin ( $lastLogin ) {
		$this->lastLogin = $lastLogin;
	}

}

?>