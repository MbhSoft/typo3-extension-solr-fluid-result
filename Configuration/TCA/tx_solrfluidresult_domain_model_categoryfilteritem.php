<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

return [
    'ctrl' => [
        'title' => 'LLL:EXT:solr_fluid_result/Resources/Private/Language/locallang_db.xlf:tx_solrfluidresult_domain_model_categoryfilteritem',
        'label' => 'title',
        'hideAtCopy' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'type' => 'type',
        'typeicon_column' => 'type',
        'useColumnsForDefaultValues' => 'type',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::siteRelPath('solr_fluid_result') . 'Resources/Public/Icons/tx_solrfluidresult_domain_model_categoryfilteritem.png',
        'searchFields' => 'uid,title',
    ],
    'interface' => [
        'showRecordFieldList' => ''
    ],
    'columns' => [
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => 0
            ]
        ],
        'cruser_id' => [
            'label' => 'cruser_id',
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'pid' => [
            'label' => 'pid',
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'crdate' => [
            'label' => 'crdate',
            'config' => [
                'type' => 'passthrough',
            ]
        ],
        'tstamp' => [
            'label' => 'crdate',
            'config' => [
                'type' => 'passthrough',
            ]
        ],
        'title' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:solr_fluid_result/Resources/Private/Language/locallang_db.xlf:tx_solrfluidresult_domain_model_categoryfilteritem.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
            ]
        ],
        'type' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:solr_fluid_result/Resources/Private/Language/locallang_db.xlf:tx_solrfluidresult_domain_model_categoryfilteritem.type',
            'config' => [
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ],
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'LLL:EXT:solr_fluid_result/Resources/Private/Language/locallang_db.xlf:tx_solrfluidresult_domain_model_categoryfilteritem.type.I.0',
                        0
                    ],
                    [
                        'LLL:EXT:solr_fluid_result/Resources/Private/Language/locallang_db.xlf:tx_solrfluidresult_domain_model_categoryfilteritem.type.I.1',
                        1
                    ],
                ],
                'size' => 1,
                'maxitems' => 1,
            ]
        ],
        'operator' => [
            'exclude' => true,
            'label' => 'LLL:EXT:solr_fluid_result/Resources/Private/Language/locallang_db.xlf:tx_solrfluidresult_domain_model_categoryfilteritem.operator',
            'config' => [
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ],
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'LLL:EXT:solr_fluid_result/Resources/Private/Language/locallang_db.xlf:tx_solrfluidresult_domain_model_categoryfilteritem.operator.I.0',
                        0
                    ],
                    [
                        'LLL:EXT:solr_fluid_result/Resources/Private/Language/locallang_db.xlf:tx_solrfluidresult_domain_model_categoryfilteritem.operator.I.1',
                        1
                    ],
                ],
                'size' => 1,
                'maxitems' => 1,
            ]
        ],
        'categories' => [
            'exclude' => true,
            'label' => 'LLL:EXT:solr_fluid_result/Resources/Private/Language/locallang_db.xlf:tx_solrfluidresult_domain_model_categoryfilteritem.categories',
            'l10n_mode' => 'mergeIfNotBlank',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectTree',
                'treeConfig' => [
                    'parentField' => 'parent',
                    'appearance' => [
                        'showHeader' => true,
                        'expandAll' => true,
                        'maxLevels' => 99,
                    ],
                ],
                'MM' => 'sys_category_record_mm',
                'MM_match_fields' => [
                    'fieldname' => 'categories',
                    'tablenames' => 'tx_solrfluidresult_domain_model_categoryfilteritem',
                ],
                'MM_opposite_field' => 'items',
                'foreign_table' => 'sys_category',
                'foreign_table_where' => ' AND (sys_category.sys_language_uid = 0 OR sys_category.l10n_parent = 0) ORDER BY sys_category.sorting',
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 99,
            ]
        ],
        'items' => [
            'exclude' => true,
            'label' => 'LLL:EXT:solr_fluid_result/Resources/Private/Language/locallang_db.xlf:tx_solrfluidresult_domain_model_categoryfilteritem.items',
            'config' => [
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ],
                'type' => 'inline',
                'foreign_table' => 'tx_solrfluidresult_domain_model_categoryfilteritem',
                'foreign_field' => 'parent',
                'maxitems' => 100,
            ]
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => 'l10n_parent, l10n_diffsource,
                --palette--;Default;default, title, operator, items,
                --div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.extended,'
        ],
        '1' => [
            'showitem' => 'l10n_parent, l10n_diffsource,
                --palette--;Default;default, title, operator, categories,
                --div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.extended,'
        ],
    ],
    'palettes' => [
        'default' => [
            'showitem' => 'type, sys_language_uid, hidden'
        ],
    ]
];
