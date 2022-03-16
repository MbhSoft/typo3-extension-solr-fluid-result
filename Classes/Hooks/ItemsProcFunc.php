<?php
namespace MbhSoftware\SolrFluidResult\Hooks;

use TYPO3\CMS\Backend\Utility\BackendUtility;
/***************************************************************
*  Copyright notice
*
*  (c) 2014 Marc Bastian Heinrichs <mbh@mbh-software.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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

use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems;

/**
 *
 */
class ItemsProcFunc
{

    /**
     * Itemsproc function to extend the selection of templateLayouts in the plugin
     *
     * @param array &$processorParameters configuration array
     * @return void
     */
    public function addTemplateLayouts(array &$processorParameters)
    {
        if (!empty($processorParameters['flexParentDatabaseRow']['pid'])) {
            $row = $processorParameters['flexParentDatabaseRow'];
        } else {
            $row = $processorParameters['row'];
        }

            // Add tsconfig values
        if (is_numeric($row['pid'])) {
            $pagesTsConfig = BackendUtility::getPagesTSconfig($row['pid']);
            if (isset($pagesTsConfig['tx_solrfluidresult.']['templateLayouts.']) && \is_array($pagesTsConfig['tx_solrfluidresult.']['templateLayouts.'])) {
                // Add every item
                foreach ($pagesTsConfig['tx_solrfluidresult.']['templateLayouts.'] as $key => $label) {
                    $additionalLayout = [
                        htmlspecialchars($GLOBALS['LANG']->sL($label)),
                        $key
                    ];
                    $processorParameters['items'][] = $additionalLayout;
                }
            }
        }
    }

    /**
     * Itemsproc function to extend the selection of templateLayouts in the plugin
     *
     * @param array &$processorParameters configuration array
     * @return void
     */
    public function addQuerySettings(array &$processorParameters)
    {
        if (!empty($processorParameters['flexParentDatabaseRow']['pid'])) {
            $row = $processorParameters['flexParentDatabaseRow'];
        } else {
            $row = $processorParameters['row'];
        }
            // Add tsconfig values
        if (is_numeric($row['pid'])) {
            $pagesTsConfig = BackendUtility::getPagesTSconfig($row['pid']);
            if (isset($pagesTsConfig['tx_solrfluidresult.']['querySettings.']) && \is_array($pagesTsConfig['tx_solrfluidresult.']['querySettings.'])) {
                // Add every item
                foreach ($pagesTsConfig['tx_solrfluidresult.']['querySettings.'] as $key => $label) {
                    $additionalLayout = [
                        htmlspecialchars($GLOBALS['LANG']->sL($label)),
                        $key
                    ];
                    $processorParameters['items'][] = $additionalLayout;
                }
            }
        }
    }
}
