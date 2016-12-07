<?php

/**
 * Class AnattaDesign_SetCookieFix_Model_Cookie
 */
class AnattaDesign_SetCookieFix_Model_Cookie
	extends Zend_Http_Cookie {

	/**
	 * Whether the cookie is secure or not
	 *
	 * @var boolean
	 */
	protected $httpOnly;

	/**
	 * AnattaDesign_SetCookieFix_Model_Cookie constructor.
	 *
	 * @param string $name
	 * @param string $value
	 * @param string $domain
	 * @param int $expires
	 * @param string $path
	 * @param bool $secure
	 * @param bool $httpOnly
	 *
	 * @throws Zend_Http_Exception
	 */
	public function __construct($name, $value, $domain, $expires = null, $path = null, $secure = false, $httpOnly = false) {
		parent::__construct($name, $value, $domain, $expires, $path, $secure);

		$this->httpOnly = (bool) $httpOnly;
	}

	/**
	 * Check whether the cookie should only be sent over secure connections
	 *
	 * @return boolean
	 */
	public function isHttpOnly() {
		return $this->httpOnly;
	}

	public static function fromHeader($header, $refUri = null, $encodeValue = true) {
		$prefix = 'Set-Cookie:';
		if (strpos(strtolower($header), strtolower($prefix)) !== 0) {
			return false;
		}

		$header = trim(substr($header, strlen($prefix)));

		// Set default values
		if (is_string($refUri)) {
			$refUri = Zend_Uri_Http::factory($refUri);
		}

		$name = '';
		$value = '';
		$domain = '';
		$path = '';
		$expires = null;
		$secure = false;
		$httponly = false;

		$parts = explode(';', $header);

		// If first part does not include '=', fail
		if (strpos($parts[0], '=') === false) {
			return false;
		}

		// Get the name and value of the cookie
		list($name, $value) = explode('=', trim(array_shift($parts)), 2);
		$name = trim($name);
		if ($encodeValue) {
			$value = urldecode(trim($value));
		}

		// Set default domain and path
		if ($refUri instanceof Zend_Uri_Http) {
			$domain = $refUri->getHost();
			$path = $refUri->getPath();
			$path = substr($path, 0, strrpos($path, '/'));
		}

		// Set other cookie parameters
		foreach ($parts as $part) {
			$part = trim($part);
			if (strtolower($part) == 'secure') {
				$secure = true;
				continue;
			} elseif (strtolower($part) == 'httponly') {
				$httponly = true;
				continue;
			}

			$keyValue = explode('=', $part, 2);
			if (count($keyValue) == 2) {
				list($k, $v) = $keyValue;
				switch (strtolower($k)) {
					case 'expires':
						if (($expires = strtotime($v)) === false) {
							/**
							 * The expiration is past Tue, 19 Jan 2038 03:14:07 UTC
							 * the maximum for 32-bit signed integer. Zend_Date
							 * can get around that limit.
							 *
							 * @see Zend_Date
							 */
							#require_once 'Zend/Date.php';

							$expireDate = new Zend_Date($v);
							$expires = $expireDate->getTimestamp();
						}
						break;

					case 'path':
						$path = $v;
						break;

					case 'domain':
						$domain = $v;
						break;

					default:
						break;
				}
			}
		}

		if ($name !== '') {
			$ret = new self($name, $value, $domain, $expires, $path, $secure, $httponly);
			$ret->encodeValue = ($encodeValue) ? true : false;

			return $ret;
		} else {
			return false;
		}
	}

}