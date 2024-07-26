<?php

require dirname(__DIR__) . '/helpers/process_ceph_s3.php';

const BUCKET_NAME = 'arrivalbahamas';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strtoupper($_POST['_method']) === 'PATCH') {
    $document_number = $_POST['document_number'];
    
    $document_upload = upload_photo(BUCKET_NAME, $document_number, $_FILES['document_upload']);

    if (!$document_upload || $document_upload['error']) {
        echo json_encode([
            'success' => false,
            'message' => 'File upload failed: ' . ($document_upload['error'] ?? 'Unknown error'),
        ]);
        exit();
    }

    header('location: /');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Ceph S3 Object</title>
    <link rel="stylesheet" href="https://unpkg.com/sakura.css/css/sakura.css" />
</head>
<body>
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="_method" value="patch">
        <label for="document_number">Document Number</label>
        <input type="text" name="document_number" id="document_number">
        <label for="upload">Document Upload</label>
        <div>
            <img id="pic" alt="upload preview image" src="" />
            <input type="file" name="document_upload" id="document_upload">
        </div>
        <button type="submit">Submit</button>
    </form>
    <script>
        const imageInput = document.querySelector('#document_upload')
        const img = document.querySelector('#pic')

        window.addEventListener('load', function() {
            img.style.display = 'none'
        })

        imageInput.addEventListener('input', function() {
            img.style.display = 'block'
            img.src = window.URL.createObjectURL(this.files[0])
        })
    </script>
</body>
</html>