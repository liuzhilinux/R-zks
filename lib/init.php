<?php
/*
 * 初始化数据库模块。
 */

// header('Content-type:text/html;charset=utf-8');

$current_path = dirname(__FILE__);

define('MOVE_PATH', 'E:\WEB_Tmp\r\upload_img');
// define('MOVE_PATH', '/data/home/byu4050030001/htdocs/r/upload_img');

define('BASE_URL_PATH', 'http://test.ling.com/r/');
// define('BASE_URL_PATH', 'http://e7iling.com/r/');

define('IMAGE_PATH', 'upload_img');

define('IMAGE_UPLOAD_SIZE_LIMIT', 1024 * 1024); // Byte.

define('BASE_FACT', '/r/lib/fact.php');


// crypt算法递归层数。
// 如果你不理解含义请勿修改，修改后将导致所有后台用户无法登录以及微信端无法使用支付密码。
define('DEFAULT_USER_CRYPT', 1);

// 配置用于登录的用户名密码。
$users = [
    'ling' => 'toor',
];

// 导入数据库相关类。
require_once 'db_test.php';
// require_once 'db.php';
require_once $current_path . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . 'qrframework2' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'DB.php';
require_once $current_path . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . 'qrframework2' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'Table.php';
require_once $current_path . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . 'qrframework2' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'Pagination.php';

// 导入基础支持。
/*
require_once $current_path . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . 'qrframework2' . DIRECTORY_SEPARATOR . 'base' . DIRECTORY_SEPARATOR . 'Curl.php';
require_once $current_path . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
    . 'qrframework2' . DIRECTORY_SEPARATOR . 'base' . DIRECTORY_SEPARATOR . 'SMS.php';
*/


// 导入Model。
require_once 'NodeSubjects.php';


// 导入相关工具函数。
require_once 'common.php';


/*防止跨域*/
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
} else {
    header('Access-Control-Allow-Origin: *');
}

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Content-Type: application/json');

/* 启会话 */
// if (!isset($_SESSION) || session_id() == '')
//     session_start();
