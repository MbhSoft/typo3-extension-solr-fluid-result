<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'SolrFluidResult',
        'Search',
        'LLL:EXT:solr_fluid_result/Resources/Private/Language/locallang_be.xlf:search_title'
    );

    $extensionName = $extensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase('solr_fluid_result');
    $pluginSignature = strtolower($extensionName) . '_search';

    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,recursive,select_key,pages';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        $pluginSignature,
        'FILE:EXT:solr_fluid_result/Configuration/FlexForms/flexform_search.xml'
    );
});
