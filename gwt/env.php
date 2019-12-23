<?php

$gwt->true('Test if OS/CPU is 64-bit', PHP_INT_SIZE === 8);
$gwt->true('Test function gettimeofday()', \function_exists('gettimeofday'));
$gwt->true('Test PHP extension json enabled', \extension_loaded('json'));
$gwt->true('Test PHP extension tokenizer enabled', \extension_loaded('tokenizer'));
$gwt->true('Test PHP extension mbstring enabled', \extension_loaded('mbstring'));
$gwt->true('Test PHP extension posix enabled', \extension_loaded('posix'));
$gwt->true('Test PHP extension curl enabled', \extension_loaded('curl'));
