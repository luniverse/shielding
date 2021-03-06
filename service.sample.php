<?php

// Setup environment
if(PHP_SAPI != 'cli') exit;
chdir(__DIR__);

// Load dependencies
require 'core/mysql.php';
require 'core/firewall.php';
require 'core/shielding.php';
require 'core/win32service.php';
require 'adapter/mailenable.php';
require 'adapter/winevent.php';

// Setup shielding
$shielding = new Shielding(
	
	// Adapters
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

// Setup service
$service = new Win32service([
	'service' => '<name>',
	'display' => '<displayname>',
	'description' => utf8_decode('<description>'),
	'params' => '"'.__FILE__.'" start',
	'dependencies' => ['MySQL'],
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
		$service->install(true);
		$shielding->install();
	} break;
	
	// Uninstall service and shielding
	case 'uninstall': {
		$service->install(false);
		$shielding->uninstall();
	} break;
	
	// Start service
	case 'start': {
		$service->start();
	} break;
	
	// Invalid argument
	default: throw new InvalidArgumentException();
}
