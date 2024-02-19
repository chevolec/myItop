<?php
// Copyright (C) 2013 Combodo SARL
//
//   This file is a sample extension for iTop
//
//   iTop is free software; you can redistribute it and/or modify	
//   it under the terms of the GNU Affero General Public License as published by
//   the Free Software Foundation, either version 3 of the License, or
//   (at your option) any later version.
//
//   iTop is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU Affero General Public License for more details.
//
//   You should have received a copy of the GNU Affero General Public License
//   along with iTop. If not, see <http://www.gnu.org/licenses/>

/**
 * Sample extension to show how adding menu items in iTop
 * This extension does nothing really useful but shows how to use the three possible
 * types of menu items:
 * 
 * - An URL to any web page
 * - A Javascript function call
 * - A separator (horizontal line in the menu)
 */
class AddMenuSampleExtension implements iPopupMenuExtension
{
	/**
	 * Get the list of items to be added to a menu.
	 *
	 * This method is called by the framework for each menu.
	 * The items will be inserted in the menu in the order of the returned array.
	 * @param int $iMenuId The identifier of the type of menu, as listed by the constants MENU_xxx
	 * @param mixed $param Depends on $iMenuId, see the constants defined above
	 * @return object[] An array of ApplicationPopupMenuItem or an empty array if no action is to be added to the menu
	 */
	public static function EnumItems($iMenuId, $param)
	{
		$aResult = array();
		
		switch($iMenuId) // type of menu in which to add menu items
		{
			/**
			 * Insert an item into the Actions menu of a list
			 *
			 * $param is a DBObjectSet containing the list of objects	
			 */	
/*			case iPopupMenuExtension::MENU_OBJLIST_ACTIONS:
			
			// Add a new menu item that triggers a custom JS function defined in our own javascript file: js/sample.js
			$sModuleDir = basename(dirname(__FILE__));
			$sJSFileUrl = utils::GetAbsoluteUrlModulesRoot().$sModuleDir.'/js/sample.js';
			$iCount = $param->Count(); // number of objects in the set
			$aResult[] = new JSPopupMenuItem('_Custom_JS_', 'Custom JS Function On List...', "MyCustomJSListFunction($iCount)", array($sJSFileUrl));
			break;
			
*/			/**
			 * Insert an item into the Toolkit menu of a list
			 *
			 * $param is a DBObjectSet containing the list of objects
			 */	
			case iPopupMenuExtension::MENU_OBJLIST_TOOLKIT:
			break;
			
			/**
			 * Insert an item into the Actions menu on an object's details page
			 *
			 * $param is a DBObject instance: the object currently displayed
			 */	
			case iPopupMenuExtension::MENU_OBJDETAILS_ACTIONS:
			// For any object, add a menu "Google this..." that opens google search in another window
			// with the name of the object as the text to search
/*			$aResult[] = new URLPopupMenuItem('_Google_this_', 'Google that', "http://www.google.com?q=".$param->GetName(), '_blank');
*/
			// Only for Contact: (i.e. Teams and Persons)
/*			if ($param instanceof Contact)
			{
				// add a separator
				$aResult[] = new SeparatorPopupMenuItem(); // Note: separator does not work in iTop 2.0 due to Trac #698, fixed in 2.0.1
				
				// Add a new menu item that triggers a custom JS function defined in our own javascript file: js/sample.js
				$sModuleDir = basename(dirname(__FILE__));
				$sJSFileUrl = utils::GetAbsoluteUrlModulesRoot().$sModuleDir.'/js/sample.js';
				$aResult[] = new JSPopupMenuItem('_Custom_JS_', 'Custom Contact Function...', "MyCustomJSFunction('".addslashes($param->GetName())."')", array($sJSFileUrl));
			}
			break;
*/			
			// Only for Contact: (i.e. Teams and Persons)
			if ($param instanceof PC)
			{
				// add a separator
				$aResult[] = new SeparatorPopupMenuItem(); // Note: separator does not work in iTop 2.0 due to Trac #698, fixed in 2.0.1
				
				// Add a new menu item that triggers a custom JS function defined in our own javascript file: js/sample.js
				$sModuleDir = basename(dirname(__FILE__));
				$sJSFileUrl = utils::GetAbsoluteUrlModulesRoot().$sModuleDir.'/js/sample.js';
				//$aResult[] = new JSPopupMenuItem('_Custom_JS_', 'Custom PC Function...', "MyCustomJSFunction('".addslashes($param->GetName())."')", array($sJSFileUrl));
				$aResult[] = new URLPopupMenuItem('_responsiva_', 'Responsiva', "/pages/responsiva.php?".$param->GetName(), '_blank');
			}
			break;
			
			/**
			 * Insert an item into the Dashboard menu
			 *
			 * The dashboad menu is shown on the top right corner of the page when
			 * a dashboard is being displayed.
			 * 
			 * $param is a Dashboard instance: the dashboard currently displayed
			 */	
			case iPopupMenuExtension::MENU_DASHBOARD_ACTIONS:
			break;
			
			/**
			 * Insert an item into the User menu (upper right corner of the page)
			 *
			 * $param is null
			 */
			case iPopupMenuExtension::MENU_USER_ACTIONS:
			break;
		
		}
		return $aResult;
	}
}

