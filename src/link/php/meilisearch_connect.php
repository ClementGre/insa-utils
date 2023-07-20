<?php
require_once __DIR__ . '/../../../vendor/autoload.php';
use Meilisearch\Client;

global $client;
$host = '127.0.0.1';
if (str_starts_with($_SERVER['HTTP_HOST'], 'localhost')) {
    $host = 'pdf4teachers.org';
}
$client = new Client('http://' . $host . ':7700', getenv("MEILISEARCH_KEY"));

