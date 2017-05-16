<?php
session_start(); // Starting Session
$error=''; // Variable To Store Error Message
if (isset($_POST['submit'])) {
	if (empty($_POST['username']) || empty($_POST['password'])) {
		$error = "Username or Password is invalid";
	} else {
		require_once 'database/Db.class.php'; 
		// Define $username and $password
		$username=$_POST['username'];
		$password=$_POST['password'];
		// Establishing Connection with Server by passing server_name, user_id and password as a parameter
		$db = new Db(); // New Connection to the DB
		// To protect MySQL injection for Security purpose
		$username = stripslashes($username);
		$password = stripslashes($password);
		// SQL query to fetch information of registerd users and finds user match.
		$db->bind("Username",$username);
		$users=$db->query("SELECT * FROM siteusers 
		WHERE Username = :Username");
		
		if (sizeOf($users) === 1 && password_verify($password, $users[0]['PassHash'])) {
			$_SESSION['login_user']=$username; // Initializing Session
			if ($users[0]['ChangePass']) {
				$_SESSION['ChangePass'] = 1;
				header("location:changePass.php"); // Redirecting To Other Page			
			} else 		
				header("location:validUser.php"); // Redirecting To Other Page
		} else 
			$error = "Username or Password is invalid";
	}
} ?>