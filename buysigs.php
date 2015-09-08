<?php
/* Copyright (c) 2015 by the Omnicoin Team.
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>. */

define("IN_MYBB", 1);
define("THIS_SCRIPT", "buysigs.php");

require_once "./global.php";

//check if signature seller plugin is enabled
$enabled_plugins = $cache->read("plugins");
if (!array_key_exists("signature_seller", $enabled_plugins['active'])) {
	die();
}

if (!$mybb->user['uid']) {
	error_no_permission();
}

if (isset($mybb->input['action'])) {
	if ($mybb->input['action'] == "sell") {
	  //sellers can set price per day
		if (isset($mybb->input['uid'])) {
			$uid = $mybb->input['uid']; 
		} else {
			$uid = $mybb->user[uid];
		}
		$uid = intval(preg_replace("/[^0-9]/", "", $uid));
			
		// get the username corresponding to the UID passed to the page
		$grabuser = $db->simple_select("users", "username", "uid = '" . $uid . "'");
		$user = $db->fetch_array($grabuser);
		$username = $user['username'];
		
		// grab our template
		$template = $templates->get("Signature Seller Sell");
		eval("\$page=\"" . $template . "\";");
		output_page($page);
	} else if ($mybb->input['action'] == "do_sell") {
		
	} else if ($mybb->input['action'] == "buy") {
		//&uid=XXX (view seller listing)
	} else if ($mybb->input['action'] == "do_buy") {
	  //&uid=XXX* (after buy the seller gets a PM to "accept" action the purchase)
	} else if ($mybb->input['action'] == "listings") {
	  //will show all listings of seller with username (format_name+profile link) , posts, reputation on page
	} else if ($mybb->input['action'] == "accept") {
	  
	} else if ($mybb->input['action'] == "do_accept") {
	  
	} else {
		$template = $templates->get("Signature Seller Default Page");
		eval("\$page=\"" . $template . "\";");
		output_page($page);
	}
} else {
	$template = $templates->get("Signature Seller Default Page");
	eval("\$page=\"" . $template . "\";");
	output_page($page);	
}
