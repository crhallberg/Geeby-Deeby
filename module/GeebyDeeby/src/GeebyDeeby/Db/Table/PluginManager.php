<?php
/**
 * Database table plugin manager
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
 * Database table plugin manager
 *
 * @category GeebyDeeby
 * @package  Db_Table
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/demiankatz/Geeby-Deeby Main Site
 */
class PluginManager extends \GeebyDeeby\ServiceManager\AbstractPluginManager
{
    /**
     * Constructor
     *
     * Make sure table gateways are properly initialized.
     *
     * @param null|ConfigInterface $configuration Configuration settings (optional)
     */
    public function __construct(
        \Zend\ServiceManager\ConfigInterface $configuration = null
    ) {
        parent::__construct($configuration);
        $initializer = function ($instance, $manager) {
            $instance
                ->setAdapter($manager->getServiceLocator()->get('GeebyDeeby\DbAdapter'));
            $instance->initialize();
        };
        $this->addInitializer($initializer, false);
    }

    /**
     * Return the name of the base class or interface that plug-ins must conform
     * to.
     *
     * @return string
     */
    protected function getExpectedInterface()
    {
        return 'GeebyDeeby\Db\Table\Gateway';
    }
}