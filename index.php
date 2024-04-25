<!DOCTYPE html>
<html>
<head>
    <title>qNotes</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0, target-densityDpi=device-dpi" />
    <style>
        body {
            background-color: #1e1e1e;
            color: #fff;
            font-family: "Menlo", "Consolas", monospace;
            margin: 0;
            padding: 0;
            touch-action: pan-y;
        }
        ::selection {
            background-color: rgba(255,135,0,.5);
        }

        .button-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .button-container button {
            margin: 0 10px;
        }

        .container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
            max-width: 100%;
        }

        .head-container {
            position: relative;
            border: 1px solid #111111;
            padding: 20px;
            border-radius: 10px;
            overflow: hidden;
        }

        .file-container {
            position: relative;
            background-color: #2c2c2c;
            border-right: 2px solid #181818;
            border-bottom: 3px solid #181818;
            padding: 10px;
            transition: background-color 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
        }

        .file-container:hover {
            background-color: #3a3a3a;
        }
        
        .file {
            height: calc(100% - 55px);
        }

        .file .content {
            height: 100%;
            color: #cccccc;
            font-size: 14px;
            margin-top: 0;
            margin-bottom: 30px;
            word-wrap: break-word;
            padding: 10px;
        }

        .file .content:focus {
            outline: 1px dashed #999;
            border-radius: 10px;
            background-color: #2c2c2c; 
        }

        .file-info {
            position: absolute;
            bottom: 10px;
            left: 10px;
            right: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .file-name {
            background-color: #555;
            color: #CCC;
            font-size: 10px;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 12px;
        }

        .file-name a {
            text-decoration: none;
            color: #CCC;
        }

        .modification-date {
            font-size: 12px;
            color: #999999;
        }

        .content-size-left {
            font-size: 12px;
            color: #dddddd;
        }

        .content-size-left.warning {
            color: #ff5555;
            font-weight: bold;
        }

        .note-checkbox {
            position: absolute;
            top: 4px;
            right: 4px;
            z-index: 1;
        }

        .select-all-btn {
            background-color: #007bff;
        }

        .select-all-btn:hover {
            background-color: #0056b3;
        }

        #createFileBtn {
            background-color: #4CAF50;
        }

        #createFileBtn,
        #deleteSelectedBtn,
        #selectAllBtn {
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: block;
            margin: 20px auto 0 auto;
            font-size: 14px;
            max-width: 30%;
        }

        #createFileBtn:hover {
            background-color: #45a049;
        }

        .delete-selected-btn {
            background-color: #ff5555;
        }

        .delete-selected-btn:hover {
            background-color: #ff3333;
        }

        .subfolder-list {
            list-style-type: none;
            padding: 0;
        }
        
        .subfolder-list li {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .subfolder-list li.active {
            cursor: default;
        }

        .subfolder-list li .count {
            border: 1px solid #777;
            color: #CCC;
            font-size: 10px;
            border-radius: 16px;
            padding: 1px 10px;
            cursor: default;
        }

        .subfolder-list a {
            text-decoration: none;
            color: #FF8700;
            font-weight: bold;
        }
        
        .subfolder-list a:hover {
            color: white;
        }
        
        .saved-status {
            position: absolute;
            background: transparent;
            top:8px;
            left: 8px;
            font-size: 0;
            padding: 0;
            border-radius: 12px;
            height: 6px;
            width: 6px;
        }

        .disabled {
            background-color: #333 !important;
            color: #666 !important;
            cursor: not-allowed !important;
        }

        .save-btn {
            display:none;
            background-color: #f55;
            color: #FFF;
            font-size: 10px;
            font-weight: bold;
            padding: 5px 20px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
        }

        .select-btn {
            position: absolute;
            display: none;
            transform: translate(-50%,25px);
        }

        .btn {
            position: relative;
            float: left;
            background-color: #444;
            padding: 5px;
            color: #eee;
            border: none;
            cursor: pointer;
            width:28px;
            font-size: 16px;
        }

        .bold-btn {
            font-weight:bold;
        }

        .italic-btn {
            font-style:italic;
        }

        .underline-btn {
            text-decoration:underline;
        }

        .strike-btn {
            text-decoration:line-through;
        }

        .color-btn {
            color:#FF8700;
        }

        .btn:hover {
            background-color: #222;
        }

        @media screen and (max-width: 892px) {
            .container {
                grid-template-columns: repeat(1, 1fr);
            }
        }

        @media only screen and (max-device-width: 892px) and (orientation: landscape) and (min-aspect-ratio: 13/9) {
            .container {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="button-container">
        <button id="createFileBtn">+ New Note</button>
        <button class="select-all-btn" id="selectAllBtn">Select All</button>
        <button id="deleteSelectedBtn" class="delete-selected-btn disabled">Delete Selected (<span class="selected-notes-count">0</span>)</button>
    </div>

<div class="container">
    <?php

    $folder = "./text_files/";

    // List of subfolders
    $subfolders = array_filter(glob('text_files/*'), 'is_dir');
    $subfolderNames = array_map('basename', $subfolders);
    // Get the selected subfolder from the dropdown menu
    $selectedSubfolder = isset($_GET['subfolder']) ? $_GET['subfolder'] : $subfolderNames[0];

    // Get the list of files from the selected subfolder
    $subfolderPath = $folder . $selectedSubfolder . "/";
    $files = array_diff(scandir($subfolderPath), array('..', '.'));

    // Filter out non-text files
    $textFiles = array_filter($files, function ($fileName) {
        return pathinfo($fileName, PATHINFO_EXTENSION) === 'txt';
    });

    // Sort files numerically
    usort($textFiles, function ($a, $b) {
        // Extract numeric part from filenames
        $numericPartA = intval(preg_replace('/[^0-9]/', '', $a));
        $numericPartB = intval(preg_replace('/[^0-9]/', '', $b));

        // Compare numeric parts
        return $numericPartA - $numericPartB;
    });

    // Print number of files
    $filesCount = count($textFiles);
    echo "<div class='head-container'>
            <h2>$filesCount note". (($filesCount > 1) ? "s" : "") .".</h2>
                <ul class='subfolder-list'>";
    foreach ($subfolderNames as $subfolder) {
        $allSubfolderPath = $folder . $subfolder . "/";
        $allFiles = array_diff(scandir($allSubfolderPath), array('..', '.'));
        $notesCount = count($allFiles);
    if ($subfolder !== $selectedSubfolder) {
        echo "<li><a href='?subfolder=$subfolder'>$subfolder</a> <span class='count'>$notesCount</span></li>";
        } else {
        echo "<li class=\"active\">$subfolder <span class='count'>$notesCount</span></li>";
        }
          }
    echo "</ul>
          </div>";

    // Display files
    foreach ($textFiles as $file) {
        if (is_file($subfolderPath . $file)) {
            $content = file_get_contents($subfolderPath . $file);
            $decodedContent = htmlspecialchars_decode($content); // Decode HTML entities
            $fileName = pathinfo($file, PATHINFO_FILENAME);
            $modificationDate = date("Y-m-d H:i:s", filemtime($subfolderPath . $file));
            echo "<div class='file-container'>
                    
                    <input type='checkbox' class='note-checkbox' data-file='$file'>
                    <div class='file' data-file='$file'>
                        <div class='saved-status'>File saved!</div>
                        <div class='content' contenteditable='true'>$decodedContent</div>
                        <div class='file-info'>
                            <span class='file-name'><a href='./text_files/$selectedSubfolder/$fileName.txt' target='_blank'>NOTE #$fileName</a></span>
                            <button class='save-btn'>SAVE</button>
                            <span class='content-size-left'></span>
                            <span class='modification-date'>$modificationDate</span>
                        </div>
                    </div>
                  </div>";
        }
    }
    ?>
</div>


    <div class="select-btn">
        <button class="btn bold-btn">B</button>
        <button class="btn italic-btn">i</button>
        <button class="btn underline-btn">U</button>
        <button class="btn strike-btn">S</button>
        <button class="btn color-btn">C</button>
        <button class="btn remove-btn">⌫</button>
        <button class="btn list-btn">•li</button>
        <button class="btn h1-btn">h1</button>
        <button class="btn h2-btn">h2</button>
    </div>

    <script>
        document.getElementById("selectAllBtn").addEventListener("click", function () {
            var checkboxes = document.querySelectorAll(".note-checkbox");
            var selectAllBtn = document.getElementById("selectAllBtn");
            var allChecked = true;

            checkboxes.forEach(function (checkbox) {
                if (!checkbox.checked) {
                    allChecked = false;
                    checkbox.checked = true;
                }
            });

            if (allChecked) {
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = false;
                });
                selectAllBtn.textContent = "Select All";
            } else {
                selectAllBtn.textContent = "Deselect All";
            }
            updateSelectedNotesCount();
        });

        var checkboxes = document.querySelectorAll(".note-checkbox");
        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener("click", function () {
                updateSelectedNotesCount();
            });
        });

        function updateSelectedNotesCount() {
            var checkboxes = document.querySelectorAll(".note-checkbox");
            var selectedCount = 0;

            checkboxes.forEach(function (checkbox) {
                if (checkbox.checked) {
                    selectedCount++;
                }
            });

            var countDiv = document.querySelector(".selected-notes-count");
            countDiv.textContent = selectedCount;

            var deleteBtn = document.getElementById("deleteSelectedBtn");
            if (selectedCount === 0) {
                deleteBtn.classList.add("disabled");
            } else {
                deleteBtn.classList.remove("disabled");
            }
        }

    document.getElementById("deleteSelectedBtn").addEventListener("click", function () {
        checkFilesUnsaved();
        if (unsavedFound == 0) {
        var checkboxes = document.querySelectorAll(".note-checkbox:checked");
        var fileNames = [];
        var selectedSubfolder = "<?php echo $selectedSubfolder; ?>";
        checkboxes.forEach(function (checkbox) {
            fileNames.push(checkbox.getAttribute("data-file"));
            // Uncheck the checkboxes after adding the file names to the array
            checkbox.checked = false;
        });
        if (fileNames.length != 0) {
            if (confirm("Are you sure you want to delete selected notes?")) {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        location.reload();
                    }
                };
                xhttp.open("POST", "delete_file.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("subfolder=" + encodeURIComponent(selectedSubfolder) + "&fileNames=" + encodeURIComponent(JSON.stringify(fileNames))); // Send selected subfolder as POST parameter
            }
            updateSelectedNotesCount();
        }
        } else {
         alert('save all notes before deleting one.');
        }
    });
    document.getElementById("createFileBtn").addEventListener("click", function () {
        checkFilesUnsaved();
        if (unsavedFound == 0) {
        var xhttp = new XMLHttpRequest();
        var selectedSubfolder = "<?php echo $selectedSubfolder; ?>";
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                location.reload();
            }
        };
        xhttp.open("POST", "create_file.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("subfolder=" + encodeURIComponent(selectedSubfolder)); // Send selected subfolder as POST parameter
    } else {
         alert('save all notes before creating a new one.');
    }
    });


        var files = document.querySelectorAll(".file");
        files.forEach(function (file) {
            var content = file.querySelector(".content");
            var initialContent = content.innerHTML.trim();

            content.addEventListener("input", function () {
                var currentContent = this.innerHTML.trim();
                if (currentContent !== initialContent) {
                    updateSaveButton(file);
                }
            });
        });
     

     var unsavedFound = 0;
     function checkFilesUnsaved() {
         var buttons = document.getElementsByClassName("save-btn");
     
         for (var i = 0; i < buttons.length; i++) {
             if (buttons[i].style.display === "block") {
                 unsavedFound++;
             }
         }
     }
     
      window.onbeforeunload = function() {
         checkFilesUnsaved(); 
         if (unsavedFound > 0) {
           //  unsavedFound = 0;
             return "There are notes unsaved. Are you sure you want to leave?";
         }
     };



   function updateSaveButton(file) {
       var content = file.querySelector(".content");
       var saveButton = file.querySelector(".save-btn");
       var savedStatus = content.parentNode.querySelector(".saved-status");
       var fileNameInfo = content.parentNode.querySelector(".file-name");
       saveButton.style.display = 'block';
       savedStatus.style.background = '#ff5555';
       fileNameInfo.style.display = 'none';

           var newContent = content.innerHTML.trim();
           var fileName = content.parentNode.getAttribute("data-file");

           var contentSize = getContentSize(newContent);
           var sizeLimit = 512;

           var sizeLeft = sizeLimit - contentSize;

           // Update the content size left span
           var sizeLeftSpan = file.querySelector('.content-size-left');

           if (sizeLeftSpan) {
               if (sizeLeft < 0) {
               sizeLeftSpan.classList.add("warning");
               sizeLeftSpan.textContent = "Too large.";
               } else {
               sizeLeftSpan.classList.remove("warning");
               sizeLeftSpan.textContent = sizeLeft + "b left";
               }
           }

       saveButton.addEventListener("click", function () {
               var xhttp = new XMLHttpRequest();
               var selectedSubfolder = "<?php echo $selectedSubfolder; ?>";
               var newContent = content.innerHTML.trim();
               var contentSize = getContentSize(newContent);
               var sizeLeft = sizeLimit - contentSize;
           if (sizeLeft >= 0) {
               xhttp.onreadystatechange = function () {
                   if (this.readyState == 4 && this.status == 200) {
                       initialContent = newContent;
                       fileNameInfo.style.display = 'block';
                       saveButton.style.display = 'none';
                       savedStatus.style.background = '#4CAF50';
                       checkFilesUnsaved();
                       sizeLeftSpan.textContent = "saved.";
                       if (unsavedFound > 0) {
                       unsavedFound = 0;
                       } else {
                       //location.reload();
                       }
                   }
               };
               xhttp.open("POST", "save_file.php", true);
               xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
               xhttp.send("subfolder=" + encodeURIComponent(selectedSubfolder) + "&fileName=" + encodeURIComponent(fileName) + "&content=" + encodeURIComponent(newContent));
           } else {
               sizeLeftSpan.textContent = "TOO LARGE.";
           }

           });

   }

   function getContentSize(content) {
       var bytes = new Blob([content]).size;
       return bytes;
   }

    // Function to check if selection is within a content file div
    function isSelectionInContent(selection) {
        var contentDivs = document.querySelectorAll('.content');
        for (var i = 0; i < contentDivs.length; i++) {
            if (contentDivs[i].contains(selection.anchorNode)) {
                return true;
            }
        }
        return false;
    }

    // Function to toggle the visibility of select button based on selection
    function toggleSelectButton() {
        var selection = window.getSelection();
        var selectedText = selection.toString().trim();
        var selectBtn = document.querySelector('.select-btn');

        // Show select button if text is selected within a content div, hide otherwise
        if (selectedText.length > 0 && isSelectionInContent(selection)) {
            positionSelectButton(selection);
            selectBtn.style.display = 'block';
        } else {
            selectBtn.style.display = 'none';
        }
    }

    // Function to position the select button next to the cursor
    function positionSelectButton(selection) {
        var selectBtn = document.querySelector('.select-btn');
        var range = selection.getRangeAt(0);
        var rect = range.getBoundingClientRect();
        selectBtn.style.top = (rect.top + window.pageYOffset) + 'px';
        selectBtn.style.left = (rect.right + window.pageXOffset) + 'px';
    }


    // Event listener for text selection
    document.addEventListener('selectionchange', toggleSelectButton);

        function toggleHeaderTag(tagName) {
            var selection = window.getSelection();
            var range = selection.getRangeAt(0);
            var node = range.commonAncestorContainer;
            while (node) {
                if (node.nodeName === tagName.toUpperCase()) {
                    var newTag = document.createElement("p");
                    newTag.innerHTML = node.innerHTML;
                    node.parentNode.replaceChild(newTag, node);
                    return;
                }
                node = node.parentNode;
            }
            document.execCommand('formatBlock', false, '<' + tagName + '>');
        }

        document.querySelector('.bold-btn').addEventListener('click', function() {
            document.execCommand('bold');
        });

        document.querySelector('.italic-btn').addEventListener('click', function() {
            document.execCommand('italic');
        });

        document.querySelector('.underline-btn').addEventListener('click', function() {
            document.execCommand('underline');
        });

        document.querySelector('.remove-btn').addEventListener('click', function() {
            document.execCommand('removeFormat');
                var selection = window.getSelection();
                var range = selection.getRangeAt(0);
                var node = range.commonAncestorContainer;

                // If the node is a paragraph, h1, or h2 tag, or a line break, unwrap its children
                var tagNamesToRemove = ['p', 'h1', 'h2'];
                tagNamesToRemove.forEach(function(tagName) {
                    if (node.nodeName.toLowerCase() === tagName) {
                        var parent = node.parentNode;
                        while (node.firstChild) {
                            parent.insertBefore(node.firstChild, node);
                        }
                        parent.removeChild(node);
                    }
                });
        });

        document.querySelector('.color-btn').addEventListener('click', function() {
            document.execCommand('foreColor', false, '#FF8700');
        });

        document.querySelector('.strike-btn').addEventListener('click', function() {
            document.execCommand('strikeThrough');
        });

        document.querySelector('.list-btn').addEventListener('click', function() {
            document.execCommand('insertOrderedList');
        });

        document.querySelector('.h1-btn').addEventListener('click', function() {
            toggleHeaderTag('h1');
        });

        document.querySelector('.h2-btn').addEventListener('click', function() {
            toggleHeaderTag('h2');
        });

        updateSelectedNotesCount();
    </script>

</body>
</html>

