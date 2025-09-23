<?php

if (! function_exists('format_count')) {
    function format_count($number)
    {
        if ($number >= 1000000) {
            return number_format($number / 1000000, 1) . 'M';
        }

        if ($number >= 1000) {
            return number_format($number / 1000, 1) . 'k';
        }

        return (string) $number;
    }
}
