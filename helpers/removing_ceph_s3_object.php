<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';


return function (string $bucket_name, string $filename) {

    $client = (require __DIR__ . '/ceph_s3_bootstrap.php')();
    
    $client->deleteObject([
        'Bucket' => $bucket_name,
        'Key' => $filename
    ]);
};