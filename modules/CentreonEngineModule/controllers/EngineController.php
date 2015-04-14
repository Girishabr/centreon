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

namespace CentreonEngine\Controllers;

use Centreon\Internal\Form;
use Centreon\Internal\Di;
use Centreon\Controllers\FormController;

/**
 * Description of EngineController
 *
 * @author lionel
 */
class EngineController extends FormController
{
    public static $moduleShortName = 'centreon-engine';
    protected $objectDisplayName = 'Engine';
    public static $objectName = 'engine';
    protected $objectBaseUrl = '/centreon-engine';
    protected $objectClass = '\CentreonEngine\Models\Engine';
    protected $datatableObject = '\CentreonEngine\Internal\EngineDatatable';
    protected $repository = '\CentreonEngine\Repository\EngineRepository';
    public static $moduleName = 'CentreonEngine';
    public static $relationMap = array();
    
    /**
     * Update a engine
     *
     *
     * @method post
     * @route /update
     */
    public function updateAction()
    {
        $givenParameters = clone $this->getParams('post');
        $updateSuccessful = true;
        $updateErrorMessage = '';
        
        $validationResult = Form::validate("form", $this->getUri(), static::$moduleName, $givenParameters);
        if ($validationResult['success']) {
            $repository = $this->repository;
            try {
                $repository::save($givenParameters['object_id'], $givenParameters);
            } catch (Exception $e) {
                $updateSuccessful = false;
                $updateErrorMessage = $e->getMessage();
            }
        } else {
            $updateSuccessful = false;
            $updateErrorMessage = $validationResult['error'];
        }
        
        $router = Di::getDefault()->get('router');
        if ($updateSuccessful) {
            unset($_SESSION['form_token']);
            unset($_SESSION['form_token_time']);
            $router->response()->json(array('success' => true));
        } else {
            $router->response()->json(array('success' => false,'error' => $updateErrorMessage));
        }
    }
    
    /**
     * Update a engine
     *
     *
     * @method get
     * @route /[i:id]
     */
    public function editAction()
    {
        $requestParam = $this->getParams('named');
        $additionnalParams = array(
            'formRedirect'=> true,
            'formRedirectRoute' => '/centreon-configuration/poller/' . $requestParam['id']
        );
        parent::editAction($additionnalParams);
    }
    
    /**
     *
     * @method get
     * @route /[i:id]/globalhosteventhandler
     */
    public function globalHostEventHandlerForEngineAction()
    {
        parent::getSimpleRelation('global_host_event_handler', '\CentreonConfiguration\Models\Command');
    }
    
    /**
     *
     * @method get
     * @route /[i:id]/globalserviceeventhandler
     */
    public function globalServiceEventHandlerForEngineAction()
    {
        parent::getSimpleRelation('global_service_event_handler', '\CentreonConfiguration\Models\Command');
    }
    
    /**
     *
     * @method get
     * @route /[i:id]/ochpcommand
     */
    public function ochpCommandForEngineAction()
    {
        parent::getSimpleRelation('ochp_command', '\CentreonConfiguration\Models\Command');
    }
    
    /**
     *
     * @method get
     * @route /[i:id]/ocspcommand
     */
    public function ocspCommandForEngineAction()
    {
        parent::getSimpleRelation('ocsp_command', '\CentreonConfiguration\Models\Command');
    }
}
