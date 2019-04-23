<?php
require(dirname(__FILE__).'/include/config.inc.php');

/**
 * Created by PhpStorm.
 * User: 重剑无锋
 * Date: 17/7/23
 * Time: 下午9:19
 */


$mode = $_GET['m'];
global $db;

// $db = new Mysql($cfg['db_host'],$cfg['db_user'],$cfg['db_pass'],$cfg['db_name'],$cfg['db_charset'],$cfg['db_charset'],$cfg['db_pre']);

    $sql = "SELECT * FROM valid_ip order by score desc";
    $results = $db->query($sql);

    if (mysql_num_rows($results) > 0){
        $i = 1;
        while ($fs = $db->fetch_array($results))
        {

            echo $fs["0"];
            echo '<br>';
            $i ++;
        }}
        #print $html_str;
    else{
        print "False";}

?>


