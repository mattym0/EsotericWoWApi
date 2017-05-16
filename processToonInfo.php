<?php 

require 'database/Db.class.php';

$db = new Db();

$toonName=$_POST['toonName'];
$toonSpec=$_POST['toonSpec'];


print_r('<b>Information for Toon: '.$toonName.' for Spec: '.$toonSpec.'<br /><br /></b>');

$db->bindMore(array("ToonName"=>$toonName,"ToonSpec"=>$toonSpec));
$talents = $db->row('
		SELECT `Tier1`,
		`Tier2`,
		`Tier3`,
		`Tier4`,
		`Tier5`,
		`Tier6`,
		`Tier7`
		FROM `toons` 
		JOIN `talents` on toons.ToonId = talents.ToonId
		WHERE `ToonName` = :ToonName AND `ToonSpec` = :ToonSpec ');	

if($talents){
	print_r('<table>'); // start a table tag in the HTML
	print_r('<tr>Talents</tr>');
	for ($i = 0; $i < sizeOf($talents); $i++) {
		$currentTier = 'Tier'.($i+1);
		$currentTierTalent = json_decode($talents[$currentTier]);
		$iconSource = 'http://media.blizzard.com/wow/icons/56/'.$currentTierTalent->icon.'.jpg';
		$wowhead = 'http://www.wowhead.com/spell='.$currentTierTalent->id;
		print_r ('<tr><td>'
			.'Tier '.($i+1).'</td><td>'
			.'<a href="'.$wowhead.'"><img src="'.$iconSource.'" style="width: 50%; height: 50%"></td><td>'
			.$currentTierTalent->name.'</td></tr>');
	}
	print_r('</table><br />');	
} else
	print_r('Cannot find talents for Toon: '.$toonName.' for Spec: '.$toonSpec);
	

$gearSlot = $db->column("SELECT `GearSlotName` FROM `gearSlots`");
$averageIlvl = 0;
print_r('<table>'); // start a table tag in the HTML
print_r('<tr>Gear</tr>');
for ($x = 0; $x < sizeOf($gearSlot); $x++) {
	$currentSlot = $gearSlot[$x];
	$db->bindMore(array("ToonName"=>$toonName,"ToonSpec"=>$toonSpec,"ItemSlot"=>$currentSlot));
	$currentGear = $db->row('
		SELECT toonitems.ItemId,
		`ItemLevel`,
		`ItemName`
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
	$wowhead = 'http://wowhead.com/item='.$currentGear['ItemId'];
	print_r ('<tr><td>'
			.$currentSlot.'</td><td>'
			.$currentGear['ItemLevel'].'</td><td>'
			.'<a href="'.$wowhead.'">'.$currentGear['ItemName'].'</td></tr>');
	//$averageIlvl += $currentGear['ItemLevel'];
}
$averageIlvl /= 16;
$averageIlvl = round($averageIlvl, 2);

print_r ('<tr><td>AverageIlvl</td><td>'
			.$averageIlvl.'</td><td>');
print_r('</table><br />'); //Close the table in HTML
	

$db->bindMore(array("ToonName"=>$toonName,"ToonSpec"=>$toonSpec));
$progression = $db->query('
		SELECT `ArtifactPower`,
		`ArtifactLevel`, 
		`ArtifactKnowledge`, 
		`Mythics`, 
		`Mythic+2`, 
		`Mythic+5`, 
		`Mythic+10`, 
		`Mythic+15`, 
		`WorldQuests`, 
		`InsertDate`
		FROM `toons` 
		JOIN `progression` on toons.ToonId = progression.ToonId
		WHERE `ToonName` = :ToonName AND 
		`ToonSpec` = :ToonSpec
		ORDER BY InsertDate DESC');	

if ($progression) {
	print_r('<table>'); // start a table tag in the HTML
	print_r('<tr><td>
		Artifact Level</td><td>
		Artifact Power</td><td>
		Artifact Knowledge</td><td>
		Mythics</td><td>
		Mythic +2s</td><td>
		Mythic +5s</td><td>
		Mythic +10s</td><td>
		Mythic +15s</td><td>
		World Quests</td><td>
		Date</td></tr>');

	for ($i = 0; $i < sizeOf($progression); $i++) {
		print_r ('<tr><td>'
		.$progression[$i]['ArtifactLevel'].'</td><td>'
		.$progression[$i]['ArtifactPower'].'</td><td>'
		.$progression[$i]['ArtifactKnowledge'].'</td><td>'
		.$progression[$i]['Mythics'].'</td><td>'
		.$progression[$i]['Mythic+2'].'</td><td>'
		.$progression[$i]['Mythic+5'].'</td><td>'
		.$progression[$i]['Mythic+10'].'</td><td>'
		.$progression[$i]['Mythic+15'].'</td><td>'
		.$progression[$i]['WorldQuests'].'</td><td>'
		.$progression[$i]['InsertDate'].'</td></tr>');
	
	}
	print_r('</table><br />'); //Close the table in HTML
} else
	print_r('Cannot find progress for Toon: '.$toonName.' for Spec: '.$toonSpec);	

//print_r('<a href="getProgress.php">Go Back</a><br/>');
print_r('<a href="index.php">Goto Main</a>');


?>