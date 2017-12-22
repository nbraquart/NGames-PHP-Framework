<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__)->exclude('vendor');

return PhpCsFixer\Config::create()->setFinder($finder);