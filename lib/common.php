<?php
/**
 * Created by PhpStorm.
 * User: ling
 * Date: 1/22/2019
 * Time: 22:29
 */

/**
 * 覆盖系统自带的 getallheaders() .
 *
 * @return array 请求头信息。
 */
if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(
                    ' ',
                    '-',
                    ucwords(
                        strtolower(
                            str_replace('_', ' ', substr($name, 5))
                        )
                    )
                )] = $value;
            }
        }

        return $headers;
    }
}

/**
 * API 接口返回数据。
 *
 * @param mixed $data 要返回的数据。
 * @param int $code   代码。
 * @param string $msg 提示信息。
 */
if (!function_exists('super_dump')) {
    function super_dump($data, $code = 1, $msg = 'success!')
    {
        echo json_encode([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

/**
 * URL 重定向。
 *
 * @param string $url 要重定向的 URL。
 */
if (!function_exists('url_redirect')) {
    function url_redirect($url)
    {
        // header('location: ' . $url);

        $html = '<script language="javascript" type="text/javascript">';
        $html .= 'window.location.href="';
        $html .= $url;
        $html .= '"</script>';

        echo $html;
        exit;
    }
}

/**
 * 转换为驼峰命名法。
 */
if (!function_exists('str_to_camel_case')) {
    function str_to_camel_case($s, $ucfirst = false)
    {
        if (!strpos($s, '_')) {
            return $s;
        }

        $str = '';
        $tmp = explode('_', $s);

        $str .= $ucfirst ? ucfirst($tmp[0]) : $tmp[0];

        $tmp_len = count($tmp);
        for ($i = 1; $i < $tmp_len; $i++) {
            $str .= ucfirst($tmp[$i]);
        }

        return $str;
    }
}

/**
 * 处理图片上传。
 */
function image_upload()
{
    $today_dir = date('Ymd');

    $upTypes = [
        'image/jpg',
        'image/jpeg',
        'image/png',
        'image/pjpeg',
        'image/gif',
        'image/bmp',
        'image/x-png',
        'image/webp'
    ];

    if (empty($_FILES)) super_dump(false, -1, '未上传图片。');

    if (!file_exists(MOVE_PATH) && !mkdir(MOVE_PATH))
        super_dump(false, -2, '创建目录失败。');

    $file_path = MOVE_PATH . DIRECTORY_SEPARATOR . $today_dir . DIRECTORY_SEPARATOR;

    if (!file_exists($file_path) && !mkdir($file_path))
        super_dump(false, -1, '创建目录失败。');

    $results = [];

    foreach ($_FILES as $idx => $file) {
        if ($file) {
            $file_size = $file['size'];
            $tmp_name = $file['tmp_name'];
            $path_info = pathinfo($file['name']);

            $file_extension = $path_info['extension'];

            $file_name = md5(microtime(true)) . '.' . $file_extension;

            $destination = $file_path . $file_name;

            if ($file_size >= IMAGE_UPLOAD_SIZE_LIMIT) {
                $file_size_kb = ceil($file_size / 1024);
                $size_limit_kb = ceil(IMAGE_UPLOAD_SIZE_LIMIT / 1024);
                $result = ['error' => '图片文件大小超过 ' . $size_limit_kb . 'KB 限制：' . $idx . ': ' . $file_size_kb . 'KB.'];
            } elseif (!in_array($file['type'], $upTypes)) {
                $result = ['error' => '非法图片：' . $idx . ' 所包含文件 ' . $file['name'] . ' 不是图片文件。'];
            } elseif (move_uploaded_file($tmp_name, $destination)) {
                $result = [];
                $file_name = str_replace('\\', '/', $file_name);
                $result['file_name'] = $today_dir . '/' . $file_name;
            } else {
                $result = ['error' => '文件上传失败!'];
            }

            $results[$idx] = $result;
        } else {
            $results[$idx] = ['error' => '文件未空。'];
        }
    }

    super_dump([
        'path' => BASE_URL_PATH . IMAGE_PATH,
        'results' => $results
    ]);
}

/**
 * 验证手机号。
 *
 * @param $phone
 *
 * @return bool
 */
function check_mobile_phone($phone)
{
    $pattern = '/^(1[1|2|3|4|5|6|7|8|9])\d{9}$/';
    return preg_match($pattern, $phone) === 1;
}

/**
 * 生成订单编号。
 *
 * @return string
 */
function generate_order_no()
{
    $order_no = date('Ymd') .
        substr(implode(NULL, array_map('ord', str_split(
            substr(uniqid(), 7, 13), 1))), 0, 8);

    for ($i = 0; $i < 8; $i++) {
        $order_no .= rand(0, 9);
    }

    return $order_no;
}
