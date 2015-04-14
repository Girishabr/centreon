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

namespace CentreonConfiguration\Models\Relation\Servicetemplate;

use Centreon\Internal\Di;
use Centreon\Models\CentreonRelationModel;

class Icon extends CentreonRelationModel
{
    protected static $relationTable = "cfg_services_images_relations";
    protected static $firstKey = "service_id";
    protected static $secondKey = "service_tpl_id";
    public static $firstObject = "\CentreonConfiguration\Models\Servicetemplate";
    public static $secondObject = "\CentreonConfiguration\Models\Servicetemplate";
    
    /**
     * 
     * @param int $fkey
     * @param int $skey
     */
    public static function insert($fkey, $skey = null)
    {
        if (isset($skey) && is_numeric($skey)) {
            $sql = 'INSERT INTO cfg_services_images_relations(service_id, binary_id) VALUES(?, ?)';
            $db = Di::getDefault()->get('db_centreon');
            $stmt = $db->prepare($sql);
            $stmt->execute(array($fkey, $skey));
        }
    }
    
    /**
     * 
     * @param int $fkey
     * @param int $skey
     */
    public static function delete($fkey, $skey = null)
    {
        $sql = 'DELETE FROM cfg_services_images_relations WHERE service_id = ?';
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare($sql);
        $stmt->execute(array($fkey));
    }
    
    /**
     * 
     * @param int $hostId
     * @param int $limit
     * @return array
     */
    public static function getIconForService($hostId, $limit = 1)
    {
        $sql = "SELECT b.binary_id, b.filename FROM cfg_binaries b, cfg_services_images_relations hir "
            . "WHERE hir.service_id = ? "
            . "AND filetype = 1 "
            . "AND hir.binary_id = b.binary_id "
            . "LIMIT $limit";
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare($sql);
        $stmt->execute(array($hostId));
        $rawIconList = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $finalIconList = array();
        
        if (($limit == 1) && isset($rawIconList[0])) {
            $finalIconList = $rawIconList[0];
        } elseif (count($rawIconList) > 0) {
            $finalIconList = $rawIconList;
        }
        
        return $finalIconList;
    }
}
