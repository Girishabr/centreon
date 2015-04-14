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
 *
 */

namespace Centreon\Internal;

use Centreon\Internal\Di;

abstract class Controller extends HttpCore
{
    /**
     *
     * @var type 
     */
    protected $tpl;

    /**
     * 
     */
    protected function __construct($request)
    {
        parent::__construct($request);
        $this->tpl = Di::getDefault()->get('template');
        $this->init();
    }
    
    /**
     * 
     * @param string $varname
     * @param mixed $value
     */
    protected function assignVarToTpl($varname, $value)
    {
        $this->tpl->assign($varname, $value);
    }
    
    /**
     * 
     * @param string $cssFile
     * @param string $origin
     */
    protected function addCssToTpl($cssFile, $origin = 'current')
    {
        $this->tpl->addCss($cssFile);
    }
    
    /**
     * 
     * @param string $jsFile
     * @param string $origin
     */
    protected function addJsToTpl($jsFile, $origin = 'current')
    {
        $this->tpl->addJs($jsFile);
    }
    
    /**
     * 
     * @param string $tplFile
     */
    protected function display($tplFile)
    {
        $tplDirectory = 'file:['. static::$moduleName . 'Module]';
        $this->tpl->display($tplDirectory . $tplFile);
    }
    
    /**
     *
     */
    protected function init()
    {
        $md5Email = "";
        if (isset($_SESSION['user'])) {
            try {
                $md5Email = md5($_SESSION['user']->getEmail());
            } catch (Exception $e) {
                ;
            }
        }
        /*
         * Set md5Email for Gravatar
         */
        $this->tpl->assign("md5Email", $md5Email);
    }
}
