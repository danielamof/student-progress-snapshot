<?php
session_start();

// Coursename
if (isset($_SESSION["coursename"])&&!isset($_GET["coursename"]))
	$courseName = $_SESSION["coursename"];
else {
	$courseName = $_GET["coursename"];
	$_SESSION["coursename"] = $_GET["coursename"];
}

// Username
$userName = "";
if (isset($_GET["username"]))
	$userName = $_GET["username"];

function number_from_locale($number)
{
	//$a = new NumberFormatter("ca", NumberFormatter::DECIMAL); 
	//return $a->format($number);
	return number_format($number, 0, ",", ".");
}

function num_of_course_users() {
	global $courseName;
	if ($recordset = open_query("SELECT COUNT(DISTINCT USERNAME) AS TOTAL_USERS FROM logs WHERE COURSENAME='".$courseName."'")) {
		if ($row = query_fetch_assoc($recordset))
			return $row["TOTAL_USERS"];
	}
	return 0;
}

define('DEF_COMP_SOCIALS', "'Chat','Comentaris de la tramesa','Feedback','Forum','Submission comments'");
define('DEF_COMP_ASSIGNMENTS', "'Assignment','File submissions','Fitxers de la tramesa','Online text submissions','Qüestionari','Quiz','Tasca','Trameses de text en línia'");
define('DEF_COMP_RESOURCES', "'Base de dades','Carpeta','File','Fitxer','Folder','Glossari','Page','Pàgina','URL'");
define('DEF_COMP_REPORTS', "'Activity report','Course completion','Grader report','Historial de qualificacions','Informe d\'\'activitat','Informe d\'\'usuari','Informe global','Logs','Overview report','Qualificador','Registres','Sistema','System','User report'");

function compWhereByType($type = "") {
	$typeWhere = "";
	if ($type != "") {
		switch ($type) {
			case 'socials':
				$componentList = " (".DEF_COMP_SOCIALS.")";
				break;
			case 'assignments':
				$componentList = " (".DEF_COMP_ASSIGNMENTS.")";
				break;
			case 'resources':
				$componentList = " (".DEF_COMP_RESOURCES.")";
				break;
			case 'reports':
				$componentList = " (".DEF_COMP_REPORTS.")";
				break;
			default:
				$componentList = " 1=1 ";
				break;
		}
		$typeWhere = " AND COMPONENT IN ".$componentList;
	}

	return $typeWhere;
}

function max_total_course_interactions($eventContext = "", $type = "") {
	global $courseName;

	$result = array();

	$eventContextWhere = '';
	if ($eventContext != "")
		$eventContextWhere = " EVENT_CONTEXT = '".str_replace("'", "''", $eventContext)."' AND ";

	$typeWhere = compWhereByType($type);

	//MAX(MAX_MIN_READ) AS MAX_READ, SUM(MAX_MIN_READ) AS TOTAL_READ, MAX(MAX_MIN_WRITE) AS MAX_WRITE, SUM(MAX_MIN_WRITE) AS TOTAL_WRITE
	$sqlQuery = "SELECT
		MAX(MAX_MIN_READ) AS MAX_READ, '' AS TOTAL_READ, MAX(MAX_MIN_WRITE) AS MAX_WRITE, '' AS TOTAL_WRITE
		FROM (
			SELECT
			(SUM(IF(DESCRIPTION LIKE '%view%', 1, 0))) AS MAX_MIN_READ,
			(SUM(IF(DESCRIPTION LIKE '%create%' OR DESCRIPTION LIKE '%update%' OR DESCRIPTION LIKE '%add%', 1, 0))) AS MAX_MIN_WRITE
			FROM logs
			WHERE ".$eventContextWhere." COURSENAME='".$courseName."' ".$typeWhere."
			GROUP BY USERNAME
			) AS MAX_MIN_TABLE";

 	if ($recordset = open_query($sqlQuery)) {
		if ($row = query_fetch_assoc($recordset)) {
			$result = array($row["MAX_READ"],$row["TOTAL_READ"],$row["MAX_WRITE"],$row["TOTAL_WRITE"]);
		}
	}
	return $result;
}

function num_of_course_interactions($type = "") {
	global $courseName, $userName;

	$result = array();

	$userWhere = '';
	if ($userName != "")
		$userWhere = " USERNAME = '".$userName."' AND ";

	$typeWhere = compWhereByType($type);

	// Read
	$sqlQuery = "SELECT COUNT(*) AS TOTAL_INTERACTIONS
		FROM logs
		WHERE ".$userWhere." COURSENAME='".$courseName."' ".$typeWhere." AND DESCRIPTION LIKE '%view%'";

	if ($recordset = open_query($sqlQuery)) {
		if ($row = query_fetch_assoc($recordset))
			$result[] = $row["TOTAL_INTERACTIONS"];
	}

	// Write
	$sqlQuery = "SELECT COUNT(*) AS TOTAL_INTERACTIONS
		FROM logs
		WHERE ".$userWhere." COURSENAME='".$courseName."' ".$typeWhere." AND (DESCRIPTION LIKE '%create%' OR DESCRIPTION LIKE '%update%' OR DESCRIPTION LIKE '%add%')";

 	if ($recordset = open_query($sqlQuery)) {
		if ($row = query_fetch_assoc($recordset))
			$result[] = $row["TOTAL_INTERACTIONS"];
	}
	return $result;
}

function list_course_interactions($type = "") {
	global $courseName, $userName;

	$result = array();

	$userWhere = '';
	if ($userName != "")
		$userWhere = " USERNAME = '".$userName."' AND ";

	$typeWhere = compWhereByType($type);

	// Read
	$sqlQuery = "SELECT EVENT_CONTEXT,
		SUM(IF(DESCRIPTION LIKE '%view%', 1, 0)) AS EVENT_CONTEXT_READ,
		SUM(IF(DESCRIPTION LIKE '%create%' OR DESCRIPTION LIKE '%update%' OR DESCRIPTION LIKE '%add%', 1, 0)) AS EVENT_CONTEXT_WRITE
		FROM logs
		WHERE ".$userWhere." COURSENAME='".$courseName."' ".$typeWhere."
		GROUP BY EVENT_CONTEXT
		HAVING SUM(IF(DESCRIPTION LIKE '%view%', 1, 0))>0 OR SUM(IF(DESCRIPTION LIKE '%create%' OR DESCRIPTION LIKE '%update%' OR DESCRIPTION LIKE '%add%', 1, 0))>0
		ORDER BY EVENT_CONTEXT_READ DESC, EVENT_CONTEXT_WRITE DESC, EVENT_CONTEXT";

	if ($recordset = open_query($sqlQuery)) {
		while ($row = query_fetch_assoc($recordset))
			$result[] = $row;
	}

	return $result;
}

function hits_by_month($type = "") {
	global $courseName, $userName;

	$userWhere = '';
	if ($userName != "")
		$userWhere = " USERNAME = '".$userName."' AND ";

	$typeWhere = compWhereByType($type);

	$sqlQuery = "";
	for ($x = 9;$x <= 12; $x++)
	$sqlQuery .= "
		(SELECT COUNT(*) AS TOTAL, DATE_FORMAT(STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i'), '%m') AS MONTH
			FROM logs WHERE ".$userWhere." COURSENAME='".$courseName."' ".$typeWhere."
			GROUP BY DATE_FORMAT(STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i'), '%M') HAVING MONTH=".$x."
			ORDER BY DATE_FORMAT(STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i'), '%m') DESC)
		UNION\n";
	$maxMonth = 5;
	for ($x = 1;$x <= 5; $x++) {
		$sqlQuery .= "
			(SELECT COUNT(*) AS TOTAL, DATE_FORMAT(STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i'), '%m') AS MONTH
				FROM logs WHERE ".$userWhere." COURSENAME='".$courseName."' ".$typeWhere."
				GROUP BY DATE_FORMAT(STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i'), '%M') HAVING MONTH=".$x."
				ORDER BY DATE_FORMAT(STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i'), '%m') DESC)\n";
		if ($x<$maxMonth)
			$sqlQuery .= "UNION\n";
	}

	if ($recordset = open_query($sqlQuery)) {
		$data = array();
		while ($row = query_fetch_assoc($recordset))
			$data[] = array($row['MONTH'], $row['TOTAL']);
		return $data;
	}
	return 0;
}

function time_dedication_total($maxDedication = false, $pEventContext = "") {
	global $courseName, $userName;
	if ($maxDedication) {
		$pCourseName = "";
		$pUserName = "";
	}
	else {
		$pCourseName = $courseName;
		$pUserName = $userName;
	}
	$queries = array();
	$queries[] = "SET @rownr=0;";
	$queries[] = "SET @rownr2=0;";
	$queries[] = "DROP TEMPORARY TABLE IF EXISTS temp_table;";
	$queries[] = "DROP TEMPORARY TABLE IF EXISTS temp_table2;";
	$queries[] = "CREATE TEMPORARY TABLE IF NOT EXISTS 
		  temp_table ENGINE=MEMORY AS (
		SELECT @rownr:=@rownr+1 AS id, USERNAME, EVENT_CONTEXT, DATE_FORMAT(STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i'), '%m') AS MONTH, STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i') AS countedDate
		FROM logs
		WHERE COURSENAME LIKE '%".$pCourseName."%' AND USERNAME LIKE '%".$pUserName."%' AND EVENT_CONTEXT LIKE '%".str_replace("'","''",$pEventContext)."%'
		ORDER BY STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i') DESC
		);";
	$queries[] = "CREATE TEMPORARY TABLE IF NOT EXISTS 
		  temp_table2 ENGINE=MEMORY AS (
		SELECT @rownr2:=@rownr2+1 AS id, USERNAME, EVENT_CONTEXT, DATE_FORMAT(STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i'), '%m') AS MONTH, STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i') AS countedDate
		FROM logs
		WHERE COURSENAME LIKE '%".$pCourseName."%' AND USERNAME LIKE '%".$pUserName."%' AND EVENT_CONTEXT LIKE '%".str_replace("'","''",$pEventContext)."%'
		ORDER BY STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i') DESC
		);";
	$queries[] = "SELECT id, from_date, to_date, ".(($maxDedication)?"MAX":"SUM")."(IF(abs(date_diff)<30, abs(date_diff), 0)) as date_diff
		FROM(
		SELECT 
		    g1.id,
		    g1.countedDate from_date,
		    g2.countedDate to_date,
		    (TIME_TO_SEC(g2.countedDate) - TIME_TO_SEC (g1.countedDate))/60 AS date_diff
		FROM
		    temp_table g1
		        INNER JOIN
		    temp_table2 g2 ON g2.id = g1.id + 1) AS table_date_diff";

	for ($x=0;$x<7;$x++){
		if ($x==6) {
			if ($recordset = open_query($queries[$x])) {
				if ($row = query_fetch_assoc($recordset)) {
					return $row['date_diff'];
				} else echo "error3#";
			} else echo "error2#";
		}
		else {
			if (!open_query($queries[$x])) {
				echo "error#";
				break;
			}
		}
	}
	/*$sqlQuery = "SELECT timeDedicationTotal('".$courseName."','".$userName."','".str_replace("'","''",$eventContext)."') AS TOTAL_TIME_DEDICATION";

	if ($recordset = open_query($sqlQuery)) {
		if ($row = query_fetch_assoc($recordset)) {
			return $row['TOTAL_TIME_DEDICATION'];
		}
	}*/
	return 0;
	
}

function median_hits_by_month($type = "") {
	global $courseName, $numUsers;

	$sqlQuery = "";
	for ($x = 9;$x <= 12; $x++)
	$sqlQuery .= "
		(SELECT COUNT(*) AS TOTAL, DATE_FORMAT(STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i'), '%m') AS MONTH
			FROM logs
			WHERE COURSENAME='".$courseName."' AND COMPONENT LIKE '%".$type."%'
			GROUP BY DATE_FORMAT(STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i'), '%M')
			HAVING MONTH=".$x."
			ORDER BY DATE_FORMAT(STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i'), '%m') DESC)
		UNION\n";
	$maxMonth = 5;
	for ($x = 1;$x <= 5; $x++) {
		$sqlQuery .= "
			(SELECT COUNT(*) AS TOTAL, DATE_FORMAT(STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i'), '%m') AS MONTH
				FROM logs WHERE COURSENAME='".$courseName."' AND COMPONENT LIKE '%".$type."%'
				GROUP BY DATE_FORMAT(STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i'), '%M') HAVING MONTH=".$x."
				ORDER BY DATE_FORMAT(STR_TO_DATE(HOUR_DATE, '%d %M, %H:%i'), '%m') DESC)\n";
		if ($x<$maxMonth)
			$sqlQuery .= "UNION\n";
	}

	if ($recordset = open_query($sqlQuery)) {
		$data = array();
		while ($row = query_fetch_assoc($recordset))
			$data[] = array($row['MONTH'], round($row['TOTAL']/$numUsers));
		return $data;
	}
	return 0;
}

function course_users_list() {
	global $courseName, $userName;

	$sqlQuery = "SELECT USERNAME
	FROM logs
	WHERE COURSENAME='".$courseName."' AND USERNAME LIKE '%" . $userName . "%' GROUP BY USERNAME ORDER BY USERNAME";
	
	if ($recordset = open_query($sqlQuery)) {
		$data = array();
		while ($row = query_fetch_assoc($recordset))
			$data[] = $row['USERNAME'];
		return $data;
	}
	return 0;
}