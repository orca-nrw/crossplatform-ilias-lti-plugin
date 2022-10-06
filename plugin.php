<?php
/**
 * Copyright (c) ORCA.nrw
 * GPLv3, see LICENSE
 */

/**
 * ORCA Content plugin: base parameters
 *
 * @author A. Ruhri <ruhri@metromorph.de>
 * @author K. Mach <mach@metromorph.de>
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 */

// alphanumerical ID of the plugin; never change this
$id = "xorc";
 
// code version; must be changed for all code changes
define('xorc_version', '0.2.0');
$version = 20221006;
 
// ilias min and max version; must always reflect the versions that should
// run with the plugin
$ilias_min_version = "6.0.0";
$ilias_max_version = "7.999";
 
// optional, but useful: Add one or more responsible persons and a contact email
$responsible = "Geschäftsstelle ORCA.nrw";
$responsible_mail = "admin@orca.nrw";

$learning_progress = false;
?>
