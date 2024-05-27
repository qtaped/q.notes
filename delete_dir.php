<?php
    include 'config.php';

    $notesDir = NOTES_DIR;

    // Validate and sanitize the directory name to be deleted from the POST request
    if (isset($_POST['deldir']) && preg_match('/^[a-zA-Z0-9_-]+$/', $_POST['deldir'])) {  
        $delDir = $_POST['deldir'];
        $sanitizedDelDir = preg_replace('/[^a-zA-Z0-9-_]/', '', $delDir);
        $delDirPath = realpath($notesDir) . DIRECTORY_SEPARATOR . $sanitizedDelDir;

        // Ensure the delete directory path is within the allowed notes directory
        if (strpos($delDirPath, realpath($notesDir)) !== 0) {
            echo "ERROR: Invalid directory path.";
            http_response_code(400);
            exit(1);
        }
    } else {
        echo "ERROR: script should not be run directly.";
        header("Location: .");
        exit(1);    
    }

    // Check if the directory exists and is empty
    if (is_dir($delDirPath)) {  
        if (!glob("$delDirPath/*")) {
            if (rmdir($delDirPath)) {
                echo "Directory deleted successfully.";
            } else {
                echo "ERROR: Could not delete directory.";
                http_response_code(500);
                exit(1);
            }
        } else {
            echo "ERROR: Directory contains files and cannot be deleted.";
            http_response_code(400);
            exit(1);
        }
    } else { 
        echo "ERROR: Directory does not exist.";    
        http_response_code(400);
        exit(1);
    }
?>
