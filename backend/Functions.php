<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Functions
{

    function getRealIP()
    {

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $client_ip = (!empty($_SERVER['REMOTE_ADDR']) ) ?
                    $_SERVER['REMOTE_ADDR'] :
                    ( (!empty($_ENV['REMOTE_ADDR']) ) ?
                            $_ENV['REMOTE_ADDR'] :
                            "unknown" );

            $entries = split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);

            reset($entries);
            while (list(, $entry) = each($entries)) {
                $entry = trim($entry);
                if (preg_match("/^([0-9]+.[0-9]+.[0-9]+.[0-9]+)/", $entry, $ip_list)) {

                    $private_ip = array(
                        '/^0./',
                        '/^127.0.0.1/',
                        '/^192.168..*/',
                        '/^172.((1[6-9])|(2[0-9])|(3[0-1]))..*/',
                        '/^10..*/');

                    $found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);

                    if ($client_ip != $found_ip) {
                        $client_ip = $found_ip;
                        break;
                    }
                }
            }
        } else {
            $client_ip = (!empty($_SERVER['REMOTE_ADDR']) ) ?
                    $_SERVER['REMOTE_ADDR'] :
                    ( (!empty($_ENV['REMOTE_ADDR']) ) ?
                            $_ENV['REMOTE_ADDR'] :
                            "unknown" );
        }

        return $client_ip;
    }

    public function apiRestCall($method, $url, $data = false)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array("data" => $data)));
        $curlResponse = curl_exec($curl);
        $serverResponse = json_decode($curlResponse);
        curl_close($curl);
        return $serverResponse;
    }

}
