<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Graham Solomon <graham.solomon@powys.gov.uk>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
use TYPO3\CMS\Backend\Module\AbstractFunctionModule;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Module extension (addition to function menu) 'View by Group' for the 'group_mngr' extension.
 *
 * @author	Graham Solomon <graham.solomon@powys.gov.uk>
 */



class tx_groupmngr_modfunc1 extends AbstractFunctionModule {


    /** @var  IconFactory */
    protected $iconFactory;

	/**
	 * Returns the module menu
	 *
	 * @return	Array with menuitems
	 */
	function modMenu()	{
		global $LANG;

		return Array (
			"tx_groupmngr_modfunc1_check" => "",
		);
	}



	/**
	 * Main method of the module
	 *
	 * @return	HTML
	 */
	function main()	{
			// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		$this->id = GeneralUtility::_GP('id');       // Page Id.. irrelevant
		$this->mode   = (GeneralUtility::_GP('mode')) ? GeneralUtility::_GP('mode') : 'fe';   // BE/FE view mode
        $groupId  = GeneralUtility::_GP('groupId');  // FE/BE Group uid
        $action   = GeneralUtility::_GP('action');   // Actions...

        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        if($action) {

            switch($action) {
                case 'add':
                    if(!is_array(GeneralUtility::_GP('groupIds'))) {
                        $this->message = 'ERROR: No Group(s) selected<br />';
                    }
                    elseif(!GeneralUtility::_GP('usernames')) {
                        $this->message = 'ERROR: Please enter one or more usernames<br />';
                    }
                    else {
                        $this->addUsersToGroups(GeneralUtility::_GP('usernames'), GeneralUtility::_GP('groupIds'));
                    }
                    break;
                case 'remove':

                    if($username = $this->removeUserFromGroup($groupId, GeneralUtility::_GP('userId'))) {
                        $this->message.= 'Updated groups for \''.$username.'\' successfully<br />';
                    }
                    else {
                        $this->message.= 'ERROR: Failed to remove user from group<br />';
                    }
                    break;
                default:
            }

            if($this->message) {
                $output.=$this->pObj->doc->section("Result", '<p>'.$this->message.'</p>', 0, 1);
            }
        }

        $output.=$this->pObj->doc->spacer(5);

            // Create output frontend/backend mode switcher.
        $modeSwitcher = '<form name="mode_switcher" method="get" action="index.php">
                            <select name="mode" onchange="this.form.submit();">
                                <option value="fe"'.($this->mode=='fe' ? ' selected="selected"' : '').'>Frontend</option>
                                <option value="be"'.($this->mode=='be' ? ' selected="selected"' : '').'>Backend</option>
                            </select>
                            <input type="hidden" name="id" value="'.$this->id.'" />
                         </form>';

        $output.=$this->pObj->doc->section("Select Mode", $modeSwitcher, 0, 1);


            // Begin main module output.
		$output.=$this->pObj->doc->section("View ".($this->mode=='fe' ? 'FRONTEND' : 'BACKEND')." Groups", '', 0, 1);

            // Only allow be view for administrators.
		if($this->mode=='be' && !$BE_USER->isAdmin()) {
            $output.= '<p>Sorry but Backend mode is for administrators only.</p>';
        }
        else {

                // No groupId? output a list of groups for the current mode.
            if(!$groupId) {
                $groupList = $this->pObj->doc->table($this->showGroupList($this->mode));

                if($this->mode=='fe' && $this->manager) {
                    $output.= '<form name="add_to_groups" method="post" action="index.php">
                                   <p>To add multiple users seperate userbanes with a comma like so: user1,user2,user3</p>
                                   <strong><label for="usernames">Username(s): </label></strong>
                                   <input type="text" name="usernames" id="usernames"'.(GeneralUtility::_GP('usernames') ? ' value="'.GeneralUtility::_GP('usernames').'"' : '').' />
                                   <input type="hidden" name="id" value="'.$this->id.'" />
                                   <input type="hidden" name="action" value="add" />
                                   <input type="submit" name="submit" value="Add User(s) to Group(s)" />
                                   <hr />
                                   '.$groupList.'
                               </form>';
                }
                else {
                    $output.= $groupList;
                }
            }
                // A groupId has been specified, show a list of users for this group.
            else {
                $output.= $this->showMembersOfGroup($groupId, $this->mode);

                    // Back link.
                $altText = 'Go back';
                $output.= '<br /><a href="index.php?id='.$this->id.'&amp;mode='.$this->mode.'">
                           <img'.IconFactory::skinImg($BACK_PATH, 'gfx/goback.gif').'alt="'.$altText.'" title="'.$altText.'" />
                           <strong>Go Back</strong></a><br />';
            }
        }

		return $output;
	}



	/**
	 * Returns an array of groups
	 *
	 * @return array
	 */
	function showGroupList() {
	    global $TYPO3_DB, $BACK_PATH, $BE_USER;

	       // Get groups.
	    $dbResult = $TYPO3_DB->sql_query("SELECT * FROM ".$this->mode."_groups  WHERE deleted=0 ORDER BY title");

	    if($dbResult) {
            if($TYPO3_DB->sql_num_rows($dbResult)) {

                $output[] = array('&nbsp;', '&nbsp;', 'GROUP NAME', 'ACTIONS');

                    // Loop through array of groups.
    	        while($group = $TYPO3_DB->sql_fetch_assoc($dbResult)) {
                    $actions = '';

    	               // Add a checkbox if mode is FE (Used for selecting which groups to add user(s) to).
    	               // Only for admins or group managers.
                    if($this->mode=='fe' && $this->allowedToMngGroup($group)) {
                        $checkbox = '<input type="checkbox" name="groupIds[]" value="'.$group['uid'].'" />';
                        $this->manager = true;
                    }
                    else {
                        $checkbox='';
                    }

    	        	$icon = $this->iconFactory->getIconForRecord($this->mode.'_groups', $group);
    	        	$groupName = $group['title'];

                        // Edit group
                    if($BE_USER->isAdmin()) {
                        $altText = 'Edit';
        	        	$actions.= '<a href="#" onclick="'.BackendUtility::editOnClick('&edit['.$this->mode.'_groups]['.$group['uid'].']=edit', $BACK_PATH).'">';
        	        	$actions.= '<img'.$this->iconFactory->getIcon('actions-document-open').'alt="'.$altText.'" title="'.$altText.'" /></a>';
                    }

                        // List group members
                    $altText = 'View Members of this group('.$group['title'].')';
                    $actions.= '<a href="index.php?id='.$this->id.'&amp;mode='.$this->mode.'&amp;groupId='.$group['uid'].'">';
    	        	$actions.= '<img'.$this->iconFactory->getIcon('actions-system-list-open').'alt="'.$altText.'" title="'.$altText.'" /></a>';

                        // Allow some extendability...
    	        	if(function_exists('additionalGroupActions')) {
    	        	    $actions.= $this->additionalGroupActions($group);
    	        	}

    	        	$output[] = array($checkbox, $icon, $groupName, $actions);
    	        }
            }
            else {
                return 'No '.($this->mode=='fe' ? 'frontend' : 'backend').' groups were found.';
            }
	    }
	    else {
	        $TYPO3_DB->debug('sql_query');
	    }

	    return $output;
    }



    /**
     * Returns a HTML table of users for the specified group.
     *
     * @param integer $groupId: fe/be_groups uid
     * @return string
     */
    function showMembersOfGroup($groupId) {
        global $BACK_PATH, $BE_USER;

            // Get group record and its members.
        $group = BackendUtility::getRecord($this->mode.'_groups', $groupId, '*');
	    $members = $this->getMembersOfGroup($groupId, $this->mode);

	    if(is_array($members)) {
            $output.= '<p>Users belonging to the group \'<strong>'.$group['title'].'</strong>\': <br /><br /></p>';

            $table[] = array('&nbsp;', 'USERNAME', 'ACTIONS');

                // Loop through members array.
            foreach($members as $uid) {
                $actions = '';
                $user = BackendUtility::getRecord($this->mode.'_users', $uid, '*');

                $icon = $this->iconFactory->getIconForRecord($this->mode.'_users', $user);

                    // Edit user.
                if($BE_USER->isAdmin()) {
                    $altText = 'Edit';
                	$actions.= '<a href="#" onclick="'.BackendUtility::editOnClick('&edit['.$this->mode.'_users]['.$user['uid'].']=edit', $BACK_PATH).'">';
                	$actions.= '<img'.IconFactory::skinImg($BACK_PATH, 'gfx/edit2.gif').'alt="'.$altText.'" title="'.$altText.'" /></a>';
                }

            	   // Remove user from group.
                if($this->mode=='fe' && $this->allowedToMngGroup($group)) {
                    $altText = 'Remove \''.$user['username'].'\' from \''.$group['title'].'\'';
                    $actions.= '<a href="index.php?id='.$this->id.'&amp;mode='.$this->mode.'&amp;groupId='.$groupId.'&amp;action=remove&userId='.$user['uid'].'">';
                	$actions.= '<img'.IconFactory::skinImg($BACK_PATH, 'gfx/garbage.gif').'alt="'.$altText.'" title="'.$altText.'" /></a>';
                }

            	  // Allow some extendability...
            	if(function_exists('additionalUserActions')) {
            	    $actions.= $this->additionalUserActions($user);
            	}

                $table[] = array($icon, $user['username'], $actions);
            }

            $output.= $this->pObj->doc->table($table);
        }
        else {
            $output.= '<p>There are no users belonging to this group.</p>';
        }

        return $output;
    }



    /**
     * Returns an array of fe/beusers uid's for the specified group.
     *
     * @param integer $groupId: fe/be_groups uid
     * @return array
     */
	function getMembersOfGroup($groupId) {
	    global $TYPO3_DB;

	    $dbResult = $TYPO3_DB->sql_query("SELECT uid, usergroup FROM ".$this->mode."_users WHERE deleted=0");

	    if($dbResult) {
	        if($TYPO3_DB->sql_num_rows($dbResult)==0) {
	            return false;
	        }
	        else {

	            while(list($uid, $usergroup) = $TYPO3_DB->sql_fetch_row($dbResult)) {
    	            if($this->inList($usergroup, $groupId)) {
    	                $members[] = $uid;
    	            }
	            }

	            return $members;
	        }
	    }
	    else {
	        $TYPO3_DB->debug('sql_query');
	    }
	}



	/**
	 * Adds user(s) to one or more fe groups.
	 *
	 * @param string $usernames: List of comma seperated usernames
	 * @param array  $groupIds: Array of fe/be_groups uid's
	 */
	function addUsersToGroups($usernames, $groupIds) {
	    global $TYPO3_DB;

        $usernames = GeneralUtility::trimExplode(',', $usernames);

        foreach($usernames as $username) {

            $user = BackendUtility::getRecordRaw('fe_users', "username='".$TYPO3_DB->quoteStr($username, 'fe_users')."'");

            if(is_array($user)) {

                $usergroup = $user['usergroup'];

                foreach($groupIds as $groupId) {
                    $group = BackendUtility::getRecord('fe_groups', $groupId, 'title,tx_groupmngr_manager');

                    if($this->allowedToMngGroup($group, true)) {
                        if($this->inList($usergroup, $groupId)) {
                            $this->message.= 'ERROR: User ('.$username.') is already a member of group ('.$group['title'].')<br />';
                        }
                        else {
                            $usergroup = (!$usergroup) ? $groupId : $usergroup.','.$groupId;
                        }
                    }
                }

                if($usergroup != $user['usergroup']) {
                    $dbResult = $TYPO3_DB->exec_UPDATEquery('fe_users',
                                                            "username='".$TYPO3_DB->quoteStr($username, 'fe_users')."'",
                                                            array('usergroup' => $usergroup));

                    if($dbResult) {
                        $this->message.= 'Updated groups for \''.$username.'\' successfully<br />';
                    }
                    else {
                        $TYPO3_DB->debug('exec_UPDATEquery');
                    }
                }
            }
            else {
                $this->message.= 'ERROR: User does not exist ('.$username.')<br />';
            }
        }
	}



	/**
	 * Removes a user from the specified fe_group
	 *
	 * @param integer $groupId: fe_groups uid
	 * @param integer $userId: fe_users uid
	 * @return boolean true on success false on failure
	 */
	function removeUserFromGroup($groupId, $userId) {
        global $TYPO3_DB;

	    $group = BackendUtility::getRecord('fe_groups', intval($groupId));

	    if($this->allowedToMngGroup($group)) {
	        $user = BackendUtility::getRecord('fe_users', $userId);
	        $usergroups = GeneralUtility::trimExplode(',', $user['usergroup']);

	        foreach($usergroups as $group) if($group!=$groupId)    $array[] = $group;
	        $usergroups = (is_array($array)) ? implode(',', $array) : '';

            $dbResult = $TYPO3_DB->exec_UPDATEquery('fe_users',
                                                    "uid='".intval($userId)."'",
                                                    array('usergroup' => $usergroups));

            if($dbResult) {
                return $user['username'];
            }
            else {
                $TYPO3_DB->debug('exec_UPDATEquery');
            }
	    }
	    else {
	        return false;
	    }
	}



	/**
	 * Returns true if $item is in $list
	 *
	 * @param string $list: Comma list with items, no spaces between items!
	 * @param string $item: The string to find in the list of items
	 * @return boolean
	 */
	function inList($list, $item)	{
		return strstr(','.$list.',', ','.$item.',');
	}



	/**
	 * Determines whether a user should be allowed to make alterations
	 *
	 * Only Admin users & Group Managers should have this privilege.
	 *
	 * @param array $group: fe_users group record
	 * @return boolean
	 */
	function allowedToMngGroup($group) {
	    global $BE_USER;

	    if($this->inList($group['tx_groupmngr_manager'], $BE_USER->user['uid']) or $BE_USER->isAdmin()) {
            return true;
	    }
	    else {
	       return false;
	    }
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/group_mngr/modfunc1/class.tx_groupmngr_modfunc1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/group_mngr/modfunc1/class.tx_groupmngr_modfunc1.php']);
}

?>
