<?php
error_reporting(E_ALL);

include("conf.php");

include("helpers/db_mysql.php");
include("helpers/course_fn.php");

connect_to_db();

include("header.php");
include("content.php");
include("indexjs.php");
include("footer.php");