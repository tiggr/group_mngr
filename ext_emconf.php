<?php

########################################################################
# Extension Manager/Repository config file for ext: "group_mngr"
#
# Auto generated 22-08-2007 18:49
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Group Manager',
	'description' => 'An extension of the info module, viewing users by group in TYPO3 is confusing, listing is obscured by users belonging to multiple groups, this extension allows admins to select a specific group and view the users who have been assigned it, works for BE & FE.<br /><br />It is also possible to allow backend users to add/remove users to a fe_group by making them a \'Group Manager\'(see the extra field on the fe_group record).',
	'category' => 'module',
	'shy' => 1,
	'version' => '0.1.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Graham Solomon',
	'author_email' => 'graham.solomon@powys.gov.uk',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:10:{s:9:"ChangeLog";s:4:"56ed";s:10:"README.txt";s:4:"9fa9";s:12:"ext_icon.gif";s:4:"4c2b";s:14:"ext_tables.php";s:4:"9016";s:14:"ext_tables.sql";s:4:"8135";s:16:"locallang_db.xml";s:4:"436d";s:19:"doc/wizard_form.dat";s:4:"a06c";s:20:"doc/wizard_form.html";s:4:"0593";s:40:"modfunc1/class.tx_groupmngr_modfunc1.php";s:4:"0b24";s:22:"modfunc1/locallang.xml";s:4:"7150";}',
);

?>