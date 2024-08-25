<!DOCTYPE html>
<html>
<head>
    <?php include 'config.php'; ?>
    <title><?php echo APP_NAME; ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, maximum-scale=1.0" />
    <link rel="stylesheet" href="style.css?v=0.9.3" type="text/css">
</head>

<body>
    <div id="loadingOverlay">loading...</div>
    <div class="container">
        <?php

        $notesDir = NOTES_DIR;

        // Create notes directory if it does not exist
        $notesDirPath = dirname(__FILE__) . '/' . $notesDir;
        if (!file_exists($notesDirPath)) {
            mkdir($notesDirPath, 0700, true);
        }
        if (count(array_diff(scandir($notesDirPath), ['.', '..'])) == 0) {
            $randomString = bin2hex(random_bytes(4));
            mkdir($notesDirPath . '/' . $randomString, 0700, true);
        }

        // List of dirs
        $dirs = array_filter(glob($notesDir . '*'), 'is_dir');
        $dirNames = array_map('basename', $dirs);
        $getDir = preg_replace('/[^a-zA-Z0-9-_]/', '', $_GET['dir']);

        if (in_array($getDir, $dirNames)) {
            $selectedDir = $getDir;
        } else {
            $selectedDir = $dirNames[0];
        }

        // Get the list of notes from the selected dir
        $dirPath = $notesDir . $selectedDir . "/";
        $notes = array_diff(scandir($dirPath), array('..', '.'));

        // Filter out non-text notes
        $textNotes = array_filter($notes, function ($noteName) {
            return pathinfo($noteName, PATHINFO_EXTENSION) === 'txt';
        });

        // Sort notes
        usort($textNotes, function ($a, $b) {
            return strnatcasecmp($b, $a);
        });

        // Print number of current txt notes
        $notesCount = count($textNotes);
        echo "<div class='head-container'>
                <h2 class='notes-count'>$notesCount note". (($notesCount > 1) ? "s" : "") .".</h2>
                    <ul class='dir-list'>";
        // Count all other txt notes
        foreach ($dirNames as $dir) {
            $allDirPath = $notesDir . $dir . "/";
            $allNotes = array_diff(scandir($allDirPath), array('..', '.'));
            $allTextNotes = array_filter($allNotes, function ($noteName) {
            return pathinfo($noteName, PATHINFO_EXTENSION) === 'txt';
            });
            $allNotesCount = count($allTextNotes);
            if ($dir !== $selectedDir) {
                echo "<a href='?dir=$dir'><li>$dir <span class='count count-$dir'>$allNotesCount</span></li></a>";
                } else {
                echo "<li class=\"active\">$dir <span class='count current-count'>$allNotesCount</span></li>";
                }
        }
        echo "</ul>
              <form id='createDirForm'>
              <input id='newDir' type='text' maxlength='16' placeholder='new_directory'>
              <button id='createDirBtn'>create</button>
              </form>
              </div>";
?>

    <div class="button-container">
        <button id="newNoteBtn">New Note</button>
        <button id="saveAllBtn">Save All</button>
        <button id="selectAllBtn">Select All</button>
        <button id="moveSelectedBtn" class="disabled">Move Selected (<span class="selected-notes-count">0</span>)</button>
        <button id="deleteSelectedBtn" class="disabled">Delete Selected (<span class="selected-notes-count">0</span>)</button>
        <button id='readOnlyBtn'>read only mode</button>
        <button id='delDirBtn'>Delete this directory</button>
    </div>

<?php
        // Display notes
        $index = 0;
        foreach ($textNotes as $note) {
            if (is_file($dirPath . $note)) {
                $index++;
                $content = file_get_contents($dirPath . $note);
                $noteName = pathinfo($note, PATHINFO_FILENAME);
                $modificationDate = date("Y-m-d H:i:s", filemtime($dirPath . $note));
                echo "<div class='note-container'>

                        <div class='note' data-file='$note'>
                        <input type='checkbox' class='note-checkbox' data-file='$note'>
                            <div class='saved-status'>Note saved!</div>
                            <div class='individual-btn'>
                                    <button class='indi-move'>move</button>
                                <button class='indi-delete'>delete</button>
                            </div>
                            <div tabindex ='$index' class='content' contenteditable='false'>$content</div>
                            <div class='note-info'>
                                <input type='text' class='indi-rename' placeholder='$noteName' maxlength='9' title='rename note' autocomplete='off'>
                                <span class='popup-rename'></span>
                                <div>
                                <button class='save-btn'>save</button>
                                <button class='cancel-btn'>✕</button>
                                </div>
                                <span class='status-msg'></span>
                                <span class='modification-date'>$modificationDate</span>
                            </div>
                        </div>
                      </div>";
            }
        }
?>

    </div> <!-- container -->

    <div id="move-dir-list">
        <?php
            $dirNames = array_map('basename', glob($notesDir . '*'));
            $allowedDirs = array();
            foreach ($dirNames as $dir) {
                if ($dir !== $selectedDir) {
                    $allowedDirs[] = $dir;
                }
            }

            $dirListHtml = '<p class="move-note-msg">from <span class="dir">' . $selectedDir . '</span> to:</p>';
            $dirListHtml .= '<ul id="dir-list">';
            foreach ($allowedDirs as $dir) {
                $dirListHtml .= '<li class="dir"><a href="#' . $dir . '">' . $dir . '</a></li>';
            }
            $dirListHtml .= '</ul>';

            echo $dirListHtml;
        ?>
    </div>

    <div class="editor-menu">
        <button class="btn bold-btn">B</button>
        <button class="btn italic-btn">i</button>
        <button class="btn underline-btn">U</button>
        <button class="btn strike-btn">S</button>
        <button class="btn color-btn">C</button>
        <button class="btn bg-btn">Bg</button>
        <button class="btn remove-btn">⌫</button>
        <button class="btn alink-btn">://</button>
        <button class="btn ulist-btn">•ul</button>
        <button class="btn olist-btn">ol</button>
        <button class="btn h1-btn">h1</button>
        <button class="btn h2-btn">h2</button>
    </div>

<script>

// Loading

    document.addEventListener("DOMContentLoaded", function() {
        // This event listener ensures the DOM is fully loaded

        window.addEventListener("load", function() {
            // This event listener ensures all resources including images, scripts, etc., are fully loaded

            // Hide the loading overlay
            const loadingOverlay = document.getElementById("loadingOverlay");
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
            }
        });
    });

// Fixes


    function removeTrailingTags(content) {
        // remove last br
        let brTags = content.querySelectorAll('br');
        if (brTags.length > 0) {
            const lastBrTag = brTags[brTags.length - 1];
            let lastBrParentNode = lastBrTag.parentNode;

            if (lastBrTag.nextSibling === null) {
                lastBrParentNode.removeChild(lastBrTag);
            }
        }
        // remove empty <p>
        content.innerHTML = content.innerHTML.replace(/<p><\/p>/g, '');
    }

    // uncheck all notes
    var checkboxes = document.querySelectorAll(".note-checkbox");
    checkboxes.forEach(function (checkbox) {
        if (checkbox.checked) {
            checkbox.checked = false;
            checkbox.dispatchEvent(new Event('change'));
        }
    });

// Common Variables

    var currentCount = '<?php echo $notesCount; ?>';
    var selectedDir = '<?php echo $selectedDir; ?>';
    var newCount = currentCount;
    var unsavedFound = 0;

// Navigate with arrow keys + Delete key

    document.addEventListener('keydown', function(event) {
      const focusedElement = document.activeElement;
      if (focusedElement.contentEditable != 'true' && !(focusedElement instanceof HTMLInputElement)) {
          if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            const focusedElement = document.activeElement;
            const contentDivs = document.querySelectorAll('.content');

            // Get the index of the current focused div
            let currentIndex = -1;
            for (let i = 0; i < contentDivs.length; i++) {
              if (contentDivs[i] === focusedElement) {
                currentIndex = i;
                break;
              }
            }

            // Determine the new index based on the arrow key direction
            let newIndex;
            if (event.key === 'ArrowLeft') {
              newIndex = currentIndex - 1;
            } else {
              newIndex = currentIndex + 1;
            }

            // Loop back to the beginning or end of the list if necessary
            if (newIndex < 0) {
              newIndex = contentDivs.length - 1;
            } else if (newIndex >= contentDivs.length) {
              newIndex = 0;
            }

            // Focus on the new div
            contentDivs[newIndex].focus();
          }

            if (event.code === 'Delete') {
                event.preventDefault();
                deleteSelectedBtn.click();
            }
            if ((event.ctrlKey || event.metaKey) && event.key === 'm') {
                event.preventDefault();
                moveSelectedBtn.click();
            }
      }
    });

// Select All

    function selectAll(){
        var checkboxes = document.querySelectorAll(".note-checkbox");
        var selectAllBtn = document.getElementById("selectAllBtn");
        var allChecked = true;

        checkboxes.forEach(function (checkbox) {
            if (!checkbox.checked) {
                allChecked = false;
                checkbox.checked = true;
                checkbox.dispatchEvent(new Event('change'));
            }
        });

        if (allChecked) {
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = false;
                checkbox.dispatchEvent(new Event('change'));
            });
        }
        updateSelectedNotesCount();
    };

    document.getElementById("selectAllBtn").addEventListener("click", function () {
        selectAll();
    });

    document.addEventListener('keydown', function(e) {
      const focusedElement = document.activeElement;
      if (focusedElement.contentEditable != 'true' && !(focusedElement instanceof HTMLInputElement)) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
            e.preventDefault();
            selectAll();
        }
      }
    });

    var checkboxes = document.querySelectorAll(".note-checkbox");
    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener("change", function () {
            updateSelectedNotesCount();

          // Get the parent div of the checkbox (the one with class "note")
         const noteDiv = checkbox.parentNode;
         // Get the sibling div with class "individual-btn"
         const individualBtn = noteDiv.querySelector('.individual-btn');
         // Toggle the display property of the individual-btn div
         if (checkbox.checked) {
           individualBtn.style.display = 'block';
         } else {
           individualBtn.style.display = 'none';
         }

        });
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

    function updateSelectedNotesCount() {
        var delDirBtn = document.getElementById("delDirBtn");
        var checkboxes = document.querySelectorAll(".note-checkbox");
        var selectAllBtn = document.getElementById("selectAllBtn");
        var selectedCount = 0;

        checkboxes.forEach(function (checkbox) {
            if (checkbox.checked) {
                selectedCount++;
            }
        });

        var counts = document.querySelectorAll(".selected-notes-count");
        counts.forEach(function(countDiv) {
          countDiv.textContent = selectedCount;
        });

        var deleteBtn = document.getElementById("deleteSelectedBtn");
        var moveSelectedBtn = document.getElementById("moveSelectedBtn");
        if (selectedCount === 0) {
            deleteBtn.classList.add("disabled");
            moveSelectedBtn.classList.add("disabled");
        } else {
            deleteBtn.classList.remove("disabled");
            moveSelectedBtn.classList.remove("disabled");
        }

        var allNotesCount = (currentCount < newCount) ? currentCount : newCount;
        if (allNotesCount <= 3) addEmptyDivs(4 - allNotesCount);

        if (currentCount == 0 || newCount == 0) {
            selectAllBtn.classList.add("disabled");
            selectAllBtn.textContent = "Nothing to select.";
            delDirBtn.style.display = 'block';
        } else {
            selectAllBtn.classList.remove("disabled");
            delDirBtn.style.display = 'none';
            if (selectedCount == checkboxes.length) {
                selectAllBtn.textContent = "Deselect All";
            } else {
                selectAllBtn.textContent = "Select All";
            }
        }

    };

// Rename notes

document.querySelectorAll('.indi-rename').forEach((renameField) => {
    const popup = renameField.closest('.note').querySelector('.popup-rename');
    renameField.addEventListener("input", function() {
        popup.style.display = 'block';
        popup.classList.remove('warning');
        popup.textContent = 'Press Enter to rename';
        renameField.value = renameField.value.replace(/[^a-zA-Z0-9-_]/g, '').substring(0, 9);
        renameField.classList.remove('warning');
    });
    renameField.addEventListener("focus", function() {
        const noteElement = renameField.closest('.note');
        const currentNoteName = noteElement.getAttribute('data-file').replace(/\.txt/g, '');
        if (renameField.value === '') {
            renameField.value = currentNoteName;
        } else {
            popup.classList.remove('warning');
            popup.style.display = 'block';
            popup.textContent = 'Press Enter to rename';
        }
    });
    renameField.addEventListener("blur", function() {
        const noteElement = renameField.closest('.note');
        const currentNoteName = noteElement.getAttribute('data-file').replace(/\.txt/g, '');
        if (renameField.value === currentNoteName || renameField.value == '') {
            popup.style.display = 'none';
            renameField.value = '';
        } else {
            popup.classList.add('warning');
            popup.style.display = 'block';
            popup.textContent = 'New name not saved';
        }
    });
    renameField.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            const noteElement = renameField.closest('.note');
            const oldNoteName = noteElement.getAttribute('data-file');
            const currentNoteName = oldNoteName.replace(/\.txt/g, '');
            if (renameField.value === currentNoteName || renameField.value === '') {
                popup.textContent = 'Type a new name'
                renameField.value = '';
            } else {
                let newNoteName = renameField.value.trim();
                newNoteName = `${newNoteName}.txt`;
                handleRenameRequest(oldNoteName, newNoteName, renameField);
            }
        }
    });
});

const handleRenameRequest = (oldNoteName, newNoteName, renameField) => {
        const xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState === 4) {
                const popup = renameField.closest('.note').querySelector('.popup-rename');
                if (this.status === 200) {
                    popup.classList.remove('warning');
                    popup.textContent = 'Renamed.';
                    window.location.reload();
                } else {
                    renameField.classList.add('warning');
                    popup.classList.add('warning');
                    popup.textContent = 'Name already exists?';
                }
            }
        };
        xhttp.open("POST", "rename_note.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send(`dir=${encodeURIComponent(selectedDir)}&old_name=${encodeURIComponent(oldNoteName)}&new_name=${encodeURIComponent(newNoteName)}`);
};


// Delete notes

const handleDeleteRequest = (noteNames, checkboxes = null, button = null) => {
    if (confirm(`Are you sure you want to delete ${noteNames.length > 1 ? 'selected notes' : 'this note'}?`)) {
        const xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                if (checkboxes) {
                    checkboxes.forEach((checkbox) => {
                        const noteContainer = checkbox.closest('.note-container');
                        if (noteContainer) {
                            noteContainer.classList.add('remove-anim');
                            setTimeout(() => {
                                noteContainer.remove();
                            }, 500);
                        }
                        if (checkbox) checkbox.remove();
                        const saveBtn = checkbox.parentNode ? checkbox.parentNode.querySelector('.save-btn') : null;
                        if (saveBtn) saveBtn.remove();
                    });
                } else if (button) {
                    const noteContainer = button.closest('.note-container');
                    if (noteContainer) {
                        noteContainer.classList.add('remove-anim');
                        setTimeout(() => {
                            noteContainer.remove();
                        }, 500);
                    }
                    const checkbox = button.closest('.note') ? button.closest('.note').querySelector('.note-checkbox') : null;
                    if (checkbox) checkbox.remove();
                    const saveBtn = button.closest('.note') ? button.closest('.note').querySelector('.save-btn') : null;
                    if (saveBtn) saveBtn.remove();
                }

                const newCount = currentCount - noteNames.length;
                const notesCount = document.querySelector(".notes-count");
                const notesCurrentCount = document.querySelector(".current-count");
                notesCount.textContent = `${newCount} ${newCount > 1 ? 'notes' : 'note'}.`;
                notesCurrentCount.textContent = newCount;
                currentCount = newCount;

                updateSelectedNotesCount();
                checkNotesUnsaved();
            }
        };
        xhttp.open("POST", "delete_note.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send(`dir=${encodeURIComponent(selectedDir)}&noteNames=${encodeURIComponent(JSON.stringify(noteNames))}`);
    }
};

document.querySelectorAll('.indi-delete').forEach((button) => {
    button.addEventListener('click', () => {
        const noteName = button.closest('.note').getAttribute('data-file');
        handleDeleteRequest([noteName], null, button);
    });
});

const deleteSelectedBtn = document.getElementById("deleteSelectedBtn");
deleteSelectedBtn.addEventListener("click", () => {
    const checkboxes = document.querySelectorAll(".note-checkbox:checked");
    const noteNames = Array.from(checkboxes).map(checkbox => checkbox.getAttribute("data-file"));

    if (noteNames.length > 0) {
        handleDeleteRequest(noteNames, checkboxes);
    }
});

// Move notes

    const moveDirList = document.getElementById('move-dir-list');
    const moveSelectedBtn = document.getElementById("moveSelectedBtn");

    const displayMoveDirList = (button) => {
        moveDirList.style.display = 'block';
        const rect = button.getBoundingClientRect();
        const rectList = moveDirList.getBoundingClientRect();
        const totalHeight = document.documentElement.clientHeight;
        const positionTop = (rect.top > totalHeight - rectList.height) ? totalHeight - rectList.height : rect.top + rect.height;
        moveDirList.style.top = `${positionTop}px`;
        moveDirList.style.left = `${rect.left + rect.width / 2}px`;
        moveDirList.innerHTML = '<?php echo $dirListHtml; ?>';
    };

    const handleDirListClick = (li, noteNames, button) => {
        li.addEventListener('click', () => {
            const destinationDir = li.textContent;
            const xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const newCount = currentCount - noteNames.length;
                    const notesCurrentCount = document.querySelector('.current-count');
                    const destinationCount = document.querySelector(`.count-${destinationDir}`);
                    const notesCount = document.querySelector('.notes-count');

                    notesCount.textContent = `${newCount} ${(newCount > 1) ? 'notes' : 'note'}.`;
                    notesCurrentCount.textContent = newCount;
                    destinationCount.textContent = parseInt(destinationCount.textContent) + noteNames.length;
                    currentCount = newCount;

                    if (button) {
                        button.closest('.note-container').classList.add('move-anim');
                        setTimeout(() => {
                            button.closest('.note-container').remove();
                            destinationCount.classList.add('bump-anim');
                            setTimeout(() => {
                                destinationCount.classList.remove('bump-anim');
                                destinationCount.classList.add('moved-notes');
                            }, 400);
                        }, 400);
                    } else {
                        document.querySelectorAll(".note-checkbox:checked").forEach((checkbox) => {
                            const saveBtn = checkbox.parentNode.querySelector('.save-btn');
                            saveBtn.remove();
                            checkbox.checked = false;
                            checkbox.closest('.note-container').classList.add('move-anim');
                            setTimeout(() => {
                                checkbox.closest('.note-container').remove();
                                destinationCount.classList.add('bump-anim');
                                setTimeout(() => {
                                    destinationCount.classList.remove('bump-anim');
                                    destinationCount.classList.add('moved-notes');
                                }, 400);
                            }, 400);
                        });
                    }

                    moveDirList.style.display = 'none';
                    moveDirList.innerHTML = ' '; // Remove innerHTML to remove events

                    updateSelectedNotesCount();
                    checkNotesUnsaved();
                }
            };
            xhttp.open("POST", "move_note.php", true);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send(`selectedDir=${encodeURIComponent(selectedDir)}&destinationDir=${encodeURIComponent(destinationDir)}&noteNames=${encodeURIComponent(JSON.stringify(noteNames))}`);
        });
    };

    const addDirListEventListeners = (noteNames, button = null) => {
        document.querySelectorAll('#dir-list li.dir').forEach((li) => {
            handleDirListClick(li, noteNames, button);
        });
    };

    const handleMoveButtonClick = (button) => {
        displayMoveDirList(button);
        const noteNames = [button.closest('.note').getAttribute('data-file')];
        addDirListEventListeners(noteNames, button);
    };

    document.querySelectorAll('.indi-move').forEach((button) => {
        button.addEventListener('click', () => handleMoveButtonClick(button));
    });

    document.addEventListener('click', (event) => {
        const indiBtns = document.querySelectorAll('.indi-move');
        const isInside = moveDirList.contains(event.target) || moveSelectedBtn.contains(event.target);
        const isInsideIndiBtns = Array.from(indiBtns).some(indiBtn => indiBtn.contains(event.target));
        if (!isInside && !isInsideIndiBtns) {
            moveDirList.style.display = 'none';
        }
    });

    moveSelectedBtn.addEventListener("click", () => {
        var checkboxes = document.querySelectorAll(".note-checkbox:checked");
        const noteNames = Array.from(checkboxes).map(checkbox => checkbox.getAttribute("data-file"));

        if (noteNames.length != 0) {
            displayMoveDirList(moveSelectedBtn);
            addDirListEventListeners(noteNames);
        }
    });

// Create note

    function newNote() {
        const notesLimit = <?php echo NOTES_LIMIT; ?>;
        checkNotesUnsaved();

        if (currentCount < notesLimit) {
             if (unsavedFound == 0) {
             var xhttp = new XMLHttpRequest();
             xhttp.onreadystatechange = function () {
                 if (this.readyState == 4 && this.status == 200) {
                     location.reload();

                 };
             };
             xhttp.open("POST", "create_note.php", true);
             xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
             xhttp.send("dir=" + encodeURIComponent(selectedDir));
             } else {
                  alert('Save all notes before creating a new one.');
             }

        } else {
            alert('Limit reached. <?php echo NOTES_LIMIT; ?> notes.');
        };
    }

    const newNoteBtn = document.getElementById("newNoteBtn");
    newNoteBtn.addEventListener("click", function () {
        newNote();
    });

    document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                e.preventDefault();
                newNote();
            }
    });

// Create dir

    const newDirInput = document.getElementById("newDir");
    newDirInput.addEventListener("input", function() {
        newDirInput.value = newDirInput.value.replace(/[^a-zA-Z0-9-_]/g, '').substring(0, 16);
        newDirInput.classList.remove('warning');
    });

    const createDirForm = document.getElementById("createDirForm"); 
    createDirForm.addEventListener("submit", function() {
        event.preventDefault();
        if (!newDirInput.value) {
            document.getElementById('newDir').focus();
            randomString = '<?php echo bin2hex(random_bytes(4)); ?>';
            newDirInput.value = randomString;
        } else if (unsavedFound != 0) {
            alert('Save all notes before creating a directory.');
        } else if (confirm('Are you sure you want to create the directory < '+ newDirInput.value +' >?')) {
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange=function(){
                    if(this.readyState==4 && this.status==200){
                        window.location.href = "./?dir=" + newDirInput.value;
                        document.getElementById('newDir').value = '';   
                     } else if (this.readyState==4 && this.status!=200) {
                        newDirInput.classList.add('warning');
                     };
                 };   
                 xhttp.open("POST", "create_dir.php", true);
                 xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                 xhttp.send("newdir=" + encodeURIComponent(newDirInput.value));
          } else {
                newDirInput.value = '';
          };
        });

// Delete dir

    const delDirBtn = document.getElementById("delDirBtn");

    delDirBtn.addEventListener("click", function() {

        if (confirm("Are you sure you want to delete the directory < <?php echo $selectedDir; ?> >?")) {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange=function(){
                if(this.readyState==4&&this.status==200){
                    window.location.href = "./";
                 };
             };

             xhttp.open("POST", "delete_dir.php", true);
             xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
             xhttp.send("deldir=" + encodeURIComponent(selectedDir));
             }
        });

// Save notes

    function updateSaveState(note) {
        const content = note.querySelector(".content");
        const saveButton = note.querySelector(".save-btn");
        const cancelButton = note.querySelector(".cancel-btn");
        const savedStatus = content.parentNode.querySelector(".saved-status");
        const noteNameInfo = content.parentNode.querySelector(".indi-rename");
        const modDate = content.parentNode.querySelector(".modification-date");
        const checkbox = note.querySelector(".note-checkbox");

        var newContent = content.innerHTML.trim();

        saveButton.style.display = 'block';
        cancelButton.style.display = 'block';
        savedStatus.style.background = '#ff5555';
        noteNameInfo.style.display = 'none';

        var noteName = content.parentNode.getAttribute("data-file");
        var contentSize = getContentSize(newContent);
        var sizeLimit = <?php echo NOTE_SIZE_LIMIT; ?>;
        const brIndex = content.innerHTML.lastIndexOf('<br>');
            if (brIndex !== -1) { // Check if there is a <br> tag
                sizeLimit = sizeLimit + 4;
            }
        var sizeLeft = sizeLimit - contentSize;

        // Update the content size left span
        var statusMsg = note.querySelector('.status-msg');

        if (statusMsg) {
            if (sizeLeft < 0) {
            var sizeLeftNeg = sizeLeft * -1;
            statusMsg.classList.add("warning");
            statusMsg.textContent = sizeLeftNeg +"b in excess";
            } else {
            statusMsg.classList.remove("warning");
            statusMsg.textContent = sizeLeft + "b left";
            }
        }

        function cancelSave() {
            saveButton.style.display = 'none';
            cancelButton.style.display = 'none';
            savedStatus.style.background = '#ff8700';
            noteNameInfo.style.display = 'block';
            statusMsg.classList.add("warning");
            statusMsg.textContent = "unsaved";
            content.innerHTML = backupContent;
            checkNotesUnsaved();
        }

        function saveNote(content) {
                var xhttp = new XMLHttpRequest();
                removeTrailingTags(content);
                var newContent = content.innerHTML.trim();
                var contentSize = getContentSize(newContent);
                var sizeLeft = sizeLimit - contentSize;
                saveButton.textContent = 'saving...';

            if (sizeLeft >= 0) {
                xhttp.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        initialContent = newContent;
                        noteNameInfo.style.display = 'block';
                        saveButton.style.display = 'none';
                        cancelButton.style.display = 'none';
                        saveButton.textContent = 'SAVE';
                        savedStatus.style.background = '#4CAF50';
                        modDate.textContent = "<?php echo date('Y-m-d H:i:s'); ?>";
                        statusMsg.classList.remove("warning");
                        statusMsg.textContent = " ";
                        content.contentEditable = 'false';
                        checkNotesUnsaved();
                    } else if (this.status == 500) {
                        saveButton.textContent = 'retry?';
                        statusMsg.classList.add("warning");
                        statusMsg.textContent = "error";
                    }
                };
                xhttp.open("POST", "save_note.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("dir=" + encodeURIComponent(selectedDir) + "&noteName=" + encodeURIComponent(noteName) + "&content=" + encodeURIComponent(newContent));
            } else {
                statusMsg.textContent = "Too large.";
                saveButton.textContent = "SAVE";
            }
        };

        if (!content.dataset.listenerAdded) {
            cancelButton.addEventListener("click", function () {
                cancelSave();
            });

            content.addEventListener('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'w')  {
                    e.preventDefault();
                    cancelSave();
                }
            });

            saveButton.addEventListener("click", function () {
                saveNote(content);
            });

            content.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault(); //
                    saveNote(content);
                }
            });
        content.dataset.listenerAdded = 'true';
        };
    } // end updateSaveState

    var backupContent = '';
    function editNote(content) {
        var target = event.target;
        if(target.tagName != 'A'){
            content.contentEditable = 'true';
            content.focus();
            document.execCommand("defaultParagraphSeparator", false, "p");
            backupContent = content.innerHTML.trim();
        }
    };

    const notes = document.querySelectorAll(".note");
    notes.forEach(function (note) {
        const content = note.querySelector(".content");
        content.addEventListener('click', function (event) {
            editNote(content);
        });
        content.addEventListener('focus', function (event) {
            editNote(content);
        });
        content.addEventListener('keydown', function (event) {
            if (content.contentEditable == 'true' && (event.key === 'Escape' || event.keyCode === 27)) {
                event.preventDefault();
                content.contentEditable = 'false';
            }
        });
        content.addEventListener('blur', function (event) {
                content.contentEditable = 'false';
        });
        content.addEventListener('input', function () {
                updateSaveState(note);
                checkNotesUnsaved();
        });
    });

    function checkNotesUnsaved() {
        const buttons = document.getElementsByClassName("save-btn");
        unsavedFound = 0;

        for (var i = 0; i < buttons.length; i++) {
            if (buttons[i].style.display === "block") {
                unsavedFound++;
            }
        }
        if (unsavedFound == 0) {
            newNoteBtn.style.display = "block";
            saveAllBtn.style.display = "none";
        } else {
            newNoteBtn.style.display = "none";
            saveAllBtn.style.display = "block";
        }
    };

    window.onbeforeunload = function() {
     checkNotesUnsaved(); 
         if (unsavedFound > 0) {
             return "There are notes unsaved. Are you sure you want to leave?";
         }
     };

    // Save All

    const saveAllBtn = document.getElementById("saveAllBtn");
    saveAllBtn.addEventListener("click", function() {
      const saveBtns = document.querySelectorAll(".save-btn");
      saveBtns.forEach(function(saveBtn) {
        saveBtn.click();
      });
      checkNotesUnsaved();
    });

   function getContentSize(content) {
       var bytes = new Blob([content]).size;
       return bytes;
   }

    // Function to check if selection is within a content note div
    function isSelectionInContent(selection) {
        const content = document.querySelectorAll('.content');
        for (var i = 0; i < content.length; i++) {
            if (content[i].contains(selection.anchorNode) && content[i].contentEditable == 'true') {
                return true;
            }
        }
        return false;
    }

    // Function to toggle the visibility of editor menu based on selection
    const editorMenu = document.querySelector('.editor-menu');

    editorMenu.addEventListener("mousedown", event => {event.preventDefault(); event.stopPropagation()});

    function toggleEditorMenu() {
        var selection = window.getSelection();
        var selectedText = selection.toString().trim();

        // Show editor menu if text is selected within a content div, hide otherwise
        if (selectedText.length > 0 && isSelectionInContent(selection)) {
            positionEditorMenu(selection);
            editorMenu.style.display = 'block';
        } else {
            editorMenu.style.display = 'none';
        }
    }

    // Function to position the editor menu next to the cursor
    function positionEditorMenu(selection) {
        var range = selection.getRangeAt(0);
        var rect = range.getBoundingClientRect();
        editorMenu.style.top = (rect.bottom + window.pageYOffset) + 'px';
        editorMenu.style.left = (rect.left + window.pageXOffset) + 'px';
    }

    // Event listener for text selection
    document.addEventListener('selectionchange', toggleEditorMenu);

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
        var selection = window.getSelection();
        if (!selection.rangeCount) return;

        var range = selection.getRangeAt(0);
        var container = range.commonAncestorContainer;
        const tagNamesToRemove = ['h1', 'h2', 'a'];

        // Ensure container is an element
        if (container.nodeType !== Node.ELEMENT_NODE) {
            container = container.parentNode;
        }

        tagNamesToRemove.forEach(tagName => {
            container.querySelectorAll(tagName).forEach(tag => {
                tag.replaceWith(...tag.childNodes);
            });
        });

        selection.removeAllRanges();
        selection.addRange(range);
        document.execCommand('removeFormat');
    });


    document.querySelector('.color-btn').addEventListener('click', function() {
      document.execCommand('foreColor', false, '#FF8700');
    });

    document.querySelector('.bg-btn').addEventListener('click', function() {
      document.execCommand('foreColor', false, '#222');
      document.execCommand('backColor', false, '#FF8700');
    });

    document.querySelector('.strike-btn').addEventListener('click', function() {
      document.execCommand('strikeThrough');
    });

    document.querySelector('.olist-btn').addEventListener('click', function() {
      document.execCommand('insertOrderedList');
    });

    document.querySelector('.ulist-btn').addEventListener('click', function() {
      document.execCommand('insertUnorderedList');
    });

    document.querySelector('.h1-btn').addEventListener('click', function() {
      toggleHeaderTag('h1');
    });

    document.querySelector('.h2-btn').addEventListener('click', function() {
      toggleHeaderTag('h2');
    });

    document.querySelector('.alink-btn').addEventListener('click', function() {
    var url = prompt("Enter the link URL:");
        if (url !== null) {
          document.execCommand('CreateLink', false, url);
        }
    });

    document.querySelector('#readOnlyBtn').addEventListener('click', function() {
        window.location.href = "./readonly.php?dir=<?php echo $selectedDir; ?>";
    });

    updateSelectedNotesCount();
</script>
</body>
</html>
