<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Palm Oil Ingredient Scanner</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            margin: 0;
            padding: 0;
        }
        .container {
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            max-width: 600px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }
        .highlight {
            color: green;
            font-weight: bold;
        }
        video, canvas {
            display: none;
            margin: 10px auto;
        }
        img {
            max-width: 100%;
            margin-top: 20px;
            border: 2px solid #ccc;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Upload Ingredient Image or Use Camera</h2>

    <form action="" method="post" enctype="multipart/form-data" id="uploadForm">
        <input type="file" name="image" accept="image/*">
        <br><br>
        <button type="submit">Analyze Image</button>
    </form>

    <hr>
    <p><i>*You can upload an image or take a photo with your camera*</i></p>

    <h3>Or Take a Live Camera Snapshot</h3>
    <button onclick="startCamera()">Start Camera</button>
    <button onclick="flipCamera()">Flip Camera</button>
    <br><br>
    <video id="video" width="300" autoplay playsinline></video>
    <canvas id="canvas" width="300" height="300"></canvas>
    <br>
    <button onclick="captureImage()">Capture & Analyze</button>
    <br><br>
    <form id="cameraForm" method="post" enctype="multipart/form-data" style="display:none;">
        <input type="hidden" name="camera_image" id="cameraImage">
    </form>

    <br><button type="button" onclick="window.location.href = window.location.pathname;">Scan Another Item</button>


    <br><br>

<?php
function saveBase64Image($base64, $outputFile) {
    $data = explode(',', $base64);
    $decoded = base64_decode($data[1]);
    file_put_contents($outputFile, $decoded);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir);

    $filename = '';
    if (isset($_FILES['image'])) {
        $filename = basename($_FILES['image']['name']);
        $filepath = $uploadDir . $filename;
        move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
    } elseif (isset($_POST['camera_image'])) {
        $filename = 'captured_' . time() . '.png';
        $filepath = $uploadDir . $filename;
        saveBase64Image($_POST['camera_image'], $filepath);
    }

    if (!empty($filepath)) {
        $command = escapeshellcmd("python3 analyze.py " . escapeshellarg($filepath));
        $output = shell_exec($command);

        $status = $color = $message = $keywords = $resultImage = '';
        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            if (strpos($line, "STATUS:") !== false) {
                $status = trim(str_replace("STATUS:", "", $line));
            } elseif (strpos($line, "COLOR:") !== false) {
                $color = trim(str_replace("COLOR:", "", $line));
            } elseif (strpos($line, "MESSAGE:") !== false) {
                $message = trim(str_replace("MESSAGE:", "", $line));
            } elseif (strpos($line, "KEYWORDS:") !== false) {
                $keywords = trim(str_replace("KEYWORDS:", "", $line));
            } elseif (strpos($line, "RESULT_IMAGE:") !== false) {
                $resultImage = trim(str_replace("RESULT_IMAGE:", "", $line));
            }
        }

        $boxColor = $color === 'green' ? '#4CAF50' : ($color === 'yellow' ? '#ffc107' : '#f44336');

        echo "<div style='background-color: $boxColor; padding: 20px; border-radius: 10px; color: white;'>";
        echo "<strong>Result:</strong><br>";
        echo "STATUS: $status<br>";
        echo "COLOR: $color<br>";
        echo "MESSAGE: $message<br>";
        echo "KEYWORDS: <span class='highlight'>$keywords</span><br>";

        if (!empty($resultImage)) {
            echo "<br><img src='uploads/$resultImage' alt='Analyzed Image with Highlight'>";
        }
        echo "</div>";
    }
}
?>
</div>

<script>
let video = document.getElementById('video');
let canvas = document.getElementById('canvas');
let cameraImage = document.getElementById('cameraImage');
let currentStream;
let usingFrontCamera = true;

function startCamera() {
    if (currentStream) {
        currentStream.getTracks().forEach(track => track.stop());
    }

    const constraints = {
        video: {
            facingMode: usingFrontCamera ? 'user' : 'environment'
        }
    };

    navigator.mediaDevices.getUserMedia(constraints)
        .then(stream => {
            currentStream = stream;
            video.srcObject = stream;
            video.style.display = 'block';
        })
        .catch(error => {
            alert('Camera error: ' + error);
        });
}

function flipCamera() {
    usingFrontCamera = !usingFrontCamera;
    startCamera();
}

function captureImage() {
    canvas.style.display = 'block';
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

    let imageData = canvas.toDataURL('image/png');
    cameraImage.value = imageData;

    document.getElementById('cameraForm').submit();
}
</script>
</body>
</html>
