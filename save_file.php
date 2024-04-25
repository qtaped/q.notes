<?php
$folder = "./text_files/";

// Get the selected subfolder from the POST request
$selectedSubfolder = isset($_POST['subfolder']) ? $_POST['subfolder'] : 'Important';

// Define the subfolder path
$subfolderPath = $folder . $selectedSubfolder . "/";

// Get the filename and content from the POST request
$fileName = $_POST['fileName'];
$content = $_POST['content'];

// Define the size limit for the content (in bytes)
$sizeLimit = 512;

// Calculate the size of the content in bytes
$contentSize = strlen($content);

// Check if the content size exceeds the limit
if (isset($fileName) && ($contentSize <= $sizeLimit)) {
    // Save the content to the file in the selected subfolder
    file_put_contents($subfolderPath . $fileName, $content);
    // Respond with a success message
    echo "File saved successfully.";
} else {
    // Respond with an error message if content size exceeds the limit
    echo "Content size exceeds the limit of $sizeLimit bytes.";
}
?>

