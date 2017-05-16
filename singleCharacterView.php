
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="icon" href="wow.png">
        <title>Single Character View</title>

        <!-- Bootstrap Core CSS -->
        <link href="css/bootstrap.min.css" rel="stylesheet"> 
        <link href="css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <link href="css/custom.css" rel="stylesheet">  
        <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
    </head>
    <?php
    require 'database/Db.class.php';
    require 'functions/convertAp.php'; // Include class

    $db = new Db();

    $toonName = '';
    $toonSpec = '';

    $url = filter_input(INPUT_SERVER, 'QUERY_STRING');

    if ($url) {
        $url = explode('&', $url);
        for ($x = 0; $x < sizeOf($url); $x++) {
            $parsedURL = explode('=', $url[$x]);
            if ($parsedURL[0] === 'toonName') {
                if ($parsedURL[1] === 'Toon+Name') {
                    $newLoad = true;
                } else {
                    $toonName = rawurldecode($parsedURL[1]);
                }
            } else if ($parsedURL[0] === 'toonSpec') {
                if ($parsedURL[1] === 'Spec+Name') {
                    $newLoad = true;
                } else {
                    $toonSpec = $parsedURL[1];
                }
            }
        }
        if (empty($toonName) || empty($serverName)) {
            $error = true;
        }
    } else {
        $newLoad = true;
    }

    //Pull toons
    $toons = $db->query("SELECT 
	`ToonName`, 
	`Specs`
	FROM `toons` 
	JOIN toonclass on toons.ToonId = toonclass.ToonId 
	JOIN specs on specs.ClassId = toonclass.ClassId
        JOIN `toonmisc` on toonmisc.ToonId = toons.ToonId
        WHERE `ToonLevel` = 110
	ORDER BY toons.ToonName");
    
    if ($toonName && $toonSpec) {
        $header = 'Single Character View for ' . $toonName . ' (' . $toonSpec . ')';
    } else {
        $header = 'Please select a Toon and Spec';
    }
        
    ?>

    <body>
        <div id="wrapper">
            <?php include('navbar.php'); ?>

            <div class="container-lg" id="page-wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <h3 class="page-header text-center text-white"><?php echo $header; ?></h3>
                    </div> 
                    <!-- /.col-lg-12 -->
                </div> 
                <!-- /.row -->
                
                <?php 
                if ($toonName && $toonSpec) { ?>
                    <div class="card-group">
                                                                                                                                   
                        <div class="card">
                            <h6 class="card-header-xsm text-center bg-darkgreen text-white"><?php echo ($toonSpec); ?> Talents</h6>
                            <div class="card-text">
                                <table class="table table-sm table-hover mb-0">
                                    <colgroup>
                                        <col span="1" style="width: 12%;">
                                        <col span="1" style="width: 12%;">
                                        <col span="1" style="width: 76%;">
                                    </colgroup>
                                    <tbody>
                                        <?php
                                        $db->bindMore(array("ToonName" => $toonName, "ToonSpec" => $toonSpec));
                                        $talents = $db->row('SELECT 
                                                            `Tier1`,
                                                            `Tier2`,
                                                            `Tier3`,
                                                            `Tier4`,
                                                            `Tier5`,
                                                            `Tier6`,
                                                            `Tier7`
                                                            FROM `toons` 
                                                            JOIN `talents` on toons.ToonId = talents.ToonId
                                                            WHERE `ToonName` = :ToonName AND `ToonSpec` = :ToonSpec');
                                        $db->bind("ToonName",$toonName);
                                        $class = $db->single('SELECT
                                            `ClassName`
                                            FROM `classinfo`
                                            JOIN `toonclass` on toonclass.ClassId = classinfo.ClassId
                                            JOIN `toons` on toons.ToonId = toonclass.ToonId
                                            WHERE `ToonName` = :ToonName');
                                        if ($talents) {
                                            for ($i = 0; $i < sizeOf($talents); $i++) {
                                                $currentTier = 'Tier' . ($i + 1);
                                                $currentTierTalent = json_decode($talents[$currentTier]);
                                                $iconSource = 'http://media.blizzard.com/wow/icons/56/' . $currentTierTalent->icon . '.jpg';
                                                $wowhead = 'http://www.wowhead.com/spell=' . $currentTierTalent->id;?>
                                                <tr>
                                                    <td class="text-center"><?php 
                                                    if ($class === 'Demon Hunter'){
                                                        if ($i === 0) {
                                                            echo 99;
                                                        } else {
                                                            echo (100 + (2 * ($i-1)));
                                                        }
                                                    } else if ($class === 'Death Knight') {
                                                        if ($i < 3) {
                                                            echo ($i + 56); 
                                                        } else if ($i < 6) {
                                                            echo (($i + 1)*15); 
                                                        } else if ($i === 6) {
                                                            echo 100;
                                                        }
                                                    } else {
                                                        if ($i === 6) {
                                                            echo 100; 
                                                        } else {
                                                            echo (($i + 1)*15); 
                                                        } 
                                                    } ?>
                                                    </td>
                                                    <td><img src="<?php echo $iconSource; ?>" style="width: 70%; height: 40%"></td>
                                                    <td><a href="<?php echo $wowhead; ?>"><?php echo $currentTierTalent->name; ?></td>
                                                </tr>
                                            <?php 
                                            }
                                        } else {
                                            for ($i = 0; $i < 7; $i++) { ?>
                                                <tr>
                                                    <td class="text-center"><?php 
                                                    if ($class === 'Demon Hunter'){
                                                        if ($i === 0) {
                                                            echo 99;
                                                        } else {
                                                            echo (100 + (2 * ($i-1)));
                                                        }
                                                    } else if ($class === 'Death Knight') {
                                                        if ($i < 3) {
                                                            echo ($i + 56); 
                                                        } else if ($i < 6) {
                                                            echo (($i + 1)*15); 
                                                        } else if ($i === 6) {
                                                            echo 100;
                                                        }
                                                    } else {
                                                        if ($i === 6) {
                                                            echo 100; 
                                                        } else {
                                                            echo (($i + 1)*15); 
                                                        } 
                                                    } ?> 
                                                    </td>
                                                    <td class="text-center text-primary" colspan="2">No Data</td>
                                                </tr>
                                            <?php 
                                            }
                                        } ?>
                                    </tbody>
                                </table>
                            </div>                        
                        </div>
                            
                                                    
                        <div class="card">
                            <?php
                            $db->bindMore(array("ToonName" => $toonName, "ToonSpec" => $toonSpec));
                            $enchants = $db->query('SELECT 
                                `ItemSlot`, 
                                `EnchantName`,
                                `EnchantId`
                                FROM `toonitems`
                                JOIN `toons` on toons.ToonId = toonitems.ToonId
                                JOIN `enchants` on enchants.EnchantId = toonitems.Enchant
                                WHERE `ItemSlot` in ("neck","back","finger1","finger2") AND `ToonName` = :ToonName and `ToonSpec` = :ToonSpec');
                            $count = 0;
                            for ($x = 0; $x < sizeof($enchants); $x ++) {
                                if ($enchants[$x]['ItemSlot'] === 'finger1' || $enchants[$x]['ItemSlot'] === 'finger2' || $enchants[$x]['ItemSlot'] === 'back') {
                                    if (strpos($enchants[$x]['EnchantName'],'Binding') !== false) {
                                        $enchants[$x]['Check'] = '<i class="fa fa-check" aria-hidden="true">';
                                        $count += 1;
                                    } else if (strpos($enchants[$x]['EnchantName'],'Word') !== false){
                                        $enchants[$x]['Check'] = '<i class="fa fa-minus" aria-hidden="true">';
                                        $count += 1;
                                    } else if (strpos($enchants[$x]['EnchantName'],'Gift') !== false && $enchants[$x]['ItemSlot'] === 'back') {
                                        $enchants[$x]['Check'] = '<i class="fa fa-minus" aria-hidden="true">';
                                        $count += 1;
                                    } else {
                                        $enchants[$x]['Check'] = '<i class="fa fa-times" aria-hidden="true">';
                                    }
                                } else {
                                    if (strpos($enchants[$x]['EnchantName'],'Deadly') !== false || strpos($enchants[$x]['EnchantName'],'Quick') !== false || strpos($enchants[$x]['EnchantName'],'Master') !== false || strpos($enchants[$x]['EnchantName'],'Versatile') !== false) {
                                        $enchants[$x]['Check'] = '<i class="fa fa-minus" aria-hidden="true">';
                                        $count += 1;
                                    } else if (strpos($enchants[$x]['EnchantName'],'Mark') !== false){
                                        $enchants[$x]['Check'] = '<i class="fa fa-check" aria-hidden="true">';
                                        $count += 1;
                                    } else {
                                        $enchants[$x]['Check'] = '<i class="fa fa-times" aria-hidden="true">';
                                    }
                                }

                            } ?>

                            <h6 class="card-header-xsm text-white text-center <?php if ($count != 4 && $enchants) echo 'bg-danger'; else echo 'bg-darkgreen'; ?>">Enchants (<?php echo $count; ?>/4)</h6>
                            <div class="card-text">
                                <table class="table table-sm table-hover mb-0">
                                    <colgroup>
                                        <col span="1" style="width: 15%;">
                                        <col span="1" style="width: 10%;">
                                        <col span="1" style="width: 75%;">
                                    </colgroup>
                                    <tbody>
                                        <?php 
                                        if ($enchants) { 
                                            $slots = ['neck','back','finger1','finger2'];
                                            for ($x = 0; $x < sizeof($slots); $x++) { ?>
                                                <?php
                                                for ($i = 0; $i < sizeOf($enchants); $i++) { ?>
                                                    <?php 
                                                    if ($enchants[$i]['ItemSlot'] === strtolower($slots[$x])) { ?>
                                                        <tr>
                                                            <td class="text-center text-capitalize"><strong><?php echo $slots[$x]; ?></strong></td>
                                                            <td class="text-center"><?php if ($enchants[$i]['Check']) echo $enchants[$i]['Check'];?> </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan ="2" class="text-center text-primary"><?php echo $enchants[$i]['EnchantName']; ?></td>
                                                        </tr>
                                                    <?php 
                                                    } 
                                                } 
                                            }
                                        } else { ?>
                                            <tr>
                                                <td class="text-primary text-center">No Data</td>
                                            </tr>
                                        <?php 
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="card">
                            <h6 class="card-header-xsm text-center bg-darkgreen text-white">Per Specialization</h6>
                            <?php
                            $gearSlot = $db->column("SELECT `GearSlotName` FROM `gearSlots`");
                            $db->bind("ToonName", $toonName);
                            $specs = $db->row("SELECT  
                                `Spec1`,
                                `Spec2`,
                                `Spec3`,
                                `Spec4`,
                                `ClassName`
                                FROM `specs` 
                                JOIN toonclass on specs.ClassId = toonclass.ClassId
                                JOIN toons on toonclass.ToonId = toons.ToonId
                                JOIN classinfo on classinfo.ClassId = toonclass.ClassId
                                WHERE ToonName = :ToonName"); ?>
                            <div class="card-text">
                                <table class="table table-sm table-hover mb-0">
                                    <colgroup>
                                        <col span="1" style="width: 50%;">
                                        <col span="1" style="width: 50%;">
                                    </colgroup>
                                    <tbody>
                                        <?php
                                        for ($x = 1; $x <= 4; $x ++) {
                                            $averageIlvl = 0;
                                            $mainHandIlvl = 0;
                                            if ($specs['Spec' . $x]) {
                                                $db->bindMore(array("ToonName" => $toonName, "ToonSpec" => $specs['Spec' . $x]));
                                                $artifactLevel = $db->single('
                                                    SELECT `ArtifactLevel`
                                                    FROM `progression`
                                                    JOIN toons on toons.ToonId = progression.ToonId
                                                    WHERE ToonName = :ToonName and ToonSpec = :ToonSpec
                                                    ORDER BY InsertDate DESC limit 1'); 
                                                    $class = 'bg-' . strtolower(str_replace(' ', '', $specs['ClassName'])); ?>
                                                <tr>
                                                    <td class="text-center <?php echo $class; ?>" colspan="2"><strong><?php print ($specs['Spec' . $x]); ?></strong></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-center">
                                                        <?php
                                                        if ($artifactLevel) {
                                                            echo ($artifactLevel.' Traits');
                                                        } else { 
                                                            echo ('No Data');
                                                        } ?>
                                                    </td>
                                                        <?php
                                                        for ($y = 0; $y < sizeOf($gearSlot); $y ++) {
                                                            $currentSlot = $gearSlot[$y];
                                                            $db->bindMore(array("ToonName" => $toonName, "ToonSpec" => $specs['Spec' . $x], "ItemSlot" => $currentSlot));
                                                            $currentGear = $db->row('
                                                                SELECT toonitems.ItemId,
                                                                `ItemLevel`,
                                                                `ItemName`,
                                                                `ItemQuality`
                                                                FROM `toonitems` 
                                                                JOIN `toons` on toons.ToonId = toonitems.ToonId
                                                                JOIN `items` on items.ItemId = toonitems.ItemId
                                                                WHERE `ToonName` = :ToonName AND `ToonSpec` = :ToonSpec AND `ItemSlot` = :ItemSlot');
                                                            if ($currentSlot === 'mainHand' && $currentGear)
                                                                $mainHandIlvl = $currentGear['ItemLevel'];
                                                            if ($currentSlot === 'offHand' && !$currentGear)
                                                                $averageIlvl += $mainHandIlvl;
                                                            else
                                                                $averageIlvl += $currentGear['ItemLevel'];
                                                        }
                                                        $averageIlvl /= 16;
                                                        $averageIlvl = round($averageIlvl, 2); ?>
                                                    <td class="text-center">
                                                    <?php
                                                        if ($averageIlvl) {
                                                            echo ($averageIlvl.' ilvl');
                                                        } else {
                                                            echo ('No Data');
                                                        } ?>
                                                    </td>
                                                    <?php 
                                                    }
                                                } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="card">
                            <h6 class="card-header-xsm text-center bg-darkgreen text-white">Toon Quick Look</h6>
                            <div class="card-text">
                                <table class="table table-sm table-hover mb-0">
                                    <colgroup>
                                        <col span="1" style="width: 55%;">
                                        <col span="1" style="width: 45%;">
                                    </colgroup>
                                    <tbody>
                                        <?php
                                        $db->bind("ToonName", $toonName);
                                        $quickInfo = $db->row('SELECT * FROM `toonmisc` 
                                            JOIN toons on toons.ToonId = toonmisc.ToonId
                                            JOIN toonclass on toonclass.ToonId = toonmisc.ToonId
                                            JOIN classinfo on classinfo.ClassId = toonclass.ClassId
                                            JOIN roles on roles.RoleId = toons.RoleId
                                            JOIN progression on progression.ToonId = toons.ToonId
                                            WHERE toons.ToonName = :ToonName
                                            ORDER BY InsertDate DESC');
                                            $class = 'bg-' . strtolower(str_replace(' ', '', $quickInfo['ClassName']));
                                        if ($quickInfo) { ?>
                                            <tr>
                                                <td class="text-right">Class</td>
                                                <td class="text-center <?php echo $class; ?>"><?php echo ($quickInfo['ClassName']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right">Role</td>
                                                <td class="text-center"><?php echo ($quickInfo['RoleName']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right">Achievements</td>
                                                <td class="text-center"><?php echo (number_format($quickInfo['AchievementPoints'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right">Mounts</td>
                                                <td class="text-center"><?php echo ($quickInfo['Mounts']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right">Unique Pets</td>
                                                <td class="text-center"><?php echo ($quickInfo['UniquePets']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right">Exalted Reps</td>
                                                <td class="text-center"><?php echo ($quickInfo['ExaltedReps']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right">World Quests</td>
                                                <td class="text-center"><?php echo (number_format($quickInfo['WorldQuests'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right">Artifact Knowledge</td>
                                                <td class="text-center"><?php echo (number_format($quickInfo['ArtifactKnowledge'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right">Total AP</td>
                                                <td class="text-center"><?php echo (convertAP($quickInfo['ArtifactPower'])); ?></td>
                                            </tr>
                                        <?php 
                                        } else { ?>
                                            <tr>
                                                <td class="text-center">Class</td>
                                                <td>N/A</td>
                                            </tr>
                                        <?php 
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="card">
                            <h6 class="card-header-xsm text-center bg-darkgreen text-white">Completed This Week</h6>
                            <div class="card-text">
                                <table class="table table-sm table-hover mb-0">
                                    <colgroup>
                                        <col span="1" style="width: 70%;">
                                        <col span="1" style="width: 30%;">
                                    </colgroup>
                                    <tbody>
                                        <?php
                                        if (strtotime('Tuesday this week 11:00') > time()) {
                                            $lastReset = strtotime('Tuesday last week 11:00'); // Set the reset time for realms (11:00AM EDT, Tuesdays)
                                        } else
                                            $lastReset = strtotime('Tuesday this week 11:00'); // Set the reset time for realms (11:00AM EDT, Tuesdays)

                                        $db->bindMore(array("ToonName" => $toonName, "Time" => $lastReset, "ToonName2" => $toonName, "Time2" => $lastReset));
                                        $artifactPower = $db->single('
                                            SELECT 
                                            (SELECT `ArtifactPower`
                                            FROM `progression`
                                            JOIN toons on toons.ToonId = progression.ToonId
                                            WHERE ToonName = :ToonName and InsertDate > FROM_UNIXTIME( :Time )
                                            ORDER BY InsertDate DESC limit 1) -
                                            (SELECT `ArtifactPower`
                                            FROM `progression`
                                            JOIN toons on toons.ToonId = progression.ToonId
                                            WHERE ToonName = :ToonName2 and InsertDate < FROM_UNIXTIME( :Time2 )
                                            ORDER BY InsertDate DESC limit 1) AS Difference');

                                        $db->bindMore(array("ToonName" => $toonName, "Time" => $lastReset, "ToonName2" => $toonName, "Time2" => $lastReset));
                                        $worldQuests = $db->single('
                                            SELECT 
                                            (SELECT `WorldQuests`
                                            FROM `progression`
                                            JOIN toons on toons.ToonId = progression.ToonId
                                            WHERE ToonName = :ToonName and InsertDate > FROM_UNIXTIME( :Time )
                                            ORDER BY InsertDate DESC limit 1) -
                                            (SELECT `WorldQuests`
                                            FROM `progression`
                                            JOIN toons on toons.ToonId = progression.ToonId
                                            WHERE ToonName = :ToonName2 and InsertDate < FROM_UNIXTIME( :Time2 )
                                            ORDER BY InsertDate DESC limit 1) AS Difference');

                                        $db->bindMore(array("ToonName" => $toonName, "Time" => $lastReset, "ToonName2" => $toonName, "Time2" => $lastReset));
                                        $mythics = $db->single('
                                            SELECT 
                                            (SELECT `Mythics`
                                            FROM `progression`
                                            JOIN toons on toons.ToonId = progression.ToonId
                                            WHERE ToonName = :ToonName and InsertDate > FROM_UNIXTIME( :Time )
                                            ORDER BY InsertDate DESC limit 1) -
                                            (SELECT `Mythics`
                                            FROM `progression`
                                            JOIN toons on toons.ToonId = progression.ToonId
                                            WHERE ToonName = :ToonName2 and InsertDate < FROM_UNIXTIME( :Time2 )
                                            ORDER BY InsertDate DESC limit 1) AS Difference');
                                        if ($quickInfo) {
                                            ?>
                                            <tr>
                                                <td class="text-right">Mythic Dungeons</td>
                                                <td class="text-center"><?php echo (number_format($mythics)); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right">World Quests</td>
                                                <td class="text-center"><?php echo (number_format($worldQuests)); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right">Artifact Power</td>
                                                <td class="text-center"><?php echo (convertAP($artifactPower)); ?></td>
                                            </tr>
                                        <?php 
                                        } else { ?>
                                            <tr>
                                                <td class="text-center">Class</td>
                                                <td>N/A</td>
                                            </tr>
                                        <?php 
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php
                            $db->bind("ToonName", $toonName);
                            $tierInfo = $db->row('SELECT *
                                FROM `tiersets` 
                                JOIN toonclass on tiersets.ClassId = toonclass.ClassId
                                JOIN toons on toonclass.ToonId = toons.ToonId
                                WHERE `ToonName` = :ToonName');
                            $db->bindMore(array("ToonName" => $toonName, "Head" => $tierInfo['HeadId'], "Shoulder" => $tierInfo['ShoulderId'],
                                "Back" => $tierInfo['BackId'], "Chest" => $tierInfo['ChestId'], "Hands" => $tierInfo['HandsId'], "Legs" => $tierInfo['LegsId']));
                            $tierItems = $db->query('SELECT 
                                toonitemshistory.ItemId,
                                `ItemSlot`,
                                MAX(`ItemLevel`)
                                FROM `toonitemshistory` 
                                JOIN items on items.ItemId = toonitemshistory.ItemId
                                JOIN toons on toons.ToonId = toonitemshistory.ToonId
                                WHERE toonitemshistory.ItemId in (:Head,:Shoulder,:Back,:Chest,:Hands,:Legs) AND `ToonName` = :ToonName
                                GROUP BY toonitemshistory.ItemId, `ItemSlot`');
                            ?>
                            <h6 class="card-header-xsm text-center bg-darkgreen text-white">Tier Pieces Owned (<?php echo (sizeOf($tierItems)); ?>/6)</h6>
                            <div class="card-text">
                                <table class="table table-sm table-hover mb-0">
                                    <colgroup>
                                        <col span="1" style="width: 30%;">
                                        <col span="1" style="width: 20%;">
                                        <col span="1" style="width: 30%;">
                                        <col span="1" style="width: 20%;">
                                    </colgroup>
                                    <tbody>
                                        <?php 
                                        if ($tierItems) { 
                                            $tierSlots = ['Head','Chest','Shoulder','Hands','Back','Legs']; 
                                            for ($x = 0; $x < sizeof($tierSlots); $x++) { ?>
                                                <?php 
                                                if ($x % 2 === 0) { ?>
                                                    <tr>
                                                <?php 
                                                } ?>
                                                    <td class="text-center"><?php echo $tierSlots[$x]; ?></td>
                                                    <td class="text-center"><?php
                                                        for ($i = 0; $i < sizeOf($tierItems); $i++) {
                                                            if ($tierItems[$i]['ItemSlot'] === strtolower($tierSlots[$x])) {
                                                                $wowhead = 'http://wowhead.com/item=' . $tierItems[$i]['ItemId'];
                                                                echo ('<a href="' . $wowhead . '">' . $tierItems[$i]['MAX(`ItemLevel`)']);
                                                            }
                                                        } ?>
                                                    </td>								
                                                <?php
                                                if ($x % 2 === 1) { ?>
                                                    </tr>
                                                <?php 
                                                }
                                            } 
                                        } else { ?>
                                            <tr>
                                                <td class="text-center">No Tier!</td>
                                            </tr>
                                        <?php 
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                            <h6 class="card-header-xsm text-center bg-darkgreen text-white">Toon Links</h6>
                            <div class="card-text">
                                <table class="table table-sm table-hover mb-0">
                                    <colgroup>
                                        <col span="1" style="width: 20%;">
                                        <col span="1" style="width: 40%;">
                                        <col span="1" style="width: 40%;">
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <?php 
                                            $armory = 'https://worldofwarcraft.com/en-us/character/area-52/' . $toonName; 
                                            $wowProgress = 'https://www.wowprogress.com/character/us/area-52/' . $toonName; 
                                            $warcraftLogs = ''; ?>
                                            <td><a href="<?php echo $armory; ?>" target="_blank">Armory</a></td>
                                            <td><a href="<?php echo $wowProgress; ?>" target="_blank">WoWProgress</a></td>
                                            <td><a href="<?php echo $armory; ?>" target="_blank">Warcraft Logs</a></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                    </div>
                    
                    <div class="card-columns-3-sm">
                        <?php 
                        $averageIlvl = 0;
                        $db->bindMore(array("ToonName" => $toonName, "ToonSpec" => $toonSpec));
                        $dbIlvl = $db->row('
                            SELECT SUM(`ItemLevel`), 
                            COUNT(`ItemLevel`)
                            FROM `toonitems` 
                            JOIN `toons` on toons.ToonId = toonitems.ToonId
                            JOIN `items` on items.ItemId = toonitems.ItemId
                            WHERE `ToonName` = :ToonName AND `ToonSpec` = :ToonSpec');
                        $averageIlvl = $dbIlvl['SUM(`ItemLevel`)'];
                        if ($dbIlvl['COUNT(`ItemLevel`)'] === 15) {
                            $test = 'mainHand';
                            $db->bindMore(array("ToonName" => $toonName, "ToonSpec" => $toonSpec, "ItemSlot" => $test));
                            $averageIlvl += $db->single('
                                SELECT `ItemLevel`
                                FROM `toonitems` 
                                JOIN `toons` on toons.ToonId = toonitems.ToonId
                                JOIN `items` on items.ItemId = toonitems.ItemId
                                WHERE `ToonName` = :ToonName AND `ToonSpec` = :ToonSpec AND `ItemSlot` = :ItemSlot');
                        }
                        $averageIlvl /= 16;
                        $averageIlvl = round($averageIlvl, 2);
                        ?>
                        <div class="card">
                            <h5 class="card-header-sm text-center bg-darkgreen text-white"><?php echo ($toonSpec); ?> Gear (<?php echo $averageIlvl; ?>)</h5>
                            <?php $gearSlot = $db->column("SELECT `GearSlotName` FROM `gearSlots`"); ?>
                            <div class="card-text">
                                <table class="table table-sm table-hover mb-0">
                                    <colgroup>
                                        <col span="1" style="width: 10%;">
                                        <col span="1" style="width: 10%;">
                                        <col span="1" style="width: 80%;">
                                    </colgroup>
                                    <!-- /.colgroup -->
                                    <tbody>					
                                        <?php
                                        for ($y = 0; $y < sizeOf($gearSlot); $y ++) {
                                            $currentSlot = $gearSlot[$y];
                                            $db->bindMore(array("ToonName" => $toonName, "ToonSpec" => $toonSpec, "ItemSlot" => $currentSlot));
                                            $currentGear = $db->row('
                                                SELECT toonitems.ItemId,
                                                `ItemLevel`,
                                                `ItemName`,
                                                `ItemQuality`
                                                FROM `toonitems` 
                                                JOIN `toons` on toons.ToonId = toonitems.ToonId
                                                JOIN `items` on items.ItemId = toonitems.ItemId
                                                WHERE `ToonName` = :ToonName AND `ToonSpec` = :ToonSpec AND `ItemSlot` = :ItemSlot');
                                            if ($currentGear) {
                                                if (strpos($currentGear['ItemName'],'of the') !== false) {
                                                    $currentGear['ItemName'] = substr($currentGear['ItemName'],0,strpos($currentGear['ItemName'],'of the'));
                                                }
                                                $wowhead = 'http://wowhead.com/item=' . $currentGear['ItemId']; ?>
                                                <tr>
                                                    <td class="text-center"><strong><?php echo $currentGear['ItemLevel'] ?></strong></td>
                                                    <td class="text-right text-capitalize"><strong><?php echo $currentSlot; ?></strong></td>
                                                    <td colspan="2" class="text-center"><a href="<?php echo $wowhead; ?>" <?php if ($currentGear['ItemQuality'] === 5) echo 'class="text-warning"'; ?>><?php echo $currentGear['ItemName']; ?></td>
                                                </tr>
                                                <?php 
                                            } else { ?>
                                                <tr>
                                                    <td class="text-center"><strong>N/A</strong></td>
                                                    <td class="text-right text-capitalize"><strong><?php echo $currentSlot; ?></strong></td>
                                                    <td class="text-center text-primary">No Data</td>
                                                </tr>
                                                <?php 
                                                }
                                            } ?>
                                    </tbody>
                                    <!-- /.tbody -->
                                </table>
                                <!-- /.table -->
                            </div>
                        </div>     
                        
                        <div class="col-xs-12" style="height:0px;"></div>  <!-- Temporary to force cards over in Chrome -->
                        
                        <div class="card">
                        <?php 
                        $db->bind("ToonName", $toonName);
                        $mythicDungeons = $db->row('SELECT * FROM `dungeonprogress` 
                            JOIN toons on toons.ToonId = dungeonprogress.ToonId
                            JOIN progression on toons.ToonId = progression.ToonId
                            WHERE toons.toonName = :ToonName
                            ORDER BY InsertDate DESC'); ?>
                            <h5 class="card-header-sm text-center bg-darkgreen text-white">Mythic Dungeons Completed (<?php echo (number_format($mythicDungeons['Mythics'])) ?>)</h5>
                            <div class="card-text">
                                <table class="table table-sm table-hover mb-0">
                                    <colgroup>
                                        <col span="1" style="width: 80%;">
                                        <col span="1" style="width: 20%;">
                                    </colgroup>
                                    <tbody>
                                        <?php 
                                        if ($mythicDungeons) { ?>
                                            <tr>
                                                <td class="text-right"><strong>Eye of Azshara</strong></td>
                                                <td class="text-center"><?php echo (number_format($mythicDungeons['EoA'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><strong>Darkheart Thicket</strong></td>
                                                <td class="text-center"><?php echo (number_format($mythicDungeons['DT'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><strong>Neltharion's Lair</strong></td>
                                                <td class="text-center"><?php echo (number_format($mythicDungeons['NL'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><strong>Halls of Valor</strong></td>
                                                <td class="text-center"><?php echo (number_format($mythicDungeons['HoV'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><strong>Assault on Violet Hold</strong></td>
                                                <td class="text-center"><?php echo (number_format($mythicDungeons['AoVH'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><strong>Vault of the Wardens</strong></td>
                                                <td class="text-center"><?php echo (number_format($mythicDungeons['VotW'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><strong>Black Rook Hold</strong></td>
                                                <td class="text-center"><?php echo (number_format($mythicDungeons['BRH'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><strong>Maw of Souls</strong></td>
                                                <td class="text-center"><?php echo (number_format($mythicDungeons['MoS'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><strong>The Arcway</strong></td>
                                                <td class="text-center"><?php echo (number_format($mythicDungeons['Arcway'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><strong>Court of Stars</strong></td>
                                                <td class="text-center"><?php echo (number_format($mythicDungeons['CoS'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><strong>Return to Karazhan</strong></td>
                                                <td class="text-center"><?php echo (number_format($mythicDungeons['RtK'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><strong>Cathedral of Eternal Night</strong></td>
                                                <td class="text-center"><?php echo (number_format($mythicDungeons['CoEN'])); ?></td>
                                            </tr>
                                        <?php 
                                        } else { ?>
                                            <tr>
                                                <td colspan="2" class="text-center">No Data</td>
                                            </tr>
                                        <?php 
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-xs-12" style="height:0px;"></div>  <!-- Temporary to force cards over in Chrome -->
                        
                        <div class="card">
                        <?php
                        $db->bind("ToonName", $toonName);
                        $legendaries = $db->query('SELECT 
                            DISTINCT(toonitemshistory.ItemId), 
                            `ItemName` 
                            FROM `toonitemshistory` 
                            JOIN items on items.ItemId = toonitemshistory.ItemId
                            JOIN toons on toons.ToonId = toonitemshistory.ToonId
                            WHERE ItemQuality = 5 AND `ToonName` = :ToonName'); ?>
                            <h5 class="card-header-sm text-center bg-darkgreen text-white">Legendaries Owned (<?php echo (sizeOf($legendaries)); ?>)</h5>
                            <div class="card-text">
                                <table class="table table-sm table-hover mb-0">
                                    <tbody>
                                    <?php
                                    if ($legendaries) {
                                        for ($i = 0; $i < sizeOf($legendaries); $i++) {
                                            $wowhead = 'http://wowhead.com/item=' . $legendaries[$i]['ItemId']; ?>
                                            <tr>
                                                <td class="text-center"><a href="' . $wowhead . '" class="text-warning"><?php echo $legendaries[$i]['ItemName']; ?></td>
                                            </tr>
                                        <?php 
                                        }
                                    } else { ?>
                                        <tr>
                                        <td class="text-center">No Legendaries!</td>
                                        </tr>
                                    <?php 
                                    } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card">
                            <h5 class="card-header-sm text-center bg-darkgreen text-white">Legion Reputations</h5>
                            <?php 
                            $db->bind("ToonName", $toonName);
                            $reputation = $db->row("SELECT  
                                `Nightfallen`,
                                `Valarjar`,
                                `Wardens`,
                                `Dreamweavers`,
                                `HT`,
                                `CoF`,
                                `AoL`
                                FROM `reputation` 
                                JOIN toons on reputation.ToonId = toons.ToonId
                                WHERE ToonName = :ToonName"); 
                            $reps = ['AoL','Nightfallen','CoF','Dreamweavers','HT','Valarjar','Wardens']; ?>
                            <div class="card-text">
                                <table class="table table-sm table-hover mb-0">
                                    <colgroup>
                                        <col span="1" style="width: 40%;">
                                        <col span="1" style="width: 60%;">
                                    </colgroup>
                                    <tbody>
                                    <?php 
                                    for ($x = 0; $x < sizeof($reps); $x++) { ?>
                                        <tr>
                                            <?php 
                                            $currentRep = json_decode($reputation[$reps[$x]]);
                                            switch ($currentRep->standing) {
                                                case(3):
                                                    $standing = 'Neutral';
                                                    break;

                                                case(4):
                                                    $standing = 'Friendly';
                                                    break;

                                                case(5):
                                                    $standing = 'Honored';
                                                    break;

                                                case(6):
                                                    $standing = 'Revered';
                                                    break;

                                                case(7):
                                                    $standing = 'Exalted';
                                                    break;

                                                default:
                                                    $standing = 'Unknown';
                                                    break;
                                            } ?>
                                            <td class="align-middle text-right"><strong><?php echo ($currentRep->name); ?></strong></td>
                                            <td class="align-middle text-center">
                                            <?php
                                            if ($currentRep->value === 0 && $currentRep->max === 0 && $currentRep->standing === 7) {
                                                echo ($standing . ' (999/999)');
                                            } else {
                                                echo ($standing . ' (' . $currentRep->value . '/' . $currentRep->max . ')');
                                            } ?>
                                            </td> 
                                        </tr>
                                    <?php 
                                    } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        
                    </div>
                <?php 
                } ?>
            </div>

        </div> 
        <!-- /#wrapper -->

        <!-- jQuery -->
        <script src="js/jquery.min.js"></script>

        <!-- Bootstrap Core JavaScript -->
        <script src="js/bootstrap.min.js"></script>

        <script src="js/jquery.dataTables.min.js"></script>
        <script src="js/dataTables.bootstrap4.min.js"></script>

        <script type="text/javascript">
            function configureDropDownLists(ddl1, ddl2) {
                var toons = <?php echo json_encode($toons); ?>;
                var specs = '';

                ddl2.options.length = 1;
                for (i = 0; i < toons.length; i++) {
                    specs = toons[i].Specs.split(',');
                    if (toons[i].ToonName == ddl1.value) {
                        for (x = 0; x < specs.length; x++) {
                            createOption(ddl2, specs[x], specs[x]);
                        }
                    }
                }
            }

            function createOption(ddl, text, value) {
                var opt = document.createElement('option');
                opt.value = value;
                opt.text = text;
                ddl.options.add(opt);
            }
        </script>



    </body>
