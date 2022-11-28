<?php
/**
 * Copyright (c) ORCA.nrw
 * GPLv3, see LICENSE
 */
include_once('./Services/Repository/classes/class.ilObjectPluginGUI.php');
include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ORCAContent/classes/class.ilObjORCAContent.php');


/**
 * ORCA Content plugin: repository object GUI
 *
 * @author A. Ruhri <ruhri@metromorph.de>
 * @version $Id$
 * 
 * @ilCtrl_isCalledBy ilObjORCAContentGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
 * @ilCtrl_Calls ilObjORCAContentGUI: ilPermissionGUI, ilORCAContentLogGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonactionDispatcherGUI
 */

class ilObjORCAContentGUI extends ilObjectPluginGUI
{

    private $orcaTools, $orcaCats;

    protected $form;
    /**
     * Initialisation
     *
     * @access protected
     */
    protected function afterConstructor()
    {
        // anything needed after object has been constructed

        // load tools and categories
        $this->orcaTools = $this->get_orca_tools();
        $this->orcaCats = $this->get_orca_categories_json();
    }

    /**
     * Get type.
     * @return string
     */
    final function getType()
    {
        return "xorc";
    }

    function getTitle()
    {
        return $this->object->getTitle();
    }

    /**
     * After object has been created -> jump to this command
     * @return string
     */
    function getAfterCreationCmd()
    {
        return "edit";
    }

    /**
     * Get standard command
     * @return string
     */
    function getStandardCmd()
    {
        return "view";
    }

    /**
     * Extended check for being in creation mode
     *
     * @return bool		creation mode
     */
    protected function checkCreationMode()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];

        $cmd = $ilCtrl->getCmd();
        if ($cmd == "create" || $cmd == "cancelCreate" || $cmd == "save" || $cmd == "Save") {
            $this->setCreationMode(true);
        }
        return $this->getCreationMode();
    }



    /**
     * Perform command
     *
     * @access public
     */
    public function performCommand($cmd)
    {
        global $DIC;
        $ilErr = $DIC['ilErr'];
        $ilCtrl = $DIC['ilCtrl'];

        switch ($cmd) {
            case "update":
                $this->checkPermission("write");
                $cmd .= "Object";
                $this->$cmd();
                break;

            default:

                if ($this->checkCreationMode()) {
                    $this->$cmd();
                } else {
                    $this->checkPermission("read");
//                    if (!$cmd) {
//                        $cmd = "viewObject";
//                    }
                    $cmd .= "Object";
                    $this->$cmd();
                }
        }
    }

    /**
     * create new object form
     *
     * @access	public
     */
    function create()
    {
        global $rbacsystem, $ilErr;

        $this->setCreationMode(true);
        if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], 'lti')) {
            $ilErr->raiseError($this->txt("permission_denied"), $ilErr->MESSAGE);
        } else {
            $this->initForm("create");
            $this->tpl->setVariable('ADM_CONTENT', $this->form->getHTML());
        }
    }


    /**
     * cancel creation of a new object
     *
     * @access	public
     */
    function cancelCreate()
    {
        $this->ctrl->returnToParent($this);
    }


    /**
     * update object
     *
     * @access public
     */
    public function updateObject()
    {
        $this->initForm("edit");
        if ($this->form->checkInput()) {
            $this->saveFormValues();
            ilUtil::sendInfo($this->lng->txt("settings_saved"), true);
            $this->ctrl->redirect($this, "edit");
        } else {
            $this->form->setValuesByPost();
            $this->tpl->setVariable('ADM_CONTENT', $this->form->getHTML());
        }
    }


    /**
     * Init properties form
     *
     * @param        int        $a_mode        Form Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE)
     * @param		 array		(assoc) form values
     * @access       protected
     */
    protected function initForm($a_mode, $a_values = array())
    {
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");

        if (is_object($this->form)) {
            return true;
        }

        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));

        $this->tpl->setTitleIcon('./Customizing/global/plugins/Services/Repository/RepositoryObject/ORCAContent/templates/images/icon_xorc.svg', "ORCA");
        $this->tpl->setTitle("ORCA");
        $this->tpl->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/ORCAContent/js/ilORCALTISelector.js');

        $this->form->setTitle($this->txt('xorc_new'));

        if (isset($a_values['TOOL_ID']) && (int) $a_values['TOOL_ID'] > 0) {
            $toolid = $a_values['TOOL_ID'];
        } else {
            $toolid = -1;
        }

        if (isset($a_values['toolurl']) && (int) $a_values['toolurl'] > 0) {
            $toolurl = $a_values['toolurl'];
        } else {
            $toolurl = '';
        }

        $item = new ilCustomInputGUI($this->txt('choosed_content'), 'toolselector');
        $item->setHtml($this->get_orcalti_spa_domstring($toolid));
        $item->setInfo($this->txt('choose_orca_tool'));
        $this->form->addItem($item);

        $item = new  ilHiddenInputGUI('TOOL_ID', 'TOOL_ID');
        $item->setValue($a_values['TOOL_ID']);
        $this->form->addItem($item);

        $item = new  ilHiddenInputGUI('toolurl', 'toolurl');
        $item->setValue($a_values['toolurl']);
        $this->form->addItem($item);

        $this->form->addCommandButton("update", $this->lng->txt("save"));
        $this->form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));
    }



    /**
     * Save the property form values to the object
     *
     * @access   protected
     */
    protected function saveFormValues()
    {
        global $DIC; /* ILIAS\DI\Container*/

        $id = trim($this->form->getInput("TOOL_ID"));
        $toolurl = trim($this->form->getInput("toolurl"));

        $getCourseObject = $this->get_orca_course($id, $toolurl);

        $provider_url = ($getCourseObject) ? $getCourseObject->tool_url : '';
        $provider_key = ($getCourseObject) ? $getCourseObject->key : '';
        $provider_secret = ($getCourseObject) ? $getCourseObject->secret : '';
        $description = ($getCourseObject && isset($getCourseObject->description)) ? $getCourseObject->description : '';

        $provId = ilObjORCAContent::getProviderIdByUrlKeySecret($provider_url,$provider_key,$provider_secret);

        $objProv = new ilLTIConsumeProvider($provId);
        $objProv->setTitle($this->form->getInput("TOOL_NAME"));
        $objProv->setDescription($description);
        $objProv->setProviderKey($provider_key);
        $objProv->setProviderSecret($provider_secret);
        $objProv->setProviderKeyCustomizable(false);
        $objProv->setProviderUrl($provider_url);
        $objProv->setAvailability($objProv::AVAILABILITY_CREATE);
        $objProv->setIsGlobal(true);
        $objProv->setProviderIconFilename('icon_xorc.svg');
        $objProv->setLaunchMethod($objProv::LAUNCH_METHOD_NEW);
        $objProv->setCreator($this->user->getId());
        $objProv->setAlwaysLearner(true);
        $objProv->setKeywords('ORCA');
//        $objProv->setPrivacyIdent($objProv::PRIVACY_IDENT_IL_UUID_SHA256);
        $objProv->setPrivacyIdent($objProv::PRIVACY_IDENT_IL_UUID_USER_ID);
        $objProv->setPrivacyName($objProv::PRIVACY_NAME_NONE);
        $objProv->setHasOutcome(true);
//        $objProv->setMasteryScorePercent(80); //default
        $objProv->save();

        $objCon = new ilObjLTIConsumer();
        $objCon->setTitle($this->form->getInput("TOOL_NAME"));
        $objCon->setDescription($description);
        $objCon->create();
        $objCon->createReference();
        $objCon->setProviderId($objProv->getId());
        $objCon->setProvider($objProv);
        $objCon->setLaunchMethod($objProv::LAUNCH_METHOD_NEW);
        $objCon->setOfflineStatus(false);
        $objCon->setMasteryScorePercent(80);
        $objCon->save();
//        $this->initMetadata($objCon);
        $objCon->putInTree($_GET["ref_id"]);
        $objCon->setPermissions($_GET["ref_id"]);
        $DIC->ctrl()->returnToParent($this);
    }



    /**
     * Get the data of orca tools from provider-system.
     *
     * @return array
     */
    function get_orca_tools()
    {
        global $DIC;
        $ilErr = $DIC['ilErr'];
        $ilLog = $DIC->logger()->sess();

        include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ORCAContent/classes/class.ilORCAContentConfigGUI.php');
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ORCAContent/classes/class.ilORCAContentFunctions.php');

        $this->config = new ilORCAContentConfigGUI;
        $config_username = $this->config->getProviderUsername();
        $config_passwort = $this->config->getProviderPass();
        $config_url = $this->config->getProviderUrl();
        if (!$config_url) {
            // \todo Hardcoded ORCA-URL here. Check if correct at least...
            $config_url = "https://provider.orca.nrw/ltidir";
        }

        $auth = base64_encode("{$config_username}:{$config_passwort}");
        $context = stream_context_create([
            "http" => [
                "header" => "Authorization: Basic $auth"
            ],
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ]);
        $orca_tools = array();
        $json = @file_get_contents($config_url . "/shared", false, $context);
        if (is_array($http_response_header)) {
            $head = ilORCAContentFunctions::parseHttpHeaders($http_response_header);
        }

        if (!is_array($http_response_header) or $head["response_code"] != "200") {
            $ilLog->write("Error getting tools from provider. \nRequested URL: "
                . $config_url . "/shared \nHTTP-response: \n" . print_r($head, TRUE));
            $ilErr->raiseError($this->txt('no_tools_found'), $ilErr->MESSAGE);
        }

        if ($json) {
            return json_decode($json);
        }
    }


    /**
     * Get the reduced data of orca tools as JSON.
     *
     * @return array
     */
    function get_orca_tools_json()
    {

        global $ilErr;
        foreach ($this->orcaTools as $tool_data) {
            $orca_tool = array();
            if (empty($tool_data->name)) {
                $orca_tool['name'] = $tool_data->fullname;
            } else {
                $orca_tool['name'] = $tool_data->name;
            }
            $orca_tool['category'] = $tool_data->category;
            $orca_tool['toolid'] = $tool_data->toolid;
            $orca_tool['description'] = $tool_data->description;
            $orca_tool['url'] = $tool_data->tool_url;
            $orca_tool['key'] = $tool_data->key;
            $orca_tools[] = $orca_tool;
        }

        return json_encode($orca_tools);
    }


    /**
     * Get the data of orca categories.
     *
     * @return string json string with categories
     */
    public function get_orca_categories_json()
    {

        include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ORCAContent/classes/class.ilORCAContentConfigGUI.php');
        require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/ORCAContent/classes/class.ilORCAContentFunctions.php');

        $this->config = new ilORCAContentConfigGUI;
        $config_username = $this->config->getProviderUsername();
        $config_passwort = $this->config->getProviderPass();
        $config_url = $this->config->getProviderUrl();

        if (!$config_url) {
            // \todo Hardcoded ORCA-URL here. Check if correct at least...
            $config_url = "https://provider.orca.nrw/ltidir";
        }

        $auth = base64_encode("{$config_username}:{$config_passwort}");
        $context = stream_context_create([
            "http" => [
                "header" => "Authorization: Basic $auth"
            ],
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ]);

        $json = @file_get_contents($config_url . "/categories", false, $context);
        $head = ilORCAContentFunctions::parseHttpHeaders($http_response_header);

        if ($head["response_code"] == "404") {
            $json = [];
        }
        return $json;
    }


    /**
     * Get the name of orca tool.
     * @param  string tool_id
     * @return string name
     */
    public function get_orca_name($tool_id)
    {
        global $DIC;
        $ilErr = $DIC['ilErr'];

        $index = array_search($tool_id, array_column($this->orcaTools, 'toolid'));

        if ($index !== FALSE) { // Check type-identity for index can be 0!!!
            if (empty($this->orcaTools[$index]->name)) {
                $name = $this->orcaTools[$index]->fullname;
            } else {
                $name = $this->orcaTools[$index]->name;
            }
        }
        return $name;
    }

    /**
     * Get the description of orca tool.
     * @param  string tool_id
     * @return string description
     */
    public function get_orca_description($tool_id)
    {
        $description = "";
        $index = array_search($tool_id, array_column($this->orcaTools, 'toolid'));

        if ($index !== FALSE) { // Check type-identity for index can be 0!!!
            if (!empty($this->orcaTools[$index]->description)) {
                $description = $this->orcaTools[$index]->description;
            }
        }
        return $description;
    }


    /**
     * Get the launch_url of orca tool.
     * @param  string tool_id
     * @return string tool_launch_url
     */
    public function get_orca_launch_url($tool_id)
    {
        global $DIC;
        $ilErr = $DIC['ilErr'];

        $index = array_search($tool_id, array_column($this->orcaTools, 'toolid'));

        if ($index !== FALSE) { // Check type-identity for index can be 0!!!
            $launch_url = $this->orcaTools[$index]->tool_url;
        }

        if ($index === FALSE || $launch_url == "") {
            $ilErr->raiseError($this->txt('no_launch_url_found'), $ilErr->MESSAGE);
        }
        return $launch_url;
    }



    /**
     * Get the secret data of orca tools.
     * @param  string tool_id
     * @return string secret
     */
    public function get_orca_secret($tool_id, $toolurl)
    {
        global $ilErr;
        $secret = "";

        $json = $this->orcaTools;
        if ($json && $tool_id) {
            $tools_data = json_decode($json);

            $filteredItems = array_filter($tools_data, function($item, $k) use ($tool_id, $toolurl) {
                return $item->toolid == $tool_id && $item->tool_url == $toolurl;
              }, ARRAY_FILTER_USE_BOTH);

            $item = ($filteredItems)? reset($filteredItems) : null;
            $secret = ($item)? $item->secret : "";

        }

        if ($secret == "") {
            $ilErr->raiseError($this->txt('no_passwd_found'), $ilErr->MESSAGE);
        }
        return $secret;
    }


    /**
     * Get the key data of orca tools.
     * @param  string tool_id
     * @return string key
     */
    public function get_orca_key($tool_id)
    {
        $key = "";
        $index = array_search((int)$tool_id, array_column($this->orcaTools, 'toolid'));

        if ($index !== FALSE) { // Check type-identity for index can be 0!!!
            $key = $this->orcaTools[$index]->key;
        }
        if ($index === FALSE || $key == "") {
            $key = "dummy";
        }
        return $key;
    }

    public function get_orca_course($tool_id, $toolurl)
    {
        global $ilErr;

        $tools_data = $this->orcaTools;
        $courseObject = "";

        if ($tools_data && $tool_id) {
            $filteredItems = array_filter($tools_data, function($item, $k) use ($tool_id, $toolurl) {
                return $item->toolid == $tool_id && $item->tool_url == $toolurl;
              }, ARRAY_FILTER_USE_BOTH);

            $item = ($filteredItems)? reset($filteredItems) : null;
            $courseObject = ($item)? $item : "";
        }

        if ($courseObject == "") {
            $ilErr->raiseError($this->txt('no_course_found'), $ilErr->MESSAGE);
        }
        return $courseObject;
    }


    /**
     * Get the translations for orcalti tools.
     *
     * @return string
     */
    public function get_orcalti_translations()
    {

        $string_arr = array("modal_fullscreen_description", "modal_close", "orca_logo_alt", "website_url_orca", "orca_link_title", "categories", "contact", "email_address_orca", "send_support_request", "input_search_id", "input_search_placeholder", "searching_in_all_categories", "searching_in_category", "search_no_content_found", "no_search_no_content_found", "expand_description", "collapse_description", "button_select", "pagination_label", "pagination_next", "pagination_prev", "error", "open_category_menu");

        $en = array();
        $de = array();

        foreach ($string_arr as $item) {
            $en[$item] = $this->txt($item);
            $de[$item] = $this->txt($item);
        }
        $translations = array(
            "en" => $en,
            "de" => $de
        );


        return json_encode($translations);
    }

    /**
     * Get the options for orcalti SPA.
     *
     * @return array
     */
    public function get_orcalti_options()
    {
        $options = array(
            "root_id" => "mnrw-orca-lti-root",
            "selected_tool_url_field_name" => "toolurl",
            "selected_tool_id_field_name" => "TOOL_ID",
            "selected_tool_toolname_field_name" => "TOOL_NAME"
        );

        return $options;
    }

    /**
     * Get the domstring for orcalti SPA.
     *
     * @return string
     */
    public function get_orcalti_spa_domstring($toolid)
    {
        $spa_tools = $this->get_orca_tools_json();
        $spa_categories = $this->orcaCats;
        $spa_translations = $this->get_orcalti_translations();
        $spa_options = $this->get_orcalti_options();

        $spa_root_id = $spa_options["root_id"];
        $spa_options_json = json_encode($spa_options);

        // TODO Understand why "KEY" = "dummy" needs to be set! What is this anyway?

        $domstring = '
        <input aria-label="Tool" 
               class="form-control" 
               type="text" 
               id="TOOL_NAME" 
               maxlength="200" 
               style="margin-bottom:10px; width: 60%; display: inline;" 
               name="TOOL_NAME" 
               value="' . $this->get_orca_name($toolid) . '"
               readonly>
        <div id="' . $spa_root_id . '"> </div>
       <script type="text/javascript"> 
            window.addEventListener("DOMContentLoaded", function() {
                window.orcalti.init(' . $spa_tools . ',' . $spa_categories . ',' . $spa_translations . ',' . $spa_options_json . ');
                document.getElementById("KEY").value = "dummy";
            });
        </script>';

        return $domstring;
    }
}
