<?php
/**
 * Provides functions to get data from the public blizzar World of Warcraft API.
 *
 * This is a free, none commercial package to give a basic standardised function
 * set to communicate between your own tool and the public web api for the game
 * World of Warcraft from Blizzard Entertainment's®. Before you use this package
 * please read the Legal FAQ from Blizzard:
 * http://eu.blizzard.com/en-gb/company/about/legal-faq.html
 *
 * @package jpWoW
 * @author Philipp John <info@jplace.de>
 * @copyright (c) 2015, Philipp John
 * @license http://opensource.org/licenses/MIT MIT see LICENSE.txt
 *
 * @link http://blizzard.github.io/api-wow-docs
 */
class jpWoW
{
	/**
	 * @var string
	 */
	private $_apiKey;

	/**
	 * @var jpWoWregion
	 */
	private $_region;

	/**
	 * @var false|jpWoWAuthentication
	 */
	private $_authentication;

	/**
	 * @var bool
	 */
	private $_decodeResult = true;

	/**
	 * @param string $key
	 */
	public function setApiKey($key)
	{
		$this->_apiKey = (string)$key;
	}

	/**
	 * Set to false if you wand that the class returns the data in raw json instead of an array.
	 *
	 * @param bool $val
	 */
	public function setDecodeResult($val)
	{
		$this->_decodeResult = (bool)$val;
	}

	/**
	 * @param jpWoWregion $region
	 * @param jpWoWAuthentication $auth
	 */
	public function __construct(jpWoWregion $region, jpWoWAuthentication $auth = null)
	{
		$this->_setRegion($region);

		if(!empty($auth)) {
			$this->setAuthentication($auth);
		} else {
			$this->_authentication = false;
		}
	}

	/**
	 * @param jpWoWregion $region
	 */
	protected function _setRegion(jpWoWregion $region)
	{
		$this->_region = $region;
	}

	/**
	 * @param string $subPath
	 * @return mixed
	 */
	private function _performRequest($subPath)
	{
		$urlPath = '/wow/'.$subPath;
		
		if($this->_authentication !== false) {
			$this->_authentication->setHost($this->_region->getHost());
			$this->_authentication->setTimestamp(time());
			$this->_authentication->setUrlPath($urlPath);
		}

		$url = 'https://'.$this->_region->getHost().$urlPath;

		if(strpos($url, '?') !== false) {
			$url .= '&';
		} else {
			$url .= '?';
		}

		$url .= 'locale='.$this->_region->getLocale()
			  . '&apikey='.$this->_apiKey;

		$cr = curl_init();
		curl_setopt($cr, CURLOPT_URL, $url);
		curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($cr, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($cr);

		if($output === false) {
			$output = array (
				'errorno' => curl_errno($cr),
				'error' => curl_error($cr),
			);
		}

		curl_close($cr);

		if (
			$this->_decodeResult
			&& !is_array($output)
			&& strpos($output, '{') !== false
			&& strpos($output, '}') !== false
		) {
			return json_decode($output, true);
		} else {
			if(is_array($output)) {
				$output = json_encode($output);
			}
			return $output;
		}
	}

	/**
	 * @param jpWoWAuthentication $auth
	 */
	public function setAuthentication(jpWoWAuthentication $auth)
	{
		$this->_authentication = $auth;
	}

	/**
	 * Reset the authentication to <b>false</b> to allow using the none
	 * authentification mode.
	 */
	public function resetAuthentication()
	{
		$this->_authentication = false;
	}

	/**
	 * @param int $achievementID
	 * @return mixed
	 */
	public function getAchievment($achievementID)
	{
		$subUrl = 'achievement/'.(int)$achievementID;

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $realm
	 * @return mixed
	 */
	public function getAuction($realm)
	{
		$subUrl = 'auction/data/'.$realm;

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $bossID
	 * @return mixed
	 */
	public function getBoss($bossID)
	{
		$subUrl = 'boss/'.$bossID;

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return mixed
	 */
	public function getPetList()
	{
		$subUrl = 'pet/';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param int $abilityID
	 * @return mixed
	 */
	public function getPetAbility($abilityID)
	{
		$subUrl = 'pet/ability/'.(int)$abilityID;

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param int $speciesID
	 * @return mixed
	 */
	public function getPetSpecies($speciesID)
	{
		$subUrl = 'pet/species/'.(int)$speciesID;

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param int $speciesID
	 * @param int $level
	 * @param int $breedId
	 * @param int $qualityId
	 * @return mixed
	 */
	public function getPetStats($speciesID, $level = null, $breedId = null, $qualityId = null)
	{
		$params = array();

		if(!empty($level)) {
			$params[] = 'level='.(int)$level;
		}

		if(!empty($breedId)) {
			$params[] = 'breedId='.(int)$breedId;
		}

		if(!empty($qualityId)) {
			$params[] = 'qualtityId='.(int)$qualityId;
		}

		$subUrl = 'pet/stats/'.(int)$speciesID;

		if(!empty($params)) {
			$subUrl .= '?'.implode('&', $params);
		}

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $realm
	 * @return mixed
	 */
	public function getChallangeMode($realm)
	{
		$subUrl = 'challenge/'
				. rawurlencode($realm);

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacter($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName);

		return $this->_performRequest($subUrl);
	}

	
	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterSpecificFields($charName, $realm, $fields)
	{
		/*$fields = array (
			'achievements',
			'appearance',
			'feed',
			'guild',
			'hunterPets',
			'items',
			'mounts',
			'pets',
			'petSlots',
			'progression',
			'pvp',
			'quests',
			'reputation',
			'statistics',
			'stats',
			'talents',
			'titles',
			'audit',
		);*/

		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields='.implode(',', $fields);
	
		return $this->_performRequest($subUrl);
	}
	
	
	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterAllFields($charName, $realm)
	{
		$fields = array (
			'achievements',
			'appearance',
			'feed',
			'guild',
			'hunterPets',
			'items',
			'mounts',
			'pets',
			'petSlots',
			'progression',
			'pvp',
			'quests',
			'reputation',
			'statistics',
			'stats',
			'talents',
			'titles',
			'audit',
		);

		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields='.implode(',', $fields);

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterAchievements($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields=achievements';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterAppearance($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields=appearance';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 */
	public function getCharacterFeed($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields=feed';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterGuild($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)#
				. '?fields=guild';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterHunterPets($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields=hunterPets';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterItems($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields=items';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterMounts($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurldecode($realm).'/'
				. rawurlencode($charName)
				. '?fields=mounts';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterPets($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields=pets';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterPetSlots($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields=petSlots';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterProgression($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields=progression';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterPvp($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields=pvp';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterQuests($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields=quests';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterReputation($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields=reputation';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterStatistics($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields=statistics';

		return $this->_performRequest($subUrl);
	}	

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterStats($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields=stats';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterTalents($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields=talents';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterTitles($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields=titles';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $charName
	 * @param string $realm
	 * @return mixed
	 */
	public function getCharacterAudit($charName, $realm)
	{
		$subUrl = 'character/'
				. rawurlencode($realm).'/'
				. rawurlencode($charName)
				. '?fields=audit';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param int $itemID
	 * @return mixed
	 */
	public function getItem($itemID)
	{
		$subUrl = 'item/'.(int)$itemID;

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param int $setID
	 * @return mixed
	 */
	public function getItemSet($setID)
	{
		$subUrl = 'item/set/'.(int)$setID;

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $guildName
	 * @param string $realm
	 * @return mixed
	 */
	public function getGuild($guildName, $realm)
	{
		$subUrl = 'guild/'
				. rawurlencode($realm).'/'
				. rawurlencode($guildName);

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $guildName
	 * @param string $realm
	 * @return mixed
	 */
	public function getGuildAllFields($guildName, $realm)
	{
		$fields = array (
			'members',
			'achievements',
			'news',
			'challenge',
		);

		$subUrl = 'guild/'
				. rawurlencode($realm).'/'
				. rawurlencode($guildName)
				. '?fields='.implode(',', $fields);

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $guildName
	 * @param string $realm
	 * @return mixed
	 */
	public function getGuildMembers($guildName, $realm)
	{
		$subUrl = 'guild/'
				. rawurlencode($realm).'/'
				. rawurlencode($guildName)
				. '?fields=members';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $guildName
	 * @param string $realm
	 * @return mixed
	 */
	public function getGuildAchievements($guildName, $realm)
	{
		$subUrl = 'guild/'
				. rawurlencode($realm).'/'
				. rawurlencode($guildName)
				. '?fields=achievements';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $guildName
	 * @param string $realm
	 * @return mixed
	 */
	public function getGuildNews($guildName, $realm)
	{
		$subUrl = 'guild/'
				. rawurlencode($realm).'/'
				. rawurlencode($guildName)
				. '?fields=news';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param string $guildName
	 * @param string $realm
	 * @return mixed
	 */
	public function getGuildChallenge($guildName, $realm)
	{
		$subUrl = 'guild/'
				. rawurlencode($realm).'/'
				. rawurlencode($guildName)
				. '?fields=challenge';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return mixed
	 */
	public function getLeaderboard2v2()
	{
		$subUrl = 'leaderboard/2v2';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return mixed
	 */
	public function getLeaderboard3v3()
	{
		$subUrl = 'leaderboard/3v3';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return mixed
	 */
	public function getLeaderboard5v5()
	{
		$subUrl = 'leaderboard/5v5';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return mixed
	 */
	public function getLeaderboardRbg()
	{
		$subUrl = 'leaderboard/rbg';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param int $questID
	 * @return mixed
	 */
	public function getQuest($questID)
	{
		$subUrl = 'quest/'.$questID;

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return mixed
	 */
	public function getRealmStatus()
	{
		$subUrl = 'realm/status';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param int $recipeID
	 * @return mixed
	 */
	public function getRecipe($recipeID)
	{
		$subUrl = 'recipe/'.(int)$recipeID;

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param int $spellID
	 * @return mixed
	 */
	public function getSpell($spellID)
	{
		$subUrl = 'spell/'.(int)$spellID;

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return mixed
	 */
	public function getZoneList()
	{
		$subUrl = 'zone/';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @param int $zoneID
	 * @return mixed
	 */
	public function getZone($zoneID)
	{
		$subUrl = 'zone/'.(int)$zoneID;

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return mixed
	 */
	public function getDataResourceBattlegroups()
	{
		$subUrl = 'data/battlegroups/';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return mixed
	 */
	public function getDataResourceCharacterRaces()
	{
		$subUrl = 'data/character/races';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return mixed
	 */
	public function getDataResourceCharacterClasses()
	{
		$subUrl = 'data/character/classes';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return mixed
	 */
	public function getDataResourceCharacterAchievements()
	{
		$subUrl = 'data/character/achievements';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return mixed
	 */
	public function getDataResourceGuildRewards()
	{
		$subUrl = 'data/guild/rewards';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return mixed
	 */
	public function getDataResourceGuildPerks()
	{
		$subUrl = 'data/guild/perks';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return mixed
	 */
	public function getDataResourceGuildAchievements()
	{
		$subUrl = 'data/guild/achievements';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return mixed
	 */
	public function getDataResourceItemClasses()
	{
		$subUrl = 'data/item/classes';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return mixed
	 */
	public function getDataResourceTalents()
	{
		$subUrl = 'data/talents';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return mixed
	 */
	public function getDataResourcePetTypes()
	{
		$subUrl = 'data/pet/types';

		return $this->_performRequest($subUrl);
	}

	/**
	 * @return string
	 */
	public function getStaticRenderPath()
	{
		$path = 'http://'
			  . $this->_region->getHost().'/'
			  . 'static-render/'
			  . $this->_region->getRegionSubdomain().'/';

		return $path;
	}

	/**
	 * @param int $size Only 18, 36 or 56
	 * @return string
	 */
	public function getIconsPath($size)
	{
		$path = 'http://'
			  . $this->_region->getRegionSubdomain()
			  . '.media.blizzard.com/wow/icons/'
			  .(int)$size.'/';

		return $path;
	}

	/**
	 * @param string $value
	 * @return string
	 */
	public function getItemColor($value) 
	{
		$ret = '';

		switch ($value) {
			case 1: $ret = "ffffff"; //White
				break;

			case 2: $ret = "1eff00"; //Green
				break;

			case 3: $ret = "0070dd"; //Blue
				break;

			case 4: $ret = "a335ee"; //Purple
				break;

			case 5: $ret = "ff8000"; //Orange
				break;

			default: $ret = "9d9d9d"; //Grey
		}
	}
}
