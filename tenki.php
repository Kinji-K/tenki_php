<?php
header("Content-type: text/plain; charset=UTF-8");
$today = date("Y/m/d");

$url = 'https://www.drk7.jp/weather/xml/13.xml';
$res = file_get_contents($url);
$xml = htmlspecialchars($res,ENT_QUOTES);

$dom = new DOMDocument('1.0','UTF-8');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($res);
$rainfall = 0;

$xpath = new DOMXPath($dom);

//降水確率の取得
$result = $xpath->query("//area[@id = \"東京地方\"]/info[@date = \"$today\"]//period[@hour = \"06-12\" or @hour = \"12-18\"]");
foreach($result as $node){
    if ($rainfall < (int) $node->nodeValue){
        $rainfall = (int) $node->nodeValue;
    }
}
$rain_color = "#0000".sprintf("%02X",dechex(rainfall * 255 / 100));

$result = $xpath->query("//area[@id = \"東京地方\"]/info[@date = \"$today\"]/weather")->item(0);
$string = $result->nodeValue;
if (strpos($string,"雪")){
    $weather_color = "#FFFFFF";
} elseif (strpos($string,"霰") !== false){  
    $weather_color = "#CCFFFF";
} elseif (strpos($string,"雨") !== false){
    $weather_color = "#0000FF";
} elseif ((strpos($string,"くもり") !== false ) and (strpos($string,"晴れ") !== false)){
    $weather_color = "#FFFFCC";
} elseif (strpos($string,"晴れ") !== false){
    $weather_color = "#FFFF00";
} elseif (strpos($string,"くもり") !== false){
    $weather_color = "#C0C0C0";
}

echo "data,".$rain_color.",".$weather_color;
?>

