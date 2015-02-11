<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=="BE")	{
	t3lib_extMgm::insertModuleFunction(
		"web_info",
		"tx_groupmngr_modfunc1",
		t3lib_extMgm::extPath($_EXTKEY)."modfunc1/class.tx_groupmngr_modfunc1.php",
		"LLL:EXT:group_mngr/locallang_db.xml:moduleFunction.tx_groupmngr_modfunc1"
	);
}

$tempColumns = Array (
	"tx_groupmngr_manager" => Array (
		"exclude" => 1,
		"label" => "LLL:EXT:group_mngr/locallang_db.xml:fe_groups.tx_groupmngr_manager",
		"config" => Array (
			"type" => "group",
			"internal_type" => "db",
			"allowed" => "be_users",
			"size" => 3,
			"minitems" => 0,
			"maxitems" => 5,
		)
	),
);


t3lib_extMgm::addTCAcolumns("fe_groups",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_groups","tx_groupmngr_manager;;;;1-1-1");
?>
