<?php
include('login.php'); // Includes Login Script

if (isset($_SESSION['ChangePass'])) {
    if ($_SESSION['ChangePass']) {
        header("location: changePass.php");
    } else if (isset($_SESSION['login_user'])) {
        header("location: validUser.php");
    }
} else if (isset($_SESSION['login_user'])) {
    header("location: validUser.php");
}

require_once 'database/Db.class.php'; // Include DB Class

$db = new Db(); // New Connection to the DB

$guildMembers = $db->query("SELECT * from guildtoons
    JOIN classinfo on guildtoons.ToonClass = classinfo.ClassId
    JOIN genders on guildtoons.ToonGender = genders.GenderId
    JOIN guildranks on guildtoons.ToonRank = guildranks.RankId
    JOIN toonraces on guildtoons.ToonRace = toonraces.RaceId");
?>


<!DOCTYPE html>
<!-- Enable bootstrap 4 theme -->
<script>window.__theme = 'bs4';</script>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="icon" href="wow.png">
        <title>Esoteric - Area-52</title>

        <link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <link href="css/responsive.bootstrap4.min.css" rel="stylesheet">
	<link href="css/custom.css" rel="stylesheet"> 
        
        <!-- Custom Fonts -->
        <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div id="wrapper">
            <?php include('navbar.php'); ?>

            <div class="container" id="page-wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <?php echo $error; ?>
                        <h3 class="page-header text-center text-white">Welcome to Esoteric Area-52</h3>
                    </div> <!-- /.col-lg-12 -->
                </div> <!-- /.row -->

                
                <div class="card">
                    <h5 class="card-header-sm text-center bg-darkgreen text-white">Esoteric Members</h5>
                <div class="card-text">
                    <table id="guildMembers" class="table nowrap table-sm table-hover mb-0 dt-responsive" width="100%">
                        <thead>
                            <tr>
                                <th class="bg-faded text-center">Name</th>
                                <th class="bg-faded text-center">Level</th>
                                <th class="bg-faded text-center">Class</th>
                                <th class="bg-faded text-center">Spec</th>
                                <th class="bg-faded text-center">Rank</th>
                                <th class="bg-faded text-center">Race</th>
                                <th class="bg-faded text-center">Gender</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            for ($i = 0; $i < sizeOf($guildMembers); $i++) { ?>
                                <tr>
                                    <td class="text-center"><?php echo $guildMembers[$i]['ToonName']; ?></td>
                                    <td class="text-center"><?php echo $guildMembers[$i]['ToonLevel']; ?></td>
                                    <td class="text-center"><?php echo $guildMembers[$i]['ClassName']; ?></td>
                                    <td class="text-center"><?php if ($guildMembers[$i]['ToonSpec'] != 'Unknown') echo $guildMembers[$i]['ToonSpec']; ?></td>
                                    <td class="text-center" data-order="<?php echo $guildMembers[$i]['ToonRank']; ?>"><?php echo $guildMembers[$i]['RankName']; ?></td>
                                    <td class="text-center"><?php echo $guildMembers[$i]['RaceName']; ?></td>
                                    <td class="text-center"><?php echo $guildMembers[$i]['GenderName']; ?></td>
                                </tr>
                            <?php 
                            } ?>
                        </tbody>
                    </table>

                </div> <!-- /.panel-body -->
                </div> <!-- /.col-lg-2 -->
            </div> <!-- /#wrapper -->
        </div>


    </body>
    <!-- jQuery -->
    <script src="js/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>
	
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap4.min.js"></script>
    <script src="js/dataTables.responsive.min.js"></script>
    <script src="js/responsive.bootstrap4.min.js"></script>
    
    <script> 
    $(document).ready(function() {
        $('#guildMembers').DataTable( {
            lengthMenu: [ [ 25, 50, 75, -1 ], [ 25, 50, 75, 'All' ] ],
            responsive: true,
            scrollX:true,
            order: [[ 4, "asc" ]]
        } );
    } );
    </script>
</html>