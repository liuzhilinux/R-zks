<?php

class SMS
{
    private $sign;
    private $apikey;
    private $api = [
        /* 发送单条短信接口 */
        'single_send' => 'https://sms.yunpian.com/v2/sms/single_send.json',
        /* 批量发送短信接口 */
        'batch_send' => 'https://sms.yunpian.com/v2/sms/batch_send.json',
        /* 批量个性化发送接口 */
        'multi_send' => 'https://sms.yunpian.com/v2/sms/multi_send.json',

    ];
    private $ch;
    private $result;
    private $error;

    public function __construct()
    {
        $ch = $this->ch = curl_init();
        /* 设置验证方式 */
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept:text/plain;charset=utf-8',
            'Content-Type:application/x-www-form-urlencoded', 'charset=utf-8'
        ]);

        /* 设置返回结果为流 */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        /* 设置超时时间*/
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        /* 设置通信方式 */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $this->sign = YUNPIAN_CFG_SIGN;
        $this->apikey = YUNPIAN_CFG_APIKEY;
    }

    /**
     * 发送短信。
     * 其中，方法根据传入的手机号码个数和短信模板对应的替换参数形式来判定发送短信的类型。
     * 单条发送：当只传入一个手机号码且传入的短信模板参数为一维数组的情况。
     * 群发： 当传入多个手机号码且传入的短信模板参数为一维数组（只能匹配出一条短信）。
     * 自定义群发：根据云片接口要求，要发送的短信条数必须和手机号个数一致，且一一对应，要发送自定义群发需传入多个号码，
     * 且传入一个二维数组给短信模板参数，且模板参数和手机号码之间要相匹配。
     *
     * @param string|array $mobiles  手机号码，多个号码可以以‘,’（英文逗号）分隔的字符串传入，或传入数组。
     * @param string       $tpl_flag 短信模板标记，短信模板请在 项目目录下的 _config/extra/yunpian.php 中配置。
     * @param array        $params   短信模板参数，以键值对形式的数组传入，其中键为短信模板中的替代字符，不包含‘#’。
     *
     * @return bool
     */
    public function send($mobiles, $tpl_flag, $params)
    {
        $sms_template = $GLOBALS['sms_template'];
        if (isset($sms_template[$tpl_flag])) {
            $tpl = $sms_template[$tpl_flag];
            $text = $tpl['text'];
            $predefine_params = $tpl['params'];

            $texts = [];

            if (!empty($predefine_params)) {
                if (empty($params)) {
                    return false;
                }

                foreach ($params as $name => $value) {
                    if (is_array($value)) {
                        foreach ($value as $n => $v) {
                            if (!in_array($n, $predefine_params)) {
                                return false;
                            }

                            $texts[] = $this->sign . str_replace('#' . $n . '#', $v, $text);
                        }
                    } else {
                        if (!in_array($name, $predefine_params)) {
                            return false;
                        }

                        $texts = $this->sign . str_replace('#' . $name . '#', $value, $text);
                    }
                }
            } else {
                $texts = $text;
            }

            if (is_string($mobiles) && strpos($mobiles, ',') === false) {
                // 发送单条短信的情况。
                if (is_string($texts)) {
                    $this->singleSend($mobiles, $texts);
                    return true;
                }

            } else {
                // 发送多条短信的情况。
                if (is_array($texts)) {
                    // 批量个性化发送。
                    $texts_count = count($texts);
                    $mobiles_count = is_array($mobiles) ? count($mobiles) : (substr_count($mobiles, ',') + 1);

                    // 手机号码的个数必须和短信条数相等。
                    if ($texts_count === $mobiles_count) {
                        $this->multiSend($mobiles, $texts);
                    }

                } else {
                    // 批量发送。
                    $this->batchSend($mobiles, $texts);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 发送单条短信。
     *
     * @param string $mobile 手机号码。
     * @param string $text   要发送的文本信息。
     */
    private function singleSend($mobile, $text)
    {
        $url = $this->api['single_send'];
        $data = [
            'text' => $text,
            'mobile' => $mobile
        ];

        $this->exec($url, $data);
    }

    /**
     * 批量发送短信。
     *
     * @param string|array $mobiles 要群发的手机号码，可以是字符串，以 ','分隔，或数组形式。
     * @param              $text
     */
    private function batchSend($mobiles, $text)
    {
        if (is_array($mobiles)) {
            $mobiles = implode(',', $mobiles);
        }

        $url = $this->api['batch_send'];
        $data = [
            'text' => $text,
            'mobile' => $mobiles
        ];

        $this->exec($url, $data);
    }

    /**
     * 批量个性化发送短信。
     *
     * @param string|array $mobiles 要群发的手机号码，可以是字符串，以 ','分隔，或数组形式。
     * @param array        $texts   要发送的信息，必须传入数组。
     */
    private function multiSend($mobiles, $texts)
    {
        if (is_array($mobiles)) {
            $mobiles = implode(',', $mobiles);
        }

        $texts = implode(',', $texts);

        $url = $this->api['batch_send'];

        $data = [
            'text' => $texts,
            'mobile' => $mobiles
        ];

        $this->exec($url, $data);
    }

    /**
     * 执行短信发送。
     *
     * @param $url
     * @param $data
     */
    private function exec($url, $data)
    {
        $apikey = $this->apikey;
        $ch = $this->ch;
        $data['apikey'] = $apikey;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $this->result = curl_exec($ch);
        $this->error = curl_error($ch);
    }

    /**
     * 返回处理的结果。
     *
     * @return mixed
     */
    public function getResult()
    {
        $result = $this->result;

        $r = json_decode($result, true);

        return $r ?: $result;
    }

    /**
     * 获取 curl 执行错误原因信息。
     *
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }
}