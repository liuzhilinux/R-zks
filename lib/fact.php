<?php
/**
 * Created by PhpStorm.
 * User: ling
 * Date: 1/22/2019
 * Time: 22:30
 */

/*
 * 交互 API 。
 */
// 初始化。
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'init.php';
require_once 'funs.php';

if (!isset($_SESSION) || session_id() == '')
    session_start();

$ev_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';

// 获取请求的数据。
// $ev_request = $_POST;
$ev_request = count($_POST) > 0 ? $_POST :
    (isset($_SERVER['HTTP_ACCEPT']) && (strstr($_SERVER['HTTP_ACCEPT'], 'application/json') ||
        strstr($_SERVER['HTTP_ACCEPT'], '*/*')) ?
        json_decode(file_get_contents("php://input"), true) : []);

$fn = isset($_GET['fn']) ? $_GET['fn'] : '';

$base_fns = ['image_upload', 'login', 'logout', 'base_config'];

$fns = [
    'create_node_subject',

    'update_node_subject',
    'update_node_qtype',
    'update_node_parent_id',
    'update_node_subject_content',
    'set_node_subjects_sequence',

    'get_node_subject',
    'get_nodes',

    'delete_node_subject',
];

$fns = array_merge($base_fns, $fns);

if (strtolower($ev_method) != 'post' && $fn != 'base_config')
    super_dump(false, -127, 'Illegal request![get]');

if (in_array($fn, $fns)) {
    if ($fn != 'login' && $fn != 'base_config' && !isset($_SESSION['sess_mk'])) {
        super_dump(false, -57, 'Session time out!');
    }

    $fn();

} else super_dump(false, -127, 'Illegal request![func not exi]');


