<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

const BUCKET_NAME = 'arrivalbahamas';

$client = (require __DIR__ . '/ceph_s3_bootstrap.php')();

$listResponse = $client->listObjects(['Bucket' => BUCKET_NAME]);
$objects = $listResponse['Contents'] ?? [];