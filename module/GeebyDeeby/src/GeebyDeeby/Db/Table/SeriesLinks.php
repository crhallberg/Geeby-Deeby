<?php
/**
 * Table Definition for Series_Links
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
 * @package  Db_Table
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/demiankatz/Geeby-Deeby Main Site
 */
namespace GeebyDeeby\Db\Table;

/**
 * Table Definition for Series_Links
 *
 * @category GeebyDeeby
 * @package  Db_Table
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/demiankatz/Geeby-Deeby Main Site
 */
class SeriesLinks extends Gateway
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('Series_Links');
    }

    /**
     * Get a list of links for the specified series.
     *
     * @var int $seriesID Series ID
     *
     * @return mixed
     */
    public function getLinksForSeries($seriesID)
    {
        $callback = function ($select) use ($seriesID) {
            $select->join(
                array('l' => 'Links'), 'Series_Links.Link_ID = l.Link_ID'
            );
            $select->order(array('Link_Name'));
            $select->where->equalTo('Series_ID', $seriesID);
        };
        return $this->select($callback);
    }

    /**
     * Get a list of series for the specified link.
     *
     * @var int $linkID Link ID
     *
     * @return mixed
     */
    public function getSeriesForLink($linkID)
    {
        $callback = function ($select) use ($linkID) {
            $select->join(
                array('s' => 'Series'),
                'Series_Links.Series_ID = s.Series_ID'
            );
            $select->order('s.Series_Name');
            $select->where->equalTo('Link_ID', $linkID);
        };
        return $this->select($callback);
    }
}
