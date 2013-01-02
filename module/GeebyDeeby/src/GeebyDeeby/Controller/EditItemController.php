<?php
/**
 * Edit item controller
 *
 * PHP version 5
 *
 * Copyright (C) Demian Katz 2012.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category GeebyDeeby
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/demiankatz/Geeby-Deeby Main Site
 */
namespace GeebyDeeby\Controller;

/**
 * Edit item controller
 *
 * @category GeebyDeeby
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/demiankatz/Geeby-Deeby Main Site
 */
class EditItemController extends AbstractBase
{
    /**
     * Display a list of items
     *
     * @return mixed
     */
    public function listAction()
    {
        return $this->getGenericList(
            'item', 'items', 'geeby-deeby/edit-item/render-items'
        );
    }

    /**
     * Operate on a single item
     *
     * @return mixed
     */
    public function indexAction()
    {
        $assignMap = array(
            'name' => 'Item_Name',
            'len' => 'Item_Length',
            'endings' => 'Item_Endings',
            'errata' => 'Item_Errata',
            'thanks' => 'Item_Thanks',
            'material' => 'Material_Type_ID'
        );
        $view = $this->handleGenericItem('item', $assignMap, 'item');

        $view->materials = $this->getDbTable('materialtype')->getList();

        // Add extra fields/controls if outside of a lightbox:
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $view->adaptedInto = $this->getDbTable('itemsadaptations')
                ->getAdaptedFrom($view->itemObj->Item_ID);
            $view->adaptedFrom = $this->getDbTable('itemsadaptations')
                ->getAdaptedInto($view->itemObj->Item_ID);
            $view->itemsBib = $this->getDbTable('itemsbibliography')
                ->getItemsDescribedByItem($view->itemObj->Item_ID);
            $view->peopleBib = $this->getDbTable('peoplebibliography')
                ->getPeopleDescribedByItem($view->itemObj->Item_ID);
            $view->seriesBib = $this->getDbTable('seriesbibliography')
                ->getSeriesDescribedByItem($view->itemObj->Item_ID);
            $view->translatedInto = $this->getDbTable('itemstranslations')
                ->getTranslatedFrom($view->itemObj->Item_ID);
            $view->item_alt_titles = $this->getDbTable('itemsalttitles')
                ->getAltTitles($view->itemObj->Item_ID);
            $view->translatedFrom = $this->getDbTable('itemstranslations')
                ->getTranslatedInto($view->itemObj->Item_ID);
            $view->setTemplate('geeby-deeby/edit-item/edit-full');
        }

        // Process series ID linkage if necessary:
        if ($this->getRequest()->isPost()) {
            if ($series = $this->params()->fromPost('series_id', false)) {
                $this->getDbTable('itemsinseries')->insert(
                    array(
                        'Item_ID' => $view->affectedRow->Item_ID,
                        'Series_ID' => $series
                    )
                );
            }
        }

        return $view;
    }

    /**
     * Deal with item references
     *
     * @return mixed
     */
    public function aboutitemAction()
    {
        return $this->handleGenericLink(
            'itemsbibliography', 'Bib_Item_ID', 'Item_ID',
            'itemsBib', 'getItemsDescribedByItem',
            'geeby-deeby/edit-item/item-ref-list.phtml'
        );
    }

    /**
     * Deal with series references
     *
     * @return mixed
     */
    public function aboutseriesAction()
    {
        return $this->handleGenericLink(
            'seriesbibliography', 'Item_ID', 'Series_ID',
            'seriesBib', 'getSeriesDescribedByItem',
            'geeby-deeby/edit-item/series-ref-list.phtml'
        );
    }

    /**
     * Deal with person references
     *
     * @return mixed
     */
    public function aboutpersonAction()
    {
        return $this->handleGenericLink(
            'peoplebibliography', 'Item_ID', 'Person_ID',
            'peopleBib', 'getPeopleDescribedByItem',
            'geeby-deeby/edit-item/person-ref-list.phtml'
        );
    }

    /**
     * Deal with adaptations
     *
     * @return mixed
     */
    public function adaptationAction()
    {
        return $this->handleGenericLink(
            'itemsadaptations', 'Source_Item_ID', 'Adapted_Item_ID',
            'adaptedInto', 'getAdaptedFrom',
            'geeby-deeby/edit-item/adapted-into-list.phtml'
        );
    }

    /**
     * Deal with adaptation sources
     *
     * @return mixed
     */
    public function adaptedfromAction()
    {
        return $this->handleGenericLink(
            'itemsadaptations', 'Adapted_Item_ID', 'Source_Item_ID',
            'adaptedFrom', 'getAdaptedInto',
            'geeby-deeby/edit-item/adapted-from-list.phtml'
        );
    }

    /**
     * Work with alternate titles
     *
     * @return mixed
     */
    public function alttitleAction()
    {
        // Special case: new publisher:
        if ($this->getRequest()->isPost()) {
            $table = $this->getDbTable('itemsalttitles');
            $row = $table->createRow();
            $row->Item_ID = $this->params()->fromRoute('id');
            $row->Note_ID = $this->params()->fromPost('note_id');
            $row->Item_AltName = $this->params()->fromPost('title');
            $table->insert((array)$row);
            return $this->jsonReportSuccess();
        } else {
            // Otherwise, treat this as a generic link:
            return $this->handleGenericLink(
                'itemsalttitles', 'Item_ID', 'Sequence_ID',
                'item_alt_titles', 'getAltTitles',
                'geeby-deeby/edit-item/alt-title-list.phtml'
            );
        }
    }

    /**
     * Deal with translations
     *
     * @return mixed
     */
    public function translationAction()
    {
        return $this->handleGenericLink(
            'itemstranslations', 'Source_Item_ID', 'Trans_Item_ID',
            'translatedInto', 'getTranslatedFrom',
            'geeby-deeby/edit-item/trans-into-list.phtml'
        );
    }

    /**
     * Deal with translation sources
     *
     * @return mixed
     */
    public function translatedfromAction()
    {
        return $this->handleGenericLink(
            'itemstranslations', 'Trans_Item_ID', 'Source_Item_ID',
            'translatedFrom', 'getTranslatedInto',
            'geeby-deeby/edit-item/trans-from-list.phtml'
        );
    }
}