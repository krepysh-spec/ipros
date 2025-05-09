<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in(__DIR__ . '/src');

$config = (new Config())->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect());

return $config->setRules([
    'declare_strict_types' => true
])
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setFinder($finder);