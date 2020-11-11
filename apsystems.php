<?
//https://doc.eedomus.com/view/Scripts

$adressOption = getArg('adress');
$valOption = getArg('val');

//$adressOption = '192.168.0.26';
//$valOption = 1;

$address = 'http://'.$adressOption.'/index.php/realtimedata/old_energy_graph?date=' . date('Y-m-d') . '&period=weekly';
$request = httpQuery($address, $action = 'GET', $post = NULL, $oauth_token = NULL, $headers = NULL, $use_cookies = false, $ignore_errors = true);

$xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>\n";
$xml .= "<APSYSTEMS>\n";

if (strpos($request, '<h1>Not Found</h1>')){
    $xml .= "<ETAT> AP Systems injoignable</ETAT>\n";
    $xml .= "</APSYSTEMS>";  
    // Alternative
    sdk_header('text/xml');
    echo $xml;
    return;
}

$xml .= "<ETAT> ok </ETAT>\n";
//{"energy":[{"date":"11\/02","energy":2.07},{"date":"11\/03","energy":3.2},{"date":"11\/04","energy":3.77},{"date":"11\/05","energy":4.09},{"date":"11\/06","energy":3.89},{"date":"11\/07","energy":3.2},{"date":"11\/08","energy":2.28}],"total_energy":22.5,"subtitle":""}
$returnValues = sdk_json_decode($request);
if ($valOption == 1) {
    $valreturn = $returnValues['energy'][count($returnValues['energy'])-1]['energy'];
} else {
    $valreturn = $returnValues['total_energy'];
}
$xml .= "<VALUE>" . $valreturn . "</VALUE>\n";
$xml .= "</APSYSTEMS>";  
sdk_header('text/xml');
echo $xml;

?>