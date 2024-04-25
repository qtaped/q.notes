<?php
$folder = "./text_files/";

// Get the selected subfolder from the POST request
$selectedSubfolder = isset($_POST['subfolder']) ? $_POST['subfolder'] : 'Important';

// Define the subfolder path
$subfolderPath = $folder . $selectedSubfolder . "/";

// Get the list of filenames to delete from the POST request
$fileNames = json_decode($_POST['fileNames']);

// Iterate through the filenames and delete each file from the selected subfolder
foreach ($fileNames as $fileName) {
    unlink($subfolderPath . $fileName);
}
?>

