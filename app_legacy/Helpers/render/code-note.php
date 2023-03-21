<?php

function RenderCodeNotes(array $codeNotes, bool $editable = false): void
{
    echo "<table class='table-highlight'>";

    echo "<thead>";
    echo "<tr class='do-not-highlight'>";
    echo "<th style='font-size:100%;'>Mem</th>";
    echo "<th style='font-size:100%;'>Note</th>";
    echo "<th style='font-size:100%;'>Author</th>";
    if ($editable) {
        echo "<th>Dev</th>";
    }
    echo "</tr>";
    echo "</thead>";

    echo "<tbody>";

    $rowIndex = 0;
    foreach ($codeNotes as $nextCodeNote) {
        if (empty(trim($nextCodeNote['Note'])) || $nextCodeNote['Note'] == "''") {
            continue;
        }

        echo "<tr id='row-$rowIndex'>";

        $addr = $nextCodeNote['Address'];
        $addrInt = hexdec($addr);

        $addrFormatted = sprintf("%04x", $addrInt);
        $originalMemNote = $nextCodeNote['Note'];

        sanitize_outputs($originalMemNote);

        $memNote = nl2br($originalMemNote);

        echo "<td data-address='$addr' style='width: 25%;'>";
        echo "<span class='font-mono'>0x$addrFormatted</span>";
        echo "</td>";

        echo "<td>";
        echo "<div class='font-mono note-display block' style='word-break:break-word'>$memNote</div>";
        echo "<textarea class='w-full font-mono note-edit hidden'>$originalMemNote</textarea>";
        echo "<button class='save-btn hidden' onclick='saveCodeNote($rowIndex)'>Save</button>";
        echo "</td>";

        echo "<td>";
        echo userAvatar($nextCodeNote['User'], label: false, iconSize: 24);
        echo "</td>";

        if ($editable) {
            echo "<td>";
            echo "<button class='edit-btn inline' onclick='beginEditMode($rowIndex)'>Edit</button>";
            echo "<button class='cancel-btn hidden' onclick='cancelEditMode($rowIndex)'>Cancel</button>";
            echo "</td>";
        }

        echo "</tr>";

        $rowIndex++;
    }

    echo "</tbody></table>";
}
