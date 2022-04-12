<?php
ini_set("display_errors", "1");
ini_set("display_startup_errors", "1");
ini_set('error_reporting', E_ALL);

//AUTH
require_once 'auth.php';

$task = $_REQUEST['data']['FIELDS_AFTER']['ID'];
$event = $_REQUEST['event'];

if ($event == 'ONTASKADD') {
    sleep(3);
    $taskGet = executeREST(
        'task.item.getdata',
        array(
            $task,

        ),
        $domain, $auth, $user);

    $tags = $taskGet['result']['TAGS'][0];
    $deal = $taskGet['result']['UF_CRM_TASK'][0];

    if (!empty($deal) and $tags != 'Автоматика') {
        preg_match_all("/\d+/", $deal, $matches);
        $deal = $matches[0][0];
        $getdeal = executeREST(
            'crm.deal.get',
            array(
                'ID' => $deal,
            ),
            $domain, $auth, $user);

        $idOfBP = $getdeal['result']['UF_CRM_1647844191'];
        if (empty($idOfBP)) {
            exit;
        }

        $listGet = executeREST(
            'bizproc.workflow.instances',
            array(
                'select' => array('ID'),
                'order' => array('STARTED' => 'DESC'),
                'filter' => array('DOCUMENT_ID' => $idOfBP),
            ),
            $domain, $auth, $user);

        $idOfWf = $listGet['result'][0]['ID'];

        $killBP = executeREST(
            'bizproc.workflow.terminate',
            array(
                'ID' => $idOfWf,
            ),
            $domain, $auth, $user);


    } else {

    }
} else {
    $taskGet = executeREST(
        'task.item.getdata',
        array(
            $task,

        ),
        $domain, $auth, $user);

    $tags = $taskGet['result']['TAGS'][0];
    $deal = $taskGet['result']['UF_CRM_TASK'][0];
    $taskStatus = $taskGet['result']['STATUS'];
    preg_match_all("/\d+/", $deal, $matches);
    $deal = $matches[0][0];

    if (!empty($deal) and $tags != 'Автоматика' and $taskStatus == 5) {

        $getdeal = executeREST(
            'crm.deal.get',
            array(
                'ID' => $deal,
            ),
            $domain, $auth, $user);
        $idOfBP = $getdeal['result']['UF_CRM_1647844191'];

        if (empty($idOfBP)) {
            exit;
        }

        $startworkflow = executeREST(
            'bizproc.workflow.start',
            array(
                'TEMPLATE_ID' => '187',
                'DOCUMENT_ID' => array(
                    'lists', 'BizprocDocument', $idOfBP,
                ),
            ),
            $domain, $auth, $user);

    } else {

    }
}

function executeREST($method, array $params, $domain, $auth, $user)
{
    $queryUrl = 'https://' . $domain . '/rest/' . $user . '/' . $auth . '/' . $method . '.json';
    $queryData = http_build_query($params);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $queryUrl,
        CURLOPT_POSTFIELDS => $queryData,
    ));
    return json_decode(curl_exec($curl), true);
    curl_close($curl);
}

function writeToLog($data, $title = '')
{
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/gettask.log', $log, FILE_APPEND);
    return true;
}
