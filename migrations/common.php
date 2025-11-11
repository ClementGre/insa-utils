<?php

use ByJG\DbMigration\Database\MySqlDatabase;
use ByJG\DbMigration\Migration;
use ByJG\Util\Uri;

function getMigrationController(): Migration
{
    require __DIR__ . '/../vendor/autoload.php';

    $connectionUri = new Uri('mysql://' . getenv("DATABASE_USER") . ':' . getenv("DATABASE_PASSWORD") . '@' . getenv("DATABASE_SERVER") . ':' . getenv("DATABASE_PORT") . '/' . getenv("DATABASE_NAME"));

    // Register the Database or Databases can handle that URI:
    Migration::registerDatabase(MySqlDatabase::class);

    // Create the Migration instance
    $migration = new Migration($connectionUri, __DIR__);

    // Add a callback progress function to receive info from the execution
    $migration->addCallbackProgress(function ($action, $currentVersion, $fileInfo) {
        echo "$action, $currentVersion, {$fileInfo['description']}\n";
    });

    return $migration;
}
