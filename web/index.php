<?php


require_once __DIR__.'/../blog/BlogKernel.php';

$kernel = new BlogKernel('dev', true);
$kernel->handle()->send();
