<?php

// Load dependencies
require 'core/mysql.php';
require 'core/firewall.php';
require 'core/shielding.php';
require 'core/wrapper.php';
require 'source/mailenable.php';
require 'source/winevent.php';

// Setup environment
if(PHP_SAPI != 'cli') exit;
chdir(__DIR__);

// Setup shielding
$shielding = new Shielding(
	
	// Sources
	[
		new MailEnable('<path to SMTP logs>'),
		new WinEvent(),
	],
	
	// Store
	new MySQL('<username>', '<password>', '<dbname>'),
	
	// Target
	new Firewall([
		'name' => '<name>',
		'description' => '<description>',
	]),
);

// Set a badness threshold
$shielding->threshold = 6;

// Setup wrapper
$wrapper = new Wrapper([
	'service' => '<name>',
	'display' => '<displayname>',
	'description' => utf8_decode('<description>'),
	'params' => '"'.__FILE__.'" start',
],[
	'logpath' => '<directory>',
	'runner' => [$shielding, 'cycle'],
	'interval' => 500000,
	'pause' => 60,
]);

// Handle command line argument
switch($argv[1] ?? NULL) {
	
	// Install service and shielding
	case 'install': {
		$wrapper->toggleServiceInstallation(true);
		$shielding->install();
	} break;
	
	// Uninstall service and shielding
	case 'uninstall': {
		$wrapper->toggleServiceInstallation(false);
		$shielding->uninstall();
	} break;
	
	// Start service
	case 'start': {
		$wrapper->startService();
	} break;
	
	// Invalid argument
	default: throw new InvalidArgumentException();
}