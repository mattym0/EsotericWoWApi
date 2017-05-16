<?php
$message=''; // Variable To Store Message
if (isset($_POST['submit'])) {
	if (empty($_POST['userName']) || empty($_POST['tempPassword'])) {
		$message = "Need to fill in all fields";
	} else {
		//require 'database/Db.class.php'; 
		// Define $username and $password
		$userName=$_POST['userName'];
		$tempPass=$_POST['tempPassword'];
		
		// To protect MySQL injection for Security purpose
		$userName = stripslashes($userName);
		$tempPass = stripslashes($tempPass);
		// SQL query to fetch information of registerd users and finds user match.
		$db->bindMore(array("Username"=>$userName,"Hash"=>password_hash($tempPass, PASSWORD_DEFAULT, ['cost' => 12])));
		$dbMessage=$db->query("INSERT into siteusers (`UserName`, `PassHash`, `ChangePass`) VALUES(:Username,:Hash,1)");
		if ($dbMessage === 1)
			$message = 'User: '.$userName.' added successfully!';
		else
			$message = "Something went wrong in the DB";
	}
} ?>