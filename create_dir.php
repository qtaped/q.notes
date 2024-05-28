<?php
    include 'config.php';

    $notesDir = NOTES_DIR;
    $notesDirLimit = NOTES_DIR_LIMIT;

    // Validate and sanitize the new directory name from the POST request
    if (isset($_POST['newdir'])) {
        $newDir = $_POST['newdir'];
        $sanitizedNewDir = substr(preg_replace('/[^a-zA-Z0-9-_]/', '', $newDir), 0, 16);
        $newDirPath = realpath($notesDir) . DIRECTORY_SEPARATOR . $sanitizedNewDir;

        // Ensure the new directory path is within the allowed notes directory
        if (strpos($newDirPath, realpath($notesDir)) !== 0) {
            echo "ERROR: Invalid directory path.";
            http_response_code(400);
            exit(1);
        }
    } else {
        echo "ERROR: script should not be run directly.";
        header("Location: .");
        exit(1);
    }

    $loremIpsum = '<h1>First note</h1><p>in new dir <i><font color="#ff8700">'. htmlspecialchars($sanitizedNewDir, ENT_QUOTES, 'UTF-8') .'</font></i>!</p><ul><li>create</li><li>edit</li><li>move</li><li>delete</li></ul>';

    // Count directories
    $dirsPath = realpath($notesDir) . DIRECTORY_SEPARATOR;
    $dirs = array_diff(scandir($dirsPath), array('..', '.'));
    $dirsCount = count($dirs);

    if ($dirsCount >= $notesDirLimit) {
        echo "ERROR: Exceed dir limit.";
        http_response_code(500);
        exit(1);
    }

    // Create the new directory if it doesn't exist and write the Lorem Ipsum text to a file
    if (!is_dir($newDirPath)) {
        if (mkdir($newDirPath, 0744)) {
            if (file_put_contents($newDirPath . '/1.txt', $loremIpsum) === false) {
                echo "ERROR: Could not write to file.";
                http_response_code(500);
                exit(1);
            }
        } else {
            echo "ERROR: Could not create directory.";
            http_response_code(500);
            exit(1);
        }
    } else {
        echo "ERROR: Directory already exists.";
        http_response_code(400);
        exit(1);
    }
?>
