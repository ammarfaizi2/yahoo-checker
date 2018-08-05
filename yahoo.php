<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('max_execution_time', 300000000);

function csrf($socks = 0)
    {
        $c = curl_init("https://login.yahoo.com/");
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36");
        curl_setopt($c, CURLOPT_REFERER, 'https://www.google.com');
        if ($socks):
          curl_setopt($c, CURLOPT_HTTPPROXYTUNNEL, true); 
          curl_setopt($c, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5); 
          curl_setopt($c, CURLOPT_PROXY, $socks);
        endif; 
        curl_setopt($c, CURLOPT_ENCODING, 'gzip, deflate, br');  
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);  
        curl_setopt($c, CURLOPT_HEADER, true);
        // curl_setopt($c, CURLOPT_HTTPHEADER, $header);
        curl_setopt($c, CURLOPT_COOKIEJAR, dirname(__FILE__) . "/cookie.txt");
        curl_setopt($c, CURLOPT_COOKIEFILE, dirname(__FILE__) . "/cookie.txt");
        $response = curl_exec($c);
        $httpcode = curl_getinfo($c);
        if(!$httpcode) return false; 
        else
        {
            $header = substr($response, 0, curl_getinfo($c, CURLINFO_HEADER_SIZE));
            $body = substr($response, curl_getinfo($c, CURLINFO_HEADER_SIZE));
        }
        
        //echo $response;
        if($response !== null || $response != FALSE || $response != '')
        {
            preg_match_all('#name="crumb" value="(.*?)" />#', $response, $crumb);
            preg_match_all('#name="acrumb" value="(.*?)" />#', $response, $acrumb);
            preg_match_all('#name="config" value="(.*?)" />#', $response, $config);
            preg_match_all('#name="sessionIndex" value="(.*?)" />#', $response, $sesindex);

            $result['status'] = "ok";
            $result['crumb'] = isset($crumb[1][0]) ? $crumb[1][0] : "";
            $result['acrumb'] = $acrumb[1][0];
            $result['config'] = isset($config[1][0]) ? $config[1][0] : "";
            $result['sesindex'] = $sesindex[1][0];

            return $result;

        }
        else
        {
            $result['status'] = "fail";
            return $result;
        }
    }

function getValid($email, $data, $socks = 0)
    {
        $crumb = trim($data['crumb']);
        $acrumb = trim($data['acrumb']);
        $config = trim($data['config']);
        $sesindex = trim($data['sesindex']);

        $header[] = "Host: login.yahoo.com";
        $header[] = "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:56.0) Gecko/20100101 Firefox/56.0";
        $header[] = "Accept: */*";
        $header[] = "Accept-Language: en-US,en;q=0.5";
        $header[] = "content-type: application/x-www-form-urlencoded; charset=UTF-8";
        $header[] = "X-Requested-With: XMLHttpRequest";
        $header[] = "Referer: https://login.yahoo.com/";
        $header[] = "Connection: keep-alive";


        $data = "acrumb=$acrumb&sessionIndex=$sesindex&username=".urlencode($email)."&passwd=&signin=Next";

        $c = curl_init("https://login.yahoo.com/");
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36");
        curl_setopt($c, CURLOPT_REFERER, 'https://login.yahoo.com/');
        if ($socks):
          curl_setopt($c, CURLOPT_HTTPPROXYTUNNEL, true); 
          curl_setopt($c, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5); 
          curl_setopt($c, CURLOPT_PROXY, $socks);
        endif; 
        curl_setopt($c, CURLOPT_ENCODING, 'gzip, deflate, br');  
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);  
        curl_setopt($c, CURLOPT_HEADER, true);
        curl_setopt($c, CURLOPT_HTTPHEADER, $header);
        curl_setopt($c, CURLOPT_COOKIEJAR, dirname(__FILE__) . "/cookie.txt");
        curl_setopt($c, CURLOPT_COOKIEFILE, dirname(__FILE__) . "/cookie.txt");
        curl_setopt($c, CURLOPT_POSTFIELDS, $data);
        curl_setopt($c, CURLOPT_POST, 1);
        $response = curl_exec($c);
        $httpcode = curl_getinfo($c);
        if(!$httpcode) return false; 
        else
        {
            $header = substr($response, 0, curl_getinfo($c, CURLINFO_HEADER_SIZE));
            $body = substr($response, curl_getinfo($c, CURLINFO_HEADER_SIZE));
        }

        if($response !== null || $response != FALSE || $response != '')
        {
            $res = json_decode($body, true);
            
            if(@$res['error'] === false)
            {
                $result['email'] = $email;
                $result['status'] = "live";
            }
            elseif(@$res['render']['error'] == "messages.ERROR_INVALID_USERNAME")
            {
                $result['email'] = $email;
                $result['status'] = "die";
            }
        }
        else
        {
            $result['email'] = $email;
            $result['status'] = "unchecked";
        }

        return json_encode($result);
    }

$socks = @$_GET['socks'];
$email = @$_GET['email'];

$a = csrf($socks);
//print_r($a);
if($a['status'] == "ok")
{
    $b = getValid($email, $a, $socks);

    echo $b;

    unlink(dirname(__FILE__) . "/cookie.txt");
}
else
{
    unlink(dirname(__FILE__) . "/cookie.txt");
    die("Sorry, can't reach the site!");
}
