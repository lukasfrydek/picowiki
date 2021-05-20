<?php

define('DS', DIRECTORY_SEPARATOR);
define('PHP', '.php');
define('MD', '.md');

define('HOST', getenv('HTTP_HOST'));
define('BASE_URL', dirname(getenv('SCRIPT_NAME')) );
define('CURRENT_URL', trim(str_replace(BASE_URL, '', getenv('REQUEST_URI')), '/'));

define('ROOT', __DIR__ . DS);
define('PATH_BACKEND', ROOT . 'backend' . DS);
define('PATH_FILES', ROOT . 'files' . DS);
define('PATH_TEMPLATES', PATH_BACKEND . 'templates' . DS);
define('PATH_PLUGINS', PATH_BACKEND . 'plugins' . DS);


$config = [
	'app_name' => 'PicoWiki',
	'app_url' => null, // (auto-detected, although you can manually specify it if you need to)
	'version' => '1.2.0',
	'theme' => 'default',
];

function dd() {
	echo '<pre>'. print_r( (func_num_args()>1 ? func_get_args() : func_get_arg(0)), true) .'</pre>'."\n\n";
}

require_once PATH_BACKEND . 'picowiki' . PHP;


$PicoWiki = new PicoWiki($config);
$PicoWiki->run();
