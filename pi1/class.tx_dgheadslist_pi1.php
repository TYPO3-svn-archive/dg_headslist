<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Dennis Grote <d.grote@dd-medien.de>
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
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   52: class tx_dgheadslist_pi1 extends tslib_pibase
 *   65:     function main($content, $conf)
 *  103:     function init($conf)
 *  162:     function record_list()
 *  268:     function group_menu()
 *  325:     function group_link($row, $wrap)
 *  349:     function tooltip_setup()
 *
 * TOTAL FUNCTIONS: 6
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'heads list' for the 'dg_headslist' extension.
 *
 * @author	Dennis Grote <d.grote@dd-medien.de>
 * @package	TYPO3
 * @subpackage	tx_dgheadslist
 */
class tx_dgheadslist_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_dgheadslist_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_dgheadslist_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'dg_headslist';	// The extension key.
	var $pi_checkCHash = true;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->init($conf);

		// set Variable
		$content='';
		$this->extUploadFolder = 'uploads/tx_dgheadslist/';
		
		// test if static TS is loaded
		if ($this->conf['isLoaded'] != 'yes') return '<H1>'.$this->pi_getLL('errorIncludeStatic').'</H1>';
		
		// what will be displayed
		switch ($this->theCode) {
			case 'NORMAL':
				if ($this->conf['use_tooltip']) $content .= $this->tooltip_setup();
				$content .= $this->record_list();
				if ($this->conf['group_list']) $content .= $this->group_menu();
				break;
				 
			case 'GROUPMENU':
				$content .= $this->group_menu();
				break;
				 
			default:
				$content = '<H1>'.$this->pi_getLL('errorWhatDisplay').'</H1>';
		}

		//t3lib_div::debug($this->conf, FLEXandTS);

		return $this->pi_wrapInBaseClass($content);
	}
	
	
	/**
	 * Initialize the Plugin
	 *
	 * @param	array	$conf: The PlugIn configuration
	 * @return	void
	 */
	function init($conf) {

		$this->conf = $conf;			// TypoScript configuration
		$this->pi_setPiVarDefaults();	// GetPost-parameter configuration
		$this->pi_loadLL();				// localized language variables
		$this->pi_initPIflexForm();		// Initialize the FlexForms array

		// load flexform in &conf only if TS code is not set
		if (!$this->conf['code'] && $this->cObj->data['pi_flexform']) {
			$piFlexForm = $this->cObj->data['pi_flexform'];
			foreach($piFlexForm['data'] as $sheet => $data) {
				foreach($data as $lang => $value) {
					foreach($value as $key => $val) {
						$this->conf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
					}
				}
			}
		}

		// load template priority on Flexform
		if ($this->conf['template_file']) {
			$this->tmpl = $this->cObj->fileResource($this->conf['template_file']);
		} else {
			$this->tmpl = $this->cObj->fileResource($this->conf['templateFile']);
		}

		// from which ID are the entries priority on Flexform
		if ($this->conf['storage_pid']) {
			$this->pidList = $this->conf['storage_pid'];
		} elseif ($this->conf['pidList']) {
			$this->pidList = $this->conf['pidList'];
		} else {
			$this->pidList = $GLOBALS['TSFE']->id;
		}

		// put what to display in theCode priority on TS
		$this->theCode = $this->conf['code'] ? $this->conf['code'] : $this->conf['what_to_display'];

		// pid for NORMAL with priority on Flexform
		$this->conf['normalPid'] = $this->conf['normal_pid'] ? $this->conf['normal_pid'] : $this->conf['normalPid'];

		// load GET parameter
		$this->currentGroup = (int) $this->piVars['group'];
		
		// default group
		if ($this->currentGroup == '' && $this->conf['default_group'] ) {
			$this->currentGroup = $this->conf['default_group'];
		} elseif ($this->currentGroup == '0') {
			$this->currentGroup = '';
		}
		
	}	
	
	
	/**
	 * display recordlist
	 *
	 * @return	html code of the recordlist
	 */
	function record_list() {
		// Read in the part of the template file for keyword listing
		$template = $this->cObj->getSubpart($this->tmpl, '###HEADSLIST###');
		// Get subpart template
		$subTemplate = $this->cObj->getSubpart($template, '###RECORD###');
		
		// WHERE clause addition
		// language overlay also for data records
		$whereAddition .= $this->conf['lang_overlay'] ? 'AND sys_language_uid = '.$GLOBALS['TSFE']->config['config']['sys_language_uid'].'' : '';
		// Show inactive images
		$whereAddition .= $this->conf['inactive_images'] ? '' : ' AND (categorys = "'.$this->currentGroup.'" OR categorys like ("'.$this->currentGroup.',%") OR categorys like ("%,'.$this->currentGroup.'") OR categorys like ("%,'.$this->currentGroup.',%"))';
		
		// The database query including support for database abstraction
		$select = 'name, pic_active, pic_inactive, sys_language_uid, link_id, categorys, no_tooltip';
		$from = 'tx_dgheadslist_main';
		$where = 'pid = '.$this->pidList.' '.$whereAddition.' '.$this->cObj->enableFields('tx_dgheadslist_main').'';
		$groupBy = '';
		$orderBy = 'sorting';
		$limit = '';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy, $limit);
				
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			// Gets the translated record if the content language is not the default language
			/*if ($GLOBALS['TSFE']->sys_language_content) {
				$OLmode = ($this->sys_language_mode == 'strict' ? 'hideNonTranslated' : '');
				$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_dgheadslist_main', $row, $GLOBALS['TSFE']->sys_language_content, $OLmode);
			}*/
			
			// get the image			
			if (in_array($this->currentGroup, split(',',$row['categorys'])) || $this->currentGroup == '') {   // Which picture is to be used
				$picType = 'pic_active';
			} else {
				$picType = 'pic_inactive';
			}
			
			$this->conf['image.']['file.']['10.']['file'] = $this->extUploadFolder . $row[$picType];
			
			// image dimentions
			if ($this->conf['image_max_width']) {
				$this->conf['image.']['file.']['10.']['file.']['width'] = ($this->conf['image_max_width']);
			}			
			if ($this->conf['image_max_height']) {
				$this->conf['image.']['file.']['10.']['file.']['height'] = ($this->conf['image_max_height']);
			}
			
			$this->conf['image.']['altText'] = $row['name'];
			
			// ToolTip or not
			if ($row['no_tooltip'] == '0') {
	      		if ($this->conf['active_tooltips'] && $picType == 'pic_active') {  				
    	  			$this->conf['image.']['params'] = 'class="'.$this->prefixId.'_ToolTips"';
	      		} elseif ($this->conf['active_tooltip'] && $picType == 'pic_inactive') {
    	  			$this->conf['image.']['params'] = '';
	      		} elseif ($this->conf['use_tooltip']) {
					$this->conf['image.']['params'] = 'class="'.$this->prefixId.'_ToolTips"';
	      		} else {
      				$this->conf['image.']['params'] = '';
      			}
    		} else {
    			$this->conf['image.']['params'] = '';
    		}
      			
       		$image = $this->cObj->cObjGetSingle($this->conf['image'], $this->conf['image.']);
			
       		// define link for an image
       		if ($row['link_id']) {
       			if ($this->conf['active_link'] && $picType == 'pic_inactive') {
    	  			$imageLink = $image;
	   			} else {
      				if ($this->conf['link_param'] && $this->currentGroup) {
      					
      					// image link with params
      					//$imageLink = $this->cObj->getTypoLink($image, $row['link_id'], array($this->prefixId.'[group]' => $this->currentGroup));
      					$imageLink = $this->pi_linkTP($image,array($this->prefixId.'[group]' => $this->currentGroup), 1, $row['link_id']);
      				} else {
      					
      					// normal image link
      					$imageLink = $this->cObj->getTypoLink($image, $row['link_id']);
      				}
      			}
      		} else {
      			$imageLink = $image;
      		}
      		
      		// image wrap
      		$imageLink = $this->cObj->wrap($imageLink, $this->conf['imageWrap']);
      		
      		// substitute marker
      		$subPartContent .= $this->cObj->substituteMarker($subTemplate, '###IMAGES###', $imageLink);
		}
		
		// substitute subpart
		$content = $this->cObj->substituteSubpart($template, '###RECORD###', $subPartContent);

		return $content;
		
	}
	
	
	
	/**
	 * display groupmenu
	 *
	 * @return	html code of the groupmenu
	 */
	function group_menu() {
		// Read in the part of the template file for keyword listing
		$template = $this->cObj->getSubpart($this->tmpl, '###HEADSLISTGROUP###');
		// Get subpart template
		$subTemplate = $this->cObj->getSubpart($template, '###GROUP###');
		
		// The database query including support for database abstraction
		$select = 'uid, title, sys_language_uid, l18n_parent';
		$from = 'tx_dgheadslist_cat';
		$where = 'sys_language_uid = '.$GLOBALS['TSFE']->config['config']['sys_language_uid'].' AND pid = '.$this->pidList.' '.$this->cObj->enableFields('tx_dgheadslist_cat').'';
		$groupBy = '';
		$orderBy = 'sorting';
		$limit = '';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy, $limit);
		
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			// Gets the translated record if the content language is not the default language
			/*if ($GLOBALS['TSFE']->sys_language_content) {
				$OLmode = ($this->sys_language_mode == 'strict' ? 'hideNonTranslated' : '');
				$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_dgheadslist_cat', $row, $GLOBALS['TSFE']->sys_language_content, $OLmode);
			}*/
			
			if ($row['uid'] == $this->currentGroup  || $row['l18n_parent'] == $this->currentGroup) {
				if ($this->theCode == 'GROUPMENU') {
					$listItem = $this->group_link($row, $this->conf['activeWrap']);
				} else {
					// wrap for current group
					$listItem = $this->cObj->wrap($row['title'], $this->conf['currentWrap']);
				}
				
			} else {
				$listItem = $this->group_link($row, $this->conf['linkWrap']);
			}

			// subtitute marker
			$subPartContent .= $this->cObj->substituteMarker($subTemplate, '###GROUPITEM###', $listItem);
		}

		// show groups only if groups are available
		if (isset($subPartContent) && !empty($subPartContent)) {
			$content = $this->cObj->substituteSubpart($template, '###GROUP###', $subPartContent);
		} else {
			$content = '';
		}
		
		return $content;
	}
	
	
	/**
	 * link for groupmenu
	 *
	 * @param	array	$row: database record
	 * @param	array	$wrap: wrap for the link
	 * @return	html code of the groupmenu links
	 */
	function group_link($row, $wrap) {
		// some parameter for link
		$altPageId = $this->conf['normalPid'] ? $this->conf['normalPid'] : '';
		$groupPrefix = $row['sys_language_uid'] == '0' ? $row['uid'] : $row['l18n_parent'];
		
		// the link it self
		$link = $this->pi_linkTP($row['title'],array($this->prefixId.'[group]' => $groupPrefix), 1, $altPageId);
		
		// title parameter for link
		$params = array('title' => $row['title']);
		$link = $this->cObj->addParams($link, $params);
		
		// wrap for link
		$link = $this->cObj->wrap($link, $wrap);
		
		return $link;
	}
		
		
	/**
	 * ToolTip setup
	 *
	 * @return	void
	 */
	function tooltip_setup() {
    	// checks if t3mootools is loaded
		if (t3lib_extMgm::isLoaded('t3mootools')) require_once(t3lib_extMgm::extPath('t3mootools').'class.tx_t3mootools.php');
				 	 
		// if t3mootools is loaded and the custom Library had been created
		if (defined('T3MOOTOOLS')) {
 			tx_t3mootools::addMooJS();
		// if none of the previous is true, you need to include your own library
		// just as an example in this way
		} else {
			$GLOBALS['TSFE']->additionalHeaderData['tx_dgheadslist_ToolTip_js_mootools'] = '<script type="text/javascript" src="'. t3lib_extMgm::siteRelPath('dg_headslist') .'res/mootools.js"></script>';
		}

		$GLOBALS['TSFE']->additionalHeaderData['tx_dgheadslist_ToolTip_conf'] = '<script type="text/javascript">window.addEvent("domready", function() {var myTips = new Tips($$(".'. $this->prefixId .'_ToolTips"));});</script>';
	} 
	
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dg_headslist/pi1/class.tx_dgheadslist_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dg_headslist/pi1/class.tx_dgheadslist_pi1.php']);
}

?>