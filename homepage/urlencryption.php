<?php
// UrlEncryption.php
class UrlEncryption {
    private static $multiplier = 525325.24;
    
    public static function encrypt($data) {
        $id = (double)$data * self::$multiplier;
        return base64_encode($id);
    }
    
    public static function decrypt($data) {
        $url_id = base64_decode($data);
        $id = (double)$url_id / self::$multiplier;
        return round($id); // Round to get original integer
    }
    
    // For multiple parameters
    public static function encryptArray($dataArray) {
        $encrypted = array();
        foreach($dataArray as $key => $value) {
            $encrypted[$key] = self::encrypt($value);
        }
        return $encrypted;
    }
    
    public static function decryptArray($encryptedArray) {
        $decrypted = array();
        foreach($encryptedArray as $key => $value) {
            $decrypted[$key] = self::decrypt($value);
        }
        return $decrypted;
    }
}
?>
