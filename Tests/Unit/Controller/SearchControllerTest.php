<?php

namespace MbhSoftware\SolrFluidResult\Tests\Unit\Controller;

use MbhSoftware\SolrFluidResult\Controller\SearchController;
use MbhSoftware\SolrFluidResult\Domain\Model\CategoryFilterItem;

class SearchControllerTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    /**
     * @var SearchController
     */
    protected $searchController;

    protected function setUp()
    {
        parent::setUp();
        $this->searchController = $this->getAccessibleMock(
            SearchController::class,
            ['dummy'],
            [],
            '',
            false
        );
    }

    /**
     * @test
     * @dataProvider buildFilterStringFromFilterItemsCreatesCorrectStringDataProvider
     *
     * @param array $filterItems
     * @param string $expectedString
     */
    public function buildFilterStringFromFilterItemsCreatesCorrectString($filterItems, $expectedString)
    {
        $result = $this->searchController->_call(
            'buildFilterStringFromCategoryFilterItems',
            $filterItems,
            'categories_stringM'
        );
        $this->assertSame($expectedString, $result);
    }

    /**
     * @test
     */
    public function buildFilterStringFromFilterItemsCreatesCorrectStringDataProvider()
    {
        return [
            'one' => [
                'filterItems' => [
                    'type' => CategoryFilterItem::TYPE_OPERATOR,
                    'operator' => CategoryFilterItem::OPERATOR_AND,
                    'items' => [
                        0 => [
                            'type' => CategoryFilterItem::TYPE_CATEGORY,
                            'operator' => CategoryFilterItem::OPERATOR_AND,
                            'categories' => [
                                0 => 'Webinar'
                            ]
                        ]
                    ]
                ],
                'expectedString' => 'categories_stringM:Webinar'
            ],
            'two' => [
                'filterItems' => [
                    'type' => CategoryFilterItem::TYPE_OPERATOR,
                    'operator' => CategoryFilterItem::OPERATOR_AND,
                    'items' => [
                        0 => [
                            'type' => CategoryFilterItem::TYPE_CATEGORY,
                            'operator' => CategoryFilterItem::OPERATOR_AND,
                            'categories' => [
                                0 => 'Webinar'
                            ]
                        ],
                        1 => [
                            'type' => CategoryFilterItem::TYPE_CATEGORY,
                            'operator' => CategoryFilterItem::OPERATOR_AND,
                            'categories' => [
                                0 => 'Foo Bar'
                            ]
                        ],
                    ]
                ],
                'expectedString' => '(categories_stringM:Webinar AND categories_stringM:Foo\ Bar)'
            ],
            'three' => [
                'filterItems' => [
                    'type' => CategoryFilterItem::TYPE_OPERATOR,
                    'operator' => CategoryFilterItem::OPERATOR_OR,
                    'items' => [
                        0 => [
                            'type' => CategoryFilterItem::TYPE_OPERATOR,
                            'operator' => CategoryFilterItem::OPERATOR_AND,
                            'items' => [
                                0 => [
                                    'type' => CategoryFilterItem::TYPE_CATEGORY,
                                    'operator' => CategoryFilterItem::OPERATOR_OR,
                                    'categories' => [
                                        0 => 'Webinar',
                                        1 => 'Training'
                                    ]
                                ]
                            ],
                        ],
                        1 => [
                            'type' => CategoryFilterItem::TYPE_CATEGORY,
                            'operator' => CategoryFilterItem::OPERATOR_AND,
                            'categories' => [
                                0 => 'Foo Bar',
                                1 => 'Foo Bier'
                            ]
                        ],
                    ]
                ],
                'expectedString' => '((categories_stringM:Webinar OR categories_stringM:Training) OR (categories_stringM:Foo\ Bar AND categories_stringM:Foo\ Bier))'
            ],
            'four' => [
                'filterItems' => [
                    'type' => CategoryFilterItem::TYPE_QUERY,
                    'query' => '554_intS:574',
                ],
                'expectedString' => '554_intS:574'
            ],
            'five' => [
                'filterItems' => [
                    'type' => CategoryFilterItem::TYPE_OPERATOR,
                    'operator' => CategoryFilterItem::OPERATOR_AND,
                    'items' => [
                        0 => [
                            'type' => CategoryFilterItem::TYPE_QUERY,
                            'query' => '554_intS:574',
                        ],
                        1 => [
                            'type' => CategoryFilterItem::TYPE_CATEGORY,
                            'operator' => CategoryFilterItem::OPERATOR_OR,
                            'categories' => [
                                0 => 'Webinar',
                                1 => 'Training'
                            ]
                        ]
                    ]
                ],
                'expectedString' => '(554_intS:574 AND (categories_stringM:Webinar OR categories_stringM:Training))'
            ],
        ];
    }

    /**
     * @test
     * @dataProvider buildFilterStringFromFilterItemsWithoutCategoryFilterFieldNameCreatesCorrectStringDataProvider
     *
     * @param array $filterItems
     * @param string $expectedString
     */
    public function buildFilterStringFromFilterItemsWithoutCategoryFilterFieldNameCreatesCorrectString($filterItems, $expectedString)
    {
        $result = $this->searchController->_call(
            'buildFilterStringFromCategoryFilterItems',
            $filterItems,
            ''
        );
        $this->assertSame($expectedString, $result);
    }

    /**
     * @test
     */
    public function buildFilterStringFromFilterItemsWithoutCategoryFilterFieldNameCreatesCorrectStringDataProvider()
    {
        return [
            'one' => [
                'filterItems' => [
                    'type' => CategoryFilterItem::TYPE_OPERATOR,
                    'operator' => CategoryFilterItem::OPERATOR_AND,
                    'items' => [
                        0 => [
                            'type' => CategoryFilterItem::TYPE_CATEGORY,
                            'operator' => CategoryFilterItem::OPERATOR_AND,
                            'categories' => [
                                0 => 'Webinar'
                            ]
                        ]
                    ]
                ],
                'expectedString' => ''
            ],
            'two' => [
                'filterItems' => [
                    'type' => CategoryFilterItem::TYPE_OPERATOR,
                    'operator' => CategoryFilterItem::OPERATOR_AND,
                    'items' => [
                        0 => [
                            'type' => CategoryFilterItem::TYPE_CATEGORY,
                            'operator' => CategoryFilterItem::OPERATOR_AND,
                            'categories' => [
                                0 => 'Webinar'
                            ]
                        ],
                        1 => [
                            'type' => CategoryFilterItem::TYPE_CATEGORY,
                            'operator' => CategoryFilterItem::OPERATOR_AND,
                            'categories' => [
                                0 => 'Foo Bar'
                            ]
                        ],
                    ]
                ],
                'expectedString' => '( AND )'
            ],
            'three' => [
                'filterItems' => [
                    'type' => CategoryFilterItem::TYPE_QUERY,
                    'query' => '554_intS:574',
                ],
                'expectedString' => '554_intS:574'
            ],
            'five' => [
                'filterItems' => [
                    'type' => CategoryFilterItem::TYPE_OPERATOR,
                    'operator' => CategoryFilterItem::OPERATOR_AND,
                    'items' => [
                        0 => [
                            'type' => CategoryFilterItem::TYPE_QUERY,
                            'query' => '554_intS:574',
                        ],
                        1 => [
                            'type' => CategoryFilterItem::TYPE_CATEGORY,
                            'operator' => CategoryFilterItem::OPERATOR_AND,
                            'categories' => [
                                0 => 'Foo Bar',
                                1 => 'Foo Bier'
                            ]
                        ]
                    ]
                ],
                'expectedString' => '(554_intS:574 AND )'
            ],
        ];
    }
}
