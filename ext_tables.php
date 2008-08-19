<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform'; // for flexform


t3lib_extMgm::addPlugin(array('LLL:EXT:dg_headslist/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","heads list");

// NOTE: Be sure to change sampleflex to the correct directory name of your extension!                   // for flexform
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:dg_headslist/flexform_pi1.xml');             // for flexform

t3lib_extMgm::allowTableOnStandardPages('tx_dgheadslist_main');

$TCA["tx_dgheadslist_main"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:dg_headslist/locallang_db.xml:tx_dgheadslist_main',		
		'label'     => 'name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_dgheadslist_main.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, pic_active, pic_inactive, name, categorys, link_id",
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_dgheadslist_cat');

$TCA["tx_dgheadslist_cat"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:dg_headslist/locallang_db.xml:tx_dgheadslist_cat',		
		'label'     => 'title',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'sortby' => 'sorting',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_dgheadslist_cat.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, title",
	)
);

if (TYPO3_MODE=="BE")	$TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_dgheadslist_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_dgheadslist_pi1_wizicon.php';
?>