<?php
include('session.php');
include('processPassChange.php');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="icon" href="wow.png">
	<title>Esoteric - Area-52</title>

	<!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">   
	
	<!-- Custom Fonts -->
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php 
require_once 'database/Db.class.php';

$db = new Db();

?>
	<div id="wrapper">
		<?php include('navbar.php'); ?>

		<div class="container-fluid" id="page-wrapper">
			<div class="row">
				<div class="col-lg-12">
					<h3 class="page-header text-center">Change Password</h3>
				</div>
				<div class="container col-2">
					<form class="form my-2 my-lg-0" action="" method="POST" role="form">
					<div class="form-group mx-sm-3">
						<input type="password" class="form-control" id="currentPassword" name="currentPassword" placeholder="Current Password">
					</div>
					<div class="form-group mx-sm-3">
						<input type="password" class="form-control" id="newPassword" name="newPassword" placeholder="New Password">
					</div>
					<div class="form-group mx-sm-3">
						<input type="password" class="form-control" id="newPassword2" name="newPassword2" placeholder="New Password Again">
					</div>
					<div class="form-group mx-sm-3">
					<button type="submit" name="submit" class="btn btn-primary">Login</button>
					</div>
					</form>
				<?php echo $error; ?>
				</div> <!-- /.col-lg-12 -->
			</div> <!-- /.row -->
		</div>
	</div>

</body>
</html>