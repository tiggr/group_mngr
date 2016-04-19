<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=="BE")	{
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(
		"web_info",
		"tx_groupmngr_modfunc1",
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY)."modfunc1/class.tx_groupmngr_modfunc1.php",
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


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns("fe_groups",$tempColumns,1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes("fe_groups","tx_groupmngr_manager;;;;1-1-1");
?>
