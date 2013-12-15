<?php if(! SwpaUtil::canLoad()) { return; } ?>
<?php
/*
 * DISPLAY AVAILABLE DOWNLOADS
 *======================================================
 */
?>
<?php
$files = SwpaUtil::getAvailableBackupFiles();

    if (empty($files))
    {
        echo '<p>',__("You don't have any backup files yet!"),'</p>';
    }
    else {
        echo '<div class="acx-section-box">';
            echo '<ul id="bck-list" class="acx-common-list">';
            foreach($files as $fileName) {
                echo '<li>';
                    echo '<a href="',SWPA_PLUGIN_URL.'res/backups/',$fileName,'" title="',__('Click to download'),'">',$fileName,'</a>';
                echo '</li>';
            }
            echo '</ul>';
        echo '</div>';
    }
?>