<?php
    include 'config.php';

    $notesDir = NOTES_DIR;
    $notesLimit = NOTES_LIMIT;

    // Validate and sanitize the selected dir from the POST request
    if (isset($_POST['dir']) && preg_match('/^[a-zA-Z0-9_-]+$/', $_POST['dir'])) {
        $selectedDir = $_POST['dir'];
    } else {
        echo "ERROR: Invalid directory.";
        header("Location: .");
        exit(1);
    }

    // Define the dir path and prevent directory traversal
    $dirPath = realpath($notesDir . DIRECTORY_SEPARATOR . $selectedDir) . DIRECTORY_SEPARATOR;
    if (strpos($dirPath, realpath($notesDir)) !== 0) {
        echo "ERROR: Invalid directory path.";
        http_response_code(400);
        exit(1);
    }

    // Get the list of txt files in the selected dir
    $files = array_diff(scandir($dirPath), array('..', '.'));
    $textFiles = array_filter($files, function ($fileName) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'txt';
    });
    $filesCount = count($textFiles);

    if ($filesCount >= $notesLimit) {
        echo "ERROR: Exceed note limit.";
        http_response_code(500);
        exit(1);
    }

    // Extract numeric parts from filenames and find the maximum
    $maxNumber = 0;
    foreach ($files as $file) {
        $numericPart = intval(pathinfo($file, PATHINFO_FILENAME));
        if ($numericPart > $maxNumber) {
            $maxNumber = $numericPart;
        }
    }

    // Increment the maximum number by 1 to create the new filename
    $newNumber = ($maxNumber + 1);
    $newFileName = $newNumber . ".txt";

    $loremIpsum = '<h2>new <font color="#ff8700">note</font></h2><p>#'. htmlspecialchars($newNumber, ENT_QUOTES, 'UTF-8') .' - created in dir: <i><font color="#ff8700">'. htmlspecialchars($selectedDir, ENT_QUOTES, 'UTF-8') .'</font></i></p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>';

    // Create an empty file in the selected dir and write the Lorem Ipsum text to it
    $file = fopen($dirPath . $newFileName, 'w');
    if ($file === false) {
        echo "ERROR: Could not create file.";
        http_response_code(500);
        exit(1);
    }

    if (fwrite($file, $loremIpsum) === false) {
        echo "ERROR: Could not write to file.";
        http_response_code(500);
        fclose($file);
        exit(1);
    }

    fclose($file);

    echo 'note created';

    exit(0);
?>
