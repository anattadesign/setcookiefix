<?php

class AnattaDesign_SetCookieFix_Model_Observer {

	/**
	 * Observer method that will use setcookie to set all of the final cookie values once
	 * so that we never duplicate the cookie in the response headers
	 *
	 * @param $observer
	 */
	public function httpResponseSendBefore($observer) {
		$cookieJar = Mage::getSingleton('setcookiefix/cookie_jar');

		$cookies = $cookieJar->getCookies();
		foreach ($cookies as $cookie) {
			// set the cookies
			setcookie(
				$cookie->getName(),
				$cookie->getValue(),
				$cookie->getExpire(),
				$cookie->getPath(),
				$cookie->getDomain(),
				$cookie->getSecure(),
				$cookie->getHttpOnly()
			);
		}
	}

}