<?php

// Class based on Zend_Http_CookieJar
class AnattaDesign_SetCookieFix_Model_Cookie_Jar {

	/**
	 * Array for storing cookies
	 *
	 * Cookies are stored according to domain and path:
	 * $cookies
	 *  + www.mydomain.com
	 *    + /
	 *      - cookie1
	 *      - cookie2
	 *    + /somepath
	 *      - othercookie
	 *  + www.otherdomain.net
	 *    + /
	 *      - alsocookie
	 *
	 * @var array
	 */
	protected static $cookies = array();

	/**
	 * Stores the cookie data in a storage so we can pull them all out
	 * right before the headers are sent and will override any previous
	 * versions of the cookie so we always have the latest copy
	 *
	 * @param string $name
	 * @param string $value
	 * @param int $period
	 * @param string $path
	 * @param string $domain
	 * @param int|bool $secure
	 * @param bool $httponly
	 */
	public function add($name, $value, $period, $path, $domain, $secure, $httponly) {
		$cookieHelper = Mage::getSingleton('core/cookie');

		if ($period === true) {
			$period = 3600 * 24 * 365;
		} elseif (is_null($period)) {
			$period = $cookieHelper->getLifetime();
		}

		if ($period == 0) {
			$expire = 0;
		} else {
			$expire = time() + $period;
		}

		if (is_null($path)) {
			$path = $cookieHelper->getPath();
		}
		if (is_null($domain)) {
			$domain = $cookieHelper->getDomain();
		}
		if (is_null($secure)) {
			$secure = $cookieHelper->isSecure();
		}
		if (is_null($httponly)) {
			$httponly = $cookieHelper->getHttponly();
		}

		// create the cookie
		/** @var AnattaDesign_SetCookieFix_Model_Cookie $cookie */
		$cookie = Mage::getModel('setcookiefix/cookie')->setData(array(
			'name' => $name,
			'value' => $value,
			'expire' => $expire,
			'path' => $path,
			'domain' => $domain,
			'secure' => $secure,
			'httponly' => $httponly,
		));

		Mage::dispatchEvent('anattadesign_setcookiefix_add_cookie_to_jar_before', array(
			'cookie_jar' => $this,
			'cookie' => $cookie
		));

		// make sure the arrays are setup
		if (!isset(self::$cookies[$cookie->getDomain()])) {
			self::$cookies[$cookie->getDomain()] = array();
		}
		if (!isset(self::$cookies[$cookie->getDomain()][$cookie->getPath()])) {
			self::$cookies[$cookie->getDomain()][$cookie->getPath()] = array();
		}

		// store the cookie in the cookie storage for later.
		self::$cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;

		Mage::dispatchEvent('anattadesign_setcookiefix_add_cookie_to_jar_after', array(
			'cookie_jar' => $this,
			'cookie' => $cookie
		));
	}

	/**
	 * Get a flattened version of the cookies so that we can just iterate over them
	 *
	 * @return AnattaDesign_SetCookieFix_Model_Cookie[]
	 */
	public function getCookies() {
		return $this->_flattenCookieArray(self::$cookies);
	}

	/**
	 * Returns the raw multidimensional array of cookies
	 *
	 * @return array
	 */
	public function getRawCookies() {
		return self::$cookies;
	}

	/**
	 * Recursive function that will collapse the multidimensional cookie array
	 * down to an array of AnattaDesign_SetCookieFix_Model_Cookie
	 *
	 * @param $ptr
	 * @return array
	 */
	protected function _flattenCookieArray($ptr) {
		if (is_array($ptr)) {
			$ret = array();
			foreach ($ptr as $item) {
				$ret = array_merge($ret, $this->_flattenCookieArray($item));
			}

			return $ret;
		}

		return array($ptr);
	}

}