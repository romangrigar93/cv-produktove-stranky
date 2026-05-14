<?php

namespace ToretZasilkovna\Toret\Library;

class Text
{
    /**
     * Generate random string
     */
    public static function generate_random_sring($length = 20): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Generate random number string
     */
    public static function generate_random_number_sring($length = 7): string
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    function clean_string($string)
    {
        $string = str_replace(' ', '', $string);

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    }

    function alter_wc_statuses($array): array
    {
        $new_array = array();
        foreach ($array as $key => $value) {
            $new_array[substr($key, 3)] = $value;
        }

        return $new_array;
    }

}