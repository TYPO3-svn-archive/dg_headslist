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
		$img_pfad = "uploads/tx_dgheadslist/";
		
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
		
		
		// Die Designvorlage laden
		$tmpl = $this->cObj->fileResource($conf["templateFile"]);
		if ($tmpl == "") $tmpl=$this->cObj->fileResource($this->conf["template_file"]);
		if ($tmpl == "") $tmpl=$this->cObj->fileResource("EXT:dg_headslist/res/tmpl/headlist.tmpl");
		
		// Teilbereiche der Designvorlage auslesen f�r den Wrap
		$tmpl_main = $this->cObj->getSubpart($tmpl, "###HEADLISTWRAP###");	
		$tmpl_maindiv = $this->cObj->getSubpart($tmpl_main, "###MAIN###");
		
		// Teilbereiche der Designvorlage auslesen f�r die Headlist ansich
		$tmpl_rec = $this->cObj->getSubpart($tmpl, "###HEADLIST###");
		$tmpl_record = $this->cObj->getSubpart($tmpl_rec, "###RECORD###");
		
		// Teilbereiche der Designvorlage auslesen f�r die Kategorien
		$tmpl_cat = $this->cObj->getSubpart($tmpl, "###HEADLISTCAT###");
		$tmpl_category = $this->cObj->getSubpart($tmpl_cat, "###CATEGORY###");

		
		// von welcher ID kommen die Eintr�ge
		$headslistPageId = $conf["headslistPageId"];
		if ($headslistPageId == "") $headslistPageId=$GLOBALS["TSFE"]->id;
		
		
		// Get Parameter auslesen
		$link_vars = t3lib_div::GPvar($this->prefixId);
		$group = $link_vars['group'];
		
		
		// Die Datenbankabfrage inkl. Unterst�tzung von Datenbankabstraktion
		// Abfrage f�r die Bilder
		if ($group == "") {
			$res = $GLOBALS["TYPO3_DB"]->exec_SELECTquery("name, pic_active, pic_inactive, link_id, categorys", "tx_dgheadslist_main", "deleted = 0 AND hidden = 0 AND pid = '".$headslistPageId."'", "sorting");
		} else {
			$res = $GLOBALS["TYPO3_DB"]->exec_SELECTquery("name, pic_active, pic_inactive, link_id", "tx_dgheadslist_main", "deleted = 0 AND hidden = 0 AND (categorys = '".$group."' OR categorys like ('".$group.",%') OR categorys like ('%,".$group."') OR categorys like ('%,".$group.",%')) AND pid = '".$headslistPageId."'", "sorting");	
		}
		
			while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($res)) {
				
				// Das Bild auslesen und verarbeiten
				$conf["picture."]["file"] = $img_pfad . $row["pic_active"];
				$conf["picture."]["file."]["maxW"] = ($this->conf["imageMaxWidth"]);
				$conf["picture."]["file."]["maxH"] = ($this->conf["imageMaxHeight"]);
      			$conf["picture."]["altText"] = $row["name"];
      			
      			// ToolTips einstellungen
      			if ($this->conf["useToolTips"]) {
      				$tipsclass = "tx_dgheadslist_ToolTips";
      				$conf["picture."]["params"] = 'class="' . $tipsclass . '"';
      				
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
					
      				$GLOBALS["TSFE"]->additionalHeaderData["tx_dgheadslist_ToolTip_conf"] = "
						<script type=\"text/javascript\">
							window.addEvent('domready', function() {
								var myTips = new Tips($$('.$tipsclass'));
							});
						</script>
					";
					$GLOBALS['TSFE']->additionalHeaderData['tx_dgheadslist_ToolTip_css'] = '<link rel="stylesheet" href="'.t3lib_extMgm::siteRelPath('dg_headslist').'res/tx_dgheadslist_ToolTips.css" type="text/css" media="screen" />';
      			} // ende ToolTips
      			
       			$picture = $this->cObj->IMAGE($conf["picture."]);
       			
       			// Marker belegen
       			if ($row["link_id"]) {
       				$marker["###BILD###"] = $this->cObj->getTypoLink($picture,$row["link_id"]);
       			} else {
       				$marker["###BILD###"] = $picture;
       			}
				// Den Teilbereich ###RECORD### und das Array miteinander "vereinen"
				$record .= $this->cObj->substituteMarkerArrayCached($tmpl_record, $marker);
			}
			
		
		
		// Kategorie Abfrage
		$cat = $GLOBALS["TYPO3_DB"]->exec_SELECTquery("title, uid", "tx_dgheadslist_cat", "deleted = 0 AND hidden = 0 AND pid = '".$headslistPageId."'", "sorting");
		//$cat = $GLOBALS["TYPO3_DB"]->exec_SELECTquery("title", "tx_dgheadslist_cat", "deleted = 0 AND hidden = 0 AND pid = '".$headslistPageId."' AND sys_language_uid = '".$GLOBALS["TSFE"]->config["config"]["sys_language_uid"]."'", "sorting");

			while ($row = $GLOBALS["TYPO3_DB"]->sql_fetch_assoc($cat)) {
				
				$marker["###CATEGORYS###"] = $this->pi_linkTP($row["title"],array($this->prefixId."[group]" => $row["uid"]));
				//$marker["###CATEGORYS###"] = $row["title"];
				
				// Den Teilbereich ###CATEGORYS### und das Array miteinander "vereinen"
				$categorys .= $this->cObj->substituteMarkerArrayCached($tmpl_category, $marker);
			}
			
		// Letztmalig den umh�llenden Teilberich ersetzen und das Ergebnis ausgeben
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