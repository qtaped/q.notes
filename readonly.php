<!DOCTYPE html>
<html>
<head>
    <?php include 'config.php'; ?>

    <title><?php echo APP_NAME; ?> -  readonly</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0" />
    <link rel="stylesheet" href="style.css" type="text/css">
</head>
<body>

    <div class="container">
        <?php

        $notesDir = NOTES_DIR;

        // List of dirs
        $dirs = array_filter(glob($notesDir . '*'), 'is_dir');
        $dirNames = array_map('basename', $dirs);
        // Get the selected dir if not in list take the first
        $getDir = preg_replace('/[^a-zA-Z0-9-_]/', '', $_GET['dir']);

        if (in_array($getDir, $dirNames)) {
            $selectedDir = $getDir;
        } else {
            $selectedDir = $dirNames[0];
        }

        // Get the list of files from the selected dir
        $dirPath = $notesDir . $selectedDir . "/";
        $files = array_diff(scandir($dirPath), array('..', '.'));

        // Filter out non-text files
        $textFiles = array_filter($files, function ($fileName) {
            return pathinfo($fileName, PATHINFO_EXTENSION) === 'txt';
        });

        // Sort files
        usort($textFiles, function ($a, $b) {
            return strnatcasecmp($b, $a);
        });

        // Print number of current txt files
        $filesCount = count($textFiles);
        echo "<div class='head-container'>
                <h2 class='files-count'>$filesCount note". (($filesCount > 1) ? "s" : "") .".</h2>
                    <ul class='dir-list'>";
        // Count all other txt files
        foreach ($dirNames as $dir) {
            $allDirPath = $notesDir . $dir . "/";
            $allFiles = array_diff(scandir($allDirPath), array('..', '.'));
            $allTextFiles = array_filter($allFiles, function ($fileName) {
            return pathinfo($fileName, PATHINFO_EXTENSION) === 'txt';
            });
            $notesCount = count($allTextFiles);
            if ($dir !== $selectedDir) {
                echo "<a href='?dir=$dir'><li>$dir <span class='count count-$dir'>$notesCount</span></li></a>";
                } else {
                echo "<li class=\"active\">$dir <span class='count current-count'>$notesCount</span></li>";
                }
        }
        echo "</ul>
              </div>";
?>
    <div class="button-container">
        <button id="newNoteBtn" class="disabled">New Note</button>
        <button id="selectAllBtn" class="disabled">Select All</button>
        <button id="moveSelectedBtn" class="disabled">Move Selected (<span class="selected-notes-count">0</span>)</button>
        <button id="deleteSelectedBtn" class="disabled">Delete Selected (<span class="selected-notes-count">0</span>)</button>
        <button id='readOnlyBtn'>edit mode</button>
    </div>
    <?php

        // Display files
        foreach ($textFiles as $file) {
            if (is_file($dirPath . $file)) {
                $content = file_get_contents($dirPath . $file);
                $fileName = pathinfo($file, PATHINFO_FILENAME);
                $modificationDate = date("Y-m-d H:i:s", filemtime($dirPath . $file));
                echo "<div class='file-container'>

                        <div class='file' data-file='$file'>
                            <div class='content'>$content</div>
                            <div class='file-info'>
                                <span class='file-name'><a href='$notesDir$selectedDir/$fileName.txt' target='_blank' title='open $selectedDir/$fileName.txt'>NOTE #$fileName</a></span>
                                <span class='modification-date'>$modificationDate</span>
                            </div>
                        </div>
                      </div>";
            }
        }
        ?>

    </div> <!-- container -->
<script>
        document.querySelector('#readOnlyBtn').addEventListener('click', function() {
        window.location.href = "./?dir=<?php echo $selectedDir; ?>";
    });

    const addEmptyDivs = (n) => {
        let containerDiv = document.querySelector('.container');
        let emptyDivCount = document.querySelectorAll('.container .empty').length;
          for (let i = 0; i < n - emptyDivCount; i++) {
                let newDiv = document.createElement('div');
                newDiv.className = 'empty';
                document.querySelector('.container').appendChild(newDiv);
          }
    };

    let notesCount = document.querySelectorAll('.file-container').length;
    if (notesCount <= 4 ) addEmptyDivs(5 - notesCount);

</script>

</body>
</html>
