<?php

class AnattaDesign_SetCookieFix_Model_Core_Cookie
	extends Mage_Core_Model_Cookie {

	/**
	 * Set cookie
	 *
	 * @param string $name The cookie name
	 * @param string $value The cookie value
	 * @param int $period Lifetime period
	 * @param string $path
	 * @param string $domain
	 * @param int|bool $secure
	 * @param bool $httponly
	 *
	 * @return Mage_Core_Model_Cookie
	 */
	public function set($name, $value, $period = null, $path = null, $domain = null, $secure = null, $httponly = null) {
		/**
		 * Check headers sent
		 */
		if (!$this->_getResponse()->canSendHeaders(false)) {
			return $this;
		}

		// add the cookie to the cookie jar
		Mage::getSingleton('setcookiefix/cookie_jar')
			->add($name, $value, $period, $path, $domain, $secure, $httponly);

		return $this;
	}

	/**
	 * Delete cookie
	 *
	 * @param string $name
	 * @param string $path
	 * @param string $domain
	 * @param int|bool $secure
	 * @param int|bool $httponly
	 * @return Mage_Core_Model_Cookie
	 */
	public function delete($name, $path = null, $domain = null, $secure = null, $httponly = null) {
		return $this->set($name, null, null, $path, $domain, $secure, $httponly);
	}

}