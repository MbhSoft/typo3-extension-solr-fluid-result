<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'SolrFluidResult',
    'Search',
    [
        \MbhSoftware\SolrFluidResult\Controller\SearchController::class => 'index',
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:solr_fluid_result/Configuration/TsConfig/page.ts">');
