<?php
header("Content-type: text/plain; charset=UTF-8");
$today = date("Y/m/d");

$url = 'https://www.drk7.jp/weather/xml/13.xml';
// $res = file_get_contents($url);
// $xml = htmlspecialchars($res,ENT_QUOTES);
$filename = 'webhook.txt';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$xml = curl_exec($ch);
curl_close($ch);

$dom = new DOMDocument('1.0','UTF-8');
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
$dom->loadXML($xml);
$rainfall = 0;

$xpath = new DOMXPath($dom);

//降水確率の取得
$result = $xpath->query("//area[@id = \"東京地方\"]/info[@date = \"$today\"]//period[@hour = \"06-12\" or @hour = \"12-18\"]");
foreach($result as $node){
    if ($rainfall < (int) $node->nodeValue){
        $rainfall = (int) $node->nodeValue;
    }
}
$rain_color = "#0000".sprintf("%02X",dechex($rainfall * 255 / 100));

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

//$fp = fopen($filename, 'r');
//$txt = fgets($fp);
//$txt = str_replace(array("\r\n", "\r", "\n"), '', $txt);

$txt = getenv("URL");

$message = [
    "text" => "今日の天気は「".$string."」\n降水確率は".$rainfall." %です"
];

$ch = curl_init();
$options = array(
    CURLOPT_URL => $txt,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query(array(
        'payload' => json_encode($message)
	))
);
curl_setopt_array($ch, $options);
curl_exec($ch);
curl_close($ch);
echo "data,".$rain_color.",".$weather_color;
?>

