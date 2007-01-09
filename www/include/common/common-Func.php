<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	if (file_exists("./include/common/common-Func-ACL.php"))
		include_once("./include/common/common-Func-ACL.php");

	function myDecode($arg)	{
		return html_entity_decode($arg, ENT_QUOTES);
	}

	#
	## SMARTY
	#
	
	function initSmartyTpl($path = NULL, $tpl = NULL, $subDir = NULL)	{
		if (!$tpl)
			return;
		$tpl->template_dir = $path . $subDir;
		$tpl->compile_dir = "../GPL_LIB/SmartyCache/compile";
		$tpl->config_dir = "../GPL_LIB/SmartyCache/config";
		$tpl->cache_dir = "../GPL_LIB/SmartyCache/cache";

		$tpl->caching = 0;
		$tpl->compile_check = true;
		$tpl->force_compile = true;
		return $tpl;
	}

	function initSmartyTplForLogs($path = NULL, $tpl = NULL)	{
		if (!$tpl)
			return;
		$tpl->template_dir = $path;
		$tpl->compile_dir = "../../../GPL_LIB/SmartyCache/compile";
		$tpl->config_dir = "../../../GPL_LIB/SmartyCache/config";
		$tpl->cache_dir = "../../../GPL_LIB/SmartyCache/cache";

		$tpl->caching = 0;
		$tpl->compile_check = true;
		$tpl->force_compile = true;
		return $tpl;
	}

	#
	## HOST
	#
	
	function getMyHostTemplateModel($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$row =& $DBRESULT->fetchRow();
		if ($row["host_template_model_htm_id"])
			return $row["host_template_model_htm_id"];
		else
			NULL;
	}
	
	function getMyHostName($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT host_name, host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row =& $DBRESULT->fetchRow();
			if ($row["host_name"])
				return $row["host_name"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else
				break;
		}
	}
	
	function isAHostTpl($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT host_register FROM host WHERE host_id = '".$host_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$row =& $DBRESULT->fetchRow();
		if ($row["host_register"])
			return true;
		else
			return false;
	}

	function getMyHostAddress($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT host_address, host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row =& $DBRESULT->fetchRow();
			if ($row["host_address"])
				return $row["host_address"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else
				break;
		}
	}

	function getMyHostAddressByName($host_name = NULL)	{
		if (!$host_name) return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT host_address, host_template_model_htm_id FROM host WHERE host_name = '".$host_name."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row =& $DBRESULT->fetchRow();
			if ($row["host_address"])
				return $row["host_address"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else
				break;
		}
	}

	function getMyHostParents($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT host_template_model_htm_id AS tpl FROM host WHERE host_id = '".$host_id."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$host = clone($DBRESULT->fetchRow());
			$DBRESULT =& $pearDB->query("SELECT hpr.host_parent_hp_id FROM host_hostparent_relation hpr WHERE hpr.host_host_id = '".$host_id."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			if ($DBRESULT->numRows())
				return $DBRESULT;
			else if (isset($host["tpl"]) && $host["tpl"])
				$host_id = $host["tpl"];
			else
				return $DBRESULT;
		}
	}	

	function getMyHostGroups($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		$hgs = array();
		$DBRESULT =& $pearDB->query("SELECT hg.hg_name, hgr.hostgroup_hg_id FROM hostgroup hg, hostgroup_relation hgr WHERE hgr.host_host_id = '".$host_id."' AND hgr.hostgroup_hg_id = hg.hg_id");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		while ($DBRESULT->fetchInto($hg))
			$hgs[$hg["hostgroup_hg_id"]] = $hg["hg_name"];
		return $hgs;
	}
	
	// Search community in Host, Host Tpl, Hostgroup, General Options
	function getMySnmpCommunity($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		$host_id_bkp = $host_id;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT host_snmp_community, host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row =& $DBRESULT->fetchRow();
			if ($row["host_snmp_community"])
				return $row["host_snmp_community"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else	{
				$hgs = getMyHostGroups($host_id_bkp);
				foreach ($hgs as $key=>$value)	{
					$DBRESULT =& $pearDB->query("SELECT hg_snmp_community FROM hostgroup WHERE hg_id = '".$key."' LIMIT 1");
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
					$row =& $DBRESULT->fetchRow();
					if ($row["hg_snmp_community"])
						return $row["hg_snmp_community"];
				}
				$DBRESULT =& $pearDB->query("SELECT snmp_community FROM general_opt LIMIT 1");
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
				$row =& $DBRESULT->fetchRow();
				if (isset($row["snmp_community"]))
					return $row["snmp_community"];
				return NULL;
			}
		}
		return NULL;
	}

	// Search version in Host, Host Tpl, Hostgroup, General Options
	function getMySnmpVersion($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		$host_id_bkp = $host_id;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT host_snmp_version, host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row =& $DBRESULT->fetchRow();
			if ($row["host_snmp_version"])
				return $row["host_snmp_version"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else	{
				$hgs = getMyHostGroups($host_id_bkp);
				foreach ($hgs as $key=>$value)	{
					$DBRESULT =& $pearDB->query("SELECT hg_snmp_version FROM hostgroup WHERE hg_id = '".$key."' LIMIT 1");
					if (PEAR::isError($DBRESULT))
						print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
					$row =& $DBRESULT->fetchRow();
					if ($row["hg_snmp_version"])
						return $row["hg_snmp_version"];
				}
				$DBRESULT =& $pearDB->query("SELECT snmp_version FROM general_opt LIMIT 1");
				if (PEAR::isError($DBRESULT))
					print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
				$row =& $DBRESULT->fetchRow();
				if (isset($row["snmp_version"]))
					return $row["snmp_version"];
				else
					break;
				break;
			}
		}
		return NULL;
	}
	
	function getMyHostField($host_id = NULL, $field)	{
		if (!$host_id) return;
		global $pearDB;
		while(1)	{
			$res =& $pearDB->query("SELECT ".$field.", host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			if (PEAR::isError($pearDB))
				print "Mysql Error : ".$pearDB->getMessage();
			$row =& $res->fetchRow();
			$field_result = $row[$field];
			if ($row[$field])
				return $row[$field];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else
				break;
		}
	}
	function getMyHostExtendedInfoField($host_id = NULL, $field)	{
		if (!$host_id) return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT ehi.".$field.", h.host_template_model_htm_id FROM host h, extended_host_information ehi WHERE ehi.host_host_id = '".$host_id."' AND h.host_id = '".$host_id."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row =& $DBRESULT->fetchRow();
			$field_result = $row[$field];
			if ($row[$field])
				return $row[$field];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else
				break;
		}
	}
	
	#
	## HOST GROUP
	#
	
	function getMyHostGroupName($hg_id = NULL)	{
		if (!$hg_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT hg_name FROM hostgroup WHERE hg_id = '".$hg_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$row =& $DBRESULT->fetchRow();
		if ($row["hg_name"])
			return $row["hg_name"];
		return NULL;
	}
	
	function getMyHostGroupHosts($hg_id = NULL)	{
		if (!$hg_id) return;
		global $pearDB;
		$hosts = array();
		$DBRESULT =& $pearDB->query("SELECT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$hg_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT)) 
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		while ($DBRESULT->fetchInto($elem))
			$hosts[$elem["host_host_id"]] = $elem["host_host_id"];
		$DBRESULT->free();
		return $hosts;
	}
	
	function getMyHostGroupCommunity($hg_id = NULL)	{
		if (!$hg_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT hg_snmp_community FROM hostgroup WHERE hg_id = '".$hg_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$row =& $DBRESULT->fetchRow();
		if ($row["hg_snmp_community"])
			return $row["hg_snmp_community"];
		return NULL;
	}
	
	function getMyHostGroupVersion($hg_id = NULL)	{
		if (!$hg_id) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT hg_snmp_version FROM hostgroup WHERE hg_id = '".$hg_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$row =& $DBRESULT->fetchRow();
		if ($row["hg_snmp_version"])
			return $row["hg_snmp_version"];
		return NULL;
	}
	
	#
	## SERVICE
	#

	function getMyServiceField($service_id = NULL, $field)	{
		if (!$service_id) return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT ".$field.", service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row =& $DBRESULT->fetchRow();
			$field_result = $row[$field];
			if ($row[$field])
				return $row[$field];
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				break;
		}
	}

	function getMyServiceExtendedInfoField($service_id = NULL, $field)	{
		if (!$service_id) return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT esi.".$field.", sv.service_template_model_stm_id FROM service sv, extended_service_information esi WHERE esi.service_service_id = '".$service_id."' AND sv.service_id = '".$service_id."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row =& $DBRESULT->fetchRow();
			$field_result = $row[$field];
			if ($row[$field])
				return $row[$field];
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				break;
		}
	}

	function getMyServiceName($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT service_description, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row =& $DBRESULT->fetchRow();
			if ($row["service_description"])
				return $row["service_description"];
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				break;
		}
	}

	function getMyServiceAlias($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT service_alias, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row =& $DBRESULT->fetchRow();
			if ($row["service_alias"])
				return $row["service_alias"];
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				break;
		}
	}

	function getMyServiceGraphID($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT esi.graph_id, service_template_model_stm_id FROM service, extended_service_information esi WHERE service_id = '".$service_id."' AND esi.service_service_id = service_id LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row =& $DBRESULT->fetchRow();
			if ($row["graph_id"])
				return $row["graph_id"];
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				break;
		}
		return NULL;
	}

	function getMyServiceID($service_description = NULL, $host_id = NULL, $hg_id = NULL)	{
		if (!$service_description && (!$host_id || !$hg_id)) return;
		global $pearDB;
		if ($host_id)	{
			$DBRESULT =& $pearDB->query(	"SELECT service_id FROM service, host_service_relation hsr " .
									"WHERE hsr.host_host_id = '".$host_id."' AND hsr.service_service_id = service_id " .
									"AND service_description = '".$service_description."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row =& $DBRESULT->fetchRow();
			# Service is directely link to a host, no problem
			if ($row["service_id"])
				return $row["service_id"];
			# The Service might be link with a HostGroup
			$DBRESULT =& $pearDB->query(	"SELECT service_id FROM hostgroup_relation hgr, service, host_service_relation hsr" .
									" WHERE hgr.host_host_id = '".$host_id."' AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id" .
									" AND service_id = hsr.service_service_id AND service_description = '".$service_description."'");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row =& $DBRESULT->fetchRow();
			if ($row["service_id"])
				return $row["service_id"];
		}
		if ($hg_id)	{
			$DBRESULT =& $pearDB->query("SELECT service_id FROM service, host_service_relation hsr WHERE hsr.hostgroup_hg_id = '".$hg_id."' AND hsr.service_service_id = service_id AND service_description = '".$service_description."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row =& $DBRESULT->fetchRow();
			if ($row["service_id"])
				return $row["service_id"];
		}
		return NULL;
	}

	function getMyHostServices($host_id = NULL)	{
		if (!$host_id) return;
		global $pearDB;
		$hSvs = array();
		$DBRESULT =& $pearDB->query("SELECT service_id, service_description FROM service, host_service_relation hsr WHERE hsr.host_host_id = '".$host_id."' AND hsr.service_service_id = service_id");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		while ($DBRESULT->fetchInto($elem))
			$hSvs[$elem["service_id"]]	= $elem["service_description"];
		$DBRESULT->free();
		$DBRESULT =& $pearDB->query("SELECT service_id, service_description FROM hostgroup_relation hgr, service, host_service_relation hsr" .
				" WHERE hgr.host_host_id = '".$host_id."' AND hsr.hostgroup_hg_id = hgr.hostgroup_hg_id" .
				" AND service_id = hsr.service_service_id");
		if (PEAR::isError($DBRESULT)) 
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		while ($DBRESULT->fetchInto($elem))
			$hSvs[$elem["service_id"]]	= $elem["service_description"];
		$DBRESULT->free();
		return $hSvs;
	}

	function getAllMyServiceHosts($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		$hosts = array();
		$DBRESULT =& $pearDB->query("SELECT host_host_id, hostgroup_hg_id FROM host_service_relation hsr WHERE hsr.service_service_id = '".$service_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		while ($DBRESULT->fetchInto($elem))	{
			if ($elem["host_host_id"])
				$hosts[$elem["host_host_id"]] = $elem["host_host_id"];
			else if ($elem["hostgroup_hg_id"]){
				$DBRESULT2 =& $pearDB->query("SELECT host_host_id FROM hostgroup_relation hgr WHERE hgr.hostgroup_hg_id = '".$elem["hostgroup_hg_id"]."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
				while ($DBRESULT2->fetchInto($elem2))
					$hosts[$elem2["host_host_id"]] = $elem2["host_host_id"];
				$DBRESULT2->free();
			}
		}
		$DBRESULT->free();
		return $hosts;
	}

	function getMyServiceHosts($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		$hosts = array();
		$DBRESULT =& $pearDB->query("SELECT DISTINCT host_host_id FROM host_service_relation hsr WHERE hsr.service_service_id = '".$service_id."'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		while ($DBRESULT->fetchInto($elem))
			if ($elem["host_host_id"])
				$hosts[$elem["host_host_id"]] = $elem["host_host_id"];
		$DBRESULT->free();
		return $hosts;
	}

	function getMyServiceHostGroups($service_id = NULL)	{
		if (!$service_id) return;
		global $pearDB;
		$hgs = array();
		$DBRESULT =& $pearDB->query("SELECT DISTINCT hostgroup_hg_id FROM host_service_relation hsr WHERE hsr.service_service_id = '".$service_id."'");
		if (PEAR::isError($DBRESULT)) 
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		while ($DBRESULT->fetchInto($elem))
			if ($elem["hostgroup_hg_id"])
				$hgs[$elem["hostgroup_hg_id"]] = $elem["hostgroup_hg_id"];
		$DBRESULT->free();
		return $hgs;
	}

	function getMyServiceTPLID($service_description = NULL)	{
		if (!$service_description) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT service_id FROM service WHERE service_description = '".htmlentities($service_description, ENT_QUOTES)."' AND service_register = '0' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$row =& $DBRESULT->fetchRow();
		if ($row["service_id"])
			return $row["service_id"];
		return NULL;
	}

	function isACheckGraphService($service_id = NULL)	{
		if (!$service_id)	return;
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT command_command_id, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row =& $DBRESULT->fetchRow();
			if ($row["command_command_id"])	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM command WHERE command_id = '".$row["command_command_id"]."' LIMIT 1");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
				$row2 =& $DBRESULT2->fetchRow();
				if (strstr($row2["command_name"], "check_graph_"))
					return true;
				else
					return false;
			}
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				return NULL;
		}
		return NULL;
	}
	
	#
	## COMMAND
	#
		
	function getMyCheckCmdGraph($service_id = NULL, $host_id = NULL)	{
		if (!$service_id)	return;
		global $pearDB;
		$i = 0;
		$host_id ? $host_id = "!".$host_id."_".$service_id : NULL;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT command_command_id, command_command_id_arg, service_template_model_stm_id FROM service WHERE service_id = '".$service_id."' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$row =& $DBRESULT->fetchRow();
			if ($row["command_command_id"])	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM command WHERE command_id = '".$row["command_command_id"]."' LIMIT 1");
				if (PEAR::isError($DBRESULT2)) 
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br>";
				$row2 =& $DBRESULT2->fetchRow();
				$row["command_command_id_arg"] = str_replace('#BR#', "\\n", $row["command_command_id_arg"]);
				$row["command_command_id_arg"] = str_replace('#T#', "\\t", $row["command_command_id_arg"]);
				$row["command_command_id_arg"] = str_replace('#R#', "\\r", $row["command_command_id_arg"]);
				$row["command_command_id_arg"] = str_replace('#S#', "/", $row["command_command_id_arg"]);
				$row["command_command_id_arg"] = str_replace('#BS#', "\\", $row["command_command_id_arg"]);
				if (strstr($row2["command_name"], "check_graph_"))
					return ($row2["command_name"].$row["command_command_id_arg"].$host_id);
				else if (!$i)
					return ($row2["command_name"].$row["command_command_id_arg"]);
				else
					return NULL;
			}
			else if ($row["service_template_model_stm_id"])
				$service_id = $row["service_template_model_stm_id"];
			else
				return NULL;
			$i++;
		}
		return NULL;
	}

	#
	## Upload conf needs
	#

	function getMyHostID($host_name = NULL)	{
		if (!$host_name) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT host_id FROM host WHERE host_name = '".htmlentities($host_name, ENT_QUOTES)."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["host_id"];
		}
		return NULL;
	}

	function getMyHostGroupID($hostgroup_name = NULL)	{
		if (!$hostgroup_name) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT hg_id FROM hostgroup WHERE hg_name = '".htmlentities($hostgroup_name, ENT_QUOTES)."' LIMIT 1");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["hg_id"];
		}
		return NULL;
	}

	function getMyServiceGroupID($servicegroup_name = NULL)	{
		if (!$servicegroup_name) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT sg_id FROM servicegroup WHERE sg_name = '".htmlentities($servicegroup_name, ENT_QUOTES)."' LIMIT 1");
		if (PEAR::isError($DBRESULT)) 
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["sg_id"];
		}
		return NULL;
	}

	function getMyContactID($contact_name = NULL)	{
		if (!$contact_name) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT contact_id FROM contact WHERE contact_name = '".htmlentities($contact_name, ENT_QUOTES)."' LIMIT 1");
		if (PEAR::isError($DBRESULT)) 
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["contact_id"];
		}
		return NULL;
	}

	function getMyContactGroupID($cg_name = NULL)	{
		if (!$cg_name) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT cg_id FROM contactgroup WHERE cg_name = '".htmlentities($cg_name, ENT_QUOTES)."' LIMIT 1");
		if (PEAR::isError($DBRESULT)) 
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["cg_id"];
		}
		return NULL;
	}

	function getMyCommandID($command_name = NULL)	{
		if (!$command_name) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT command_id FROM command WHERE command_name = '".htmlentities($command_name, ENT_QUOTES)."' LIMIT 1");
		if (PEAR::isError($DBRESULT)) 
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["command_id"];
		}
		return NULL;
	}

	function getMyTPID($tp_name = NULL)	{
		if (!$tp_name) return;
		global $pearDB;
		$DBRESULT =& $pearDB->query("SELECT tp_id FROM timeperiod WHERE tp_name = '".htmlentities($tp_name, ENT_QUOTES)."' LIMIT 1");
		if (PEAR::isError($DBRESULT)) 
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if ($DBRESULT->numRows())	{
			$row =& $DBRESULT->fetchRow();
			return $row["tp_id"];
		}
		return NULL;
	}

	function copyRrdDB($arg = NULL, $new_id = NULL, $host_id = NULL)	{
		if (!$arg || !$new_id || !$host_id) return;
		global $oreon;
		if (is_file($oreon->optGen["oreon_path"]."filesUpload/rrd/".$arg.".rrd"))
			copy($oreon->optGen["oreon_path"]."filesUpload/rrd/".$arg.".rrd", $oreon->optGen["oreon_path"]."rrd/".$host_id."_".$new_id.".rrd");
	}
	
	#
	## GRAPHS	
	#
	
	function getDefaultMetaGraph ($meta_id = NULL)	{
		global $pearDB;		
		$DBRESULT =& $pearDB->query("SELECT graph_id FROM meta_service WHERE meta_id = '".$meta_id."' LIMIT 1");
		if (PEAR::isError($DBRESULT)) 
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$gt =& $DBRESULT->fetchRow();
		if ($gt["graph_id"])
			return $gt["graph_id"];	
		else {
			$DBRESULT =& $pearDB->query("SELECT graph_id FROM giv_graphs_template WHERE default_tpl1 = '1' LIMIT 1");
			if (PEAR::isError($DBRESULT)) 
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			if ($DBRESULT->numRows())	{
				$gt =& $DBRESULT->fetchRow();
				return $gt["graph_id"];
			}
		}
		$DBRESULT =& $pearDB->query("SELECT graph_id FROM giv_graphs_template LIMIT 1");
		if (PEAR::isError($DBRESULT)) 
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if ($DBRESULT->numRows())	{
			$gt =& $DBRESULT->fetchRow();
			return $gt["graph_id"];
		}
		return NULL;
	}
	
	function getDefaultGraph ($service_id = NULL, $rrdType = NULL)	{
		// rrdType = 1 -> Graphs Perfparse
		// rrdType = 2 -> Graphs Plugins
		// rrdType = 3 -> Graphs Customs
		global $pearDB;
		if (!$rrdType)	$rrdType = 1;
		if ($rrdType != 3)	{			
			$gt["graph_id"] = getMyServiceGraphID($service_id);
			if ($gt["graph_id"])
				return $gt["graph_id"];
		}
		else	{
			$DBRESULT =& $pearDB->query("SELECT grapht_graph_id FROM giv_graphs WHERE graph_id = '".$service_id."' LIMIT 1");
			if (PEAR::isError($DBRESULT)) 
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$gt =& $DBRESULT->fetchRow();
			if ($gt["grapht_graph_id"])
				return $gt["grapht_graph_id"];	
		}
		if ($rrdType != 2)	{
			$DBRESULT =& $pearDB->query("SELECT graph_id FROM giv_graphs_template WHERE default_tpl1 = '1' LIMIT 1");
			if (PEAR::isError($DBRESULT)) 
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			if ($DBRESULT->numRows())	{
				$gt =& $DBRESULT->fetchRow();
				return $gt["graph_id"];
			}
		} else {
			$DBRESULT =& $pearDB->query("SELECT graph_id FROM giv_graphs_template WHERE default_tpl2 = '1' LIMIT 1");
			if (PEAR::isError($DBRESULT))  
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			if ($DBRESULT->numRows())	{
				$gt =& $DBRESULT->fetchRow();
				return $gt["graph_id"];
			}
		}
		$DBRESULT =& $pearDB->query("SELECT graph_id FROM giv_graphs_template LIMIT 1");
		if (PEAR::isError($DBRESULT)) 
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if ($DBRESULT->numRows())	{
			$gt =& $DBRESULT->fetchRow();
			return $gt["graph_id"];
		}
		return NULL;
	}
	
	function getDefaultDS ($graph_id = NULL, $current_ds = NULL, $rrdType = NULL)	{
		// rrdType = 1 -> Graphs Perfparse
		// rrdType = 2 -> Graphs Plugins
		// rrdType = 3 -> Graphs Customs
		if (!$graph_id) return NULL;
		global $pearDB;
		if (!$rrdType)	$rrdType = 1;
		$ds = array();
		$DBRESULT =& $pearDB->query("SELECT gct.compo_id FROM giv_components_template gct, giv_graphT_componentT_relation ggcr WHERE ggcr.gg_graph_id = '".$graph_id."' AND ggcr.gc_compo_id = gct.compo_id ORDER BY gct.ds_order");
		if (PEAR::isError($DBRESULT)) 
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$cpt = 0;
		$sum = $DBRESULT->numRows();
		while ($DBRESULT->fetchInto($ds))	{
			if ($current_ds == $cpt)
				return $ds["compo_id"];
			$cpt++;				 
		}
		if ($rrdType != 2)	{
			$DBRESULT =& $pearDB->query("SELECT compo_id FROM giv_components_template WHERE default_tpl1 = '1' LIMIT 1");
			if (PEAR::isError($DBRESULT)) 
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			if ($DBRESULT->numRows())	{
				$ds =& $DBRESULT->fetchRow();
				return $ds["compo_id"];
			}
		}
		else	{
			$DBRESULT =& $pearDB->query("SELECT compo_id FROM giv_components_template WHERE default_tpl2 = '1' LIMIT 1");
			if (PEAR::isError($DBRESULT)) 
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			if ($DBRESULT->numRows())	{
				$ds =& $DBRESULT->fetchRow();
				return $ds["compo_id"];
			}
		}
		$DBRESULT =& $pearDB->query("SELECT compo_id FROM giv_components_template LIMIT 1");
		if (PEAR::isError($DBRESULT)) 
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		if ($DBRESULT->numRows())	{
			$ds =& $DBRESULT->fetchRow();
			return $ds["compo_id"];
		}
		return NULL;
	}

	# Nagios Images	
	function return_image_list($rep = NULL){
		global $oreon;
		$elems = array();
		$images = array(NULL=>NULL);
		$is_not_an_image = array("."=>".", ".."=>"..", "README"=>"README", "readme"=>"readme", "LICENSE"=>"LICENSE", "license"=>"license");
		if (substr($oreon->optGen["nagios_path_img"], -1) == "/" && isset($rep[0]) && $rep[0] == "/")
			$rep = substr($rep, 1);
		if ($rep == NULL)	{
			$elems = scandir($oreon->optGen["nagios_path_img"]);
			foreach ($elems as $key => $value)
				if (!is_dir($oreon->optGen["nagios_path_img"].$value) || $value == "." || $value == "..")
					$elems[$key] = NULL;
		}
		$elems[count($elems)] = $rep;
		for ($i = 0; $i < count($elems); $i++)	{
			if ($elems[$i])	{
				$handle = @opendir($oreon->optGen["nagios_path_img"].$elems[$i]);
				while (false !== ($filename = @readdir($handle))){
					if (!is_dir($oreon->optGen["nagios_path_img"].$elems[$i]."/".$filename) && !array_key_exists($filename, $is_not_an_image) && substr($filename, -1)!= "~"){
						$key = substr($oreon->optGen["nagios_path_img"].$elems[$i]."/".$filename, strlen($oreon->optGen["nagios_path_img"]));
						$images[$key] = $key;
					}
				}
				ksort($images);
				@closedir($handle);
			}
		}
		return ($images);
	}
?>