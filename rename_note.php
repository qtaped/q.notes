<?php
    include 'config.php';

    $notesDir = NOTES_DIR;

    // Validate and sanitize the selected dir from the POST request
    if (isset($_POST['dir']) && preg_match('/^[a-zA-Z0-9_-]+$/', $_POST['dir'])) {
        $selectedDir = $_POST['dir'];
    } else {
        echo "ERROR: Invalid directory.";
        http_response_code(400);
        exit(1);
    }

    // Validate and sanitize the old and new filenames from the POST request
    if (isset($_POST['old_name']) && isset($_POST['new_name']) &&
        preg_match('/^[a-zA-Z0-9_-]+\.txt$/', $_POST['old_name']) && 
        preg_match('/^[a-zA-Z0-9_-]+\.txt$/', $_POST['new_name'])) {
        
        $oldFileName = $_POST['old_name'];
        $newFileName = $_POST['new_name'];
        
        // Ensure the new filename (excluding .txt) is no longer than 9 characters
        if (strlen(pathinfo($newFileName, PATHINFO_FILENAME)) > 9) {
            echo "ERROR: New filename exceeds 9 characters.";
            http_response_code(400);
            exit(1);
        }
    } else {
        echo "ERROR: Invalid file names.";
        http_response_code(400);
        exit(1);
    }

    // Define the dir path and prevent directory traversal
    $dirPath = realpath($notesDir . DIRECTORY_SEPARATOR . $selectedDir) . DIRECTORY_SEPARATOR;
    if (strpos($dirPath, realpath($notesDir) . DIRECTORY_SEPARATOR) !== 0) {
        echo "ERROR: Invalid directory path.";
        http_response_code(400);
        exit(1);
    }

    $oldFilePath = $dirPath . $oldFileName;
    $newFilePath = $dirPath . $newFileName;

    // Check if the old file exists
    if (!file_exists($oldFilePath)) {
        echo "ERROR: Old file does not exist.";
        http_response_code(404);
        exit(1);
    }

    // Check if the new file already exists
    if (file_exists($newFilePath)) {
        echo "ERROR: New file name already exists.";
        http_response_code(409);
        exit(1);
    }

    // Rename the file
    if (!rename($oldFilePath, $newFilePath)) {
        echo "ERROR: Could not rename file.";
        http_response_code(500);
        exit(1);
    }

    echo 'Note renamed successfully.';
    exit(0);
?>
