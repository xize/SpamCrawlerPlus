<?php

/*
 * SpamCrawlerPlus © 27-04-2014 all rights reserved to Guido Lucassen this file is lisenced under the GPL, MIT licenses,
 * however you are not allowed to use this in commercial use.
 * 
 * this is a easy way to block spambots for 1. visiting your site,
 * 2. prevent spam by faking the website is dead for all spambots who are listed on stopforumspam!
 * 
 * we designed this especially for xenforo not as a addon but more likely to add in the library/config.php file.
 * however this may also work on other cms's if they extend or include their mysql class almost everywhere.
 * this will support cloudflare aswell but also the normal ip's.
 *  
 * since ive did for a small time research how those spambots are working.
 * its better to fake a dead with 404 errors rather than seeing them monitoring the site
 * and when nobody is on and trying to perform a capatcha bruteforce with the autolearn feature.
 * this also will lowering content scraping as it is probably also a part of the auto process learning.
 */

class SpamCrawlerPlus {
	private $ip;
	
	/**
	 *
	 * @author xize
	 * @param add here your hardcoded ip adresses!, this allow them to visit your site.
	 * @return Array
	 */
	private function whitelist() {
		$array = array (
				"192.168.0.1",
				"192.168.0.2" 
		);
		return $array;
	}
	
	/**
	 *
	 * @author xize
	 * @param $ip - the ip adress
	 */
	public function __construct($ip) {
		$this->ip = $ip;
	}
	
	/**
	 *
	 * @author xize
	 * @param returns the ip adress
	 * @return String
	 */
	public function getIpadress() {
		return $this->ip;
	}
	
	/**
	 *
	 * @author xize
	 * @param returns true if the ip is a spamsource ip
	 * @return boolean
	 */
	public function isSpamSource() {
		if (in_array ( $this->ip, $this->whitelist () )) {
			return false;
		}
		
		$response = file_get_contents ( "http://www.stopforumspam.com/api?ip=" . $this->ip . "");
		$jsonFile = file_get_contents ( $response );
		
		$json = json_decode ( $jsonFile, true );
		
		$args = $json ["response success=\"true\""] ["appears"];
		if ($args [1] == "yes") {
			return true;
		} else {
			//search if they are possible a dedicated server, even though this could be spoofed easily.
			$hostname = gethostbyaddr ( $this->ip );
			if (strpos ( $hostname, "ovh.com" ) || strpos ( $hostname, "ovh.net" ) || strpos ( $hostname, "vps" ) || strpos ( $hostname, "mail.ru" ) || strpos ( $hostname, "ovh.com" ) || strpos ( $hostname, "softlayer" ) || strpos ( $hostname, "vpn" ) || strpos ( $hostname, "dedicated" )) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 *
	 * @author xize
	 * @param sents a 404 header, so the bot thinks the page does not exist.
	 */
	public function sent404Header() {
		header ( "HTTP/1.0 404 Not Found" );
	}
}

if (isset ( $_SERVER ['HTTP_CF_CONNECTING_IP'] )) {
	$_SERVER ['REMOTE_ADDR'] = $_SERVER ['HTTP_CF_CONNECTING_IP'];
}
$spam = new SpamCrawlerPlus ( $_SERVER ['REMOTE_ADDR'] );
if ($spam->isSpamSource ()) {
	$spam->sent404Header ();
}
?>