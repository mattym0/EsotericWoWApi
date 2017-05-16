<?php
$error=''; // Variable To Store Error Message
if (isset($_POST['submit'])) {
	if (empty($_POST['currentPassword']) || empty($_POST['newPassword']) || empty($_POST['newPassword2'])) {
		$error = "Need to fill in all fields";
	} else if ($_POST['newPassword'] != $_POST['newPassword2']){
		$error = "New Password needs to match";
	} else {
		//require 'database/Db.class.php'; 
		// Define $username and $password
		$currentPass=$_POST['currentPassword'];
		$newPass=$_POST['newPassword'];
		$username=$_SESSION['login_user'];
		// To protect MySQL injection for Security purpose
		$currentPass = stripslashes($currentPass);
		$newPass = stripslashes($newPass);
		// SQL query to fetch information of registerd users and finds user match.
		$db->bind("Username",$username);
		$users=$db->query("SELECT * FROM siteusers WHERE Username = :Username");
		if (sizeOf($users) === 1 && password_verify($currentPass, $users[0]['PassHash'])) {
			$hash = password_hash($newPass, PASSWORD_DEFAULT, ['cost' => 12]);
			$db->bindMore(array("Username"=>$username,"Hash"=>$hash));
			$rowsUpdated=$db->query("UPDATE siteusers 
			SET `PassHash` = :Hash, `ChangePass` = 0
			WHERE `Username` = :Username");
			
			if ($rowsUpdated === 1) {
				$_SESSION['ChangePass'] = 0;
				header("location:validUser.php");
			} else
				$error = "Something went wrong in password update to DB";
		} else
			$error = "Current Password Incorrect";
	}
} ?>