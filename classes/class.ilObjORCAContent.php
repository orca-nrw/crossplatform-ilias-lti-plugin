<?php
/**
 * Copyright (c) ORCA.nrw
 * GPLv3, see LICENSE
 */
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');


/**
 * @author A. Ruhri <ruhri@metromorph.de>
 * @version $Id$
 */
class ilObjORCAContent extends ilObjectPlugin
{

    /**
     * Get type.
     * The initType() method must set the same ID as the plugin ID.
     *
     * @access	public
     */
    final public function initType() {
        $this->setType('xorc');
    }


    /**
     * Check if provider exists
     * @param string $url
     * @param string $key
     * @param string $secret
     * @return int
     */
    public static function getProviderIdByUrlKeySecret($url, $key, $secret) : ?int
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $id = null;
        $query = "SELECT * FROM lti_ext_provider WHERE provider_url = %s AND provider_key = %s AND provider_secret = %s";
        $res = $DIC->database()->queryF(
            $query,
            array('string', 'string', 'string'),
            array($url, $key, $secret)
        );
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $id = $row['id'];
        }
        return $id;
    }

}

?>
