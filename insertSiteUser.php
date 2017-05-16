<?php
include('session.php');
include('processInsertSiteUser.php');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="icon" href="wow.png">
	<title>Insert Site User</title>

	<!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">   
	<link href="css/custom.css" rel="stylesheet">  
	
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

        <div class="container" id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="page-header text-center text-white">Insert Site User</h3>
                </div>
                <div class="container col-3">
                    <form class="form" action="" method="POST" role="form">
                        <div class="form-group">
                            <input type="text" class="form-control" id="userName" name="userName" placeholder="User Name">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" id="tempPassword" name="tempPassword" placeholder="Temp Password">
                        </div>
                        <div class="form-group">
                            <input type="submit" name="submit" id="btnSubmit" class="btn btn-inverse btn-success" value="Add User" />
                        </div>
                    </form>
                <?php echo $message; ?>
                </div> <!-- /.col-lg-12 -->
            </div> <!-- /.row -->
        </div>
    </div>
	
	<!-- jQuery -->
    <script src="js/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
    <script src="js/bootstrap.min.js"></script>

</body>
</html>