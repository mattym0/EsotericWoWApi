<?php
class Toon {
	
	public $name;
	public $nameId;
	
	public $level = 0;
	
	public $class;
	public $classId;
	
	public $spec;
	
	public $role;
	public $roleId;
	
	public $head;
	public $neck;
	public $shoulder;
	public $back;
	public $chest;
	public $wrist;
	public $hands;
	public $waist;
	public $legs;
	public $feet;
	public $finger1;
	public $finger2;
	public $trinket1;
	public $trinket2;
	public $mainHand;
	public $offHand;
	
	public $talents = array(
	"0"=>array("column"=>0,"name"=>"","id"=>0,"icon"=>""),
	"1"=>array("column"=>0,"name"=>"","id"=>0,"icon"=>""),
	"2"=>array("column"=>0,"name"=>"","id"=>0,"icon"=>""),
	"3"=>array("column"=>0,"name"=>"","id"=>0,"icon"=>""),
	"4"=>array("column"=>0,"name"=>"","id"=>0,"icon"=>""),
	"5"=>array("column"=>0,"name"=>"","id"=>0,"icon"=>""),
	"6"=>array("column"=>0,"name"=>"","id"=>0,"icon"=>""));
	
	public $averageitemlevel = 0;
	public $averageequippeditemlevel = 0;
	
	public $achievementPoints = 0;
	public $mounts = 0;
	public $uniquePets = 0;
	public $maxLevelPets = 0;
	public $exaltedReps = 0;
	
	public $health = 0;
	
	public $strength = 0;
	public $agility = 0;
	public $intellect = 0;
	public $stamina = 0;
	
	public $criticalStrike = 0;
	public $critRating = 0;
	public $haste = 0;
	public $hasteRating = 0;
	public $mastery = 0;
	public $masteryRating = 0;
	public $versatility = 0;
	
	public $artifactPower = 0;
	public $artifactKnowledge = 0;
	public $artifactLevel = 0;
	public $mythics = 0;
	public $mythic2s = 0;
	public $mythic5s = 0;
	public $mythic10s = 0;
	public $mythic15s = 0;
	
	public $worldQuests = 0;
	
	public function __construct() {
      $this->head = new Gear();
	  $this->neck = new Gear();
	  $this->shoulder = new Gear();
	  $this->back = new Gear();
	  $this->chest = new Gear();
	  $this->wrist = new Gear();
	  $this->hands = new Gear();
	  $this->waist = new Gear();
	  $this->legs = new Gear();
	  $this->feet = new Gear();
	  $this->finger1 = new Gear();
	  $this->finger2 = new Gear();
	  $this->trinket1 = new Gear();
	  $this->trinket2 = new Gear();
	  $this->mainHand = new Gear();
	  $this->offHand = new Gear();
    }
}
?>