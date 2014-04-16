<?php
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $loader = include_once __DIR__ . '/../vendor/autoload.php';
    $loader->add('predaddy', __DIR__ . '/src');
}
Logger::configure(__DIR__ . '/src/resources/log4php.xml');
