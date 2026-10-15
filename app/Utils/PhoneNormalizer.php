<?php
namespace App\Utils;
class PhoneNormalizer
{
    public static function normalize(string $phone):string
    {
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
    public static function isValid(string $phone):bool
    {
        $normalized=self::normalize($phone);
        return preg_match('/^62[0-9]{9,13}$/',$normalized)===1;
    }
    public static function format(string $phone,string $style='whatsapp'):string
    {
        $normalized=self::normalize($phone);
        return match($style){
            'whatsapp'=>$normalized,
            'display'=>'+'.substr($normalized,0,2).' '.substr($normalized,2,3).' '.substr($normalized,5,4).' '.substr($normalized,9),
            'local'=>'0'.substr($normalized,2),
            default=>$normalized
        };
    }
}