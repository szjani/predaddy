<?php
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $loader = include __DIR__ . '/../vendor/autoload.php';
    $loader->add('predaddy', __DIR__ . '/src');
}
Logger::configure(__DIR__ . '/src/resources/log4php.xml');
\predaddy\messagehandling\annotation\AnnotatedMessageHandlerDescriptor::setReader(
    new \Doctrine\Common\Annotations\CachedReader(
        new \Doctrine\Common\Annotations\AnnotationReader(),
        new \Doctrine\Common\Cache\ArrayCache()
    )
);
