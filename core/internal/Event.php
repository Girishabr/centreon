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
namespace Centreon\Internal;

use Centreon\Internal\Module\Informations;
use Centreon\Internal\Utils\Filesystem\File;
use Centreon\Internal\Utils\String\CamelCaseTransformation;

/**
 * Description of Event
 *
 * @author lionel
 */
class Event
{
    /**
     * Init event listeners of modules
     */
    public static function initEventListeners()
    {
        $moduleList = Informations::getModuleList();
        foreach ($moduleList as $module) {
            $listenersPath = Informations::getModulePath($module) . '/listeners/';
            if (file_exists($listenersPath)) {
                $ModuleListenersList = glob($listenersPath . '*');
                foreach ($ModuleListenersList as $moduleListenersPath) {   
                    $mTarget = substr($moduleListenersPath, strlen($listenersPath));
                    $mSource = CamelCaseTransformation::customToCamelCase($module, '-');
                    self::attachModuleEventListeners($mSource, $mTarget, $moduleListenersPath);
                }
            }
        }
    }
    
    /**
     * 
     * @param type $moduleName
     * @param type $moduleListenersPath
     */
    private static function attachModuleEventListeners($moduleSource, $moduleTarget, $moduleListenersPath)
    {
        $emitter = Di::getDefault()->get('events');
        $myListeners = File::getFiles($moduleListenersPath, 'php');

        foreach ($myListeners as $myListener) {
            $listener = (basename($myListener, '.php'));
            
            $eventName = CamelCaseTransformation::camelCaseToCustom($moduleTarget, '-')
                . '.'
                . CamelCaseTransformation::camelCaseToCustom($listener, '.');
            $emitter->on(
                strtolower($eventName),
                function ($params) use ($listener, $moduleSource, $moduleTarget) {
                    call_user_func(
                        array($moduleSource . "\\Listeners\\".$moduleTarget."\\".$listener, "execute"),
                        $params
                    );
                }
            );
        }
    }
}
