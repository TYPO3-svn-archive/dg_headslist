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
		$this->pi_USER_INT_obj=0;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
	
		// Variablen setzen
		$content="";
		$marker = array();
		$extUploadFolder = "uploads/tx_dgheadslist/";
		
		// flexform laden
		$this->pi_initPIflexForm();
		$piFlexForm = $this->cObj->data['pi_flexform'];
		foreach($piFlexForm['data'] as $sheet => $data) {
		    foreach($data as $lang => $value) {
				foreach($value as $key => $val) {
				    $this->conf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
				}
			}
		}
		
		// css einbinden
		if ($this->conf["main_css"]) {
			$css = '<link rel="stylesheet" href="'.$this->conf["main_css"].'" type="text/css" media="screen" />';
		} else {
			$css = '<link rel="stylesheet" href="'.t3lib_extMgm::siteRelPath('dg_headslist').'res/tx_dgheadslist.css" type="text/css" media="screen" />';
		}

		$GLOBALS['TSFE']->additionalHeaderData['tx_dgheadslist_css'] = $css;
		
		
		// Die Designvorlage laden
		//$tmpl = $this->cObj->fileResource($conf["templateFile"]);
		if ($this->conf["template_file"]) {
			$tmpl = $this->cObj->fileResource($this->conf["template_file"]);
		} else {
			$tmpl = $this->cObj->fileResource("EXT:dg_headslist/res/tmpl/headlist.tmpl");
		}
				
		// Teilbereiche der Designvorlage auslesen fŸr den Wrap
		$tmpl_main = $this->cObj->getSubpart($tmpl, "###HEADSLISTWRAP###");	
		$tmpl_maindiv = $this->cObj->getSubpart($tmpl_main, "###MAIN###");
		
		// Teilbereiche der Designvorlage auslesen fŸr die Headlist ansich
		$tmpl_rec = $this->cObj->getSubpart($tmpl, "###HEADSLIST###");
		$tmpl_record = $this->cObj->getSubpart($tmpl_rec, "###RECORD###");
		
		// Teilbereiche der Designvorlage auslesen fŸr die Kategorien
		$tmpl_cat = $this->cObj->getSubpart($tmpl, "###HEADSLISTCAT###");
		$tmpl_category = $this->cObj->getSubpart($tmpl_cat, "###CATEGORY###");

		
		// von welcher ID kommen die EintrŠge
		$headslistPageId = $this->conf["pages"];
		if ($headslistPageId == "") $headslistPageId=$GLOBALS["TSFE"]->id;
		
		
		// Get Parameter auslesen
		$link_vars = t3lib_div::GPvar($this->prefixId);
		$group = $link_vars['group'];
		
		// default Kategory angeben
		if ($group == "" && $this->conf["defaultCategory"] ) {
			$group = $this->conf["defaultCategory"];
		}
		
		
		// Die Datenbankabfrage inkl. UnterstŸtzung von Datenbankabstraktion
		// Abfrage fŸr die Bilder
		if ($this->conf["groupMember"]) {
			$res = $GLOBALS["TYPO3_DB"]->exec_SELECTquery("name, pic_active, pic_inactive, link_id, categorys, no_tooltip", "tx_dgheadslist_main", "deleted = 0 AND hidden = 0 AND pid = '".$headslistPageId."'", "sorting");
		} else {
			$res = $GLOBALS["TYPO3_DB"]->exec_SELECTquery("name, pic_active, pic_inactive, link_id", "tx_dgheadslist_main", "deleted = 0 AND hidden = 0 AND (categorys = '".$group."' OR categorys like ('".$group.",%') OR categorys like ('%,".$group."') OR categorys like ('%,".$group.",%')) AND pid = '".$headslistPageId."'", "sorting");	
		}
		
			while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
				
			// Das Bild auslesen und verarbeiten			
				if (in_array($group,split(",",$row["categorys"]))) {   // Welches Bild soll verwendet werden
					$picType = "pic_active";
				} elseif ($group == "") {
					$picType = "pic_active";
				} else {
					$picType = "pic_inactive";
				}
			
				$conf["picture."]["file"] = $extUploadFolder . $row[$picType];
				$conf["picture."]["file."]["maxW"] = ($this->conf["imageMaxWidth"]);
				$conf["picture."]["file."]["maxH"] = ($this->conf["imageMaxHeight"]);
   				$conf["picture."]["params"] = 'class="tx_dgheadslist_ToolTips"';
      			if ($row["no_tooltip"] == "0") {
	      			if ($this->conf["activeToolTips"] && $picType == "pic_active") {
    	  				$conf["picture."]["altText"] = $row["name"];
	      			} elseif ($this->conf["activeToolTips"] && $picType == "pic_inactive") {
    	  				$conf["picture."]["altText"] = "";
	      			} elseif ($this->conf["useToolTips"]) {
						$conf["picture."]["altText"] = $row["name"];
	      			} else {
      					$conf["picture."]["altText"] = "";
      				}
      			} else {
      				$conf["picture."]["altText"] = "";
      			}
      			
      			      			
      			
      			
      			
       			$picture = $this->cObj->IMAGE($conf["picture."]);
       			
       			// Marker belegen
       			if ($row["link_id"]) {
       				if ($this->conf["activeLink"] && $picType == "pic_inactive") {
    	  				$marker["###BILD###"] = $picture;
	      			} else {
      					$marker["###BILD###"] = $this->cObj->getTypoLink($picture,$row["link_id"]);
      				}
       			} else {
       				$marker["###BILD###"] = $picture;
       			}
				// Den Teilbereich ###RECORD### und das Array miteinander "vereinen"
				$record .= $this->cObj->substituteMarkerArrayCached($tmpl_record, $marker);
			}
			
      		// ToolTips einstellungen
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
					$GLOBALS['TSFE']->additionalHeaderData['tx_dgheadslist_ToolTip_js_mootools'] = '<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath('dg_headslist').'res/mootools.js"></script>';
				}
										
				if ($this->conf["ToolTip_css"]) {
					$GLOBALS['TSFE']->additionalHeaderData['tx_dgheadslist_ToolTip_css'] = '<link rel="stylesheet" href="'.$this->conf["ToolTip_css"].'" type="text/css" media="screen" />';
				} 

				$GLOBALS["TSFE"]->additionalHeaderData["tx_dgheadslist_ToolTip_conf"] = "
	<script type=\"text/javascript\">
		window.addEvent('domready', function() {
			var myTips = new Tips($$('.tx_dgheadslist_ToolTips'));
		});
	</script>
					";
      			} 
      			// ende ToolTips
		
		
		// Kategorie Abfrage
		if ($this->conf["categoryList"]) {
		$cat = $GLOBALS["TYPO3_DB"]->exec_SELECTquery("title, uid, sys_language_uid, l18n_parent", "tx_dgheadslist_cat", "deleted = 0 AND hidden = 0 AND pid = '".$headslistPageId."' AND sys_language_uid = '".$GLOBALS["TSFE"]->sys_language_uid."'", "sorting");
		
			while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($cat)) {
				// Gets the translated record if the content language is not the default language
				/*if ($GLOBALS["TSFE"]->sys_language_content) {
					$OLmode = ($this->sys_language_mode == "strict"?"hideNonTranslated":"");
					$row = $GLOBALS["TSFE"]->sys_page->getRecordOverlay("tx_dgheadslist_cat", $row, $GLOBALS["TSFE"]->sys_language_content, $OLmode);
				}*/
				
				if ($row["uid"] == $group || $row["l18n_parent"] == $group) {
					$marker["###CATEGORYS###"] = '<li id="tx_dgheadslist_actLink">'.$row["title"].'</li>';
				} else {
					if ($row["sys_language_uid"] == "0") {
						$marker["###CATEGORYS###"] = '<li>'.$this->pi_linkTP($row["title"],array($this->prefixId."[group]" => $row["uid"])).'</li>';
					} else {
						$marker["###CATEGORYS###"] = '<li>'.$this->pi_linkTP($row["title"],array($this->prefixId."[group]" => $row["l18n_parent"])).'</li>';
					}
				}
				
				// Den Teilbereich ###CATEGORYS### und das Array miteinander "vereinen"
				$categorys .= $this->cObj->substituteMarkerArrayCached($tmpl_category, $marker);
			}
		}
			
		// Letztmalig den umhŸllenden Teilberich ersetzen und das Ergebnis ausgeben
		//$record = $this->cObj->substituteSubpart($tmpl, "###RECORD###", $record);
		//$categorys = $this->cObj->substituteSubpart($tmpl, "###CATEGORY###", $categorys);
			
			
		// Kategorien nur zeigen wenn Kategorien vorhanden sind
		if (isset($categorys) && !empty($categorys)) {
			$content = $this->cObj->substituteSubpart($tmpl_rec, "###RECORD###", $record).$this->cObj->substituteSubpart($tmpl_cat, "###CATEGORY###", $categorys);
		}
		else {
			$content = $this->cObj->substituteSubpart($tmpl_rec, "###RECORD###", $record);
		}
		
		$content = $this->cObj->substituteSubpart($tmpl_main, "###MAIN###", $content);
		return $content;
		//return $group;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dg_headslist/pi1/class.tx_dgheadslist_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dg_headslist/pi1/class.tx_dgheadslist_pi1.php']);
}

?>