<?php

use Symfony\Component\Yaml\Yaml;

$config = [];

$config['ROOT_DIR'] = getcwd();
$config['ENV'] = getenv('APP_ENV') ?: 'development';

$phinx = Yaml::parse(file_get_contents(__DIR__ . '/../phinx.yml'));
$config['DB_NAME'] = $phinx['environments'][$config['ENV']]['name'];
$config['DB_PATH'] = $config['ENV'] === 'test'
    ? $config['DB_NAME']
    : $config['ROOT_DIR'] . '/' . $config['DB_NAME'];

return $config;
