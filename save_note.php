<?php
    include 'config.php';

    $notesDir = NOTES_DIR;
    $sizeLimit = NOTE_SIZE_LIMIT;

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

    // Get the filename and content from the POST request
    if (isset($_POST['fileName']) && preg_match('/^[a-zA-Z0-9-_]+\.txt$/', $_POST['fileName'])) {
        $fileName = $_POST['fileName'];
    } else {
        echo "ERROR: Invalid file name.";
        exit(1);
    }

    $content = isset($_POST['content']) ? $_POST['content'] : exit(1);

    if (empty($content)) {
        $content = ' ';
    }

    // Calculate the size of the content in bytes
    $contentSize = strlen($content);

    // Check if the content size exceeds the limit
    if ($contentSize <= $sizeLimit) {
        // Save the content to the file in the selected dir
        if (!file_put_contents($dirPath . $fileName, $content)) {
            http_response_code(500); // Set the error status code to 500
            echo 'Error writing file. Check permissions or try again.';
            exit(1);
        }
    } else {
        // Respond with an error message if content size exceeds the limit
        echo "ERROR: Content size exceeds the limit of $sizeLimit bytes.";
        http_response_code(400);
        exit(1);
    }
?>
