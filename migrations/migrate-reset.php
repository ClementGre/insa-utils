<?php

require __DIR__ . '/common.php';
$migration = getMigrationController();

// Restore the database using the "base.sql" script
// and run ALL existing scripts for up the database version to the latest version
$migration->reset();
