<?php
namespace App\Utils;
class PhoneNormalizer
{
    public static function normalize(?string $phone):string
    {
        if ($phone === null || $phone === '') {
            return '';
        }

        $phone=preg_replace('/[^0-9]/','',$phone);
        if(str_starts_with($phone,'62')){
            return $phone;
        }
        if(str_starts_with($phone,'0')){
            return '62'.substr($phone,1);
        }
        if(str_starts_with($phone,'8')){
            return '62'.$phone;
        }
        return $phone;
    }
    public static function isValid(?string $phone):bool
    {
        $normalized=self::normalize($phone);
        return preg_match('/^62[0-9]{9,13}$/',$normalized)===1;
    }
    public static function format(?string $phone,string $style='international'):string
    {
        $normalized=self::normalize($phone);
        if ($style === 'whatsapp') {
            return $normalized;
        }

        $local = str_starts_with($normalized, '62')
            ? '0'.substr($normalized, 2)
            : $normalized;
        $compact = $local;
        $formatted = strlen($local) === 12
            ? substr($local, 0, 4).'-'.substr($local, 4, 4).'-'.substr($local, 8)
            : $local;

        return match($style){
            'international', 'display' => str_starts_with($normalized, '62')
                ? '+62 '.substr($normalized, 2, 3).'-'.substr($normalized, 5, 4).'-'.substr($normalized, 9)
                : $formatted,
            'local' => $formatted,
            'compact' => $compact,
            default => $normalized,
        };
    }
}