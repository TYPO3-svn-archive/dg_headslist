<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Dennis Grote <d.grote@dd-medien.de>
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
 *   47: class tx_backendPreviewHeads
 *   55:     function main($params, &$pObj)
 *
 * TOTAL FUNCTIONS: 1
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */



/**
 * Hook to display verbose information about pi1 plugin in Web>Page module
 *
 * @author Dennis Grote <d.grote@dd-medien.de>
 * @package TYPO3
 * @subpackage tx_dgheadslist
 */
class tx_backendPreviewHeads {
	/**
	 * Returns information about this extension's pi1 plugin
	 *
	 * @param	array	$params Parameters to the hook
	 * @param	object	$pObj A reference to calling object
	 * @return	string	Information about pi1 plugin
	 */
	function main($params, &$pObj) {
		if ($params['row']['list_type'] == 'dg_headslist_pi1') {
			$data = t3lib_div::xml2array($params['row']['pi_flexform']);
			if (is_array($data) && $data['data']['sDEF']['lDEF']['what_to_display']['vDEF']) {
				$content = sprintf($GLOBALS['LANG']->sL('LLL:EXT:dg_headslist/locallang.xml:cms_layout.mode'),
				$data['data']['sDEF']['lDEF']['what_to_display']['vDEF']);
			}
			if (!$content) {
				$content = $GLOBALS['LANG']->sL('LLL:EXT:dg_headslist/locallang.xml:cms_layout.not_configured');
			}
		}
		return $content;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dg_headslist/class.backendPreview.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dg_headslist/class.backendPreview.php']);
}

?>