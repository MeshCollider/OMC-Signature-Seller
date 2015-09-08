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

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
	die("Direct initialization of this file is not allowed.");
}

//HOOKS
$plugins->add_hook("usercp_do_editsig_start", "buysigs_usercp_do_editsig_start");

function buysigs_info() {
	return array(
		"name"		=> "Omnicoin Signature Seller",
		"description"	=> "This plugin allows automatic management of signature sales for merchants",
		"website"	=> "http://www.omnicoin.org",
		"author"	=> "Omnicoin Team",
		"authorsite"	=> "https://github.com/Omnicoin-Project/Omnicoin/wiki/Omnicoin-Team",
		"version"	=> "v1.0.0",
		"guid" 		=> "",
		"compatibility" => "*"
	);
}

function buysigs_install() {
	//Called whenever a plugin is installed by clicking the "Install" button in the plugin manager.
	//It is common to create required tables, fields and settings in this function.	
	
	global $mybb, $db, $cache;
	
	if (!$db->table_exists("sigsales")) {
		//for the listing of sales
		$db->query("CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "sigsales` (
			`id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
			`uid` varchar(10) NOT NULL DEFAULT '',
			`price` decimal NOT NULL DEFAULT '',
			`length` smallint NOT NULL,
			`status` tinyint	NOT NULL DEFAULT 0,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
	}
	
	if (!$db->table_exists("sigbuys")) {
		//for the sales, saving and replacing of sigs
		$db->query("CREATE TABLE IF NOT EXISTS `" . TABLE_PREFIX . "sigbuys` (
			`id` smallint(10) unsigned NOT NULL AUTO_INCREMENT,
			`uid_seller` varchar(10) NOT NULL DEFAULT '',
			`uid_buyer` varchar(10) NOT NULL DEFAULT '',
			`oldsig` varchar(10) NOT NULL DEFAULT '',
			`newsig` varchar(10) NOT NULL DEFAULT '',
			`status` tinyint	NOT NULL DEFAULT 0,
			`buydate` DATETIME NOT NULL,
			`expdate` DATETIME NOT NULL,
			`txnid` varchar(64) NOT NULL DEFAULT '',
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
	}
}

function buysigs_is_installed() {
	//Called on the plugin management page to establish if a plugin is already installed or not.
	//This should return TRUE if the plugin is installed (by checking tables, fields etc) or FALSE if the plugin is not installed.
  	global $mybb, $db;
	
  	if ($db->table_exists("sigsales")) {
		return true;
	}
}

function buysigs_uninstall() {
	//Called whenever a plugin is to be uninstalled. This should remove ALL traces of the plugin from the installation (tables etc). If it does not exist, uninstall button is not shown.

	global $mybb, $db;
	//Delete the sigsales table
	if ($db->table_exists("sigsales")) {
		//delete the table here
		$db->query("DROP TABLE IF EXISTS `" . TABLE_PREFIX . "sigsales`");
	}
	//Delete the sigbuys table
	if ($db->table_exists("sigbuys")) {
		//delete the table here
		$db->query("DROP TABLE IF EXISTS `" . TABLE_PREFIX . "sigbuys`");
	}
	
}

function buysigs_activate() {
	//Called whenever a plugin is activated via the Admin CP. This should essentially make a plugin "visible" by adding templates/template changes, language changes etc.
	global $db;
	
	require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';
	
	$db->insert_query("templates", array(
		"tid"		=> NULL,
		"title"		=> "BuySigs Default Page",
		"template"	=> '<html>
	<head>
		<title>Omnicoin Signature Seller</title>
		{$headerinclude}
	</head>
	<body>
		{$header}
		<h2>Omnicoin</h2>
		<br />
		<a href="buysigs.php?action=sell">Sell your signature space</a><br />
		<a href="buysigs.php?action=listings">Hire a signature space</a><br />
		{$footer}
	</body>
</html>',
		"sid"		=> "-1"));
		
	$db->insert_query("templates", array(
		"tid"		=> NULL,
		"title"		=> "BuySigs No Sales",
		"template"	=> '<tr class="trow1"><td colspan=2>{$message}</td></tr>',
		"sid"		=> "-1"));
		
	$db->insert_query("templates", array(
		"tid"		=> NULL,
		"title"		=> "BuySigs Listings",
		"template"	=> '<html>
	<head>
		<title>Available Signatures</title>
		{$headerinclude}
	</head>
	<body>
		{$header}
		<table class="tborder">
			<tr class="thead">
				<th><strong>User:</strong></th>
				<th><strong>Price:</strong></th>
				<th><strong>Length:</strong></th>
				<th><strong>Reputation:</strong></th>
				<th><strong>Posts:</strong></th>
			</tr>
			{$entries}
		</table>
		{$footer}
	</body>
</html>',
		"sid"		=> "-1"));
		
	$db->insert_query("templates", array(
		"tid"		=> NULL,
		"title"		=> "BuySigs Listings Entry",
		"template"	=> '<tr class="trow1"><td><a href="member.php?action=profile&uid={$userid}">{$username}</td><td><a target="_blank" href="https://omnicha.in?address={$address}">{$address}</a></td><td>{$date}</td></tr>',
		"sid"		=> "-1"));
		
	$db->insert_query("templates", array(
		"tid"		=> NULL,
		"title"		=> "BuySigs Listings No Entry",
		"template"	=> '<tr class="trow1"><td colspan=3>{$message}</td></tr>',
		"sid"		=> "-1"));
	
}

function buysigs_deactivate() {
	//Called whenever a plugin is deactivated. This should essentially "hide" the plugin from view by removing templates/template changes etc. It should not, however, remove any information such as tables, fields etc - that should be handled by an _uninstall routine. When a plugin is uninstalled, this routine will also be called before _uninstall() if the plugin is active.

	global $db;
	
	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	$db->delete_query("templates", "title LIKE 'BuySigs Default Page'");
	$db->delete_query("templates", "title LIKE 'BuySigs No Sales'");
	$db->delete_query("templates", "title LIKE 'BuySigs Listings'");
	$db->delete_query("templates", "title LIKE 'BuySigs Listings Entry'");
	$db->delete_query("templates", "title LIKE 'BuySigs Listings No Entry'");
}

function buysigs_usercp_do_editsig_start() {
	//Function to prevent an active status sold signature to be altered.
	
	global $db, $mybb;
}
