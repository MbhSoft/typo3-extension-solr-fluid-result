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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * Class SearchController
 */
class SearchController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {


	/**
	 * @var \TYPO3\CMS\Extbase\Service\TypoScriptService
	 */
	protected $typoScriptService;

	/**
	 * @var \MbhSoftware\SolrFluidResult\Service\SearchService
	 */
	protected $searchService;


	/**
	 * @param \TYPO3\CMS\Extbase\Service\TypoScriptService $typoScriptService
	 * @return void
	 */
	public function injectTypoScriptService(\TYPO3\CMS\Extbase\Service\TypoScriptService $typoScriptService) {
		$this->typoScriptService = $typoScriptService;
	}

	/**
	 * @param \MbhSoftware\SolrFluidResult\Service\SearchService $searchService
	 * @return void
	 */
	public function injectSearchService(\MbhSoftware\SolrFluidResult\Service\SearchService $searchService) {
		$this->searchService = $searchService;
	}

	/**
	 * action index
	 *
	 * @return void
	 */
	public function indexAction() {

		$resultDocumentsCount = 0;
		$resultDocuments = array();

		$selectedQuerySetting = $this->settings['querySetting'];
		$selectedTemplateLayout= $this->settings['templateLayout'];

		if (isset($this->settings[$selectedTemplateLayout])) {
			ArrayUtility::mergeRecursiveWithOverrule($this->settings, $this->settings[$selectedTemplateLayout]);
		}

		$templatePath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($this->settings['templateLayouts'][$selectedTemplateLayout]);

		if (isset($this->settings['querySettings'][$selectedQuerySetting]) && $templatePath) {

			$selectedQuerySettings = $this->settings['querySettings'][$selectedQuerySetting];
			$selectedQuerySettings  = $this->typoScriptService->convertPlainArrayToTypoScriptArray($selectedQuerySettings);

			$allowedSites = '';
			if (isset($selectedQuerySettings['allowedSites']) && $selectedQuerySettings['allowedSites'] !== '') {
				$allowedSites = $selectedQuerySettings['allowedSites'];
			}

			$queryString = $this->configurationManager->getContentObject()->cObjGetSingle($selectedQuerySettings['q'], $selectedQuerySettings['q.']);
			$queryFields = $this->configurationManager->getContentObject()->cObjGetSingle($selectedQuerySettings['qf'], $selectedQuerySettings['qf.']);
			$sorting = $this->configurationManager->getContentObject()->cObjGetSingle($selectedQuerySettings['sort'], $selectedQuerySettings['sort.']);
			$filters = GeneralUtility::trimExplode('|', $this->configurationManager->getContentObject()->cObjGetSingle($selectedQuerySettings['fq'], $selectedQuerySettings['fq.']), TRUE);

			$this->searchService->buildQuery($queryString, $filters, $queryFields, $sorting, $selectedQuerySettings['maxResults'], $allowedSites);

			$query = $this->searchService->getQuery();

			if (isset($selectedQuerySettings['returnFields']) && $selectedQuerySettings['returnFields']) {
				$returnFields = isset($selectedQuerySettings['returnFields.']) ? $this->configurationManager->getContentObject()->stdWrap($selectedQuerySettings['returnFields'], $selectedQuerySettings['returnFields.']) : $selectedQuerySettings['returnFields'];
				$query->setFieldList($returnFields);
			}

			if (isset($selectedQuerySettings['grouping']) && $selectedQuerySettings['grouping']) {

				$query->setGrouping(TRUE);

				$groupField = $this->configurationManager->getContentObject()->cObjGetSingle($selectedQuerySettings['grouping.']['fields'], $selectedQuerySettings['grouping.']['fields.']);
				$query->addGroupField($groupField);

				if (isset($selectedQuerySettings['grouping.']['numberOfResultsPerGroup']) && $selectedQuerySettings['grouping.']['numberOfResultsPerGroup']) {
					$numberOfResultsPerGroup = $this->configurationManager->getContentObject()->cObjGetSingle($selectedQuerySettings['grouping.']['numberOfResultsPerGroup'], $selectedQuerySettings['grouping.']['numberOfResultsPerGroup.']);
					$query->setNumberOfResultsPerGroup($numberOfResultsPerGroup);
				}
			}

			$resultDocumentsCount = $this->searchService->search();
			$isGroupResult = FALSE;

			if ($resultDocumentsCount !== NULL && $resultDocumentsCount > 0) {
				if ($this->searchService->isGroupQuery()) {
					$isGroupResult = TRUE;
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

		$this->view->setTemplatePathAndFilename($templatePath);

		if (isset($this->settings['partialRootPath'])) {
			$partialRootPath = isset($this->settings['partialRootPath.']) ? $this->configurationManager->getContentObject()->stdWrap($this->settings['partialRootPath'], $this->settings['partialRootPath.']) : $this->settings['partialRootPath'];
			if ($partialRootPath) {
				$partialRootPath = GeneralUtility::getFileAbsFileName($partialRootPath);
				$this->view->setPartialRootPath($partialRootPath);
			}
		}
	}


}

?>