<?php
session_start();// Starting Session
require 'database/Db.class.php'; 
// Establishing Connection with Server by passing server_name, user_id and password as a parameter
$db = new Db(); // New Connection to the DB
// Storing Session
$user_check=$_SESSION['login_user'];
// SQL Query To Fetch Complete Information Of User
$db->bind("Username",$user_check);
$login=$db->row("select Username from siteusers where Username= :Username");
$login_session = $login['Username'];
if(!isset($login_session)){
	header('Location: index.php'); // Redirecting To Home Page
}
?>