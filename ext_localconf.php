<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
##t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','tt_content.CSS_editor.ch.tx_dgheadslist_pi1 = < plugin.tx_dgheadslist_pi1.CSS_editor',43);


t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_dgheadslist_pi1.php','_pi1','list_type',1);

t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_dgheadslist_main=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_dgheadslist_cat=1
');

// Page module hook
$TYPO3_CONF_VARS['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['dg_headslist_pi1'][] = 'EXT:dg_headslist/class.backendPreview.php:&tx_backendPreviewHeads->main';
?>