<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Meilisearch\Client;

global $client;
$port = '7700';
if (getenv("MEILISEARCH_PORT") !== false) {
    $port = getenv("MEILISEARCH_PORT");
}
$client = new Client('http://' . getenv("MEILISEARCH_HOST") . ':7700', getenv("MEILISEARCH_KEY"));
