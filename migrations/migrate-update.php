<?php

require __DIR__ . '/common.php';
$migration = getMigrationController();

if (!$migration->isDatabaseVersioned()) {
    $migration->reset();
}

// Run ALL existing scripts for up or down the database version
// from the current version until the $version number;
// If the version number is not specified migrate until the last database version
$migration->update($version = null);
