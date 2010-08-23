<?php
/* 
 * This is the part of SiMBiOSis
 * coyright  Roydon roydon@don.com
 */

require_once __DIR__.'/../blog/BlogKernel.php';

$kernel = new BlogKernel('dev', true);
$kernel->handle()->send();
