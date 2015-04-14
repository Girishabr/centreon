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

namespace Centreon\Internal\Install;

use Centreon\Internal\Utils\Filesystem\File;
use Centreon\Internal\Di;
use Centreon\Custom\Propel\CentreonMysqlPlatform; 
use Centreon\Internal\Module\Informations;

class Db
{
    /**
     * 
     * @param type $operation
     * @param type $targetDbName
     */
    public static function update($targetDbName = 'centreon')
    {
        ini_set('memory_limit', '-1');
        $di = Di::getDefault();
        $config = $di->get('config');
        
        $targetDb = 'db_centreon';
        $db = $di->get($targetDb);
        
        // Configuration for Propel
        $configParams = array(
            'propel.project' => 'centreon',
            'propel.database' => 'mysql',
            'propel.database.url' => $config->get($targetDb, 'dsn'),
            'propel.database.user' => $config->get($targetDb, 'username'),
            'propel.database.password' => $config->get($targetDb, 'password')
        );
        
        // Set the Current Platform and DB Connection
        $platform = new CentreonMysqlPlatform($db);
        
        // Initilize Schema Parser
        $propelDb = new \MysqlSchemaParser($db);
        $propelDb->setGeneratorConfig(new \GeneratorConfig($configParams));
        $propelDb->setPlatform($platform);
        
        // get Current Db State
        $currentDbAppData = new \AppData($platform);
        $currentDbAppData->setGeneratorConfig(new \GeneratorConfig($configParams));
        $currentDb = $currentDbAppData->addDatabase(array('name' => $targetDbName));
        $propelDb->parse($currentDb);
        
        // Retreive target DB State
        $updatedAppData = new \AppData($platform);
        self::getDbFromXml($updatedAppData, 'centreon');
        
        // Get diff between current db state and target db state
        $diff = \PropelDatabaseComparator::computeDiff(
            $currentDb,
            $updatedAppData->getDatabase('centreon'),
            false
        );
        $strDiff = $platform->getModifyDatabaseDDL($diff);
        file_put_contents("/tmp/installSqlLog.sql", $strDiff);
        //$sqlToBeExecuted = \PropelSQLParser::parseString($strDiff);
        //unlink("/tmp/installSqlLog.sql");
        
        // Loading Modules Pre Update Operations
        self::preUpdate();
        
        // to sent to verify
        //$tablesToBeDropped = self::getTablesToBeRemoved($sqlToBeExecuted);
        
        // Perform Update
        \PropelSQLParser::executeString($strDiff, $db);
        
        // Loading Modules Post Update Operations
        self::postUpdate();
        
        // Empty Target DB
        self::deleteTargetDbSchema($targetDbName);
    }
    
    /**
     * 
     * @param \AppData $myAppData
     * @param string $targetDbName
     */
    public static function getDbFromXml(& $myAppData, $targetDbName)
    {
        $db = self::getDbConnector($targetDbName);
        
        $xmlDbFiles = self::buildTargetDbSchema($targetDbName);
        
        // Initialize XmlToAppData object
        $appDataObject = new \XmlToAppData(new CentreonMysqlPlatform($db), null, 'utf-8');
        
        // Get DB File
        foreach ($xmlDbFiles as $dbFile) {
            $myAppData->joinAppDatas(array($appDataObject->parseFile($dbFile)));
            unset($appDataObject);
            $appDataObject = new \XmlToAppData(new CentreonMysqlPlatform($db), null, 'utf-8');
        }
        
        unset($appDataObject);
    }
    
    /**
     * 
     * @param array $sqlStatements
     * @return array
     */
    public static function getTablesToBeRemoved($sqlStatements)
    {
        $tablesToBeRemoved = array();
        
        foreach ($sqlStatements as $statement) {
            if (strpos($statement, "DROP TABLE IF EXISTS") !== false) {
                $tablesToBeRemoved[] = trim(substr($statement, strlen("DROP TABLE IF EXISTS")));
            }
        }
        
        return $tablesToBeRemoved;
    }
    
    private static function deleteTargetDbSchema($targetDbName = 'centreon')
    {
        // Initialize configuration
        $di = Di::getDefault();
        $config = $di->get('config');
        $centreonPath = $config->get('global', 'centreon_path');
        
        $targetFolder = $centreonPath . '/tmp/db/target/' . $targetDbName . '/';
        $currentFolder = $centreonPath . '/tmp/db/current/' . $targetDbName . '/';
        
        // Copy to destination
        if (!file_exists($currentFolder)) {
            mkdir($currentFolder, 0775, true);
            if (posix_getuid() == 0) {
                chown($currentFolder, 'centreon');
                chgrp($currentFolder, 'centreon');
            }
        }
        
        $fileList = glob($targetFolder . '/*.xml');
        $nbOfFiles = count($fileList);
        for ($i=0; $i<$nbOfFiles; $i++) {
            $targetFile = $currentFolder . basename($fileList[$i]);
            copy($fileList[$i], $targetFile);
            if (posix_getuid() == 0) {
                chmod($targetFile, 0664);
                chown($targetFile, 'centreon');
                chgrp($targetFile, 'centreon');
            }
            unlink($fileList[$i]);
        }
        
        self::deleteFolder($targetFolder);
    }

    /**
     *
     * @param string $path
     */
    private static function deleteFolder($path)
    {
        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file) {
                Delete(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        } else if (is_file($path) === true) {
            return unlink($path);
        }

        return false;
    }
    
    /**
     * 
     * @param type $targetDbName
     */
    private static function buildTargetDbSchema($targetDbName = 'centreon')
    {
        // Initialize configuration
        $di = Di::getDefault();
        $config = $di->get('config');
        $centreonPath = $config->get('global', 'centreon_path');
        $targetFolder = '';
        $tmpFolder = '';
        
        
        $tmpFolder .= trim($config->get('global', 'centreon_generate_tmp_dir'));
        if (!empty($tmpFolder)) {
            $targetFolder .= $tmpFolder . '/centreon/db/target/' . $targetDbName . '/';
        } else {
            $targetFolder .= $centreonPath . '/tmp/db/target/' . $targetDbName . '/';
        }
        
        $fileList = array();
        
        // Mandatory tables
        $fileList = array_merge(
            $fileList,
            File::getFiles($centreonPath . '/install/db/' . $targetDbName, 'xml')
        );
        
        $moduleList = Informations::getModuleList(false);
        foreach ($moduleList as $module) {
            $expModuleName = array_map(function ($n) { return ucfirst($n); }, explode('-', $module));
            $moduleFileSystemName = implode("", $expModuleName) . 'Module';
            $fileList = array_merge(
                $fileList,
                File::getFiles(
                    $centreonPath . '/modules/' . $moduleFileSystemName . '/install/db/' . $targetDbName, 'xml'
                )
            );
        }
        
        // Copy to destination
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder, 0777, true);
        }
        
        $nbOfFiles = count($fileList);
        for ($i=0; $i<$nbOfFiles; $i++) {
            $targetFile = $targetFolder . basename($fileList[$i]);
            copy($fileList[$i], $targetFile);
        }
        
        // send back the computed db
        return glob($targetFolder . '/*.xml');
    }
    
    /**
     * 
     * @param string $dirname
     * @param string $targetDbName
     */
    public static function loadDefaultDatas($dirname, $targetDbName = 'centreon')
    {
        $dirname = rtrim($dirname, '/');
        
        $orderFile = $dirname . '/' . $targetDbName . '.json';

        $db = self::getDbConnector($targetDbName);
        $db->beginTransaction();
        if (file_exists($orderFile)) {
            $insertionOrder = json_decode(file_get_contents($orderFile), true);
            foreach ($insertionOrder as $fileBaseName) {
                $datasFile = $dirname . '/' . $targetDbName . '/'. $fileBaseName . '.json';
                self::insertDatas($datasFile, $targetDbName);
            }
        } else {
            $datasFiles = File::getFiles($dirname, 'json');
            foreach ($datasFiles as $datasFile) {
                self::insertDatas($datasFile, $targetDbName);
            }
        }
        $db->commit();
    }
    
    /**
     * 
     * @param string $datasFile
     * @param string $targetDbName
     */
    private static function insertDatas($datasFile, $targetDbName)
    {
        ini_set('memory_limit', '-1');
        $db = self::getDbConnector($targetDbName);
        
        if (file_exists($datasFile)) {
            $tableName = basename($datasFile, '.json');
            $datas = json_decode(file_get_contents($datasFile), true);
            
            foreach ($datas as $data) {
                $fields = "";
                $values = "";
                foreach ($data as $key=>$value) {
                    $fields .= "`$key`,";
                    
                    if (is_array($value)) {
                        if ($value['domain'] == 'php') {
                            $values .= $db->quote($value['function']()) . ",";
                        } else {
                            $values .= "$value[function](),";
                        }
                    } else {
                        $values .= $db->quote($value) . ",";
                    }
                }
                $insertQuery = "INSERT INTO `$tableName` (". rtrim($fields, ',') .") VALUES (" . rtrim($values, ',') . ") ";
                $db->query($insertQuery);
            }
        }
    }
    
    private static function getDbConnector($dbName)
    {
        $di = Di::getDefault();
        if ($dbName == 'centreon_storage') {
            $targetDb = 'db_storage';
        } else {
            $targetDb = 'db_' . $dbName;
        }
        $db = $di->get($targetDb);
        
        return $db;
    }
    
    /**
     * 
     */
    private static function preUpdate()
    {
        
    }
    
    /**
     * 
     */
    private static function postUpdate()
    {
        
    }
}
