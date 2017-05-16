<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="icon" href="wow.png">
        <title>Guild Roster</title>

        <!-- Bootstrap Core CSS -->
        <link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/dataTables.bootstrap4.min.css" rel="stylesheet">
	<link href="css/custom.css" rel="stylesheet"> 
        
    </head>

    <?php
    $queryMains = false;
    $queryAlts = false;
    $queryAFK = false;

    $url = "$_SERVER[REQUEST_URI]";
    $url = parse_url($url);
    if (isSet($url['query'])) {
        $url = explode('&', $url['query']);
        for ($x = 0; $x < sizeOf($url); $x++) {
            $parsedURL = explode('=', $url[$x]);
            if ($parsedURL[1] === 'Mains')
                $queryMains = true;
            else if ($parsedURL[1] === 'Alts')
                $queryAlts = true;
            else if ($parsedURL[1] === 'Afk')
                $queryAFK = true;
        }
    } else
        $queryMains = true; // Default mains on or we have nothing to display
    ?>


    <body>
        <div id="wrapper">
            <?php include('navbar.php'); ?>

            <?php
            require 'database/Db.class.php'; // Include DB Class

            $db = new Db(); // New Connection to the DB


            if ($queryMains && $queryAlts && $queryAFK) {
                $toons = $db->query("SELECT * FROM toons
			JOIN `raidteams` on toons.RaidTeamId = raidteams.RaidTeamId
			JOIN `roles` on toons.RoleId = roles.RoleId
			JOIN `toonclass` on toons.ToonId = toonclass.ToonId
			JOIN `classinfo` on toonclass.ClassId = classinfo.ClassId");
            } else if ($queryMains && $queryAlts) {
                $db->bindMore(array("RaidTeamName" => "Mains", "RaidTeamName2" => "Alts"));
                $toons = $db->query("
			SELECT * FROM toons 
			JOIN `raidteams` on toons.RaidTeamId = raidteams.RaidTeamId
			JOIN `roles` on toons.RoleId = roles.RoleId
			JOIN `toonclass` on toons.ToonId = toonclass.ToonId
			JOIN `classinfo` on toonclass.ClassId = classinfo.ClassId
			WHERE `RaidTeamName` = :RaidTeamName OR `RaidTeamName` = :RaidTeamName2");
            } else if ($queryMains && $queryAFK) {
                $db->bindMore(array("RaidTeamName" => "Mains", "RaidTeamName2" => "Afk"));
                $toons = $db->query("
			SELECT * FROM toons 
			JOIN `raidteams` on toons.RaidTeamId = raidteams.RaidTeamId
			JOIN `roles` on toons.RoleId = roles.RoleId
			JOIN `toonclass` on toons.ToonId = toonclass.ToonId
			JOIN `classinfo` on toonclass.ClassId = classinfo.ClassId
			WHERE `RaidTeamName` = :RaidTeamName OR `RaidTeamName` = :RaidTeamName2");
            } else if ($queryAlts && $queryAFK) {
                $db->bindMore(array("RaidTeamName" => "Alts", "RaidTeamName2" => "Afk"));
                $toons = $db->query("
			SELECT * FROM toons 
			JOIN `raidteams` on toons.RaidTeamId = raidteams.RaidTeamId
			JOIN `roles` on toons.RoleId = roles.RoleId
			JOIN `toonclass` on toons.ToonId = toonclass.ToonId
			JOIN `classinfo` on toonclass.ClassId = classinfo.ClassId
			WHERE `RaidTeamName` = :RaidTeamName OR `RaidTeamName` = :RaidTeamName2");
            } else if ($queryMains) {
                $db->bind("RaidTeamName", "Mains");
                $toons = $db->query("
			SELECT * FROM toons 
			JOIN `raidteams` on toons.RaidTeamId = raidteams.RaidTeamId
			JOIN `roles` on toons.RoleId = roles.RoleId
			JOIN `toonclass` on toons.ToonId = toonclass.ToonId
			JOIN `classinfo` on toonclass.ClassId = classinfo.ClassId
			WHERE `RaidTeamName` = :RaidTeamName");
            } else if ($queryAlts) {
                $db->bind("RaidTeamName", "Alts");
                $toons = $db->query("
			SELECT * FROM toons 
			JOIN `raidteams` on toons.RaidTeamId = raidteams.RaidTeamId
			JOIN `roles` on toons.RoleId = roles.RoleId
			JOIN `toonclass` on toons.ToonId = toonclass.ToonId
			JOIN `classinfo` on toonclass.ClassId = classinfo.ClassId
			WHERE `RaidTeamName` = :RaidTeamName");
            } else if ($queryAFK) {
                $db->bind("RaidTeamName", "Afk");
                $toons = $db->query("
			SELECT * FROM toons 
			JOIN `raidteams` on toons.RaidTeamId = raidteams.RaidTeamId
			JOIN `roles` on toons.RoleId = roles.RoleId
			JOIN `toonclass` on toons.ToonId = toonclass.ToonId
			JOIN `classinfo` on toonclass.ClassId = classinfo.ClassId
			WHERE `RaidTeamName` = :RaidTeamName");
            }
            ?>

            <div class="container" id="page-wrapper">
                
                <div class="row">
                    <div class="col-lg-12">
                        <h3 class="page-header text-center text-white"><?php echo ('Roster of Esoteric'); ?></h3>
                    </div> <!-- /.col-lg-12 -->
                </div> <!-- /.row -->
                
                
                <div class="card-deck">
                    <div class="card">
                        <h5 class="card-header-xsm text-center bg-darkgreen text-white">Armor Types</h5>
                        <div class="card-text">
                            <table class="table table-sm table-hover mb-0" cellspacing="0" width="100%">
                                <colgroup>
                                    <col span="1" style="width: 80%;">
                                    <col span="1" style="width: 20%;">
                                </colgroup>
                                <tbody>
                                    <tr>
                                        <td class="text-center"> Cloth </td>
                                        <td class="text-center">
                                            <?php
                                            $count = 0;
                                            for ($i = 0; $i < sizeOf($toons); $i++) {
                                                if ($toons[$i]['ArmorType'] === 'Cloth')
                                                    $count += 1;
                                            }
                                            echo ($count);
                                            ?> 
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"> Leather </td>
                                        <td class="text-center">
                                            <?php
                                            $count = 0;
                                            for ($i = 0; $i < sizeOf($toons); $i++) {
                                                if ($toons[$i]['ArmorType'] === 'Leather')
                                                    $count += 1;
                                            }
                                            echo ($count);
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"> Mail </td>
                                        <td class="text-center">
                                            <?php
                                            $count = 0;
                                            for ($i = 0; $i < sizeOf($toons); $i++) {
                                                if ($toons[$i]['ArmorType'] === 'Mail')
                                                    $count += 1;
                                            }
                                            echo ($count);
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"> Plate </td>
                                        <td class="text-center">
                                            <?php
                                            $count = 0;
                                            for ($i = 0; $i < sizeOf($toons); $i++) {
                                                if ($toons[$i]['ArmorType'] === 'Plate')
                                                    $count += 1;
                                            }
                                            echo ($count);
                                            ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Armor Type Card -->

                    <div class="card">
                        <h5 class="card-header-xsm text-center bg-darkgreen text-white">
                            Raid Composition (<?php
                            $countTanks = 0;
                            $countRanged = 0;
                            $countHealers = 0;
                            $countMelee = 0;
                            for ($i = 0; $i < sizeOf($toons); $i ++) {
                                if ($toons[$i]['RoleName'] === 'Ranged')
                                    $countRanged ++;
                                else if ($toons[$i]['RoleName'] === 'Healer')
                                    $countHealers ++;
                                else if ($toons[$i]['RoleName'] === 'Tank')
                                    $countTanks ++;
                                else if ($toons[$i]['RoleName'] === 'Melee')
                                    $countMelee ++;
                            }
                            echo ($countRanged + $countHealers + $countTanks + $countMelee);
                            ?>)
                        </h5>
                        <div class="card-text">
                            <table class="table table-sm table-hover mb-0" cellspacing="0" width="100%">
                                <colgroup>
                                    <col span="1" style="width: 80%;">
                                    <col span="1" style="width: 20%;">
                                </colgroup>
                                <tbody>
                                    <tr>
                                        <td class="text-center">Tanks</td>
                                        <td class="text-center"><?php echo $countTanks; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">Healers</td>
                                        <td class="text-center"><?php echo $countHealers; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">Ranged</td>
                                        <td class="text-center"><?php echo $countRanged; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">Melee</td>
                                        <td class="text-center"><?php echo $countMelee; ?></td>
                                    </tr>	
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Raid Composition Card -->

                    <div class="card">
                        <h5 class="card-header-xsm text-center bg-darkgreen text-white">Tier Tokens</h5>
                        <div class="card-text">
                            <table class="table table-sm table-hover mb-0" width="100%">
                                <colgroup>
                                    <col span="1" style="width: 80%;">
                                    <col span="1" style="width: 20%;">
                                </colgroup>
                                <tbody>
                                    <tr>
                                        <td class="text-center"> Conqueror </td>
                                        <td class="text-center">
                                            <?php
                                            $count = 0;
                                            for ($i = 0; $i < sizeOf($toons); $i++) {
                                                if ($toons[$i]['TierToken'] === 'Conqueror')
                                                    $count += 1;
                                            }
                                            echo ($count);
                                            ?>
                                    </tr>
                                    <tr>
                                        <td class="text-center"> Vanquisher </td>
                                        <td class="text-center">
                                            <?php
                                            $count = 0;
                                            for ($i = 0; $i < sizeOf($toons); $i++) {
                                            if ($toons[$i]['TierToken'] === 'Vanquisher')
                                                $count += 1;
                                            }
                                            echo ($count);
                                            ?>  
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"> Protector </td>
                                        <td class="text-center">
                                            <?php
                                            $count = 0;
                                            for ($i = 0; $i < sizeOf($toons); $i++) {
                                            if ($toons[$i]['TierToken'] === 'Protector')
                                                $count += 1;
                                            }
                                            echo ($count);
                                            ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Tier Token Card -->
                </div>
                <!-- Card Deck -->
                
                <div class="col-xs-12" style="height:0px;"></div>                

                
                <div class="card-columns-3-lg">
                    <?php 
                    $count = 0;
                    for ($i = 0; $i < sizeOf($toons); $i ++) {
                        if ($toons[$i]['RoleName'] === 'Tank')
                            $count ++;
                    } ?>
                    <div class="card">
                        <h5 class="card-header-sm text-center bg-darkgreen text-white">Tanks (<?php echo ($count); ?>)</h5>
                        <div class="card-text">
                            <table class="table table-sm table-hover mb-0" width="100%">
                                <colgroup>
                                    <col span="1" style="width: 60%;">
                                    <col span="1" style="width: 40%;">
                                </colgroup>
                                <tbody>
                                    <?php
                                    for ($i = 0; $i < sizeOf($toons); $i++) {
                                        $class = 'bg-' . strtolower(str_replace(' ', '', $toons[$i]['ClassName']));
                                        if ($toons[$i]['RoleName'] === 'Tank') { ?>
                                            <tr>
                                                <td class="text-center"><?php echo $toons[$i]['ToonName']; ?></td>
                                                <td class="text-center <?php echo $class; ?>"> <?php echo $toons[$i]['ClassName']; ?></td>
                                            </tr>
                                        <?php 
                                        }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- ./tanks card -->

                    <?php 
                    $count = 0;
                    for ($i = 0; $i < sizeOf($toons); $i ++) {
                        if ($toons[$i]['RoleName'] === 'Melee')
                            $count ++;
                    } ?>
                    <div class="card">
                        <h5 class="card-header-sm text-center bg-darkgreen text-white">Melee (<?php echo ($count); ?>)</h5>
                        <div class="card-text">
                            <table class="table table-sm table-hover mb-0">
                                <colgroup>
                                    <col span="1" style="width: 60%;">
                                    <col span="1" style="width: 40%;">
                                </colgroup>
                                <tbody>
                                    <?php
                                    for ($i = 0; $i < sizeOf($toons); $i++) {
                                        $class = 'bg-' . strtolower(str_replace(' ', '', $toons[$i]['ClassName']));
                                        if ($toons[$i]['RoleName'] === 'Melee') { ?>
                                            <tr>
                                                <td class="text-center"><?php echo $toons[$i]['ToonName']; ?></td>
                                                <td class="text-center <?php echo $class; ?>"> <?php echo $toons[$i]['ClassName']; ?></td>
                                            </tr>
                                        <?php 
                                        }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- ./Melee card -->

                    <div class="col-xs-12" style="height:0px;"></div>  <!-- Temporary to force cards over in Chrome -->


                    <?php 
                    $count = 0;
                    for ($i = 0; $i < sizeOf($toons); $i ++) {
                        if ($toons[$i]['RoleName'] === 'Healer')
                            $count ++;
                    } ?>
                    <div class="card">
                        <h5 class="card-header-sm text-center bg-darkgreen text-white">Healers (<?php echo ($count); ?>)</h5>
                        <div class="card-text">
                            <table class="table table-sm table-hover mb-0">
                                <colgroup>
                                    <col span="1" style="width: 60%;">
                                    <col span="1" style="width: 40%;">
                                </colgroup>
                                <tbody>
                                    <?php
                                    for ($i = 0; $i < sizeOf($toons); $i++) {
                                        $class = 'bg-' . strtolower(str_replace(' ', '', $toons[$i]['ClassName']));
                                        if ($toons[$i]['RoleName'] === 'Healer') { ?>
                                            <tr>
                                                <td class="text-center"><?php echo $toons[$i]['ToonName']; ?></td>
                                                <td class="text-center <?php echo $class; ?>"> <?php echo $toons[$i]['ClassName']; ?></td>
                                            </tr>
                                        <?php 
                                        }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- ./Healer cards -->

                    <?php
                    $count = 0;
                    for ($i = 0; $i < sizeOf($toons); $i ++) {
                        if ($toons[$i]['RoleName'] === 'Ranged')
                            $count ++;
                    } ?>
                    <div class="card">
                        <h5 class="card-header-sm text-center bg-darkgreen text-white">Ranged (<?php echo ($count); ?>)</h5>
                        <div class="card-text">
                            <table class="table table-sm table-hover mb-0">
                                <colgroup>
                                    <col span="1" style="width: 60%;">
                                    <col span="1" style="width: 40%;">
                                </colgroup>
                                <tbody>
                                    <?php
                                    for ($i = 0; $i < sizeOf($toons); $i++) {
                                        $class = 'bg-' . strtolower(str_replace(' ', '', $toons[$i]['ClassName']));
                                        if ($toons[$i]['RoleName'] === 'Ranged') { ?>
                                            <tr>
                                                <td class="text-center"><?php echo $toons[$i]['ToonName']; ?></td>
                                                <td class="text-center <?php echo $class; ?>"> <?php echo $toons[$i]['ClassName']; ?></td>
                                            </tr>
                                        <?php 
                                        }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- ./Ranged Card -->


                    <div class="card">
                        <h5 class="card-header-sm text-center bg-darkgreen text-white">Classes</h5>
                        <div class="card-text">
                            <table class="table table-sm table-hover mb-0" cellspacing="0" cellpadding="0 width="100%">
                                <colgroup>
                                    <col span="1" style="width: 80%;">
                                    <col span="1" style="width: 20%;">
                                </colgroup>
                                <tbody>
                                    <tr>
                                        <td class="text-center" style="background-color:#C79C6E;"> Warrior </td>
                                        <td class="text-center">
                                            <?php
                                            $count = 0;
                                            for ($i = 0; $i < sizeOf($toons); $i++) {
                                                if ($toons[$i]['ClassName'] === 'Warrior')
                                                    $count += 1;
                                            }
                                            echo ($count);
                                            ?>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="background-color:#F58CBA;"> Paladin </td>
                                        <td class="text-center">
                                            <?php
                                            $count = 0;
                                            for ($i = 0; $i < sizeOf($toons); $i++) {
                                                if ($toons[$i]['ClassName'] === 'Paladin')
                                                    $count += 1;
                                            }
                                            echo ($count);
                                            ?>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="background-color:#ABD473;"> Hunter </td>
                                        <td class="text-center">
                                        <?php
                                        $count = 0;
                                        for ($i = 0; $i < sizeOf($toons); $i++) {
                                            if ($toons[$i]['ClassName'] === 'Hunter')
                                                $count += 1;
                                        }
                                        echo ($count);
                                        ?>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="background-color:#FFF569;"> Rogue </td>
                                        <td class="text-center">
                                            <?php
                                            $count = 0;
                                            for ($i = 0; $i < sizeOf($toons); $i++) {
                                                if ($toons[$i]['ClassName'] === 'Rogue')
                                                    $count += 1;
                                            }
                                            echo ($count);
                                            ?>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="background-color:#FFFFFF;"> Priest </td>
                                        <td class="text-center">
                                            <?php
                                            $count = 0;
                                            for ($i = 0; $i < sizeOf($toons); $i++) {
                                                if ($toons[$i]['ClassName'] === 'Priest')
                                                    $count += 1;
                                            }
                                            echo ($count);
                                            ?>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="background-color:#C41F3B;"> Death Knight </td>
                                        <td class="text-center">
                                            <?php
                                            $count = 0;
                                            for ($i = 0; $i < sizeOf($toons); $i++) {
                                                if ($toons[$i]['ClassName'] === 'Death Knight')
                                                    $count += 1;
                                            }
                                            echo ($count);
                                            ?>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="background-color:#0070DE;"> Shaman </td>
                                        <td class="text-center">
                                            <?php
                                            $count = 0;
                                            for ($i = 0; $i < sizeOf($toons); $i++) {
                                                if ($toons[$i]['ClassName'] === 'Shaman')
                                                    $count += 1;
                                            }
                                            echo ($count);
                                            ?>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="background-color:#69CCF0;"> Mage </td>
                                        <td class="text-center">
                                            <?php
                                            $count = 0;
                                            for ($i = 0; $i < sizeOf($toons); $i++) {
                                                if ($toons[$i]['ClassName'] === 'Mage')
                                                    $count += 1;
                                            }
                                            echo ($count);
                                            ?>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="background-color:#9482C9;"> Warlock </td>
                                        <td class="text-center">
                                            <?php
                                            $count = 0;
                                            for ($i = 0; $i < sizeOf($toons); $i++) {
                                                if ($toons[$i]['ClassName'] === 'Warlock')
                                                    $count += 1;
                                            }
                                            echo ($count);
                                            ?>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="background-color:#00FF96;"> Monk </td>
                                        <td class="text-center">
                                            <?php
                                            $count = 0;
                                            for ($i = 0; $i < sizeOf($toons); $i++) {
                                                if ($toons[$i]['ClassName'] === 'Monk')
                                                    $count += 1;
                                            }
                                            echo ($count);
                                            ?>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="background-color:#FF7D0A;"> Druid </td>
                                        <td class="text-center">
                                            <?php
                                            $count = 0;
                                            for ($i = 0; $i < sizeOf($toons); $i++) {
                                                if ($toons[$i]['ClassName'] === 'Druid')
                                                    $count += 1;
                                            }
                                            echo ($count);
                                            ?>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="background-color:#A330C9;"> Demon Hunter </td>
                                        <td class="text-center">
                                        <?php
                                        $count = 0;
                                        for ($i = 0; $i < sizeOf($toons); $i++) {
                                            if ($toons[$i]['ClassName'] === 'Demon Hunter')
                                                $count += 1;
                                        }
                                        echo ($count);
                                        ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div> 
                    </div>
                    <!-- ./Classes card -->



                </div> 
                <!-- /.card-columns -->
                
                
            </div> <!-- /#page-wrapper -->
        </div> <!-- /#wrapper -->

        <script src="js/jquery.min.js"></script>

        <!-- Bootstrap Core JavaScript -->
        <script src="js/bootstrap.min.js"></script>

        <script src="js/jquery.dataTables.min.js"></script>
        <script src="js/dataTables.bootstrap4.min.js"></script>

    </body>
</html>