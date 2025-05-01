<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/src');

$config = (new Config())->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect());
return $config->setRules([
    'strict_param' => true
])
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setFinder($finder);