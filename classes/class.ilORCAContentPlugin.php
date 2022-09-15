<?php
/**
 * Copyright (c) ORCA.nrw
 * GPLv3, see LICENSE
 */

include_once('./Services/Repository/classes/class.ilRepositoryObjectPlugin.php');

/**
 * ORCA Content plugin
 *
 */
class ilORCAContentPlugin extends ilRepositoryObjectPlugin
{

	/**
	 * Returns name of the plugin
	 *
	 * @return <string
	 * @access public
	 */
	public function getPluginName()
	{
		return 'ORCAContent';
	}

	/**
	 * Remove all custom tables when plugin is uninstalled
	 */
	protected function uninstallCustom()
	{
	}
	

	/**
	 * decides if this repository plugin can be copied
	 *
	 * @return bool
	 */
	public function allowCopy()
	{
		return false;
	}

}
?>
