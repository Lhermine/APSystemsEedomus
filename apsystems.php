<?php
//https://doc.eedomus.com/view/Scripts

$adressOption = getArg('adress');
//$adressOption = '192.168.0.26';
$valOption = getArg('val');
//$valOption = 1; // Daily produced energy
//$valOption = 2; // Weekly produced energy
//$valOption = 3; // Real time produced energy
//$valOption = 4; // Imported(+)/Exported(-) energy in real time

function sdk_getUrlJson($adress = '', $val = 4) {
    if ($adress == '') {
        return false;
    }
    if ($val == 1 || $val == 2) {
        $page = 'realtimedata/old_energy_graph?date=' . date('Y-m-d') . '&period=weekly';
    } else { // val = 3 or val = 4
        if ($val == 3) {
            $page = 'realtimedata/old_power_graph';
        } else { // val = 4
            $page = 'meter/old_meter_power_graph';
        }    
    }
    $address = 'http://' . $adress . '/index.php/' . $page;
    $request = httpQuery($address, $action = 'GET', $post = NULL, $oauth_token = NULL, $headers = NULL, $use_cookies = false, $ignore_errors = true);
    if (strpos($request, '<h1>Not Found</h1>')) {
        return false;
    } else {
        //{"energy":[{"date":"11\/02","energy":2.07},{"date":"11\/03","energy":3.2},...],"total_energy":22.5,"subtitle":""}
        //{"power":[{"time":1618980075000,"each_system_power":201},{"time":1618985175000,"each_system_power":223},...],"today_energy":"11.23","subtitle":""}
        //{"power2":[{"time":1654939373000,"powerA":1784,"powerB":0,"powerC":0},{"time":1654939673000,"powerA":1844,"powerB":0,"powerC":0},...],"today_energy":"0","subtitle":""}
        return sdk_json_decode($request);
    }
}

function sdk_getEnergy($values = '', $val = 4) {
    if ($values == '') {
        return false;
    }
    if ($val == 1) {
        $valreturn = $values['energy'][count($values['energy'])-1]['energy'];
    } elseif ($val == 2) {
        $valreturn = $values['total_energy'];
    } elseif ($val == 3) {
        $valreturn = ($values['power'][count($values['power'])-1]['each_system_power'])/1000;
    } else { // val = 4
        $valreturn = ($values['power2'][count($values['power2'])-1]['powerA'])/1000;
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
