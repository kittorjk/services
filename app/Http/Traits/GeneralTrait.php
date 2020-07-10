<?php
/**
 * Created by PhpStorm.
 * User: Admininstrador
 * Date: 18/07/2017
 * Time: 05:42 PM
 */

namespace App\Http\Traits;

//use App\File;
//use Illuminate\Database\Eloquent\ModelNotFoundException;
//use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Exception;

trait GeneralTrait {
    
    public function convert_number_to_words($number)
    {
        //$hyphen      = '-';
        $conjunction = ' y ';
        //$separator   = ', ';
        $negative    = 'negativo ';
        //$decimal     = ' point ';
        $dictionary  = array(
            0                   => 'cero',
            1                   => 'un', //'uno',
            2                   => 'dos',
            3                   => 'tres',
            4                   => 'cuatro',
            5                   => 'cinco',
            6                   => 'seis',
            7                   => 'siete',
            8                   => 'ocho',
            9                   => 'nueve',
            10                  => 'diez',
            11                  => 'once',
            12                  => 'doce',
            13                  => 'trece',
            14                  => 'catorce',
            15                  => 'quince',
            16                  => 'diéciseis',
            17                  => 'diecisiete',
            18                  => 'dieciocho',
            19                  => 'diecinueve',
            20                  => 'veinte',
            21                  => 'veintiun', //'veintiuno',
            22                  => 'veintidos',
            23                  => 'veintitres',
            24                  => 'veinticuatro',
            25                  => 'veinticinco',
            26                  => 'veintiseis',
            27                  => 'veintisiete',
            28                  => 'veintiocho',
            29                  => 'veintinueve',
            30                  => 'treinta',
            40                  => 'cuarenta',
            50                  => 'cincuenta',
            60                  => 'sesenta',
            70                  => 'setenta',
            80                  => 'ochenta',
            90                  => 'noventa',
            100                 => 'cien',
            200                 => 'doscientos',
            300                 => 'trescientos',
            400                 => 'cuatrocientos',
            500                 => 'quinientos',
            600                 => 'seiscientos',
            700                 => 'setecientos',
            800                 => 'ochocientos',
            900                 => 'novecientos',
            1000                => 'mil',
            1000000             => 'millón',
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . Self::convert_number_to_words(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 31:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $conjunction . ($units==1 ? 'un' : $dictionary[$units]);
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = ($remainder==0 ? $dictionary[intval($hundreds)*100] : (intval($hundreds)==1 ? 'ciento' : 
                        $dictionary[intval($hundreds)*100])) . ' ';
                if ($remainder) {
                    $string .= Self::convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = ($numBaseUnits==1 ? 'un' : Self::convert_number_to_words($numBaseUnits)).' '.
                        $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : ' '; //$separator previously used separator instead of space
                    $string .= Self::convert_number_to_words($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            //$string .= $decimal;
            $string .= ' '.str_pad($fraction, 2, "0", STR_PAD_RIGHT).'/100';
            /*
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
            */
        }

        return $string; //ucfirst($string); Upper case for every word
    }    
    
}
