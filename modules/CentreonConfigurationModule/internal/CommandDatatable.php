<?php

/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of CENTREON choice, provided that 
 * CENTREON also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonConfiguration\Internal;

use Centreon\Internal\Datatable\Datasource\CentreonDb;
use Centreon\Internal\Datatable;
use CentreonConfiguration\Repository\CommandRepository;

/**
 * Description of CommandDatatable
 *
 * @author lionel
 */
class CommandDatatable extends Datatable
{
    protected static $dataprovider = '\Centreon\Internal\Datatable\Dataprovider\CentreonDb';
    
    /**
     *
     * @var type 
     */
    protected static $datasource = '\CentreonConfiguration\Models\Command';
    
    /**
     *
     * @var type 
     */
    protected static $rowIdColumn = array('id' => 'command_id', 'name' => 'command_name');
    
    /**
     *
     * @var array 
     */
    protected static $configuration = array(
        'autowidth' => false,
        'order' => array(
            array('command_name', 'asc'),
        ),
        'stateSave' => false,
        'paging' => true,
    );
    
    /**
     *
     * @var array 
     */
    public static $columns = array(
        array (
            'title' => "Id",
            'name' => 'command_id',
            'data' => 'command_id',
            'orderable' => true,
            'searchable' => false,
            'type' => 'string',
            'visible' => false,
            'width' => '5%',
            "className" => 'cell_center',
            "width" => "20px"
        ),
        array (
            'title' => 'Name',
            'name' => 'command_name',
            'data' => 'command_name',
            'orderable' => true,
            'searchable' => true,
            'searchLabel' => 'command',
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'url',
                'parameters' => array(
                    'route' => '/centreon-configuration/command/[i:id]',
                    'routeParams' => array(
                        'id' => '::command_id::'
                    ),
                    'linkName' => '::command_name::'
                )
            )
        ),
        array (
            'title' => 'Command Line',
            'name' => 'command_line',
            'data' => 'command_line',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
        ),
        array (
            'title' => 'Type',
            'name' => 'command_type',
            'data' => 'command_type',
            'orderable' => true,
            'searchable' => true,
            'type' => 'string',
            'visible' => true,
            'cast' => array(
                'type' => 'select',
                'parameters' => array(
                    '1' => '<span class="label label-info">Notifications</span>',
                    '2' => '<span class="label label-info">Check</span>',
                    '3' => '<span class="label label-info">Miscelleanous</span>',
                    '4' => '<span class="label label-info">Discovery</span>',
                )
            ),
            'searchParam' => array(
                'type' => 'select',
                'additionnalParams' => array(
                    'Notifications' => '1',
                    'Check' => '2',
                    'Miscelleanous' => '3',
                    'Discovery' => '4',
                )
            ),
            "className" => 'cell_center',
            'width' => "40px"
            
        ),
        array (
            'title' => 'Host use',
            'name' => 'NULL AS host_use',
            'data' => 'host_use',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            "className" => 'cell_center',
            'width' => "50px"
        ),
        array (
            'title' => 'Service use',
            'name' => 'NULL AS svc_use',
            'data' => 'svc_use',
            'orderable' => false,
            'searchable' => false,
            'type' => 'string',
            'visible' => true,
            "className" => 'cell_center',
            'width' => "50px"
        ),
    );
    
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
    public function formatDatas(&$resultSet)
    {
        foreach ($resultSet as $key => &$myCmdSet) {
            // @todo remove virtual hosts and virtual services
            if ($myCmdSet['command_name'] === 'check_bam_fake') {
                unset($resultSet[$key]);
                continue;
            }

            $myCmdSet['command_line'] = sprintf('%.70s', $myCmdSet['command_line'])."...";
            $myCmdSet['host_use'] = CommandRepository::getUseNumber($myCmdSet["command_id"], "host");
            $myCmdSet['svc_use'] =  CommandRepository::getUseNumber($myCmdSet["command_id"], "service");
        }
        $resultSet = array_values($resultSet);
    }
}
