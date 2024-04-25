<?php
$folder = "./text_files/";

// Get the selected subfolder from the POST request
$selectedSubfolder = isset($_POST['subfolder']) ? $_POST['subfolder'] : 'Important';

// Define the subfolder path
$subfolderPath = $folder . $selectedSubfolder . "/";

// Get the list of files in the selected subfolder
$files = array_diff(scandir($subfolderPath), array('..', '.'));

// Extract numeric parts from filenames and find the maximum
$maxNumber = 0;
foreach ($files as $file) {
    $numericPart = intval(pathinfo($file, PATHINFO_FILENAME));
    if ($numericPart > $maxNumber) {
        $maxNumber = $numericPart;
    }
}

// Increment the maximum number by 1 to create the new filename
$newFileName = ($maxNumber + 1) . ".txt";

// Generate Lorem Ipsum text
$loremIpsum = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.";

// Create an empty file in the selected subfolder and write the Lorem Ipsum text to it
$file = fopen($subfolderPath . $newFileName, 'w');
fwrite($file, $loremIpsum);
fclose($file);

// Return the new filename as a response
echo $newFileName;
?>

