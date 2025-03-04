<?php
defined('TYPO3') or die();

return [
    'ctrl' => [
        'title' => 'LLL:EXT:solr_fluid_result/Resources/Private/Language/locallang_db.xlf:tx_solrfluidresult_domain_model_categoryfilteritem',
        'label' => 'title',
        'hideAtCopy' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'type' => 'type',
        'typeicon_column' => 'type',
        'useColumnsForDefaultValues' => 'type',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'iconfile' => \TYPO3\CMS\Core\Utility\PathUtility::stripPathSitePrefix(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('solr_fluid_result')) . 'Resources/Public/Icons/tx_solrfluidresult_domain_model_categoryfilteritem.png',
        'searchFields' => 'uid,title',
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple',
                    ],
                ],
                'default' => 0,
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        '',
                        0,
                    ],
                ],
                'default' => 0,
                'foreign_table' => 'tx_masterflexpim_domain_model_product',
                'foreign_table_where' => 'AND tx_masterflexpim_domain_model_product.pid=###CURRENT_PID###'
                    . ' AND tx_masterflexpim_domain_model_product.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_source' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
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
                    [
                        'LLL:EXT:solr_fluid_result/Resources/Private/Language/locallang_db.xlf:tx_solrfluidresult_domain_model_categoryfilteritem.type.I.2',
                        2
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
        'query' => [
            'exclude' => true,
            'label' => 'LLL:EXT:solr_fluid_result/Resources/Private/Language/locallang_db.xlf:tx_solrfluidresult_domain_model_categoryfilteritem.query',
            'config' => [
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ],
                'type' => 'input',
            ]
        ],
        'categories' => [
            'exclude' => true,
            'label' => 'LLL:EXT:solr_fluid_result/Resources/Private/Language/locallang_db.xlf:tx_solrfluidresult_domain_model_categoryfilteritem.categories',
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
                ['behaviour' => ['allowLanguageSynchronization' => true]],
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
            'showitem' => '--palette--;Default;default,title,operator,items,--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.extended'
        ],
        '1' => [
            'showitem' => '--palette--;Default;default,title,operator,categories,--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.extended'
        ],
        '2' => [
            'showitem' => '--palette--;Default;default,title,query,--div--;LLL:EXT:cms/locallang_tca.xml:pages.tabs.extended'
        ],
    ],
    'palettes' => [
        'default' => [
            'showitem' => 'type, hidden, --linebreak--, sys_language_uid, l10n_parent, l10n_diffsource'
        ],
    ]
];
