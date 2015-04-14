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

namespace CentreonAdministration\Repository;

use CentreonAdministration\Models\Options;
use Centreon\Internal\Form\Validators\Validator;

/**
 * @author Lionel Assepo <lassepo@centreon.com>
 * @package Centreon
 * @subpackage Repository
 */
class OptionRepository
{
    /**
     * 
     * @param type $givenParameters
     * @param type $origin
     * @param type $route
     */
    protected static function validateForm($givenParameters, $origin = "", $route = "")
    {
        $formValidator = new Validator($origin, array('route' => $route, 'params' => array(), 'version' => '3.0.0'));
        $formValidator->validate($givenParameters->all());
    }
    
    /**
     * 
     * @param type $submittedValues
     * @param type $group
     */
    public static function update($submittedValues, $group = "default", $origin = "", $route = "")
    {
        self::validateForm($submittedValues, $origin, $route);
        $currentOptionsList = Options::getOptionsKeysList();

        $optionsToSave = array();
        $optionsToUpdate = array();

        foreach ($submittedValues as $key => $value) {
            if (in_array($key, $currentOptionsList)) {
                $optionsToUpdate[$key]= $value;
            } else {
                $optionsToSave[$key]= $value;
            }
        }

        Options::update($optionsToUpdate);
        Options::insert($optionsToSave, $group);
    }
    
    /**
     * 
     * @param type $group
     * @param array $options
     * @return array
     */
    public static function get($group = null, array $options = array())
    {
        $listOfOptions = Options::getList($group, $options);
        return $listOfOptions;
    }
    
    
}
