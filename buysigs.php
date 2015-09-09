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
if (!array_key_exists("buysigs", $enabled_plugins['active'])) {
	die();
}

if (!$mybb->user['uid']) {
	error_no_permission();
}

if (isset($mybb->input['action'])) {
	if ($mybb->input['action'] == "sell") {
	  //sellers can set price per day
		$uid = $mybb->user[uid];
		$uid = intval(preg_replace("/[^0-9]/", "", $uid));
			
		// get the username corresponding to the UID passed to the page
		$grabuser = $db->simple_select("users", "username", "uid = '" . $uid . "'");
		$user = $db->fetch_array($grabuser);
		$username = $user['username'];
		
		// grab our template
		$template = $templates->get("BuySigs Sell");
		eval("\$page=\"" . $template . "\";");
		output_page($page);
	} else if ($mybb->input['action'] == "do_sell") {
		
	} else if ($mybb->input['action'] == "buy") {
		//&uid=XXX (view seller listing)
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
	
		//get current signature listing from table
		$query = $db->simple_select("sigsales", "id, status, length, price", "uid = '" . $uid . "'", array("order_by" => "status", "order_dir" => "ASC"));
		$entries = "";
			
		if ($query->num_rows > 0) {
			// loop through each row in the database that matches our query and create a table row to display it
			while($row = $db->fetch_array($query)){
				$status = $row['status'];
				if($status != 0) continue;
				
				$userid = $uid;
				$id = $row['id'];
				$price = $row['price'];
				$length = $row['length'];
				$reputation = 0;
				$repcolor = "green";
				$posts = 0;
				$template = $templates->get("BuySigs Signature");
				eval("\$page=\"" . $template . "\";");
			}
		} else {
			//signature is not for sale
			$template = $templates->get("BuySigs No Sales");
			eval("\$entries .=\"" . $template . "\";");
		}
		
		// grab our template
		$template = $templates->get("BuySigs Signature");
		eval("\$page=\"" . $template . "\";");
		output_page($page);
	} else if ($mybb->input['action'] == "do_buy") {
	  	//&uid=XXX* (after buy the seller gets a PM to "accept" action the purchase)
	} else if ($mybb->input['action'] == "listings") {
	 	//will show all listings of seller with username (format_name+profile link) , posts, reputation on page
				
		//Get all signatures sales with status = 0
		$query = $db->simple_select("sigsales", "uid, id, price, length", "status=0", array("order_by" => "id", "order_dir" => "ASC"));
		$entries = "";
			
		if ($query->num_rows > 0) {
			//Loop through each row in the database that matches our query and create a table row to display it
			while($row = $db->fetch_array($query)){
				$grabuser = $db->simple_select("users", "username", "uid = '" . $row['uid'] . "'");
				$user = $db->fetch_array($grabuser);
					
				$username = $user['username'];
				$userid = $row['uid'];
				$price = $row['address'];
				$length = $row['date'];
				$id = $row['id'];
				$reputation = 0;
				$repcolor = "green";
				$posts = 0;
				$template = $templates->get("BuySigs Listings Entry");
				eval("\$entries .=\"" . $template . "\";");
			}
		} else {
			$message = "No signatures available";
			$template = $templates->get("BuySigs Listings No Entry");
			eval("\$entries .=\"" . $template . "\";");
		}
			
		$template = $templates->get("BuySigs Listings");
		eval("\$page=\"" . $template . "\";");
		output_page($page);
	} else if ($mybb->input['action'] == "accept") {
	  
	} else if ($mybb->input['action'] == "do_accept") {
	  
	} else {
		$template = $templates->get("BuySigs Default Page");
		eval("\$page=\"" . $template . "\";");
		output_page($page);
	}
} else {
	$template = $templates->get("BuySigs Default Page");
	eval("\$page=\"" . $template . "\";");
	output_page($page);	
}
