<?php
/**
 * Edit edition controller
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
 * Edit edition controller
 *
 * @category GeebyDeeby
 * @package  Controller
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/demiankatz/Geeby-Deeby Main Site
 */
class EditEditionController extends AbstractBase
{
    /**
     * Display a list of editions
     *
     * @return mixed
     */
    public function listAction()
    {
        return $this->getGenericList(
            'edition', 'editions', 'geeby-deeby/edit-edition/render-editions'
        );
    }

    /**
     * Operate on a single edition
     *
     * @return mixed
     */
    public function indexAction()
    {
        $assignMap = array(
            'name' => 'Edition_Name',
            'desc' => 'Edition_Description',
            'item_id' => 'Item_ID',
            'series_id' => 'Series_ID',
            'position' => 'Position',
            'len' => 'Edition_Length',
            'endings' => 'Edition_Endings'
        );
        $view = $this->handleGenericItem('edition', $assignMap, 'edition');

        // Add item/series details if necessary:
        if (isset($view->edition['Item_ID']) && !empty($view->edition['Item_ID'])) {
            $view->item = $this->getDbTable('item')
                ->getByPrimaryKey($view->edition['Item_ID']);
            $view->itemAltTitles = $this->getDbTable('itemsalttitles')
                ->getAltTitles($view->edition['Item_ID']);
        }
        if (isset($view->edition['Series_ID'])
            && !empty($view->edition['Series_ID'])
        ) {
            $view->series = $this->getDbTable('series')
                ->getByPrimaryKey($view->edition['Series_ID']);
            $view->seriesAltTitles = $this->getDbTable('seriesalttitles')
                ->getAltTitles($view->edition['Series_ID']);
            $view->publishers = $this->getDbTable('seriespublishers')
                ->getPublishers($view->edition['Series_ID']);
        }
        // Add extra fields/controls if outside of a lightbox:
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $view->roles = $this->getDbTable('role')->getList();
            $view->credits= $this->getDbTable('editionscredits')
                ->getCreditsForEdition($view->edition['Edition_ID']);
            $view->images = $this->getDbTable('editionsimages')
                ->getImagesForEdition($view->edition['Edition_ID']);
            $view->ISBNs = $this->getDbTable('editionsisbns')
                ->getISBNsForEdition($view->edition['Edition_ID']);
            $view->oclcNumbers = $this->getDbTable('editionsoclcnumbers')
                ->getOCLCNumbersForEdition($view->edition['Edition_ID']);
            $view->editionPlatforms = $this->getDbTable('editionsplatforms')
                ->getPlatformsForEdition($view->edition['Edition_ID']);
            $view->platforms = $this->getDbTable('platform')->getList();
            $view->productCodes = $this->getDbTable('editionsproductcodes')
                ->getProductCodesForEdition($view->edition['Edition_ID']);
            $view->releaseDates = $this->getDbTable('editionsreleasedates')
                ->getDatesForEdition($view->edition['Edition_ID']);
            $view->setTemplate('geeby-deeby/edit-edition/edit-full');
            $view->fullText = $this->getDbTable('editionsfulltext')
                ->getFullTextForEdition($view->edition['Edition_ID']);
            $view->fullTextSources = $this->getDbTable('fulltextsource')
                ->getList();
            $view->next = $view->editionObj->getNextInSeries();
            $view->previous = $view->editionObj->getPreviousInSeries();
        }
        return $view;
    }

    /**
     * Get drop-down of series publishers
     *
     * @return mixed
     */
    public function seriespublishersAction()
    {
        $view = $this->createViewModel();
        $view->edition = $this->getDbTable('edition')
            ->getByPrimaryKey($this->params()->fromRoute('id'));
        $view->publishers = $this->getDbTable('seriespublishers')
            ->getPublishers($view->edition['Series_ID']);
        $view->selected = $view->edition['Preferred_Series_Publisher_ID'];
        $view->setTemplate('geeby-deeby/edit-edition/series-publisher-select.phtml');
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Set a preferred publisher
     *
     * @return mixed
     */
    public function setpreferredpublisherAction()
    {
        $ok = $this->checkPermission('Content_Editor');
        if ($ok !== true) {
            return $ok;
        }
        if (!$this->getRequest()->isPost()) {
            return $this->jsonDie('Unexpected method.');
        }

        $editionId = $this->params()->fromRoute('id');
        $edition = $this->getDbTable('edition')->getByPrimaryKey($editionId);
        $pubId = $this->params()->fromPost('pub_id');
        $edition->Preferred_Series_Publisher_ID = empty($pubId) ? null : $pubId;
        $edition->save();
        return $this->jsonReportSuccess();
    }

    /**
     * Get drop-down of item alt titles
     *
     * @return mixed
     */
    public function itemalttitlesAction()
    {
        $view = $this->createViewModel();
        $view->edition = $this->getDbTable('edition')
            ->getByPrimaryKey($this->params()->fromRoute('id'));
        $view->itemAltTitles = $this->getDbTable('itemsalttitles')
            ->getAltTitles($view->edition['Item_ID']);
        $view->selected = $view->edition['Preferred_Item_AltName_ID'];
        $view->setTemplate('geeby-deeby/edit-edition/item-alt-title-select.phtml');
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Set a preferred item title
     *
     * @return mixed
     */
    public function setpreferreditemtitleAction()
    {
        $ok = $this->checkPermission('Content_Editor');
        if ($ok !== true) {
            return $ok;
        }
        if (!$this->getRequest()->isPost()) {
            return $this->jsonDie('Unexpected method.');
        }

        $editionId = $this->params()->fromRoute('id');
        $edition = $this->getDbTable('edition')->getByPrimaryKey($editionId);
        $title = $this->params()->fromPost('title_id', 'NEW');
        $titleText = trim($this->params()->fromPost('title_text'));

        if ($title == 'NEW') {
            if (empty($titleText)) {
                return $this->jsonDie('Title cannot be empty.');
            } else {
                $table = $this->getDbTable('itemsalttitles');
                $row = $table->createRow();
                $row->Item_ID = $edition->Item_ID;
                if (empty($row->Item_ID)) {
                    return $this->jsonDie('Edition must be attached to an Item.');
                }
                $row->Item_AltName = $titleText;
                $table->insert((array)$row);
                $results = $table->select((array)$row);
                foreach ($results as $result) {
                    $result = (array)$result;
                    $title = $result['Sequence_ID'];
                }
                if (empty($title)) {
                    return $this->jsonDie('Problem inserting title.');
                }
            }
        }
        $edition->Preferred_Item_AltName_ID = $title;
        $edition->save();
        return $this->jsonReportSuccess();
    }

    /**
     * Clear a preferred item title
     *
     * @return mixed
     */
    public function clearpreferreditemtitleAction()
    {
        $ok = $this->checkPermission('Content_Editor');
        if ($ok !== true) {
            return $ok;
        }
        if ($this->getRequest()->isPost()) {
            $editionId = $this->params()->fromRoute('id');
            $edition = $this->getDbTable('edition')->getByPrimaryKey($editionId);
            $edition->Preferred_Item_AltName_ID = null;
            $edition->save();
            return $this->jsonReportSuccess();
        } else {
            return $this->jsonDie('Unexpected method.');
        }
    }

    /**
     * Get drop-down of series alt titles
     *
     * @return mixed
     */
    public function seriesalttitlesAction()
    {
        $view = $this->createViewModel();
        $view->edition = $this->getDbTable('edition')
            ->getByPrimaryKey($this->params()->fromRoute('id'));
        $view->seriesAltTitles = $this->getDbTable('seriesalttitles')
            ->getAltTitles($view->edition['Series_ID']);
        $view->selected = $view->edition['Preferred_Series_AltName_ID'];
        $view->setTemplate('geeby-deeby/edit-edition/series-alt-title-select.phtml');
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Set a preferred series title
     *
     * @return mixed
     */
    public function setpreferredseriestitleAction()
    {
        $ok = $this->checkPermission('Content_Editor');
        if ($ok !== true) {
            return $ok;
        }
        if (!$this->getRequest()->isPost()) {
            return $this->jsonDie('Unexpected method.');
        }

        $editionId = $this->params()->fromRoute('id');
        $edition = $this->getDbTable('edition')->getByPrimaryKey($editionId);
        $title = $this->params()->fromPost('title_id', 'NEW');
        $titleText = trim($this->params()->fromPost('title_text'));

        if ($title == 'NEW') {
            if (empty($titleText)) {
                return $this->jsonDie('Title cannot be empty.');
            } else {
                $table = $this->getDbTable('seriesalttitles');
                $row = $table->createRow();
                $row->Series_ID = $edition->Series_ID;
                if (empty($row->Series_ID)) {
                    return $this->jsonDie('Edition must be attached to a Series.');
                }
                $row->Series_AltName = $titleText;
                $table->insert((array)$row);
                $results = $table->select((array)$row);
                foreach ($results as $result) {
                    $result = (array)$result;
                    $title = $result['Sequence_ID'];
                }
                if (empty($title)) {
                    return $this->jsonDie('Problem inserting title.');
                }
            }
        }
        $edition->Preferred_Series_AltName_ID = $title;
        $edition->save();
        return $this->jsonReportSuccess();
    }

    /**
     * Clear a preferred series title
     *
     * @return mixed
     */
    public function clearpreferredseriestitleAction()
    {
        $ok = $this->checkPermission('Content_Editor');
        if ($ok !== true) {
            return $ok;
        }
        if ($this->getRequest()->isPost()) {
            $editionId = $this->params()->fromRoute('id');
            $edition = $this->getDbTable('edition')->getByPrimaryKey($editionId);
            $edition->Preferred_Series_AltName_ID = null;
            $edition->save();
            return $this->jsonReportSuccess();
        } else {
            return $this->jsonDie('Unexpected method.');
        }
    }

    /**
     * Copy an edition
     *
     * @return mixed
     */
    public function copyAction()
    {
        $ok = $this->checkPermission('Content_Editor');
        if ($ok !== true) {
            return $ok;
        }
        if ($this->getRequest()->isPost()) {
            $editionId = $this->params()->fromRoute('id');
            $table = $this->getDbTable('edition');
            $old = $table->getByPrimaryKey($editionId);
            if (!$old) {
                return $this->jsonDie('Cannot load edition ' . $editionId);
            }
            $new = $table->createRow();
            foreach ($old->toArray() as $key => $value) {
                if ($key != 'Edition_ID') {
                    $new->$key = $value;
                }
            }
            $new->Edition_Name = 'Copy of ' . $new->Edition_Name;
            $new->save();
            $new->copyCredits($editionId);
            return $this->jsonReportSuccess();
        }
        return $this->jsonDie('Unexpected method');
    }

    /**
     * Get list of dates
     *
     * @return mixed
     */
    public function datesAction()
    {
        $table = $this->getDbTable('editionsreleasedates');
        $view = $this->createViewModel();
        $primary = $this->params()->fromRoute('id');
        $view->releaseDates = $table->getDatesForEdition($primary);
        $view->setTemplate('geeby-deeby/edit-edition/date-list.phtml');
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Get next/previous links
     *
     * @return mixed
     */
    public function nextandprevAction()
    {
        $table = $this->getDbTable('edition');
        $view = $this->createViewModel();
        $primary = $this->params()->fromRoute('id');
        $edition = $table->getByPrimaryKey($primary);
        $view->next = $edition->getNextInSeries();
        $view->previous = $edition->getPreviousInSeries();
        $view->setTemplate('geeby-deeby/edit-edition/next-and-prev.phtml');
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Add a date
     *
     * @return mixed
     */
    public function adddateAction()
    {
        $ok = $this->checkPermission('Content_Editor');
        if ($ok !== true) {
            return $ok;
        }
        if ($this->getRequest()->isPost()) {
            $table = $this->getDbTable('editionsreleasedates');
            $row = $table->createRow();
            $row->Edition_ID = $this->params()->fromRoute('id');
            $row->Year = $this->params()->fromPost('year');
            $row->Month = $this->params()->fromPost('month');
            $row->Day = $this->params()->fromPost('day');
            $row->Note_ID = $this->params()->fromPost('note_id');
            $table->insert((array)$row);
            return $this->jsonReportSuccess();
        }
        return $this->jsonDie('Unexpected method');
    }

    /**
     * Remove a date
     *
     * @return mixed
     */
    public function deletedateAction()
    {
        $ok = $this->checkPermission('Content_Editor');
        if ($ok !== true) {
            return $ok;
        }
        if ($this->getRequest()->isPost()) {
            $this->getDbTable('editionsreleasedates')->delete(
                array(
                    'Edition_ID' => $this->params()->fromRoute('id'),
                    'Year' => $this->params()->fromPost('year'),
                    'Month' => $this->params()->fromPost('month'),
                    'Day' => $this->params()->fromPost('day'),
                )
            );
            return $this->jsonReportSuccess();
        }
        return $this->jsonDie('Unexpected method');
    }

    /**
     * Get list of credits
     *
     * @return mixed
     */
    public function creditsAction()
    {
        $table = $this->getDbTable('editionscredits');
        $view = $this->createViewModel();
        $primary = $this->params()->fromRoute('id');
        $view->credits = $table->getCreditsForEdition($primary);
        $view->setTemplate('geeby-deeby/edit-edition/credits.phtml');
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Add a credit
     *
     * @return mixed
     */
    public function addcreditAction()
    {
        $ok = $this->checkPermission('Content_Editor');
        if ($ok !== true) {
            return $ok;
        }
        if ($this->getRequest()->isPost()) {
            $table = $this->getDbTable('editionscredits');
            $row = $table->createRow();
            $row->Edition_ID = $this->params()->fromRoute('id');
            $row->Person_ID = $this->params()->fromPost('person_id');
            $row->Role_ID = $this->params()->fromPost('role_id');
            $row->Position = $this->params()->fromPost('pos');
            $row->Note_ID = $this->params()->fromPost('note_id');
            $table->insert((array)$row);
            return $this->jsonReportSuccess();
        }
        return $this->jsonDie('Unexpected method');
    }

    /**
     * Remove a credit
     *
     * @return mixed
     */
    public function deletecreditAction()
    {
        $ok = $this->checkPermission('Content_Editor');
        if ($ok !== true) {
            return $ok;
        }
        if ($this->getRequest()->isPost()) {
            $this->getDbTable('editionscredits')->delete(
                array(
                    'Edition_ID' => $this->params()->fromRoute('id'),
                    'Person_ID' => $this->params()->fromPost('person_id'),
                    'Role_ID' => $this->params()->fromPost('role_id')
                )
            );
            return $this->jsonReportSuccess();
        }
        return $this->jsonDie('Unexpected method');
    }

    /**
     * Set the order of an attached credit
     *
     * @return mixed
     */
    public function creditorderAction()
    {
        $ok = $this->checkPermission('Content_Editor');
        if ($ok !== true) {
            return $ok;
        }
        if ($this->getRequest()->isPost()) {
            $this->getDbTable('editionscredits')->update(
                array('Position' => $this->params()->fromPost('pos')),
                array(
                    'Edition_ID' => $this->params()->fromRoute('id'),
                    'Person_ID' => $this->params()->fromPost('person_id'),
                    'Role_ID' => $this->params()->fromPost('role_id')
                )
            );
            return $this->jsonReportSuccess();
        }
        return $this->jsonDie('Unexpected method');
    }

    /**
     * Deal with full text
     *
     * @return mixed
     */
    public function fulltextAction()
    {
        $ok = $this->checkPermission('Content_Editor');
        if ($ok !== true) {
            return $ok;
        }
        $table = $this->getDbTable('editionsfulltext');
        if ($this->getRequest()->isPost()) {
            $insert = array(
                'Full_Text_Source_ID' => $this->params()->fromPost('source_id'),
                'Edition_ID' => $this->params()->fromRoute('id'),
                'Full_Text_URL' => trim($this->params()->fromPost('url'))
            );
            if (empty($insert['Full_Text_URL'])) {
                return $this->jsonDie('URL must not be empty.');
            }
            $table->insert($insert);
            return $this->jsonReportSuccess();
        } else if ($this->getRequest()->isDelete()) {
            $delete = $this->params()->fromRoute('extra');
            $table->delete(array('Sequence_ID' => $delete));
            return $this->jsonReportSuccess();
        }
        return $this->jsonDie('Unexpected method.');
    }

    /**
     * Get list of full text
     *
     * @return mixed
     */
    public function fulltextlistAction()
    {
        $view = $this->createViewModel();
        $primary = $this->params()->fromRoute('id');
            $view->fullText = $this->getDbTable('editionsfulltext')
                ->getFullTextForEdition($primary);
        $view->setTemplate('geeby-deeby/edit-edition/fulltext-list.phtml');
        $view->setTerminal(true);
        return $view;
    }

    /**
     * Work with ISBNs
     *
     * @return mixed
     */
    public function isbnAction()
    {
        // Special case: new ISBN:
        if ($this->getRequest()->isPost()) {
            $ok = $this->checkPermission('Content_Editor');
            if ($ok !== true) {
                return $ok;
            }
            $isbn = new \VuFindCode\ISBN($this->params()->fromPost('isbn'));
            if (!$isbn->isValid()) {
                return $this->jsonDie('Invalid ISBN -- cannot save.');
            }
            $table = $this->getDbTable('editionsisbns');
            $row = $table->createRow();
            $row->Edition_ID = $this->params()->fromRoute('id');
            $row->Note_ID = $this->params()->fromPost('note_id');
            $isbn10 = $isbn->get10();
            if (!empty($isbn10)) {
                $row->ISBN = $isbn10;
            }
            $row->ISBN13 = $isbn->get13();
            $table->insert((array)$row);
            return $this->jsonReportSuccess();
        } else {
            // Otherwise, treat this as a generic link:
            return $this->handleGenericLink(
                'editionsisbns', 'Edition_ID', 'Sequence_ID', 'ISBNs',
                'getISBNsForEdition', 'geeby-deeby/edit-edition/isbn-list.phtml'
            );
        }
    }

    /**
     * Work with OCLC numbers
     *
     * @return mixed
     */
    public function oclcnumberAction()
    {
        // Special case: new code:
        if ($this->getRequest()->isPost()) {
            $ok = $this->checkPermission('Content_Editor');
            if ($ok !== true) {
                return $ok;
            }
            $table = $this->getDbTable('editionsoclcnumbers');
            $row = $table->createRow();
            $row->Edition_ID = $this->params()->fromRoute('id');
            $row->Note_ID = $this->params()->fromPost('note_id');
            $row->OCLC_Number = $this->params()->fromPost('oclc_number');
            if (empty($row->OCLC_Number)) {
                return $this->jsonDie('OCLC number must not be empty.');
            }
            $table->insert((array)$row);
            return $this->jsonReportSuccess();
        } else {
            // Otherwise, treat this as a generic link:
            return $this->handleGenericLink(
                'editionsoclcnumbers', 'Edition_ID', 'Sequence_ID',
                'oclcNumbers', 'getOCLCNumbersForEdition',
                'geeby-deeby/edit-edition/oclc-number-list.phtml'
            );
        }
    }

    /**
     * Work with product codes
     *
     * @return mixed
     */
    public function productcodeAction()
    {
        // Special case: new code:
        if ($this->getRequest()->isPost()) {
            $ok = $this->checkPermission('Content_Editor');
            if ($ok !== true) {
                return $ok;
            }
            $table = $this->getDbTable('editionsproductcodes');
            $row = $table->createRow();
            $row->Edition_ID = $this->params()->fromRoute('id');
            $row->Note_ID = $this->params()->fromPost('note_id');
            $row->Product_Code = $this->params()->fromPost('code');
            if (empty($row->Product_Code)) {
                return $this->jsonDie('Product code must not be empty.');
            }
            $table->insert((array)$row);
            return $this->jsonReportSuccess();
        } else {
            // Otherwise, treat this as a generic link:
            return $this->handleGenericLink(
                'editionsproductcodes', 'Edition_ID', 'Sequence_ID',
                'productCodes', 'getProductCodesForEdition',
                'geeby-deeby/edit-edition/product-code-list.phtml'
            );
        }
    }

    /**
     * Work with images
     *
     * @return mixed
     */
    public function imageAction()
    {
        // Special case: new image:
        if ($this->getRequest()->isPost()) {
            $ok = $this->checkPermission('Content_Editor');
            if ($ok !== true) {
                return $ok;
            }
            $table = $this->getDbTable('editionsimages');
            $row = $table->createRow();
            $row->Edition_ID = $this->params()->fromRoute('id');
            $row->Note_ID = $this->params()->fromPost('note_id');
            $row->Image_Path = $this->params()->fromPost('image');
            if (empty($row->Image_Path)) {
                return $this->jsonDie('Image path must be set.');
            }
            $row->Thumb_Path = $this->params()->fromPost('thumb');
            // Build thumb path if none was provided:
            if (empty($row->Thumb_Path)) {
                $parts = explode('.', $row->Image_Path);
                $nextToLast = count($parts) - 2;
                $parts[$nextToLast] .= 'thumb';
                $row->Thumb_Path = implode('.', $parts);
            }
            $row->Position = $this->params()->fromPost('pos');
            $table->insert((array)$row);
            return $this->jsonReportSuccess();
        } else {
            // Otherwise, treat this as a generic link:
            return $this->handleGenericLink(
                'editionsimages', 'Edition_ID', 'Sequence_ID',
                'images', 'getImagesForEdition',
                'geeby-deeby/edit-edition/image-list.phtml'
            );
        }
    }

    /**
     * Set the order of an attached image
     *
     * @return mixed
     */
    public function imageorderAction()
    {
        $ok = $this->checkPermission('Content_Editor');
        if ($ok !== true) {
            return $ok;
        }
        if ($this->getRequest()->isPost()) {
            $image = $this->params()->fromRoute('extra');
            $pos = $this->params()->fromPost('pos');
            $this->getDbTable('editionsimages')->update(
                array('Position' => $pos), array('Sequence_ID' => $image)
            );
            return $this->jsonReportSuccess();
        }
        return $this->jsonDie('Unexpected method');
    }

    /**
     * Deal with platforms
     *
     * @return mixed
     */
    public function platformAction()
    {
        return $this->handleGenericLink(
            'editionsplatforms', 'Edition_ID', 'Platform_ID',
            'editionPlatforms', 'getPlatformsForEdition',
            'geeby-deeby/edit-edition/platform-list.phtml'
        );
    }
}
