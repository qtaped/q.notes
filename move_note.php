<?php
    include 'config.php';

    $notesDir = NOTES_DIR;
    $notesLimit = NOTES_LIMIT;

// Limit dirs

    // List of dirs
    $dirs = array_filter(glob($notesDir . '*'), 'is_dir');
    $dirNames = array_map('basename', $dirs);
    // Get the selected dirs for moving and current notes dir
    if (isset($_POST['selectedDir']) && isset($_POST['destinationDir'])) {
         $selectedDir = substr(preg_replace('/[^a-zA-Z0-9-_]/', '', $_POST['selectedDir']), 0, 16);
         $destinationDir = substr(preg_replace('/[^a-zA-Z0-9-_]/', '', $_POST['destinationDir']), 0, 16);
    } else {
        echo "ERROR: script should not be run directly."; exit(1);
    }

    $allowedDirs = array(); // Initialize an empty array for allowed dirs

    foreach ($dirNames as $dir) {
        if ($dir !== $selectedDir) {
            $allowedDirs[] = $dir;
            }
    }

    // Exit if destination dir is not in allowed dirs list
    if (!in_array($destinationDir, $allowedDirs)) {
        http_response_code(500); // Internal Server Error
        exit;
    }

    // Get the list of filenames to move from the POST request
    $noteNames = json_decode($_POST['noteNames'], true);
    asort($noteNames, SORT_DESC); // sort descending order
    $movedFilesCount = count($noteNames);

    // Define the source and destination dir paths
    $sourceDirPath = $notesDir . $selectedDir . "/";
    $destinationDirPath = $notesDir . $destinationDir . "/";

        // Get the list of txt files in the destination dir to check the limit
        $files = array_diff(scandir($destinationDirPath), array('..', '.'));
        $textFiles = array_filter($files, function ($noteName) {
            return pathinfo($noteName, PATHINFO_EXTENSION) === 'txt';
        });
        $notesCount = count($textFiles);
        
        if ($notesCount + $movedFilesCount >= $notesLimit) {
            echo "ERROR: Exceed $notesLimit notes.";
            http_response_code(500);
            exit(1);
        }

    foreach ($noteNames as $noteName) { 
         // Move the file
        $sourceFilePath = $sourceDirPath . $noteName;
        $destinationFilePath = $destinationDirPath . $noteName;
        if (file_exists($sourceFilePath)) {
            if (file_exists($destinationFilePath)) {
             // Find the highest numeric filename in the destination dir
            $maxNumber = 0;
            foreach ($textFiles as $note) {
                if (preg_match('/(\d+)(?!.*\d)/', pathinfo($note, PATHINFO_FILENAME), $matches)) {
                        $numericPart = $matches[1];
                        if ($numericPart > $maxNumber) {
                            $maxNumber = intval($numericPart);
                        }
                    } else {
                        $maxNumber = 1;
                }
             }

            $newNumber = ($maxNumber + 1);

            while (true) {
                $pattern = $destinationDirPath . '/*' . $newNumber . '.txt';
                $numFiles = glob($pattern);
                if (count($numFiles) > 0) {
                    $newNumber++;
                } else {
                    break;
                }
            }

            if (is_numeric(pathinfo($noteName, PATHINFO_FILENAME))) {
                $newFileName = $newNumber . ".txt";
            } else {
                $newFileName = preg_replace('/\d+$/', '', pathinfo($noteName, PATHINFO_FILENAME)) . $newNumber . ".txt";
            }
            rename($sourceFilePath, $destinationDirPath . '/' . $newFileName);
            } else {
                rename($sourceFilePath, $destinationFilePath);
                   }
        } else {
            echo "Error: File '$noteName' does not exist in the source dir.";
            exit;
         }
     }
?>

