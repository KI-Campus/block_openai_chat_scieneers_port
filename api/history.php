<?php

require_once('../../../config.php');
require_once($CFG->libdir . '/filelib.php');

if (get_config('block_openai_chat_scieneers', 'restrictusage') !== "0") {
    require_login();
}

$courseid = required_param('courseid', PARAM_INT);

$threadid = $SESSION->block_openai_chat_scieneers_threadids[$courseid] ?? null;

header('Content-Type: application/json');

if (!$threadid) {
    echo json_encode([
        'thread_id' => null,
        'messages' => [],
    ]);
    exit;
}

$apikey = get_config('block_openai_chat_scieneers', 'apikey');
$apiurl = trim(get_config('block_openai_chat_scieneers', 'apiurl'));

if (empty($apikey) || empty($apiurl)) {
    http_response_code(500);
    echo json_encode(['error' => 'API configuration missing']);
    exit;
}

$historyurl = preg_replace(
    '#/api/chat(?:/stream)?/?$#',
    '/api/chat/history/' . rawurlencode($threadid),
    $apiurl
);

$curl = new \curl();
$curl->setHeader([
    'Accept: application/json',
    'Api-Key: ' . $apikey,
]);

$result = $curl->get($historyurl);
$curlinfo = $curl->get_info();

if ($curlinfo['http_code'] != 200) {
    http_response_code($curlinfo['http_code'] ?: 502);
    echo $result ?: json_encode(['error' => 'History request failed']);
    exit;
}

echo $result;