<?php

if (!function_exists('helper_tituloPagina')) {
    function helper_tituloPagina(){
        return "SISTEMA DE GESTIÓN DE GANADO";
    }
}

if (!function_exists('helper_versionApp')) {
    function helper_versionApp(){
        return "1.0.0";
    }
}

if (!function_exists('helper_encrypt')) {
    function helper_encrypt(string $string)
    {
        $result = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr(env('PHP_ENCRYPT_AND_DECRYPT_KEY'), ($i % strlen(env('PHP_ENCRYPT_AND_DECRYPT_KEY'))) - 1, 1);
            $char = chr(ord($char) + ord($keychar));
            $result .= $char;
        }
        return base64_encode($result);
    }
}

if (!function_exists('helper_decrypt')) {
    function helper_decrypt(string $string)
    {
        $result = '';
        $string = base64_decode($string);
        for ($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr(env('PHP_ENCRYPT_AND_DECRYPT_KEY'), ($i % strlen(env('PHP_ENCRYPT_AND_DECRYPT_KEY'))) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $result .= $char;
        }
        return $result;
    }
}