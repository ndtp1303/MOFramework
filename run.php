<?php

require __DIR__ . '/SuSu/Platform/MOFramework.php';

use SuSu\Platform\MOFramework;

$MOFramework = new MOFramework;
$MOFramework->setViewPath(__DIR__ . '/views');
$MOFramework->setErrorPage(500, '/500');
$MOFramework->setErrorPage(404, '/404');
$MOFramework->setDefaultView('/home');
$MOFramework->setFileExtension('.mo');
$MOFramework->run();
