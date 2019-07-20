<?php

use JobTest\Model\ArticleRepository;
use JobTest\Persistence\InMemoryArticleRepository;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

return [
    // Configure PDO
    ArticleRepository::class => static function () {
        $dsn = 'mysql:dbname=job;host=percona;charset=UTF8';
        $username = 'root';
        $passwd = 'root';
        $pdo = new PDO($dsn, $username, $passwd);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return new InMemoryArticleRepository($pdo);
    },

    // Configure Twig
    Environment::class => static function () {
        $loader = new FilesystemLoader(__DIR__ . '/../src/Views');
        return new Environment($loader);
    },
];
