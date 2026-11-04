<?php

namespace App\Helpers;

class Terbilang
{
    /**
     * Konversi angka nominal rupiah ke kalimat terbilang bahasa Indonesia.
     */
    public static function make(float|int $number): string
    {
        $raw = self::convert(abs((int) $number));
        $normalized = trim(preg_replace('/\s+/', ' ', $raw));

        return ($normalized ?: 'Nol').' Rupiah';
    }

    private static function convert(int $number): string
    {
        $words = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($number < 12) {
            return ' '.$words[$number];
        }

        if ($number < 20) {
            return self::convert($number - 10).' Belas';
        }

        if ($number < 100) {
            return self::convert((int) ($number / 10)).' Puluh'.self::convert($number % 10);
        }

        if ($number < 200) {
            return ' Seratus'.self::convert($number - 100);
        }

        if ($number < 1000) {
            return self::convert((int) ($number / 100)).' Ratus'.self::convert($number % 100);
        }

        if ($number < 2000) {
            return ' Seribu'.self::convert($number - 1000);
        }

        if ($number < 1000000) {
            return self::convert((int) ($number / 1000)).' Ribu'.self::convert($number % 1000);
        }

        if ($number < 1000000000) {
            return self::convert((int) ($number / 1000000)).' Juta'.self::convert($number % 1000000);
        }

        if ($number < 1000000000000) {
            return self::convert((int) ($number / 1000000000)).' Miliar'.self::convert($number % 1000000000);
        }

        return self::convert((int) ($number / 1000000000000)).' Triliun'.self::convert($number % 1000000000000);
    }
}
