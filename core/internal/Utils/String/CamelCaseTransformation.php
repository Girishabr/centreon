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

namespace Centreon\Internal\Utils\String;

/**
 * Utils for CamelCase strings
 *
 * @author Lionel Assepo
 * @version 3.0.0
 * @package Centreon
 * @subpackage Core
 */
class CamelCaseTransformation
{
    /**
     * 
     */
    const REGEX = '/((?:^|[A-Z])[a-z]+)/';
    
    /**
     * 
     * @param string $string
     * @param string $separator
     * @return string
     */
    public static function camelCaseToCustom($string, $separator = "")
    {
        $matches = array();
        preg_match_all(self::REGEX, $string, $matches);
        return implode($separator, $matches[0]);
    }
    
    /**
     * 
     * @param string $string
     * @param string $separator
     * @return string
     */
    public static function customToCamelCase($string, $separator = "")
    {
        $stringExploded = ucwords(implode(' ', explode($separator, $string)));
        return str_replace(' ', '', $stringExploded);
    }
    
    /**
     * 
     * @param string $string
     * @return boolean
     */
    public static function isCamelCase($string)
    {
        $isCamelCase = false;
        if (preg_match(self::REGEX, $string) === 1) {
            $isCamelCase = true;
        }
        return $isCamelCase;
    }
}
