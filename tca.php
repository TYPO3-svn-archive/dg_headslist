<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_dgheadslist_main"] = array (
	"ctrl" => $TCA["tx_dgheadslist_main"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,pic_active,pic_inactive,name,categorys,link_id"
	),
	"feInterface" => $TCA["tx_dgheadslist_main"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (        
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_dgheadslist_main',
				'foreign_table_where' => 'AND tx_dgheadslist_main.pid=###CURRENT_PID### AND tx_dgheadslist_main.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"name" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:dg_headslist/locallang_db.xml:tx_dgheadslist_main.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"pic_active" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:dg_headslist/locallang_db.xml:tx_dgheadslist_main.pic_active",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 500,	
				"uploadfolder" => "uploads/tx_dgheadslist",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"pic_inactive" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:dg_headslist/locallang_db.xml:tx_dgheadslist_main.pic_inactive",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 500,	
				"uploadfolder" => "uploads/tx_dgheadslist",
				"show_thumbs" => 1,	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"categorys" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:dg_headslist/locallang_db.xml:tx_dgheadslist_main.categorys",		
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_dgheadslist_cat",	
				"size" => 4,	
				"minitems" => 0,
				"maxitems" => 100,
			)
		),
		"link_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:dg_headslist/locallang_db.xml:tx_dgheadslist_main.link_id",		
			"config" => Array (
				"type"     => "input",
				"size"     => "15",
				"max"      => "255",
				"checkbox" => "",
				"eval"     => "trim",
				"wizards"  => array(
					"_PADDING" => 2,
					"link"     => array(
						"type"         => "popup",
						"title"        => "Link",
						"icon"         => "link_popup.gif",
						"script"       => "browse_links.php?mode=wizard",
						"JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
					)
				)
			)
		),
		'no_tooltip' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:dg_headslist/locallang_db.xml:tx_dgheadslist_main.no_tooltip',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, name, pic_active, pic_inactive, categorys, link_id, no_tooltip")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_dgheadslist_cat"] = array (
	"ctrl" => $TCA["tx_dgheadslist_cat"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,title"
	),
	"feInterface" => $TCA["tx_dgheadslist_cat"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (        
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_dgheadslist_cat',
				'foreign_table_where' => 'AND tx_dgheadslist_cat.pid=###CURRENT_PID### AND tx_dgheadslist_cat.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"title" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:dg_headslist/locallang_db.xml:tx_dgheadslist_cat.title",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, title;;;;2-2-2")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);
?>