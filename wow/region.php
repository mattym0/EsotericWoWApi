<?php
/**
 * Provides functions to handle the battle net regions.
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
 * @link http://blizzard.github.io/api-wow-docs/#features/access-and-regions
 */
class jpWoWRegion
{
	/**
	 * @var string[]
	 */
	private $_regionConfig = array
	(
		'us' => array (
			'host' => 'us.api.battle.net',
			'locale' => array (
				'en_US',
				'es_MX',
				'pt_BR',
			)
		),
		'europe' => array (
			'host' => 'eu.api.battle.net',
			'locale' => array (
				'en_GB',
				'es_ES',
				'fr_FR',
				'ru_RU',
				'de_DE',
				'pt_PT',
				'it_IT',
			)
		),
		'korea' => array (
			'host' => 'kr.api.battle.net',
			'locale' => array (
				'ko_KR',
			)
		),
		'taiwan' => array (
			'host' => 'tw.api.battle.net',
			'locale' => array (
				'zh_TW',
			)
		),
		'china' => array (
			'host' => 'api.battlenet.com.cn',
			'locale' => array (
				'zh_CN'
			)
		),
		'asian' => array (
			'host' => 'sea.api.battle.net',
			'locale' => array (
				'zh_CN'
			)
		),
	);

	/**
	 * @var string
	 */
	private $_region;

	/**
	 * @var string
	 */
	private $_locale;

	/**
	 * @var string
	 */
	private $_host;

	/**
	 * @param string $region
	 * @param string $locale
	 * @see http://blizzard.github.io/api-wow-docs/#features/access-and-regions
	 */
	public function __construct($region, $locale)
	{
		$this->_setRegion($region);
		$this->_setLocale($locale);
	}

	/**
	 * @param string $region
	 * @throws InvalidArgumentException
	 */
	protected function _setRegion($region)
	{
		if(isset($this->_regionConfig[$region])) {
			$this->_region = $region;
			$this->_host = $this->_regionConfig[$region]['host'];
		} else {
			$this->_region = null;
			throw new InvalidArgumentException('Unknown string for region given');
		}
	}

	/**
	 * @param string $locale
	 * @throws InvalidArgumentException
	 */
	protected function _setLocale($locale)
	{
		if (
			!empty($this->_region)
			&& in_array($locale, $this->_regionConfig[$this->_region]['locale'])
		) {
			$this->_locale = $locale;
		} else {
			$this->_locale = null;
			throw new InvalidArgumentException('Unknown string for locale given or no region set');
		}
	}

	/**
	 * @return string
	 */
	public function getRegion()
	{
		return $this->_region;
	}

	/**
	 * @return string
	 */
	public function getLocale()
	{
		return $this->_locale;
	}

	/**
	 * @return string
	 */
	public function getHost()
	{
		return $this->_host;
	}

	/**
	 * @return string
	 */
	public function getAlpha2()
	{
		$locale = $this->getLocale();
		$alpha2 = substr($locale, 0, 2);

		return $alpha2;
	}

	/**
	 * return string
	 */
	public function getRegionSubdomain()
	{
		$parts = explode('.', $this->getHost());
		return reset($parts);
	}
}
