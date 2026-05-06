<?php
use Config\Config;

function getGoogleDriveFileId($url): string
{
    preg_match('/\/d\/([a-zA-Z0-9_-]+)(?:\/|$)/', $url, $matches);
    return $matches[1] ?? "";
}
function getYoutubeVideoID($url)
{
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/';

    if (preg_match($pattern, $url, $matches)) {
        return $matches[1]; // Trả về video ID
    }

    return false; // Không phải link hợp lệ
}


function videoFrame($url, $type)
{
    if ($type === "youtube") {
        return "https://www.youtube.com/embed/" . getYoutubeVideoID($url);
    } else {
        return "https://drive.google.com/file/d/" . getGoogleDriveFileId($url) . "/preview";
    }
}

function intToRoman($num): string
{
    $map = [
        1000 => 'M',
        900 => 'CM',
        500 => 'D',
        400 => 'CD',
        100 => 'C',
        90 => 'XC',
        50 => 'L',
        40 => 'XL',
        10 => 'X',
        9 => 'IX',
        5 => 'V',
        4 => 'IV',
        1 => 'I'
    ];

    $result = '';

    foreach ($map as $value => $symbol) {
        while ($num >= $value) {
            $result .= $symbol;
            $num -= $value;
        }
    }

    return $result;
}
function remove_emoji(string $string): string
{

    /**
     * @see https://unicode.org/charts/PDF/UFE00.pdf
     */
    $variant_selectors = '[\x{FE00}–\x{FE0F}]?'; // ? - optional

    /**
     * There are many sets of modifiers
     * such as skin color modifiers and etc
     *
     * Not used, because this range already included
     * in 'Match Miscellaneous Symbols and Pictographs' range
     * $skin_modifiers = '[\x{1F3FB}-\x{1F3FF}]';
     *
     * Full list of modifiers:
     * https://unicode.org/emoji/charts/full-emoji-modifiers.html
     */

    // Match Enclosed Alphanumeric Supplement
    $regex_alphanumeric = "/[\x{1F100}-\x{1F1FF}]$variant_selectors/u";
    $clear_string = preg_replace($regex_alphanumeric, '', $string);

    // Match Miscellaneous Symbols and Pictographs
    $regex_symbols = "/[\x{1F300}-\x{1F5FF}]$variant_selectors/u";
    $clear_string = preg_replace($regex_symbols, '', $clear_string);

    // Match Emoticons
    $regex_emoticons = "/[\x{1F600}-\x{1F64F}]$variant_selectors/u";
    $clear_string = preg_replace($regex_emoticons, '', $clear_string);

    // Match Transport And Map Symbols
    $regex_transport = "/[\x{1F680}-\x{1F6FF}]$variant_selectors/u";
    $clear_string = preg_replace($regex_transport, '', $clear_string);

    // Match Supplemental Symbols and Pictographs
    $regex_supplemental = "/[\x{1F900}-\x{1F9FF}]$variant_selectors/u";
    $clear_string = preg_replace($regex_supplemental, '', $clear_string);

    // Match Miscellaneous Symbols
    $regex_misc = "/[\x{2600}-\x{26FF}]$variant_selectors/u";
    $clear_string = preg_replace($regex_misc, '', $clear_string);

    // Match Dingbats
    $regex_dingbats = "/[\x{2700}-\x{27BF}]$variant_selectors/u";
    $clear_string = preg_replace($regex_dingbats, '', $clear_string);

    return $clear_string;
}
function TimeAgo($time, $lang = "vi"): string
{

    if (!is_numeric($time))
        $time = strtotime($time);
    $now = time();
    $ago = $now - $time;
    $ago = ($ago > 0) ? $ago : 0;

    if ($lang === '')
        $lang = 'vi';

    $language = [
        'vi' => [
            'now' => 'Vừa xong',
            'second' => '%1s giây trước',
            'minute' => '%1s phút trước',
            'hour' => '%1s giờ trước',
            'day' => '%1s ngày trước',
            'week' => '%1s tuần trước',
            'month' => '%1s tháng trước',
            'year' => '%1s năm trước',
            'decade' => '%1s thế kỷ trước'
        ],
        'en' => [
            'now' => 'Just Now',
            'second' => '%1s seconds ago',
            'minute' => '%1s minutes ago',
            'hour' => '%1s hours ago',
            'day' => '%1s days ago',
            'week' => '%1s weeks ago',
            'month' => '%1s months ago',
            'year' => '%1s years ago',
            'decade' => '%1s decades ago'
        ]
    ];

    if (!isset($language[$lang])) {
        $lang = "en";
    }

    if ($ago < 13) {
        $count = "";
        $type = "now";
    } else {
        if ($ago < 60) {
            //Check second
            $count = $ago;
            $type = "second";
        } else {
            if ($ago < (60 * 59.5)) {
                //Check minute
                $count = round($ago / 60);
                $type = "minute";
            } else {
                if ($ago < (60 * 60 * 23.5)) {
                    //Check hour
                    $count = round($ago / (60 * 60));
                    $type = "hour";
                } else {
                    if ($ago < (60 * 60 * 24 * 6.5)) {
                        //Check day
                        $count = round($ago / (60 * 60 * 24));
                        $type = "day";
                    } else {
                        if ($ago < (60 * 60 * 24 * 29.5)) {
                            //Check week
                            $count = round($ago / (60 * 60 * 24 * 7));
                            $type = "week";
                        } else {
                            if ($ago < (60 * 60 * 24 * 30 * 11.5)) {
                                //Check month
                                $count = round($ago / (60 * 60 * 24 * 30));
                                $type = "month";
                            } else {
                                if ($ago < (60 * 60 * 24 * 30 * 12 * 9.5)) {
                                    //Check year
                                    $count = round($ago / (60 * 60 * 24 * 30
                                        * 12));
                                    $type = "year";
                                } else {
                                    $count = round($ago / (60 * 60 * 24 * 30
                                        * 12));
                                    $type = "decade";
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return str_replace("%1s", $count, $language[$lang][$type]);
}
function get_ip_address()
{
    foreach (array('HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip); // just to be safe

                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
}

function minifier($code): array|string|null
{
    $search = array(

        // Remove whitespaces after tags
        '/\>[^\S ]+/s',

        // Remove whitespaces before tags
        '/[^\S ]+\</s',

        // Remove multiple whitespace sequences
        '/(\s)+/s',

        // Removes comments
        '/<!--(.|\s)*?-->/'
    );
    $replace = array('>', '<', '\\1');
    return preg_replace($search, $replace, $code);
}


if (!function_exists('str_starts_with')) {
    function str_starts_with($string, $needle): bool
    {
        return substr($string, 0, strlen($needle)) === $needle;
    }
}

function is_url($url)
{
    return filter_var($url, FILTER_VALIDATE_URL);
}


function object(): stdClass
{
    return new stdClass();
}
function remove_html($text)
{
    if (is_array($text)) {
        return array_map(__FUNCTION__, $text);
    } else {
        $search = array(
            '@<script[^>]*?>.*?</script>@si', // Chứa javascript
            '@<[\/\!]*?[^<>]*?>@si', // Chứa các thẻ HTML
            '@<style[^>]*?>.*?</style>@siU', // Chứa các thẻ style
            '@<![\s\S]*?--[ \t\n\r]*>@' // Xóa toàn bộ dữ liệu bên trong các dấu ngoặc "<" và ">"
        );
        $text = preg_replace($search, '', $text);
        $text = strip_tags($text);
        return trim($text);
    }
}



function curl_data($url, $referer = 'https://google.com')
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_REFERER, $referer);

    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

    $resp = curl_exec($curl);
    curl_close($curl);

    return $resp;
}

function getConf($type)
{
    $file = ROOT_PATH . '/backend/Config/' . $type . '.php';

    if (!file_exists($file)) {
        return [];
    }

    return include $file;
}

function setConf($type, $config)
{
    $type = ROOT_PATH . '/backend/Config/' . $type . '.php';
    file_put_contents($type, '<?php return ' . var_export($config, true) . ';');
}

function RandNum($min, $max): int
{
    $range = $max - $min;
    if ($range < 1)
        return $min; // not so random...
    $log = ceil(log($range, 2));
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd > $range);
    return $min + $rnd;
}
function RandStr($length): string
{
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet .= "0123456789";
    $max = strlen($codeAlphabet); // edited

    for ($i = 0; $i < $length; $i++) {
        $token .= $codeAlphabet[RandNum(0, $max - 1)];
    }
}

function __($key, $replace = [], $default_locale = null): string
{
    $locale = $default_locale ?? ($_SESSION['locale'] ?? 'vi');
    $segments = explode('.', $key, 2);
    $file = $segments[0] ?? 'common';
    $path = ROOT_PATH . "/resources/lang/$locale/$file.php";

    static $translations = [];

    if (!isset($translations[$locale][$file])) {
        if (file_exists($path)) {
            $translations[$locale][$file] = include $path;
        } else {
            $translations[$locale][$file] = [];
        }
    }

    $current = $translations[$locale][$file][$segments[1]] ?? null;

    if (!is_string($current)) {
        if (!empty($default_locale)) {
            return $key;
        } else {
            return __($key, $replace, "vi");
        }
    }

    foreach ($replace as $k => $v) {
        $current = str_replace(":$k", $v, $current);
    }

    return $current;
}

