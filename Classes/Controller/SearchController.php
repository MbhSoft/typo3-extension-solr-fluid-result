<?php
namespace MbhSoftware\SolrFluidResult\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Marc Bastian Heinrichs <mbh@mbh-software.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use ApacheSolrForTypo3\Solr\Domain\Search\Query\ParameterBuilder\Faceting;
use ApacheSolrForTypo3\Solr\Domain\Search\Query\ParameterBuilder\Grouping;
use ApacheSolrForTypo3\Solr\Domain\Search\Query\ParameterBuilder\ReturnFields;
use MbhSoftware\SolrFluidResult\Domain\Model\CategoryFilterItem;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Class SearchController
 */
class SearchController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{


    /**
     * @var \TYPO3\CMS\Extbase\Service\TypoScriptService
     */
    protected $typoScriptService;

    /**
     * @var \MbhSoftware\SolrFluidResult\Service\SearchService
     */
    protected $searchService;

    /**
     * @var \MbhSoftware\SolrFluidResult\Domain\Repository\CategoryFilterItemRepository
     */
    protected $categoryFilterItemRepository;

    /**
     * inject the categoryFilterItemRepository
     *
     * @param \MbhSoftware\SolrFluidResult\Domain\Repository\CategoryFilterItemRepository $categoryFilterItemRepository
     * @return void
     */
    public function injectCategoryFilterItemRepository(\MbhSoftware\SolrFluidResult\Domain\Repository\CategoryFilterItemRepository $categoryFilterItemRepository)
    {
        $this->categoryFilterItemRepository = $categoryFilterItemRepository;
    }

    /**
     * @param \TYPO3\CMS\Extbase\Service\TypoScriptService $typoScriptService
     * @return void
     */
    public function injectTypoScriptService(\TYPO3\CMS\Extbase\Service\TypoScriptService $typoScriptService)
    {
        $this->typoScriptService = $typoScriptService;
    }

    /**
     * @param \MbhSoftware\SolrFluidResult\Service\SearchService $searchService
     * @return void
     */
    public function injectSearchService(\MbhSoftware\SolrFluidResult\Service\SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * action index
     *
     * @return void
     */
    public function indexAction()
    {
        $resultDocumentsCount = 0;
        $resultDocuments = [];

        $selectedQuerySetting = $this->settings['querySetting'];
        $selectedTemplateLayout= $this->settings['templateLayout'];

        if (isset($this->settings[$selectedTemplateLayout])) {
            ArrayUtility::mergeRecursiveWithOverrule($this->settings, $this->settings[$selectedTemplateLayout]);
        }
        if (isset($this->settings[$selectedQuerySetting])) {
            ArrayUtility::mergeRecursiveWithOverrule($this->settings, $this->settings[$selectedQuerySetting]);
        }

        $templatePath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($this->settings['templateLayouts'][$selectedTemplateLayout]);

        if (isset($this->settings['querySettings'][$selectedQuerySetting]) && $templatePath) {
            $selectedQuerySettings = $this->settings['querySettings'][$selectedQuerySetting];
            $selectedQuerySettings = $this->typoScriptService->convertPlainArrayToTypoScriptArray($selectedQuerySettings);

            $allowedSites = '';
            if (isset($selectedQuerySettings['allowedSites']) && $selectedQuerySettings['allowedSites'] !== '') {
                $allowedSites = $selectedQuerySettings['allowedSites'];
            }

            $queryString = $this->configurationManager->getContentObject()->cObjGetSingle($selectedQuerySettings['q'],
                $selectedQuerySettings['q.']);
            $queryFields = $this->configurationManager->getContentObject()->cObjGetSingle($selectedQuerySettings['qf'],
                $selectedQuerySettings['qf.']);
            $sorting = $this->configurationManager->getContentObject()->cObjGetSingle($selectedQuerySettings['sort'],
                $selectedQuerySettings['sort.']);
            $filters = GeneralUtility::trimExplode('|',
                $this->configurationManager->getContentObject()->cObjGetSingle($selectedQuerySettings['fq'],
                    $selectedQuerySettings['fq.']), true);

            if (!empty($this->settings['categoryFilterItem'])) {
                $categoryFilterItem = $this->categoryFilterItemRepository->findByUid($this->settings['categoryFilterItem']);
                $categoryFilterItemFlat = $categoryFilterItem->flatten();
                $filterString = $this->buildFilterStringFromCategoryFilterItems($categoryFilterItemFlat, $selectedQuerySettings['categoryFilterFieldName']);
                if (!empty($filterString)) {
                    $filters[] = $filterString;
                }
            }

            $maxResults = 100;

            if (!empty($this->settings['maximumResults'])) {
                $maxResults = $this->settings['maximumResults'];
            } elseif (!empty($selectedQuerySettings['maxResults'])) {
                $maxResults = $selectedQuerySettings['maxResults'];
            }

            $this->searchService->buildQuery($queryString, $filters, $queryFields, $sorting, $maxResults, $allowedSites);

            $facetFields = [];
            /** @var \ApacheSolrForTypo3\Solr\Domain\Search\Query\Query $query */
            $query = $this->searchService->getQuery();

            if (isset($selectedQuerySettings['returnFields']) && $selectedQuerySettings['returnFields']) {
                $returnFields = isset($selectedQuerySettings['returnFields.']) ? $this->configurationManager->getContentObject()->stdWrap($selectedQuerySettings['returnFields'], $selectedQuerySettings['returnFields.']) : $selectedQuerySettings['returnFields'];
                $returnFieldsArray = GeneralUtility::trimExplode(',', $returnFields);
                $query->setReturnFields(ReturnFields::fromArray($returnFieldsArray));
            }

            if (isset($selectedQuerySettings['grouping']) && $selectedQuerySettings['grouping']) {
                $grouping = new Grouping(true);
                $query->setGrouping($grouping);

                $groupField = $this->configurationManager->getContentObject()->cObjGetSingle($selectedQuerySettings['grouping.']['fields'], $selectedQuerySettings['grouping.']['fields.']);
                $query->getGrouping()->addField($groupField);

                if (isset($selectedQuerySettings['grouping.']['numberOfResultsPerGroup']) && $selectedQuerySettings['grouping.']['numberOfResultsPerGroup']) {
                    $numberOfResultsPerGroup = $this->configurationManager->getContentObject()->cObjGetSingle($selectedQuerySettings['grouping.']['numberOfResultsPerGroup'], $selectedQuerySettings['grouping.']['numberOfResultsPerGroup.']);
                    $query->getGrouping()->setResultsPerGroup($numberOfResultsPerGroup);
                }
                if (isset($selectedQuerySettings['grouping.']['numberOfGroups']) && $selectedQuerySettings['grouping.']['numberOfGroups']) {
                    $numberOfGroups = $this->configurationManager->getContentObject()->cObjGetSingle($selectedQuerySettings['grouping.']['numberOfGroups'], $selectedQuerySettings['grouping.']['numberOfResultsPerGroup.']);
                    $query->getGrouping()->setNumberOfGroups($numberOfGroups);
                }
            }

            if (isset($selectedQuerySettings['faceting']) && $selectedQuerySettings['faceting']) {
                $faceting = new Faceting(true);
                $query->setFaceting($faceting);

                $facetFields =  GeneralUtility::trimExplode('|', isset($selectedQuerySettings['faceting.']['fields.']) ? $this->configurationManager->getContentObject()->cObjGetSingle($selectedQuerySettings['faceting.']['fields'], $selectedQuerySettings['faceting.']['fields.']) : $selectedQuerySettings['faceting.']['fields']);
                $query->getFaceting()->setFields($facetFields);
            }

            if (isset($selectedQuerySettings['addQueryParameter.']) && $selectedQuerySettings['addQueryParameter.']) {
                foreach ($selectedQuerySettings['addQueryParameter.'] as $addQueryParameter) {
                    $query->addQueryParameter($addQueryParameter['name'], $addQueryParameter['value']);
                }
            }


            $resultDocumentsCount = $this->searchService->search();
            $isGroupResult = false;

            if ($resultDocumentsCount !== null && $resultDocumentsCount > 0) {
                if ($this->searchService->isGroupQuery()) {
                    $isGroupResult = true;
                    $resultDocuments = $this->searchService->getGroupedDocuments();
                } else {
                    $resultDocuments = $this->searchService->getResultDocuments();
                }


                if (!empty($selectedQuerySettings['filterResults.'])) {
                    $this->searchService->filterResults($resultDocuments, $selectedQuerySettings['filterResults.']);
                }
            }
        } else {
            return 'ERROR: query settings and or template are missing!';
        }

        // reassign rewritten settings
        $this->view->assign('settings', $this->settings);

        $this->view->assign('isGroupResult', $isGroupResult);

        $this->view->assign('resultDocumentsCount', $resultDocumentsCount);
        $this->view->assign('resultDocuments', $resultDocuments);

        if (count($facetFields)) {
            $facetResults = [];
            foreach ($facetFields as $facetField) {
                $facetResults[$facetField] = $this->searchService->getFacetFieldsResult($facetField);
            }
            $this->view->assign('facetResults', $facetResults);
        }

        $this->view->setTemplatePathAndFilename($templatePath);

        if (isset($this->settings['partialRootPath'])) {
            $partialRootPath = isset($this->settings['partialRootPath.']) ? $this->configurationManager->getContentObject()->stdWrap($this->settings['partialRootPath'], $this->settings['partialRootPath.']) : $this->settings['partialRootPath'];
            if ($partialRootPath) {
                $partialRootPath = GeneralUtility::getFileAbsFileName($partialRootPath);
                $this->view->setPartialRootPaths([$partialRootPath]);
            }
        }
    }

    /**
     * @param $filterItem
     * @return string
     */
    protected function buildFilterStringFromCategoryFilterItems($filterItem, $categoryFilterFieldName)
    {
        $generatedString = '';
        $operators = [
            0 => ' AND ',
            1 => ' OR '
        ];
        switch ($filterItem['type']) {
            case CategoryFilterItem::TYPE_OPERATOR:
                if (!is_array($filterItem['items'])) {
                    throw new \RuntimeException('Items missing', 1538040791);
                }
                foreach ($filterItem['items'] as $item) {
                    $sub[] = $this->buildFilterStringFromCategoryFilterItems($item, $categoryFilterFieldName);
                }
                break;
            case CategoryFilterItem::TYPE_CATEGORY:
                if (!is_array($filterItem['categories'])) {
                    throw new \RuntimeException('Catgories missing', 1538040805);
                }
                foreach ($filterItem['categories'] as $category) {
                    $sub[] = $categoryFilterFieldName . ':' . str_replace(' ', '\ ', $category);
                }
                break;
        }
        if (count($sub) > 1) {
            $generatedString = '(' . implode($operators[$filterItem['operator']], $sub) . ')';
        } elseif (count($sub) == 1) {
            $generatedString = $sub[0];
        }
        return $generatedString;
    }
}
