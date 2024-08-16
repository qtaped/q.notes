<?php
    include 'config.php';

    $notesDir = NOTES_DIR;

    // Validate and sanitize the selected dir from the POST request
    if (isset($_POST['dir']) && preg_match('/^[a-zA-Z0-9_-]+$/', $_POST['dir'])) {
        $selectedDir = $_POST['dir'];
    } else {
        echo "ERROR: Invalid directory.";
        header("Location: .");
        http_response_code(400);
        exit(1);
    }

    // Define the dir path and prevent directory traversal
    $dirPath = realpath($notesDir . DIRECTORY_SEPARATOR . $selectedDir) . DIRECTORY_SEPARATOR;
    if (strpos($dirPath, realpath($notesDir)) !== 0) {
        echo "ERROR: Invalid directory path.";
        http_response_code(400);
        exit(1);
    }

    // Get the list of filenames to delete from the POST request
    $noteNames = json_decode($_POST['noteNames'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "ERROR: Invalid JSON input.";
        http_response_code(400);
        exit(1);
    }

    // Validate file names
    foreach ($noteNames as $noteName) {
        if (!preg_match('/^[a-zA-Z0-9_-]+\.txt$/', $noteName)) {
            echo "ERROR: Invalid note name detected.";
            http_response_code(400);
            exit(1);
        }
    }

    // Iterate through the filenames and delete each note from the selected dir
    foreach ($noteNames as $noteName) {
        $notePath = $dirPath . $noteName;
        if (file_exists($notePath)) {
            if (!unlink($notePath)) {
                echo "ERROR: Could not delete note " . htmlspecialchars($noteName, ENT_QUOTES, 'UTF-8') . ".";
                http_response_code(500);
                exit(1);
            }
        } else {
            echo "ERROR: File " . htmlspecialchars($noteName, ENT_QUOTES, 'UTF-8') . " does not exist.";
            http_response_code(400);
            exit(1);
        }
    }

    echo 'Files deleted successfully.';
?>
