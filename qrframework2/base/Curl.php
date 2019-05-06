<?php

class Curl
{
    private $url;
    private $param;
    private $isPost;
    private $timeout;
    private $forceCa;
    private $cacertPemPath;

    private $ch;

    private $errno = 0;
    private $error = '';

    /**
     * 发送请求。
     *
     * @return mixed
     */
    private function query()
    {
        $url = $this->url;
        $force_ca = $this->forceCa;
        $ssl = substr($url, 0, 8) == "https://" ? true : false;
        $ch = $this->ch;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout - 2);

        if ($ssl && $force_ca) {
            // 只信任CA颁布的证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            // CA根证书（用来验证的网站证书是否是CA颁布）
            curl_setopt($ch, CURLOPT_CAINFO, $this->cacertPemPath);
            // 检查证书中是否设置域名，并且是否与提供的主机名匹配
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        } elseif ($ssl && !$force_ca) {
            // 信任任何证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // 检查证书中是否设置域名
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        }

        if ($this->isPost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->param);
        }

        $ret = curl_exec($ch);

        if (false === $ret) {
            $this->errno = curl_errno($ch);
            $this->error = curl_error($ch);
        }

        curl_close($ch);
        return $ret;
    }

    /**
     * 发送 GET 请求。
     *
     * @param $url
     * @param $param
     *
     * @return mixed
     */
    public function get($url, $param = [])
    {
        if ($param) {
            $delimiter = strpos($url, '?') ? '&' : '?';
            $url .= $delimiter . http_build_query($param);
        }
        $this->url = $url;
        $this->isPost = false;

        return $this->query();
    }

    /**
     * 发送 POSt 请求。
     *
     * @param $url
     * @param $param
     *
     * @return mixed
     */
    public function post($url, $param = [], $query = [])
    {
        if ($query) {
            $delimiter = strpos($url, '?') ? '&' : '?';
            $url .= $delimiter . http_build_query($query);
        }
        $this->url = $url;
        $this->param = $param;
        $this->isPost = true;

        return $this->query();
    }

    /**
     * 获取错误信息。
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 获取错误编号。
     *
     * @return int
     */
    public function getErrno()
    {
        return $this->errno;
    }

    /**
     * 初始化 Curl 类。
     * Curl constructor.
     *
     * @param bool $force_ca
     * @param int $timeout
     * @param string $cacert_pem_path
     */
    public function __construct($force_ca = true, $timeout = 30, $cacert_pem_path = '')
    {
        $this->forceCa = $force_ca;
        $this->timeout = $timeout;
        $this->cacertPemPath = $cacert_pem_path ?:
            __DIR__ . DIRECTORY_SEPARATOR . '_certificate' . DIRECTORY_SEPARATOR . 'cacert.pem';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Expect:']);

        $this->ch = $ch;
    }
}