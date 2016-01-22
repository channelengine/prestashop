<?php

// This loader is required to make use of the php __autoload functionality.
// Use this loader when not using composer.

/*function __autoload($class) {
	$class = str_replace('\\', '/', $class) . '.php';
	require_once($class);
}*/

// Add the class dir to include path
set_include_path(get_include_path() . PATH_SEPARATOR);

// Use default autoload implementation
spl_autoload_register();