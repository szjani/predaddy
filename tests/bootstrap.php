<?php
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    include_once __DIR__ . '/../vendor/autoload.php';
}
Logger::configure(__DIR__ . '/src/resources/log4php.xml');
