<?php

$userId = '';
$passwd = '';

if (php_sapi_name() == 'cli' && $argc >= 3) {
    $userId = $argv[1];
    $passwd = $argv[2];
} else {
    echo "[*] Usage: $argv[0] 'username' 'Pas3w0RD@'", PHP_EOL;
    exit(0);
}

$data = send_request_auth($userId, $passwd);

if ($data && is_string($data)) {

    $data = json_decode($data, true);

    if (!$data) {
        throw new Exception(json_last_error_msg());
    }

} else {
    throw new Exception("oops");
}

echo json_encode(
    $data,
    JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
), PHP_EOL;


if (!empty($data['result'][0]['authToken'])) {
    echo "[+] authToken is: " . encrypt_blob($data['result'][0]['authToken']), PHP_EOL;
} else {
    echo "[-] Fail get authToken", PHP_EOL;
}

/* ---------------------------------------- */

function request($url)
{
    $c_fd = curl_init();
    $c_opts = [

        CURLOPT_URL => $url,

        CURLOPT_ENCODING   => 'gzip, deflate',
        // CURLOPT_USERAGENT  => 'Windows 10',
        CURLOPT_USERAGENT  => 'okhttp/4.2.1',
        CURLOPT_HTTPHEADER => [
            "Accept: application/json",
        ],

        CURLOPT_RETURNTRANSFER => true,

    ];

    curl_setopt_array($c_fd, $c_opts);
    $data = curl_exec($c_fd);
    curl_close($c_fd);

    return $data;
}

function pad_ANSI_X923($data, $len_pad)
{
    $n_pad = strlen($data) % $len_pad;

    if ($n_pad) {
        $data .= str_repeat("\x00", ($len_pad - 1) - $n_pad);
        $data .= chr($len_pad - $n_pad);
    }

    return $data;
};

function encrypt_blob($input)
{
    static $block_sz = 16;

    // pass in app
    static $ARIA_PASSWORD = 'myXyzfamily-aria-password';

    $pass_hash = hash('sha256', $ARIA_PASSWORD);
    $pass_enc = $pass_hash;

    if (strlen($pass_enc) > 0x20) {
        $pass_enc = substr($pass_enc, 0, 0x20);
    }

    $blocks = str_split($input, $block_sz);

    if (!count($blocks) || count($blocks) > 2) {
        throw new Exception("00ps");
    }

    foreach ($blocks as &$block) {
        $block = pad_ANSI_X923($block, $block_sz);
    }

    $input = implode($blocks);

    $output = openssl_encrypt(
        $input,
        'aria256',
        $pass_enc,
        OPENSSL_ZERO_PADDING,
        str_repeat("\x00", 16)
    );

    if (!$output) {
        throw new Exception('o0ps');
    }

    return $output;
}

function send_request_auth($userId, $passwd)
{
    $userId = encrypt_blob($userId);
    $passwd = encrypt_blob(base64_encode($passwd) . "\n");

    $userId =
        str_replace(
            ['&', '+', '='],
            ['%26', '%2B', '%3D'], // hardcode
            $userId
        ) . "\n";

    $passwd =
        str_replace(
            ['&', '+', '='],
            ['%26', '%2B', '%3D'], // hardcode
            $passwd
        ) . "\n";

    $query = [
        'loginType'     => 'TOTAL',
        'userId'        => $userId,
        'userPassword'  => $passwd,

        'flagOtpAuth'   => '',
        'smsAuthNumber' => '',
        'authToken'     => '',
        'tokenType'     => '',
    ];

    $url  = 'https://auction.autobell.co.kr/api/auction/requestAuctionLogin.do?';
    $url .= http_build_query($query);

    return request($url);
}
