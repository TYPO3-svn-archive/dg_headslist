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
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
	
		// set Variable
		$content="";
		$marker = array();
		$extUploadFolder = "uploads/tx_dgheadslist/";
		
		// load flexform
		$this->pi_initPIflexForm();
		$piFlexForm = $this->cObj->data['pi_flexform'];
		foreach($piFlexForm['data'] as $sheet => $data) {
		    foreach($data as $lang => $value) {
				foreach($value as $key => $val) {
				    $this->conf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
				}
			}
		}
		
		
		// load template
		if ($this->conf["template_file"]) {
			$tmpl = $this->cObj->fileResource($this->conf["template_file"]);
		} else {
			$tmpl = $this->cObj->fileResource($conf["templateFile"]);
		}
		
		// Sections of the templates out for the headslist
		$tmpl_rec = $this->cObj->getSubpart($tmpl, "###HEADSLIST###");
		$tmpl_record = $this->cObj->getSubpart($tmpl_rec, "###RECORD###");
		
		// Sections of the templates out for the categories
		$tmpl_cat = $this->cObj->getSubpart($tmpl, "###HEADSLISTCAT###");
		$tmpl_category = $this->cObj->getSubpart($tmpl_cat, "###CATEGORY###");

		
		// from which ID are the entries
		$headslistPageId = $this->conf["pages"];
		if ($headslistPageId == "") $headslistPageId = $GLOBALS["TSFE"]->id;
		
		
		// Extract Get parameters
		$link_vars = t3lib_div::GPvar($this->prefixId);
		if (is_numeric($link_vars['group']) || $link_vars['group'] == "") {
			settype($link_vars['group'], "int");
			$group = $link_vars['group'];
		} else {
			return "ERROR: <b>" .$link_vars['group']. "</b> is not a valid group ID";
		}
		
		
		// default category
		if ($group == "" && $this->conf["defaultCategory"] ) {
			$group = $this->conf["defaultCategory"];
		} elseif ($group == "0") {
			$group = "";
		}
		
		//language overlay also for data records
		if ($this->conf["langOverlay"]) {
			$lang_overlay = 'AND sys_language_uid = '.$GLOBALS['TSFE']->config['config']['sys_language_uid'].'';
		}
		
		// The database query including support for database abstraction
		// Query for the pictures
		if ($this->conf["inactiveImages"]) {
			$res = $GLOBALS["TYPO3_DB"]->exec_SELECTquery('name, pic_active, pic_inactive, sys_language_uid, link_id, categorys, no_tooltip', 'tx_dgheadslist_main', 'deleted = 0 AND hidden = 0 AND pid = '.$headslistPageId.' '.$lang_overlay.'', 'sorting');
		} else {
			$res = $GLOBALS["TYPO3_DB"]->exec_SELECTquery("name, pic_active, pic_inactive, sys_language_uid, link_id", "tx_dgheadslist_main", "deleted = 0 AND hidden = 0 AND (categorys = '".$group."' OR categorys like ('".$group.",%') OR categorys like ('%,".$group."') OR categorys like ('%,".$group.",%')) AND pid = '".$headslistPageId."' '".$lang_overlay."'", "sorting");	
		}
		
		// sys_language_mode defines what to do if the requested translation is not found
		$this->sys_language_mode = $this->conf['sys_language_mode']?$this->conf['sys_language_mode'] : $GLOBALS['TSFE']->sys_language_mode;
		
			while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
			// Gets the translated record if the content language is not the default language
				if ($GLOBALS["TSFE"]->sys_language_content == $row["sys_language_uid"]) {
					$OLmode = ($this->sys_language_mode == "strict"?"hideNonTranslated":"");
					$row = $GLOBALS["TSFE"]->sys_page->getRecordOverlay("tx_dgheadslist_main", $row, $GLOBALS["TSFE"]->sys_language_content, $OLmode);
				}
				
			// get the picture			
				if (in_array($group,split(",",$row["categorys"]))) {   // Which picture is to be used
					$picType = "pic_active";
				} elseif ($group == "") {
					$picType = "pic_active";
				} else {
					$picType = "pic_inactive";
				}
			
				$conf["image."]["file."]["10."]["file"] = $extUploadFolder . $row[$picType];
				
				if ($this->conf["imageMaxWidth"]) {
					$conf["image."]["file."]["10."]["file."]["width"] = ($this->conf["imageMaxWidth"]);
				}
				
				if ($this->conf["imageMaxHeight"]) {
					$conf["image."]["file."]["10."]["file."]["height"] = ($this->conf["imageMaxHeight"]);
				}

				$conf["image."]["altText"] = $row["name"];
				
      			if ($row["no_tooltip"] == "0") {
	      			if ($this->conf["activeToolTips"] && $picType == "pic_active") {  				
    	  				$conf["image."]["params"] = 'class="'.$this->prefixId.'_ToolTips"';
	      			} elseif ($this->conf["activeToolTips"] && $picType == "pic_inactive") {
    	  				$conf["image."]["params"] = "";
	      			} elseif ($this->conf["useToolTips"]) {
						$conf["image."]["params"] = 'class="'.$this->prefixId.'_ToolTips"';
	      			} else {
      					$conf["image."]["params"] = "";
      				}
      			} else {
      				$conf["image."]["params"] = "";
      			}
      			
       			$picture = $this->cObj->cObjGetSingle($conf["image"], $conf["image."]);
       			
       			// set markers
       			if ($row["link_id"]) {
       				if ($this->conf["activeLink"] && $picType == "pic_inactive") {
    	  				$images = $picture;
	      			} else {
      					$images = $this->cObj->getTypoLink($picture,$row["link_id"]);
      				}
       			} else {
       				$images = $picture;
       			}
       			$images = $this->cObj->wrap($images, $conf['imageWrap']);
       			$marker["###IMAGES###"] = $images;
				// Den Teilbereich ###RECORD### und das Array miteinander "vereinen"
				$record .= $this->cObj->substituteMarkerArrayCached($tmpl_record, $marker);
			}
			
      		// ToolTips setup
      		if ($this->conf["useToolTips"]) {   				
      			// checks if t3mootools is loaded
				if (t3lib_extMgm::isLoaded("t3mootools"))    {
   					require_once(t3lib_extMgm::extPath("t3mootools")."class.tx_t3mootools.php");
				}
				 	 
				// if t3mootools is loaded and the custom Library had been created
				if (defined("T3MOOTOOLS")) {
 					tx_t3mootools::addMooJS();
				// if none of the previous is true, you need to include your own library
				// just as an example in this way
				} else {
					$GLOBALS['TSFE']->additionalHeaderData['tx_dgheadslist_ToolTip_js_mootools'] = '<script type="text/javascript" src="'. t3lib_extMgm::siteRelPath('dg_headslist') .'res/mootools.js"></script>';
				}

				$GLOBALS["TSFE"]->additionalHeaderData["tx_dgheadslist_ToolTip_conf"] = '<script type="text/javascript">window.addEvent("domready", function() {var myTips = new Tips($$(".'. $this->prefixId .'_ToolTips"));});</script>';
      			} 
      			// ende ToolTips
		
		
		// Category query
		if ($this->conf["categoryList"]) {
		$cat = $GLOBALS["TYPO3_DB"]->exec_SELECTquery("title, uid, sys_language_uid, l18n_parent", "tx_dgheadslist_cat", "deleted = 0 AND hidden = 0 AND pid = '".$headslistPageId."' AND sys_language_uid = '".$GLOBALS["TSFE"]->config["config"]["sys_language_uid"]."'", "sorting");
		
		// sys_language_mode defines what to do if the requested translation is not found
		$this->sys_language_mode = $this->conf['sys_language_mode']?$this->conf['sys_language_mode'] : $GLOBALS['TSFE']->sys_language_mode;
		
			while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($cat)) {
				// Gets the translated record if the content language is not the default language
				if ($GLOBALS["TSFE"]->sys_language_content == $row["sys_language_uid"]) {
					$OLmode = ($this->sys_language_mode == "strict"?"hideNonTranslated":"");
					$row = $GLOBALS["TSFE"]->sys_page->getRecordOverlay("tx_dgheadslist_cat", $row, $GLOBALS["TSFE"]->sys_language_content, $OLmode);
				}
				
				if ($row["uid"] == $group || $row["l18n_parent"] == $group) {
					$listItem = $this->cObj->wrap($row["title"], $conf["currentWrap"]);
				} else {
					if ($row["sys_language_uid"] == "0") {
						$listItem = $this->cObj->wrap($this->pi_linkTP($row["title"],array($this->prefixId."[group]" => $row["uid"])), $conf["linkWrap"]);
					} else {
						$listItem = $this->cObj->wrap($this->pi_linkTP($row["title"],array($this->prefixId."[group]" => $row["l18n_parent"])), $conf["linkWrap"]);
					}
				}
				$listItem = $this->cObj->wrap($listItem, $conf['categoryListItemWrap']);
				$marker["###CATEGORYS###"] = $listItem;

				// Den Teilbereich ###CATEGORYS### und das Array miteinander "vereinen"
				$categorys .= $this->cObj->substituteMarkerArrayCached($tmpl_category, $marker);
			}
		}
			
		// Letztmalig den umhŸllenden Teilberich ersetzen und das Ergebnis ausgeben
		//$record = $this->cObj->substituteSubpart($tmpl, "###RECORD###", $record);
		//$categorys = $this->cObj->substituteSubpart($tmpl, "###CATEGORY###", $categorys);
		
		// show categories only if categories are available
		if (isset($categorys) && !empty($categorys)) {	
			$categoryList = $this->cObj->wrap($this->cObj->substituteSubpart($tmpl_cat, "###CATEGORY###", $categorys),$conf['categoryListWrap']);
			$categoryList = $this->cObj->wrap($categoryList, $conf['categoryWrap']);
		}
		else {
			$categoryList = "";
		}
		
		$records = $this->cObj->substituteSubpart($tmpl_rec, "###RECORD###", $record);
		$records = $this->cObj->wrap($records, $conf['recordsWrap']);
		
		$content = $records.$categoryList;
		
		return $this->pi_wrapInBaseClass($content);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dg_headslist/pi1/class.tx_dgheadslist_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dg_headslist/pi1/class.tx_dgheadslist_pi1.php']);
}

?>