<?php
/* Independent disposable fixture plumbing: deliberately no CMS includes. */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$args=json_decode(stream_get_contents(STDIN),true);
$link=new mysqli('127.0.0.1',getenv('U5CMS_TEST_USER') ?: 'root',getenv('U5CMS_TEST_PASSWORD') ?: '',null,(int)getenv('U5CMS_TEST_PORT'));
$name=$args['database'];
if (!preg_match('/^u5reference_[a-f0-9]+$/D',$name)) throw new RuntimeException('Not a disposable reference database.');
if ($args['action']==='create') {
    $link->query('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4');
    $link->select_db($name);
    $link->multi_query(file_get_contents($args['seed']));
    do {if ($result=$link->store_result()) $result->free();} while ($link->more_results() && $link->next_result());
    echo '{}';exit;
}
if ($args['action']==='drop') {$link->query('DROP DATABASE IF EXISTS `'.$name.'`');echo '{}';exit;}
$link->select_db($name);$link->set_charset('latin1');
$result=$link->query(base64_decode($args['sql']));
$rows=array();
if ($result instanceof mysqli_result) while ($row=$result->fetch_assoc()) {
    foreach ($row as &$value) if ($value!==null) $value=base64_encode($value);
    unset($value);$rows[]=$row;
}
echo json_encode(array('rows'=>$rows,'affected'=>$link->affected_rows,'id'=>$link->insert_id));
