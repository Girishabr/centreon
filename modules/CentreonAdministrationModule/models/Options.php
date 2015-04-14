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

namespace CentreonAdministration\Models;

use Centreon\Internal\Di;

/**
 * Description of Options
 *
 * @author lionel
 */
class Options
{
    /**
     * 
     * @return type
     */
    public static function getOptionsKeysList()
    {
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->query("SELECT `key` FROM `cfg_options`");
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $finalList= array();
        foreach ($list as $currentOpt) {
            $finalList[] = $currentOpt['key'];
        }
        
        return $finalList;
    }
    
    /**
     * 
     * @param type $group
     * @param array $options
     * @return type
     */
    public static function getList($group = null, array $options = array())
    {
        $db = Di::getDefault()->get('db_centreon');
        
        $conditions = "";
        if (!is_null($group)) {
            $conditions .= "WHERE `group` = '$group'";
        }
        
        if (count($options) > 0) {
            $listOfOptionKeys = "";
            foreach ($options as $optionKey) {
                $listOfOptionKeys .= "'$optionKey',";
            }
            
            if (empty($conditions)) {
                $conditions .= "WHERE ";
            } else {
                $conditions .= "AND ";
            }
            $conditions .= '`key` IN (' . rtrim($listOfOptionKeys, ',') . ')';
        }
        
        $stmt = $db->query("SELECT `key`, `value` FROM `cfg_options` $conditions");
        
        $savedOptions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $optionsList = array();
        foreach ($savedOptions as $savedOption) {
            $optionsList[$savedOption['key']] = $savedOption['value'];
        }
        return $optionsList;
    }
    
    /**
     * 
     * @param type $values
     */
    public static function update($values)
    {
        $db = Di::getDefault()->get('db_centreon');
        
        foreach ($values as $key => $value) {
            $sql = "UPDATE `cfg_options` SET `value`='$value' WHERE `key`='$key'";
            $db->exec($sql);
        }
    }
    
    /**
     * 
     * @param type $values
     * @param type $group
     */
    public static function insert($values, $group = "default")
    {
        $db = Di::getDefault()->get('db_centreon');
        
        foreach ($values as $key => $value) {
            $sql = "INSERT INTO `cfg_options`(`group`, `key`, `value`) VALUES('$group', '$key', '$value');";
            $db->exec($sql);
        }
    }
}
