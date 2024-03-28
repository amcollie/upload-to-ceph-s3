<?php

declare(strict_types=1);

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Aws\Sdk;
use Dotenv\Dotenv;

require_once dirname(__DIR__) . '/vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

function ceph_s3_bootstrap(): S3Client {
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
}

function list_ceph_s3_bucket_objects(string $bucket_name): mixed {
    $client = ceph_s3_bootstrap();

    $listResponse = $client->listObjects(['Bucket' => BUCKET_NAME]);
    
    return $listResponse['Contents'] ?? [];
}

/**
 * Verifies the file MIME type, file size and file error
 *
 * @param string $file_type
 * @param int $file_size
 * @param int $file_error
 *
 * @return array
 */
function verify_file(string $file_type, int $file_size, int $file_error): array
{
    $max_upload_file_size = 3 * 1024 * 1024;

    $result = [
        "msg" => "",
        "error" => false,
    ];

    // Verify that the file is the valid MIME type
    $allowed_mime_type = ["image/jpeg", "image/png", "image/gif", "image/heic"];
    if (!in_array($file_type, $allowed_mime_type)) {
        $result["msg"] = "MIME type not supported.";
        $result["error"] = true;
    } elseif ($file_error != 0) {
        $result["msg"] = "An error occurred while uploading file.";
        $result["error"] = true;
    } elseif ($file_size > $max_upload_file_size) {
        $result["msg"] = "The uploaded file exceeds the file exceeds size.";
        $result["error"] = true;
    }

    return $result;
}

/**
 * Upload an image to directory /uploads/passport/{current year} and
 * sets the name of the file based on passport number and file extension.
 *
 * @param string $bucket_name
 * @param string $filename
 * @param array $file
 *
 * @return array
 */
function upload_photo(string $bucket_name, string $filename, array $file): array
{
    $result = [];
    // Verify that file exists
    if (is_null($file)) {
        $result["msg"] = "Upload file was empty.";
        $result["error"] = true;
    }

    $file_tmp_name = $file["tmp_name"];
    $file_size = $file["size"];
    $file_type = $file["type"];
    $file_error = $file["error"];
    $mime_type_parts = explode("/", $file_type);
    $file_ext = strtolower(end($mime_type_parts));

    $current_date = date("Y-m-d");

    $verification_result = verify_file($file_type, $file_size, $file_error);
    if (
        array_key_exists("error", $verification_result) &&
        $verification_result["error"]
    ) {
        return $verification_result;
    }

    $file_new_name = $filename . "_" . $current_date . "." . $file_ext;

    $client = ceph_s3_bootstrap();
    if (!$client->doesBucketExist($bucket_name)) {
        $client->createBucket([
            "ACL" => "public-read",
            "Bucket" => $bucket_name,
            "CreateBucketConfiguration" => [
                "LocationConstraint" => $_ENV["AWS_S3_BUCKET_LOCATION"],
            ],
        ]);
    }

    try {
        $res = $client->putObject([
            "ACL" => "public-read",
            "Bucket" => $bucket_name,
            "Key" => $file_new_name,
            "SourceFile" => $file_tmp_name,
            "ContentType" => $file_type,
        ]);
    } catch (S3Exception $e) {
        $result["msg"] = $e->getMessage();
        $result["error"] = true;
        return $result;
    }

    return ["uri" => $res["@metadata"]["effectiveUri"]];
}

function remove_ceph_s3_object(string $bucket_name, string $filename): array {
    $result = [];
    $client = (require __DIR__ . '/ceph_s3_bootstrap.php')();

    try {
        $client->deleteObject([
            'Bucket' => $bucket_name,
            'Key' => $filename
        ]);
        
        $result['error'] = false;
        $result['msg'] = 'Object deleted successfully.';
        
        return $result;
    } catch (S3Exception $e) {
        http_response_code(500);
        $result['msg'] = $e->getMessage();
        $result['error'] = true;
            
        return $result;
    }
    
}