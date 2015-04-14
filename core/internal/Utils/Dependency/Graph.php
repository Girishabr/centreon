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

namespace Centreon\Internal\Utils\Dependency;

/**
 * Graph Dependency class
 *
 * @author Lionel Assepo
 * @version 3.0.0
 * @package Centreon
 * @subpackage Core
 */
class Graph
{
    /**
     *
     * @var array 
     */
    private $nodes = array();
    
    /**
     * 
     */
    public function __construct()
    {
        ;
    }
    
    /**
     * 
     * @param string $nodeName
     * @param array $dependencies
     */
    public function addNode($nodeName, $dependencies = array())
    {
        $myNode = new Graph\Node($nodeName);
        
        foreach($dependencies as $dependency) {
            $depNode = new Graph\Node($dependency['name']);
            $myNode->addEdge($depNode);
        }
        
        $this->nodes[$nodeName] = $myNode;
    }
    
    /**
     * 
     * @param string $nodeName
     * @return \Centreon\Internal\Utils\Dependency\Graph\Node
     */
    public function getNode($nodeName)
    {
        return $this->nodes[$nodeName];
    }


    /**
     * 
     * @param string $nodeName
     */
    public function removeNode($nodeName)
    {
        unset($this->nodes[$nodeName]);
    }
    
    /**
     * 
     * @param string $nodeName
     * @param array $resolved
     * @param array $seen
     * @throws Exception
     */
    public function resolve($nodeName, array &$resolved, array &$seen)
    {
        $myNode = $this->getNode($nodeName);
        $nodeEdges = $myNode->getEdges();
        $seen[] = $nodeName;
        
        foreach ($nodeEdges as $nodeEdge) {
            $edgeName = $nodeEdge->getName();
            if (!in_array($edgeName, $resolved)) {
                if (in_array($edgeName, $seen)) {
                    throw new Exception(sprintf("Circular reference detected %s -> %s", $nodeName, $edgeName));
                }
                $this->resolve($edgeName, $resolved, $seen);
            }
        }
        
        if (!in_array($nodeName, $resolved)) {
            $resolved[] = $nodeName;
            unset($seen[$nodeName]);
        }
    }
}
