<?php
namespace MbhSoftware\SolrFluidResult\Service;

use ApacheSolrForTypo3\Solr\Domain\Search\Query\Query;
use ApacheSolrForTypo3\Solr\Domain\Search\Query\ParameterBuilder\Sortings;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\Context\Context;
use ApacheSolrForTypo3\Solr\System\Solr\Document\Document;
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

use ApacheSolrForTypo3\Solr\ConnectionManager;
use ApacheSolrForTypo3\Solr\Domain\Search\Query\ParameterBuilder\QueryFields;
use ApacheSolrForTypo3\Solr\Domain\Search\Query\QueryBuilder;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Result\SearchResultBuilder;
use ApacheSolrForTypo3\Solr\Domain\Search\ResultSet\Result\SearchResultCollection;
use ApacheSolrForTypo3\Solr\Domain\Site\SiteHashService;
use ApacheSolrForTypo3\Solr\Search;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SearchService
 */
class SearchService implements SingletonInterface
{

    /**
     * an instance of \ApacheSolrForTypo3\Solr\Search
     *
     * @var Search
     */
    protected $search;

    /**
     * @var Query
     */
    protected $query;

    /**
     * Determines whether the solr server is available or not.
     *
     * @var boolean
     */
    protected $solrAvailable;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var SearchResultBuilder
     */
    protected $searchResultBuilder;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     * @return void
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * AbstractResultParser constructor.
     * @param SearchResultBuilder|null $resultBuilder
     */
    public function __construct(SearchResultBuilder $resultBuilder = null)
    {
        $this->searchResultBuilder = $resultBuilder === null ? GeneralUtility::makeInstance(SearchResultBuilder::class) : $resultBuilder;
    }


    public function reset()
    {
        $this->query = null;
    }

    /**
     * @param string $keywords
     * @param array $filters
     * @param string $queryFields
     * @param string $sorting
     * @throws \Exception
     * @return SearchService
     */
    public function buildQuery($keywords, array $filters = [], $queryFields = '', $sorting = '', $resultsPerPage = 10, $allowedSites = '')
    {
        $this->initializeSearch();

        if ($this->query !== null) {
            throw new \Exception('Call reset first!');
        }

        if ($allowedSites === '') {
            $allowedSites = '__solr_current_site';
        }
        /** @var SiteHashService $siteHashService */
        $siteHashService = GeneralUtility::makeInstance(SiteHashService::class);
        $allowedSites = $siteHashService->getAllowedSitesForPageIdAndAllowedSitesConfiguration($GLOBALS['TSFE']->id, $allowedSites);

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(QueryBuilder::class);
        $queryBuilder->newSearchQuery('')
            ->useQueryString($keywords)
            ->useQueryType('standard')
            ->useSiteHashFromAllowedSites($allowedSites)
            ->useUserAccessGroups(explode(',', implode(',', GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('frontend.user', 'groupIds'))));

        if ($queryFields) {
            $queryBuilder->useQueryFields(QueryFields::fromString($queryFields));
        }

        if ($sorting && preg_match('/^([a-z0-9_]+ (asc|desc)[, ]*)*([a-z0-9_]+ (asc|desc))+$/i', $sorting)) {
            $sortings = Sortings::fromString($sorting);
            $queryBuilder->useSortings($sortings);
        }

        $queryBuilder->useFilterArray($filters);
        if (is_int($resultsPerPage)) {
            $queryBuilder->useResultsPerPage($resultsPerPage);
        }

        $this->query = $queryBuilder->getQuery();

        return $this;
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Do a search - returns the number of results
     *
     * @return int|null
     */
    public function search()
    {
        try {
            $response = $this->search->search($this->query, 0, null);

            if ($this->isGroupQuery()) {
                $groupField = $this->getGroupField();
                $results = $response->grouped->$groupField->matches;
            } else {
                $results = $response->response->numFound;
            }
        } catch (\Exception $e) {
            $results = null;
        }
        return $results;
    }

    public function isGroupQuery()
    {
        return count($this->query->getGrouping()->getFields());
    }

    public function getGroupField()
    {
        return current($this->query->getGrouping()->getFields());
    }

    public function getGroupedDocuments()
    {
        $response = $this->search->getResponse();
        $groupField = $this->getGroupField();
        $groups = $response->grouped->$groupField->groups;
        $resultGroups = [];
        $groupValues = [];
        foreach ($groups as $group) {
            $resultGroup = [];
            $groupValues[] = $group->groupValue;
            $resultGroup['groupValue'] = $group->groupValue;

            $resultDocuments = $this->parseDocuments($group->doclist->docs);
            $searchResult = $this->getSearchResultCollection($resultDocuments);

            $resultGroup['resultDocuments'] = $searchResult;
            $resultGroups['groups'][] = $resultGroup;
        }
        $resultGroups['groupValues'] = $groupValues;
        return $resultGroups;
    }

    public function getFacetFieldsResult($facetField)
    {
        $result = null;
        $response = $this->search->getResponse();
        if (isset($response->facet_counts->facet_fields->$facetField)) {
            $result = (array)$response->facet_counts->facet_fields->$facetField;
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getResultDocuments()
    {
        $response = $this->search->getResponse();
        $parsedData = $response->getParsedData();
        $documents = $parsedData->response->docs;
        return $this->getSearchResultCollection($documents);
    }


    protected function getSearchResultCollection($documents)
    {
        $searchResults = GeneralUtility::makeInstance(SearchResultCollection::class);
        foreach ($documents as $document) {
            $searchResultObject = $this->searchResultBuilder->fromApacheSolrDocument($document);
            $searchResults[] = $searchResultObject;
        }
        return $searchResults;
    }


    public function filterResults(&$results, $filters)
    {
        $removeFilters = [];
        $currentDomain = \tx_solr_Site::getSiteByPageId($GLOBALS['TSFE']->id)->getDomain();
        $siteHash = \tx_solr_Util::getSiteHashForDomain($currentDomain);

        if (!empty($filters['remove.'])) {
            foreach ($filters['remove.'] as $filterKey => $filter) {
                if (!is_array($filters['remove.'][$filterKey])) {
                    if (is_array($filters['remove.'][$filterKey . '.'])) {
                        $filter = $this->configurationManager->getContentObject()->stdWrap(
                            $filters['remove.'][$filterKey],
                            $filters['remove.'][$filterKey . '.']
                        );
                    }

                    $filter = str_replace(
                        '__solr_current_site',
                        $siteHash,
                        $filter
                    );

                    $removeFilters[$filterKey] = $filter;
                }
            }
        }

        foreach ($removeFilters as $removeFilter) {
            foreach ($results as $resultKey => $result) {
                if ($result['id'] == $removeFilter) {
                    unset($results[$resultKey]);
                }
            }
        }
    }

    /**
     *
     * @return void
     */
    protected function initializeSearch()
    {
        $solrConnection = GeneralUtility::makeInstance(ConnectionManager::class)->getConnectionByPageId(
            $GLOBALS['TSFE']->id,
            GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id')
        );

        /** @var Search */
        $this->search = GeneralUtility::makeInstance(Search::class, $solrConnection);
    }

    /**
     * @param array $documents
     */
    protected function parseDocuments(array $documents)
    {
        $parsedDocuments = [];

        foreach ($documents as $originalDocument) {
            $document = new Document();
            foreach ($originalDocument as $key => $value) {
                //If a result is an array with only a single
                //value then its nice to be able to access
                //it as if it were always a single value
                if (is_array($value) && count($value) <= 1) {
                    $value = array_shift($value);
                }
                $document->$key = $value;
            }
            $parsedDocuments[] = $document;
        }

        return $parsedDocuments;
    }
}
