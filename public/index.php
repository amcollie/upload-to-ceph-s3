<?php

require dirname(__DIR__) . '/helpers/process_ceph_s3.php';

const BUCKET_NAME = 'arrivalbahamas';
const BASE_IMAGE_PATH = 'https://s3-nas.cloud.gov.bs/' . BUCKET_NAME. '/';

$objects = list_ceph_s3_bucket_objects(BUCKET_NAME);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ceph S3</title>
    <link rel="stylesheet" href="https://unpkg.com/sakura.css/css/sakura.css" />
    <style>
        body ul li {
            display: flex;
            flex-flow: column;
            align-items: center;
            justify-content: center;
            padding-bottom: 20px;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }
    </style>
</head>
<body>
    <ul>
        <?php foreach ($objects as $object): ?>
            <li>
                <?php
                    $imageUrl = ceph_s3_bootstrap()->getObject(['Bucket' => BUCKET_NAME, 'Key' => $object['Key']]); 
                    $ext = pathinfo($object['Key'], PATHINFO_EXTENSION);
                    $data = base64_encode($imageUrl);
                ?>
                <img src="<?= BASE_IMAGE_PATH . $object['Key'] ?>" alt="<?= $object['Key'] ?>">
                <?= '&ensp;' . $object['Size'] . '&ensp;' . $object['LastModified'] . PHP_EOL ?>
            </li>
        <?php endforeach; ?>
    </ul>
</body>
</html>