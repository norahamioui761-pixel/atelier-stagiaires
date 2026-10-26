<?php

function getDatabase(): PDO
{
    $host     = getenv('DB_HOST');
    $port     = getenv('DB_PORT');
    $name     = getenv('DB_NAME');
    $user     = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');

    $caPath = dirname(__DIR__) . '/certs/ca.pem';

    // Compatibilité PHP 8.4+ (Pdo\Mysql) et versions plus anciennes (PDO::MYSQL_ATTR_*)
    $sslCaAttr = defined('Pdo\Mysql::ATTR_SSL_CA')
        ? constant('Pdo\Mysql::ATTR_SSL_CA')
        : PDO::MYSQL_ATTR_SSL_CA;
    $sslVerifyAttr = defined('Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT')
        ? constant('Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT')
        : PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT;

    $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        $sslCaAttr                   => $caPath,
        $sslVerifyAttr               => true,
    ];

    return new PDO($dsn, $user, $password, $options);
}
