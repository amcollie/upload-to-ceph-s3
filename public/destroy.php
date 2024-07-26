<?php

declare(strict_types=1);

require dirname(__DIR__) . '/helpers/process_ceph_s3.php';

const BUCKET_NAME = 'arrivalbahamas';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && strtoupper($_POST['_method']) === 'DELETE') {
    remove_ceph_s3_object(BUCKET_NAME, $_POST['filename']);
    header('location: /');
    exit();
}

$objects = list_ceph_s3_bucket_objects(BUCKET_NAME);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Delete Object</title>
</head>
<body>
    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
        <input type="hidden" name="_method" value="delete"
        <label for="filename">File Name:</label>
        <select name="filename" id="filename">
            <option value="">Please select a file to delete</option>
                <?php foreach ($objects as $object): ?>
                    <option value="<?= $object['Key'] ?>"><?= $object['Key'] ?></option>
                <?php endforeach; ?>
        </select>
        <img id="pic" alt="upload preview image" src="" />
        <button id="delete-button" type="submit">Delete File</button>
    </form>
    <script>
        const dropdownMenu = document.querySelector('#filename')
        const preview = document.querySelector('#pic')
        const deleteButton = document.querySelector('#delete-button')

        window.addEventListener('load', function() {
            preview.style.display = 'none'
        })

        dropdownMenu.addEventListener('change', function() {
            preview.style.display = 'block'

            const imageFilename = dropdownMenu.value
            if (imageFilename != '') {
                preview.src = 'https://s3-nas.cloud.gov.bs/arrivalbahamas/' + imageFilename
            } else {
                preview.style.display = 'none'
            }

        })
    </script>
</body>
</html>