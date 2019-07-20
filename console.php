<?php

use JobTest\Command\ArticleParseCommand;

$container = require __DIR__ . '/app/bootstrap.php';

$app = new Silly\Application();

// Silly will use PHP-DI for dependency injection based on type-hints
$app->useContainer($container, $injectWithTypeHint = true);

$app->command('parse [--limit=] [--project=]', ArticleParseCommand::class)
    ->defaults([
        'limit' => 15,
        'project'  => 'rbcnews.spb_sz',
    ])->descriptions('Парсит новости с сайта rbc.ru', [
        '--limit'   => 'Как много записей надо добавить в БД?',
        '--project' => 'Источник новостей',
    ]);

/** @noinspection PhpUnhandledExceptionInspection */
$app->run();
