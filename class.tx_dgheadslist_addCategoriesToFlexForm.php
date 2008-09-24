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
class tx_dgheadslist_addCategoriesToFlexForm {
	
	function addCategories ($config) {
		$optionList = array();

		$res = mysql_query("SELECT uid,title FROM tx_dgheadslist_cat WHERE hidden=0 AND deleted=0 ORDER BY sorting");	
		$optionList[0] = array(0 => "", 1 => "");
		$i = 1;
 		while($row = mysql_fetch_object($res)) {
			$optionList[$i] = array(0 => $row->title, 1 => $row->uid);
			$i++;
		}
		
		$config['items'] = array_merge($config['items'],$optionList);
		
		return $config;
	}
}
?>