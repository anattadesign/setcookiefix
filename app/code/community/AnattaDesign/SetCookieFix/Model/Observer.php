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

		$headers = headers_list();
		foreach ($headers as $header) {
			$cookie = AnattaDesign_SetCookieFix_Model_Cookie::fromHeader($header, Mage::getBaseUrl());
			if (!$cookie) {
				continue;
			}

			$cookieJar->add($cookie);
		}

		// remove all the cookie headers
		header_remove('Set-Cookie');

		$cookies = $cookieJar->getCookies();
		foreach ($cookies as $cookie) {
			// set the cookies
			setcookie(
				$cookie->getName(),
				$cookie->getValue(),
				$cookie->getExpiryTime(),
				$cookie->getPath(),
				$cookie->getDomain(),
				$cookie->isSecure(),
				$cookie->isHttpOnly()
			);
		}
	}

}