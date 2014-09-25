<?php
namespace MbhSoftware\SolrFluidResult\Service;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SearchService
 */
class SearchService implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * an instance of \tx_solr_Search
	 *
	 * @var \tx_solr_Search
	 */
	protected $search;

	/**
	 * Determines whether the solr server is available or not.
	 *
	 * @var boolean
	 */
	protected $solrAvailable;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 */
	protected $configurationManager;


	/**
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param string $keywords
	 * @param array $filters
	 * @param string $queryFields
	 * @param string $sorting
	 * @return boolean
	 */
	public function search($keywords, array $filters = array(), $queryFields = '', $sorting = '', $resultsPerPage = 10,  $allowedSites = '') {

		$this->initializeSearch();

		if (!$this->solrAvailable) {
			// early return;
			return NULL;
		}

		if ($queryFields) {
			// reset default query fields before making instance of query
			$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_solr.']['search.']['query.']['fields'] = '';
		}

		/** @var \tx_solr_Query */
		$query = GeneralUtility::makeInstance('tx_solr_Query', '');

		$query->setQueryString($keywords);
		$query->useRawQueryString(TRUE);

		if ($allowedSites === '') {
			$allowedSites = '__solr_current_site';
		}
		$allowedSites = str_replace(
			'__solr_current_site',
			\tx_solr_Site::getSiteByPageId($GLOBALS['TSFE']->id)->getDomain(),
			$allowedSites
		);
		$query->setSiteHashFilter($allowedSites);

		$query->setUserAccessGroups(explode(',', $GLOBALS['TSFE']->gr_list));

		// must generate default endtime, @see http://forge.typo3.org/issues/44276
		$query->addFilter('(endtime:[NOW/MINUTE TO *] OR endtime:"' . \tx_solr_Util::timestampToIso(0) . '")');

		if ($queryFields) {
			$query->setQueryFieldsFromString($queryFields);
		}

		foreach ($filters as $filter) {
			$query->addFilter($filter);
		}

		if ($sorting && preg_match('/^([a-z0-9_]+ (asc|desc)[, ]*)*([a-z0-9_]+ (asc|desc))+$/i', $sorting)) {
			$query->setSorting($sorting);
		}

		$query->setResultsPerPage($resultsPerPage);

		$this->search->search($query, 0, NULL);

		$results = $this->search->getNumberOfResults();

		return $results;

	}

	/**
	 * @return array
	 */
	public function getResultDocuments() {
		$responseDocuments = $this->search->getResultDocuments();
		$resultDocuments  = array();

		foreach ($responseDocuments as $resultDocument) {
			$temporaryResultDocument = $this->processDocumentFieldsToArray($resultDocument);

			//$resultDocuments[] = $this->renderDocumentFields($temporaryResultDocument);
			$resultDocuments[] = $temporaryResultDocument;

		}

		return $resultDocuments;
	}

	public function filterResults(&$results, $filters) {

		$removeFilters = array();
		$currentDomain = \tx_solr_Site::getSiteByPageId($GLOBALS['TSFE']->id)->getDomain();
		$siteHash = \tx_solr_Util::getSiteHashForDomain($currentDomain);

		if (!empty($filters['remove.'])) {
			foreach($filters['remove.'] as $filterKey => $filter) {
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
	protected function initializeSearch() {

		$solrConnection = GeneralUtility::makeInstance('tx_solr_ConnectionManager')->getConnectionByPageId(
			$GLOBALS['TSFE']->id,
			$GLOBALS['TSFE']->sys_language_uid,
			$GLOBALS['TSFE']->MP
		);

		/** @var \tx_solr_Search */
		$this->search = GeneralUtility::makeInstance('tx_solr_Search', $solrConnection);
		$this->solrAvailable = $this->search->ping();
	}

	/**
	 * takes a search result document and processes its fields according to the
	 * instructions configured in TS. Currently available instructions are
	 *    * timestamp - converts a date field into a unix timestamp
	 *    * serialize - uses serialize() to encode multivalue fields which then can be put out using the MULTIVALUE view helper
	 *    * skip - skips the whole field so that it is not available in the result, usefull for the spell field f.e.
	 * The default is to do nothing and just add the document's field to the
	 * resulting array.
	 *
	 * @param \Apache_Solr_Document $document the Apache_Solr_Document result document
	 * @return    array    An array with field values processed like defined in TS
	 */
	protected function processDocumentFieldsToArray(\Apache_Solr_Document $document) {
		$processingInstructions = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_solr.']['search.']['results.']['fieldProcessingInstructions.'];
		$availableFields = $document->getFieldNames();
		$result = array();

		foreach ($availableFields as $fieldName) {
			$processingInstruction = $processingInstructions[$fieldName];

			// TODO switch to field processors
			// TODO allow to have multiple (commaseparated) instructions for each field
			switch ($processingInstruction) {
				case 'timestamp':
					// FIXME use DateTime::createFromFormat (PHP 5.3+)
					$parsedTime = strptime($document->{$fieldName}, '%Y-%m-%dT%H:%M:%SZ');

					$processedFieldValue = mktime(
						$parsedTime['tm_hour'],
						$parsedTime['tm_min'],
						$parsedTime['tm_sec'],
						// strptime returns the "Months since January (0-11)"
						// while mktime expects the month to be a value
						// between 1 and 12. Adding 1 to solve the problem
						$parsedTime['tm_mon'] + 1,
						$parsedTime['tm_mday'],
						// strptime returns the "Years since 1900"
						$parsedTime['tm_year'] + 1900
					);
					break;
				case 'serialize':
					if(!empty($document->{$fieldName})){
						$processedFieldValue = serialize($document->{$fieldName});
					} else {
						$processedFieldValue = '';
					}
					break;
				case 'skip':
					continue 2;
				default:
					$processedFieldValue = $document->{$fieldName};
			}

			$result[$fieldName] = $processedFieldValue;
		}

		return $result;
	}

}


?>