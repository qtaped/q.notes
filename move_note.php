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
    $fileNames = json_decode($_POST['fileNames'], true);
    asort($fileNames, SORT_DESC); // sort descending order
    $movedFilesCount = count($fileNames);

    // Define the source and destination dir paths
    $sourceDirPath = $notesDir . $selectedDir . "/";
    $destinationDirPath = $notesDir . $destinationDir . "/";

        // Get the list of txt files in the destination dir to check the limit
        $files = array_diff(scandir($destinationDirPath), array('..', '.'));
        $textFiles = array_filter($files, function ($fileName) {
            return pathinfo($fileName, PATHINFO_EXTENSION) === 'txt';
        });
        $filesCount = count($textFiles);
        
        if ($filesCount + $movedFilesCount >= $notesLimit) {
            echo "ERROR: Exceed $notesLimit notes.";
            http_response_code(500);
            exit(1);
        }

    foreach ($fileNames as $fileName) { 
         // Move the file
        $sourceFilePath = $sourceDirPath . $fileName;
        $destinationFilePath = $destinationDirPath . $fileName;
        if (file_exists($sourceFilePath)) {
            if (file_exists($destinationFilePath)) {
             // Find the highest numeric filename in the destination dir
            $maxNumber = 0;
            foreach ($textFiles as $file) {
                 $numericPart = intval(pathinfo($file, PATHINFO_FILENAME));
                if ($numericPart > $maxNumber) {
                     $maxNumber = $numericPart;
                 }
             }

            // Increment the maximum number by 1 to create the new filename
            $newNumber = ($maxNumber + 1);
            while (file_exists($destinationDirPath . '/' . $newNumber. ".txt")) {
                $newNumber++;
            }
            $newFileName = $newNumber . ".txt";
            rename($sourceFilePath, $destinationDirPath . '/' . $newFileName);
            } else {
                rename($sourceFilePath, $destinationFilePath);
                   }
        } else {
             // Handle error if file does not exist in source dir
            echo "Error: File '$fileName' does not exist in the source dir.";
            exit;
         }
     }
?>

