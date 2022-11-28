<?php
/**
 * Copyright (c) ORCA.nrw
 * GPLv3, see LICENSE
 */

include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");

/**
 * ORCAContent plugin: configuration GUI
 *
 * @author Alexander Ruhri <ruhri@metromorph.de>
 * @version $Id$
 */ 
class ilORCAContentConfigGUI extends ilPluginConfigGUI
{
    /** @var ilExternalContentType */
    protected $type;

    /** @var ilPropertyFormGUI */
    protected $form;



    private $provider_url;
    private $provider_username;
    private $provider_pass;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
    	// this uses the cached plugin object
		$this->plugin_object = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'ORCAContent');
        $this->read();
    }
    

    /**
     * Set Provider Url
     * @param string provider_url
     */
    public function setProviderUrl($a_provider_url)
    {
        $this->provider_url = $a_provider_url;
    }

    /**
     * Get Provider Url
     * @return string provider_url
     */
    public function getProviderUrl()
    {
        return $this->provider_url;
    }


   /**
     * Set Provider Username
     * @param string provider_username
     */
    public function setProviderUsername($a_provider_username)
    {
        $this->provider_username = $a_provider_username;
    }

    /**
     * Get Provider Username
     * @return string provider_username
     */
    public function getProviderUsername()
    {
        return $this->provider_username;
    }

   /**
     * Set Provider Pass
     * @param string provider_pass
     */
    public function setProviderPass($a_provider_pass)
    {
        $this->provider_pass = $a_provider_pass;
    }

    /**
     * Get Provider Pass
     * @return string provider_pass
     */
    public function getProviderPass()
    {
        return $this->provider_pass;
    }





    /**
     * Read function
     *
     * @access public
     */
    public function read()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilErr = $DIC['ilErr'];
        
        $query = "SELECT * FROM settings WHERE module = 'xorc' AND keyword = 'provider_url'";
        $res = $ilDB->query($query);
        $row = $ilDB->fetchObject($res);
        if($row)
            {
                $this->setProviderUrl($row->value);
            }
        $query = "SELECT * FROM settings WHERE module = 'xorc' AND keyword = 'provider_username'";
        $res = $ilDB->query($query);
        $row = $ilDB->fetchObject($res);
        if($row)
            {
                $this->setProviderUsername($row->value);
            }
        $query = "SELECT * FROM settings WHERE module = 'xorc' AND keyword = 'provider_pass'";
        $res = $ilDB->query($query);
        $row = $ilDB->fetchObject($res);
        if($row)
            {
                $this->setProviderPass($row->value);
            }
        
        return true;
    }
    

    /**
     * Update function
     *
     * @access public
     */
    public function update()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $ilDB->manipulate("UPDATE settings SET value = ".$ilDB->quote($this->getProviderUrl(), "text").
        " WHERE module = 'xorc' AND keyword = 'provider_url'");
        $ilDB->manipulate("UPDATE settings SET value = ".$ilDB->quote($this->getProviderUsername(), "text").
        " WHERE module = 'xorc' AND keyword = 'provider_username'");
        $ilDB->manipulate("UPDATE settings SET value = ".$ilDB->quote($this->getProviderPass(), "text").
        " WHERE module = 'xorc' AND keyword = 'provider_pass'");
             
        return true;
    }





    /**
     * perform command
     */
    public function performCommand($cmd)
    {
        global $DIC;
        $tree = $DIC['tree'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $tpl = $DIC['tpl'];
		
        $this->plugin_object->includeClass('class.ilObjORCAContent.php');
		
        // control flow
        $cmd = $ilCtrl->getCmd($this);
        switch ($cmd)
        {
        case 'submitFormSettings':
            $this->$cmd();
            break;
            
        default:
            $this->initTabs();
            $this->read();
            if (!$cmd)
                {
                    $cmd = "configure";
                }
            $this->$cmd();
            break;
        }
    }
    
    /**
     * Get a plugin specific language text
     * 
     * @param 	string	language var
     */
    function txt($a_var)
    {
    	return $this->plugin_object->txt($a_var);
    }
    
    /**
     * Init Tabs
     * 
     * @param string	mode ('edit_type' or '')
     */
    function initTabs($a_mode = "")
    {
    	global $DIC;
    	$ilCtrl = $DIC['ilCtrl'];
    	$ilTabs = $DIC['ilTabs'];
    	$lng = $DIC['lng'];
        $ilTabs->addTab("settings",
            $this->plugin_object->txt('settings'),
            $ilCtrl->getLinkTarget($this, 'configure')
        );
    }

    /**
     * Entry point for configuring the module
     */
    function configure()
    {
       global $DIC;
       $ilCtrl = $DIC['ilCtrl'];
       $ilTabs = $DIC['ilTabs'];
       $tpl = $DIC['tpl'];

        try {
            $stream = $DIC->filesystem()->customizing()->readStream('global/plugins/Services/Repository/RepositoryObject/ORCAContent/templates/images/icon_xorc.svg');
            $DIC->filesystem()->web()->put('lti_data/provider_icon/icon_xorc.svg', $stream);
        } finally {
            //exists
        }

       $ilTabs->activateSubTab('settings');
       $this->initFormSettings($this->loadSettings());
       $tpl->setContent($this->form->getHTML());


    }


    /**
     * Init the form to edit the settings
     * 
     * @param	array	values to set
     */
    private function initFormSettings($a_values = array())
    {
        global $DIC;
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt('settings'));


        $item1 = new ilTextInputGUI($this->txt('provider_url'), 'sp_provider_url');
        $item1->setInfo($this->txt('provider_url_info'));
        $item1->setValue($a_values['sp_provider_url']);
        $item1->setRequired(true);
        $item1->setMaxLength(255);
        $form->addItem($item1);

        $item2 = new ilTextInputGUI($this->txt('provider_username'), 'sp_provider_username');
        $item2->setValue($a_values['sp_provider_username']);
        $item2->setInfo($this->txt('provider_username_info'));
        $item2->setRequired(true);
        $item2->setMaxLength(32);
        $form->addItem($item2);

        $item3 = new ilTextInputGUI($this->txt('provider_pass'), 'sp_provider_pass');
        $item3->setValue($a_values['sp_provider_pass']);
        $item3->setInfo($this->txt('provider_pass_info'));
        $item3->setRequired(true);
        $item3->setMaxLength(32);
        $item3->setInputType('password');
        $form->addItem($item3);


        
        $form->addCommandButton('submitFormSettings', $lng->txt('save'));  
        $this->form = $form;
    }
    
    /**
     * Get the values for filling the settings form
     *
     * @return   array  settings
     */
    protected function loadSettings()
    {
        $values = array();

        $values['sp_provider_url'] = $this->getProviderUrl();
        $values['sp_provider_username'] = $this->getProviderUsername();
        $values['sp_provider_pass'] = $this->getProviderPass();
        return $values;
    }
    

    /**
     * Submit the form to save or update
     */
    function submitFormSettings()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        $tpl = $DIC['tpl'];

        $ilTabs->activateSubTab('type_settings');
        
        $this->initFormSettings();
        if (!$this->form->checkInput())
        {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
            return;
        } 
        else
        {
            $this->setProviderUrl($this->form->getInput("sp_provider_url"));
            $this->setProviderUsername($this->form->getInput("sp_provider_username"));
            $this->setProviderPass($this->form->getInput("sp_provider_pass"));
            
            $this->update();
         ilUtil::sendSuccess($this->txt('config_saved'), true);
        }

        $ilCtrl->redirect($this, 'configure');
    }
} 
