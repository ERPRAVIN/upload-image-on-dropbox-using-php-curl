<?php
try{

    $refreshToken = '97vQHJifbZcAAAsaAAAAAAAWmyYkQla6tLM5LtA9Chuq5_6NPMF1FfUDM1G3aERc1c';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://api.dropbox.com/oauth2/token');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "grant_type=refresh_token&refresh_token=$refreshToken");
    curl_setopt($curl, CURLOPT_USERPWD, 'o9r7q8wuz4xopel' . ':' . 'vj1gpfq995zeaxm');
    $headersData = array();
    $headersData[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headersData);

    $result = json_decode(curl_exec($curl),true);
    if (curl_errno($curl)) {
        echo 'Error:' . curl_error($curl);
    }
    curl_close($curl);
    $token = $result['access_token'];

    if (isset($_POST['submit'])) {
        $fileName = $_FILES["image"]["name"];
        $tempName = $_FILES["image"]["tmp_name"];
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $extensions_arr = array("jpg", "jpeg", "png", "gif");
        $folder = "/var/www/html/img/" . $fileName;

        if (empty($fileName)) {
            $errors['ImageEmpty'] = "Image is required";
        } else {
            if ($extension != "jpg" && $extension != "png" && $extension != "jpeg"
                && $extension != "gif") {
                $errors['ImageEmpty'] = "File must be jpg, jpeg, png or gif";
            }
        }
        if (!$errors) {
            move_uploaded_file($tempName, $folder);
            try {
                $path = $folder;
                $fp = fopen($path, 'rb');
                $size = filesize($path);

                $cheaders = array('Authorization: Bearer ' . $token,
                    'Content-Type: application/octet-stream',
                    'Dropbox-API-Arg: {"path":"/demo/' . $fileName . '", "mode":"add"}');

                $ch = curl_init('https://content.dropboxapi.com/2/files/upload');
                curl_setopt($ch, CURLOPT_HTTPHEADER, $cheaders);
                curl_setopt($ch, CURLOPT_PUT, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_INFILE, $fp);
                curl_setopt($ch, CURLOPT_INFILESIZE, $size);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                unlink($folder);
                curl_close($ch);
                fclose($fp);
                header("location: dropbox_finish.php");
            } catch (\exception$e) {
                print_r($e);
            }
        }
    }
} catch (\exception$e) {
    print_r($e);
}
?>
    <!doctype html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <title>Upload File</title>
        <style>
        .error {
            color: #FF0000;
            font-size: 18px;
        }
    </style>
    </head>

    <body>
    <div class="container my-5 ">
        <form class="row g-3" method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3 col-md-4">
                <label for="file" class="form-label ">Upload Image</label>
                <input class="form-control" type="file" name="image" id="file" ><span class="error">
                    <?php echo $errors['ImageEmpty']; ?>
                </span>
            </div>
            <div>
                <button type="submit" name="submit" class="btn btn-primary mb-3">Submit</button>
            </div>
        </form>
    </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
    </html>

