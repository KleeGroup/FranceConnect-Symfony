<?php
/**
 * Created by PhpStorm.
 * User: tveron
 * Date: 01/08/2016
 * Time: 12:00
 */

namespace KleeGroup\FranceConnectBundle\Manager;


class CurlWrapper
{
    const POST_DATA_SEPARATOR = "\r\n";

    private $curlHandle;

    private $lastError;

    private $postData;

    private $postFile;

    private $postFileProperties;

    public function __construct($proxy = null)
    {
        $this->curlHandle = curl_init();
        $this->setProperties(CURLOPT_RETURNTRANSFER, 1);
        $this->setProperties(CURLOPT_FOLLOWLOCATION, 1);
        $this->setProperties(CURLOPT_MAXREDIRS, 5);
        if(!empty($proxy))
        {
            $this->setProperties(CURLOPT_HTTPPROXYTUNNEL, 1);
            $this->setProperties(CURLOPT_PROXY, $proxy);
        }
        $this->postFile = array();
        $this->postData = array();
        curl_setopt($this->curlHandle, CURLOPT_SSL_VERIFYPEER, true);
    }

    public function __destruct()
    {
        curl_close($this->curlHandle);
    }

    public function httpAuthentication($username, $password)
    {
        $this->setProperties(CURLOPT_USERPWD, "$username:$password");
        $this->setProperties(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    }

    public function addHeader($name, $value)
    {
        $this->setProperties(CURLOPT_HTTPHEADER, array("$name: $value"));
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    private function setProperties($properties, $values)
    {
        curl_setopt($this->curlHandle, $properties, $values);
    }

    public function setAccept($format)
    {
        $curlHttpHeader[] = "Accept: $format";
        $this->setProperties(CURLOPT_HTTPHEADER, $curlHttpHeader);
    }

    public function dontVerifySSLCACert()
    {
        $this->setProperties(CURLOPT_SSL_VERIFYHOST, 0);
        $this->setProperties(CURLOPT_SSL_VERIFYPEER, 0);
    }

    public function setServerCertificate($serverCertificate)
    {
        $this->setProperties(CURLOPT_CAINFO, $serverCertificate);
        $this->setProperties(CURLOPT_SSL_VERIFYPEER, 0);
    }

    public function setClientCertificate($clientCertificate, $clientKey, $clientKeyPassword)
    {
        $this->setProperties(CURLOPT_SSLCERT, $clientCertificate);
        $this->setProperties(CURLOPT_SSLKEY, $clientKey);
        $this->setProperties(CURLOPT_SSLKEYPASSWD, $clientKeyPassword);
    }

    public function get($url)
    {
        $this->setProperties(CURLOPT_URL, $url);
        if ($this->postData || $this->postFile) {
            $this->curlSetPostData();
        }
        curl_setopt($this->curlHandle, CURLINFO_HEADER_OUT, true);

        $output = curl_exec($this->curlHandle);

        //print_r(curl_getinfo($this->curlHandle,CURLINFO_HEADER_OUT));

        $this->lastError = curl_error($this->curlHandle);
        if ($this->lastError) {
            $this->lastError = "Erreur de connexion au serveur : " . $this->lastError;
            return false;
        }
        return $output;
    }

    public function addPostData($name, $value)
    {
        if (!isset($this->postData[$name])) {
            $this->postData[$name] = array();
        }

        $this->postData[$name][] = $value;
    }

    public function setPostDataUrlEncode(array $post_data)
    {
        $pd = array();
        foreach ($post_data as $k => $v) {
            $pd[] = "$k=$v";
        }
        $pd = implode("&", $pd);
        $this->setProperties(CURLOPT_POST, true);
        $this->setProperties(CURLOPT_POSTFIELDS, $pd);
        $this->setProperties(CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    }


    public function addPostFile($field, $filePath, $fileName = false, $contentType = "application/octet-stream", $contentTransferEncoding = false)
    {
        if (!$fileName) {
            $fileName = basename($filePath);
        }
        $this->postFile[$field][$fileName] = $filePath;
        $this->postFileProperties[$field][$fileName] = array($contentType, $contentTransferEncoding);
    }

    private function getBoundary()
    {
        return '----------------------------' .
        substr(sha1('CurlWrapper' . microtime()), 0, 12);
    }

    private function curlSetPostData()
    {
        $this->setProperties(CURLOPT_POST, true);
        if ($this->isPostDataWithSimilarName()) {
            $this->curlSetPostDataWithSimilarFilename();
        } else {
            $this->curlPostDataStandard();
        }
    }

    private function isPostDataWithSimilarName()
    {
        $array = array();

        foreach ($this->postData as $name => $multipleValue) {
            foreach ($multipleValue as $data) {
                if (isset($array[$name])) {
                    return true;
                }
                $array[$name] = true;
            }
        }
        foreach ($this->postFile as $name => $multipleValue) {
            foreach ($multipleValue as $data) {
                if (isset($array[$name])) {
                    return true;
                }
                $array[$name] = true;
            }
        }
    }

    private function curlPostDataStandard()
    {
        $post = array();
        foreach ($this->postData as $name => $multipleValue) {
            foreach ($multipleValue as $value) {
                $post[$name] = $value;
            }
        }
        foreach ($this->postFile as $name => $multipleValue) {
            foreach ($multipleValue as $fileName => $filePath) {
                $post[$name] = "@$filePath;filename=$fileName";
            }
        }
        curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $post);
    }

    private function curlSetPostDataWithSimilarFilename()
    {
        $boundary = $this->getBoundary();

        $body = array();

        foreach ($this->postData as $name => $multipleValue) {
            foreach ($multipleValue as $value) {
                $body[] = "--$boundary";
                $body[] = "Content-Disposition: form-data; name=$name";
                $body[] = '';
                $body[] = $value;
            }
        }


        foreach ($this->postFile as $name => $multipleValue) {
            foreach ($multipleValue as $fileName => $filePath) {
                $body[] = "--$boundary";
                $body[] = "Content-Disposition: form-data; name=$name; filename=\"$fileName\"";
                $body[] = "Content-Type: {$this->postFileProperties[$name][$fileName][0]}";
                if ($this->postFileProperties[$name][$fileName][1]) {
                    $body[] = "Content-Transfer-Encoding: {$this->postFileProperties[$name][$fileName][1]}";
                }
                $body[] = '';
                $body[] = file_get_contents($filePath);
            }
        }

        $body[] = "--$boundary--";
        $body[] = '';

        $content = join(self::POST_DATA_SEPARATOR, $body);


        $curlHttpHeader[] = 'Content-Length: ' . strlen($content);
        $curlHttpHeader[] = 'Expect: 100-continue';
        $curlHttpHeader[] = "Content-Type: multipart/form-data; boundary=$boundary";

        $this->setProperties(CURLOPT_HTTPHEADER, $curlHttpHeader);
        $this->setProperties(CURLOPT_POSTFIELDS, $content);
    }

    public function getHTTPCode()
    {
        return curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
    }
}