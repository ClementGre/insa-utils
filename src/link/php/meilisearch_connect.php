<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
use Meilisearch\Client;

global $client;
$client = new Client('http://93.90.202.93:7700', getenv("MEILISEARCH_KEY"));

