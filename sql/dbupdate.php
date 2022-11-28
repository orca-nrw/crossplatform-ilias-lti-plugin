<#1>
<?php

/**
 * Add ORCA Settings
 */
    $statement = $ilDB->prepareManip("REPLACE INTO settings (module, keyword, value) VALUES (?, ?, ?)",
                         array("text","text","text")
    );

    $data = array(
        // \todo Hardcoded URL. Check at least...
        array("xorc", "provider_url", "https://provider.preview.orca.nrw/ltidir/"),
        array("xorc", "provider_username", ""),
        array("xorc", "provider_pass", "")
    );

    $ilDB->executeMultiple($statement, $data);
?>
