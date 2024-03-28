<?php

declare(strict_types=1);

use Aws\S3\S3Client;
use Aws\Sdk;
use Dotenv\Dotenv;

require_once dirname(__DIR__) . "/vendor/autoload.php";

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

return function (): S3Client {
    $config = [
        "region" => $_ENV["AWS_S3_REGION"],
        "version" => "latest",
        "endpoint" => $_ENV["AWS_S3_ENDPOINT"],
        "credentials" => [
            "user" => $_ENV["AWS_S3_USER"],
            "key" => $_ENV["AWS_ACCESS_KEY_ID"],
            "secret" => $_ENV["AWS_SECRET_ACCESS_KEY"],
        ],
        "use_path_style_endpoint" => true,
    ];

    $sdk = new Sdk($config);

    return $sdk->createS3();
};
