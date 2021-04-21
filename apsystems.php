<?php
//https://doc.eedomus.com/view/Scripts

$adressOption = getArg('adress');
//$adressOption = '192.168.0.26';
$valOption = getArg('val');
//$valOption = 1; // total du jour
//$valOption = 2; // total de la semaine
//$valOption = 3; // temps réels

function sdk_getUrlJson($adress = '', $val = 3) {
    if ($adress == '') {
        return false;
    }
    if ($val == 1 || $val == 2) {
        $page = 'old_energy_graph?date=' . date('Y-m-d') . '&period=weekly';
    } else { // val = 3
        $page = 'old_power_graph';
    }
    $address = 'http://' . $adress . '/index.php/realtimedata/' . $page;
    $request = httpQuery($address, $action = 'GET', $post = NULL, $oauth_token = NULL, $headers = NULL, $use_cookies = false, $ignore_errors = true);
    if (strpos($request, '<h1>Not Found</h1>')) {
        return false;
    } else {
        //{"energy":[{"date":"11\/02","energy":2.07},{"date":"11\/03","energy":3.2},...],"total_energy":22.5,"subtitle":""}
        //{"power":[{"time":1618980075000,"each_system_power":201},{"time":1618985175000,"each_system_power":223},...],"today_energy":"11.23","subtitle":""}
        return sdk_json_decode($request);
    }
}

function sdk_getEnergy($values = '', $val = 3) {
    if ($values == '') {
        return false;
    }
    if ($val == 1) {
        $valreturn = $values['energy'][count($values['energy'])-1]['energy'];
    } elseif ($val == 2) {
        $valreturn = $values['total_energy'];
    } else { // val = 3
        $valreturn = ($values['power'][count($values['power'])-1]['each_system_power'])/1000;
    }
    return $valreturn;
}

$returnValues = sdk_getUrlJson($adressOption, $valOption);

$xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>\n";
$xml .= "<APSYSTEMS>\n";

if ($returnValues == false){
    $xml .= "<ETAT>AP Systems down</ETAT>\n";
    $xml .= "</APSYSTEMS>";  
    // Alternative
    sdk_header('text/xml');
    echo $xml;
    return;
} else {
    $energyCount = sdk_getEnergy($returnValues, $valOption);
}

$xml .= "<ETAT>ok</ETAT>\n";
$xml .= "<VALUE>" . $energyCount . "</VALUE>\n";
$xml .= "</APSYSTEMS>";  
sdk_header('text/xml');
echo $xml;

?>