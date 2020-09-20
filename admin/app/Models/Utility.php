<?php

namespace App\Models;


class Utility
{

    public static function removeHTTP($url) {
        $url = preg_replace("~https?:~", "", $url);
        return $url;
    }

    /*
     * To overwrite default values, pass in $optArr with the key as the bit flag option
     *  pointing to the value https://www.php.net/manual/en/function.curl-setopt.php
     */
    public static function curlGet($url, $timeout = 5, $optArr = []) {

        $c = curl_init($url );

        //When CURLOPT_HEADER is set to 0 the only effect is that header info from the response is excluded from the output
        curl_setopt($c, CURLOPT_HEADER, 0);
//        /* enable TCP keep-alive for this transfer */
//        curl_setopt(curl, CURLOPT_TCP_KEEPALIVE, 1);
//        /* keep-alive idle time to 120 seconds */
//        curl_setopt(curl, CURLOPT_TCP_KEEPIDLE, 120);
//        /* interval time between keep-alive probes: 60 seconds */
//        curl_setopt(curl, CURLOPT_TCP_KEEPINTVL, 60);

        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);

        foreach($optArr as $CURLOPT_option => $value) {
            curl_setopt($c, $CURLOPT_option, $value);
        }

        $response = curl_exec($c);

        if ($response == false) {
            $info = curl_getinfo($c);
            $error = curl_error($c);

            $errInfoStr = "Error: $error";
            if (stristr($error, "operation timed out")) {
                $errInfoStr.= " | url: " . $url;
            } else {
                $errInfoStr.= " | " . json_encode($info);
            }
            \Log::error(__METHOD__ . " line " . __LINE__ . " " . $errInfoStr);
            throw new \Exception($error);
        }

        curl_close($c);

        return $response;

    }

    public static function cleanText($text)
    {

        // First, replace UTF-8 characters.
        // LEFT SINGLE QUOTATION MARK‘ etc
        $text = str_replace(
            array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6"),
            array("'", "'", '"', '"', '-', '--', '...'),
            $text
        );

        // Next, replace their Windows-1252 equivalents.
        $text = str_replace(
            array(chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133)),
            array("'", "'", '"', '"', '-', '--', '...'),
            $text
        );

        return $text;

    }

    /*
     * Remove all line breaks and replace double spaces with single space
     */
    public static function tighten($text)
    {
        $text = str_replace("\n", " ", $text);
        $text = str_replace("\r", " ", $text);
        $text = str_replace("  ", " ", $text);
        return $text;
    }

    /*
     * Execute var_dump, but return output as string.
     * @param string
     * @return string
     */
    public static function varDumpToStr($val) {
        ob_start();
        var_dump($val);
        $tmp=ob_get_contents();
        ob_end_clean();
        $tmp = str_replace("\n", "", $tmp);
        return $tmp;

    }

}