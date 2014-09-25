<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'MbhSoftware.SolrFluidResult',
	'Search',
	array(
		'Search' => 'index',
	)
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:solr_fluid_result/Configuration/TsConfig/page.ts">');



?>