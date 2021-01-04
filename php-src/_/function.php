<?php
use \Zend\Debug\Debug as ZendDebug;

function array_keys_exist(array $array, $keys)
{
    $count = 0;
    if (!is_array($keys)) {
        $keys = func_get_args();
        array_shift($keys);
    }
    foreach ($keys as $key) {
        if (isset($array[$key]) || array_key_exists($key, $array)) {
            $count++;
        }
    }
    return count($keys) === $count;
}

//function defination to convert array to xml
function array_to_xml($array, &$xml_user_info)
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            if (!is_numeric($key)) {
                $subnode = $xml_user_info->addChild("$key");
                array_to_xml($value, $subnode);
            } else {
                $subnode = $xml_user_info->addChild("item$key");
                array_to_xml($value, $subnode);
            }
        } else {
            $xml_user_info->addChild("$key", htmlspecialchars("$value"));
        }
    }
}

function startsWith($haystack, $needle)
{
    return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
}

function endsWith($haystack, $needle)
{
    return substr_compare($haystack, $needle, -strlen($needle)) === 0;
}

function randHexColor()
{
    $rand = str_pad(dechex(rand(0x000000, 0xFFFFFF)), 6, 0, STR_PAD_LEFT);
    return '#' . $rand;
}

function ifNotSet(&$arr, $key, $replace)
{
    if (isset($arr[$key])) {
        return $arr[$key];
    } else {
        $arr[$key] = $replace;
        return $replace;
    }
}

function ifNull($var, $replace)
{
    if ($var === null) {
        return $replace;
    } else {
        return $var;
    }
}

function ifEmpty($var, $replace)
{
    if ($var === "" || empty($var)) {
        return $replace;
    } else {
        return $var;
    }
}

function ifNullEmpty($var, $replace)
{
    if ($var === null || $var === "" || empty($var)) {
        return $replace;
    } else {
        return $var;
    }
}

function isNull($var)
{
    if ($var === null) {
        return true;
    } else {
        return false;
    }
}

function isEmpty($var)
{
    if ($var === "" || empty($var)) {
        return true;
    } else {
        return false;
    }
}

function isNullEmpty($var)
{
    if ($var === null || $var === "" || empty($var)) {
        return true;
    } else {
        return false;
    }
}

function csvToArrayWithHeader($file)
{
    $csv = array_map('str_getcsv', file($file));
    array_walk($csv, function (&$a) use ($csv) {
        $a = array_combine($csv[0], $a);
    });
    array_shift($csv); # remove column header
    return $csv;
}

function recur_ksort(&$array)
{
    foreach ($array as &$value) {
        if (is_array($value)) {
            recur_ksort($value);
        }

    }
    return ksort($array);
}

function recur_k1sort(&$array)
{
    foreach ($array as &$value) {
        if (is_array($value)) {
            sort($value);
        }

    }
    return ksort($array);
}

function zdebug($var, $die = false)
{
    ZendDebug::dump($var);
    if ($die) {
        die();
    }
}

function get_current_week_range($format = "Y-m-d"){
    $monday = strtotime("last monday");
    $monday = date('w', $monday)==date('w') ? $monday+7*86400 : $monday;
    $sunday = strtotime(date("Y-m-d",$monday)." +6 days");
    $this_week_sd = date($format,$monday);
    $this_week_ed = date($format,$sunday);
    // echo "Current week range from $this_week_sd to $this_week_ed ";
    return [
        "monday" =>$this_week_sd,
        "sunday" =>$this_week_ed,
    ];
}