<?php
$db[DEV] = [
    'type'           => 'mysql',
    'hostname'       => '192.168.71.238',
    'database'       => 'op_finance',
    'username'       => 'root',
    'password'       => '123456',
    'hostport'       => '',
    'charset'        => 'utf8',
    'prefix'         => 'opf_',
    'debug'          => true,
    'deploy'         => 0,
    'rw_separate'    => false,
    'master_num'     => 1,
    'slave_no'       => '',
    'fields_strict'  => true,
    'resultset_type' => 'array',
    'auto_timestamp' => false,
    'auto_datetime_format' => false,
];

$db[TEST] = [

];

$db[PRO] = [
    'type'           => 'mysql',
    'hostname'       => 'rm-bp1bv9qt44n124lbr.mysql.rds.aliyuncs.com',
    'database'       => 'op_finance',
    'username'       => 'fupaihang',
    'password'       => 'nkg5Ge^lh^GKW$iDln40sitn',
    'hostport'       => '',
    'charset'        => 'utf8',
    'prefix'         => 'opf_',
    'debug'          => true,
    'deploy'         => 0,
    'rw_separate'    => false,
    'master_num'     => 1,
    'slave_no'       => '',
    'fields_strict'  => true,
    'resultset_type' => 'array',
    'auto_timestamp' => false,
    'auto_datetime_format' => false,
];

return $db[ENVIRONMENT];