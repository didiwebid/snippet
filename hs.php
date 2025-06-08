function hs($input, $length = 7, $algorithm = "sha256", $hashsecret = "") {
    $secret = (isset($GLOBALS["secret"]) ? $GLOBALS["secret"] : "") . $hashsecret;
    return substr(hash($algorithm, $input . $secret), 0, $length);
}
