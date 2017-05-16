<?php
/**
 * Provides functions to generate the encoded authorization string for
 * registered applications.
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
 * @link http://blizzard.github.io/api-wow-docs/#features/authentication
 */
class jpWoWAuthentication
{
	/**
	 * @var string
	 */
	private $_privateKey;

	/**
	 * @var string
	 */
	private $_host;

	/**
	 * @var string
	 */
	private $_urlPath;

	/**
	 * @var string
	 */
	private $_timestamp;

	/**
	 * @var string
	 */
	private $_prefix = 'BNET';

	/**
	 * @var string
	 */
	private $_method = 'GET';

	/**
	 * @param string $val
	 */
	public function setPrivateKey($val)
	{
		$this->_privateKey = $val;
	}

	/**
	 * @param string $val
	 */
	public function setHost($val)
	{
		$this->_host = $val;
	}

	/**
	 * @param string $val
	 */
	public function setUrlPath($val)
	{
		$this->_urlPath = $val;
	}

	/**
	 * @param string $val
	 */
	public function setTimestamp($val)
	{
		$this->_timestamp = $val;
	}

	/**
	 * @param string $val
	 */
	public function setPrefix($val)
	{
		$this->_prefix = $val;
	}

	/**
	 * Sets the method to GET.
	 */
	public function setMethodGet()
	{
		$this->_method = 'GET';
	}

	/**
	 * Sets the method to POST.
	 */
	public function setMethodPost()
	{
		$this->_method = 'POST';
	}

	/**
	 * @return string
	 */
	public function getToken()
	{
		if(empty($this->_privateKey)) {
			return '';
		}

		if(empty($this->_timestamp)) {
			$this->_timestamp = time();
		} else {
			$this->_timestamp = strtotime($this->_timestamp);
		}

		$stringToSign = $this->_method . "\n"
					  . date('D, j M Y H:i:s \G\M\T', $this->_timestamp) . "\n"
					  . $this->_urlPath . "\n";

		return base64_encode( sha1(
			utf8_encode($this->_privateKey),
			$stringToSign
		));
	}
}
