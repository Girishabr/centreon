<?php
/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonRealtime\Internal;

use CentreonConfiguration\Repository\HostRepository as HostConfigurationRepository;
use Centreon\Internal\Utils\Datetime;
use Centreon\Internal\Datatable;
use CentreonAdministration\Repository\TagsRepository;

/**
 * Description of HostDatatable
 *
 * @author lionel
 */
class HostDatatable extends Datatable
{
    protected static $objectId = 'host_id';

    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => true,
        'order' => array(
            array('name', 'asc')
        ),
        'stateSave' => false,
        'paging' => true,
    );
    
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonRealtime\Models\Host';
    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'host_id', 'name' => 'name');
    
    protected static  $aFieldNotAuthorized = array('tagname');
    
    /**
     *
     * @var array 
     */
    public static $columns = array(
        array (
            'title' => "Id",
            'name' => 'host_id',
            'data' => 'host_id',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
            'className' => 'cell_center',
            'width' => "20px"
        ),
        array (
            'title' => 'Name',
            'name' => 'name',
            'data' => 'name',
            'orderable' => true,
            'searchable' => true,
            'searchLabel' => 'host',
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-realtime/host/[i:id]',
                    'routeParams' => array(
                        'id' => '::host_id::'
                    ),
                    'linkName' => '::name::'
                )
            )
        ),
        array (
            'title' => 'Address',
            'name' => 'address',
            'data' => 'address',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-realtime/host/[i:id]',
                    'routeParams' => array(
                        'id' => '::host_id::'
                    ),
                    'linkName' => '::address::'
                )
            )
        ),
        array (
            'title' => 'Status',
            'name' => 'state',
            'data' => 'state',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' =>array(
                    '0' => '<span class="label label-success">Up</span>',
                    '1' => '<span class="label label-danger">Down</span>',
                    '2' => '<span class="label label-primary">Unreachable</span>',
                    '4' => '<span class="label label-info">Pending</span>'
                )
            ),
            'searchParam' => array(
                'type' => 'select',
                'additionnalParams' => array(
                    'UP' => '0',
                    'Down' => '1',
                    'Unreachable' => '2',
                    'Pending' => '4'
                )
            ),

            'width' => "50px",
            'className' => 'cell_center'
        ),
        array(
            'title' => 'Last Check',
            'name' => '(unix_timestamp(NOW())-last_check) AS last_check',
            'data' => 'last_check',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'className' => 'cell_center'
        ),
        array (
            'title' => 'Duration',
            'name' => '(unix_timestamp(NOW())-last_hard_state_change) AS duration',
            'data' => 'duration',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'className' => 'cell_center'
        ),
        array (
            'title' => 'Retry',
            'name' => 'CONCAT(check_attempt, " / ", max_check_attempts) AS retry',
            'data' => 'retry',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            'width' => '50px',
            'className' => 'cell_center'
        ),
        array (
            'title' => 'Output',
            'name' => 'output',
            'data' => 'output',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Perfdata',
            'name' => 'perfdata',
            'data' => 'perfdata',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
        ),
        array (
            'title' => 'Tags',
            'name' => 'tagname',
            'data' => 'tagname',
            'orderable' => false,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'width' => '40px',
            'tablename' => 'cfg_tags'
        ),
    );
/*
    protected static $extraParams = array(
        'addToHook' => array(
            'objectType' => 'host'
        )
    );

    protected static $hook = 'displayTagList';
    protected static $hookParams = array(
        'resourceType' => 'host'
    );
    */
    /**
     * 
     * @param array $params
     */
    public function __construct($params, $objectModelClass = '')
    {
        parent::__construct($params, $objectModelClass);
    }
    
    /**
     * 
     * @param array $resultSet
     */
    protected function formatDatas(&$resultSet)
    {
        $previousHost = '';
        foreach ($resultSet as $key => &$myHostSet) {
            // @todo remove virtual hosts and virtual services
            if ($myHostSet['name'] === '_Module_BAM') {
                unset($resultSet[$key]);
                continue;
            }

            // Set host_name
            if ($myHostSet['name'] === $previousHost) {
                $myHostSet['name'] = '';
            } else {
                $previousHost = $myHostSet['name'];
                $myHostSet['name'] = HostConfigurationRepository::getIconImage(
                    $myHostSet['name']
                ).'&nbsp;&nbsp;'.$myHostSet['name'];
            }
            $myHostSet['duration'] = Datetime::humanReadable(
                $myHostSet['duration'],
                Datetime::PRECISION_FORMAT,
                2
            );
            $myHostSet['last_check'] = Datetime::humanReadable(
                $myHostSet['last_check'],
                Datetime::PRECISION_FORMAT,
                2
            );
            
            /* Tags */
            $myHostSet['tagname']  = "";
            $aTags = TagsRepository::getList('host', $myHostSet['host_id'], 2);
            foreach ($aTags as $oTags) {
                $myHostSet['tagname'] .= TagsRepository::getTag('host', $myHostSet['host_id'], $oTags['id'], $oTags['text'], $oTags['user_id']);
            }
            $myHostSet['tagname'] .= TagsRepository::getAddTag('host', $myHostSet['host_id']);
        }
        $resultSet = array_values($resultSet);
    }
}
