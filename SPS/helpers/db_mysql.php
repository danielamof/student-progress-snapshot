<?php

function connect_to_db() {
	global $db_link,$db_name,$db_host,$db_user,$db_userp;

	$db_link = mysqli_connect($db_host,$db_user,$db_userp);

	if (!$db_link)
		die("XO");

	mysqli_select_db($db_link, $db_name);

	mysqli_set_charset($db_link, "UTF8");

}

function open_query($query, $multi = false) {
	global $db_link;

	if ($multi)
		return @mysqli_multi_query($db_link, $query);
	else
		return @mysqli_query($db_link, $query);
}

function query_fetch_assoc($recordset) {

	return mysqli_fetch_assoc($recordset);
}