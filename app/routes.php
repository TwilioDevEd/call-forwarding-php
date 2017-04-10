<?php

$k = new \Klein\Klein();
$gp = ['GET', 'POST'];

$k->respond('GET', '/', handler('index'));
$k->respond('POST', path('welcome'), handler('welcome'));
$k->respond($gp, path('state-lookup'), handler('stateLookup'));
$k->respond($gp, path('collect-zip'), handler('collectZip'));
$k->respond($gp, path('set-state'), handler('setState'));
$k->respond($gp, path('call-senators'), handler('callSenators'));
$k->respond($gp, path('call-second-senator'), handler('callSecondSenator'));
$k->respond($gp, path('goodbye'), handler('goodbye'));

$k->dispatch();

// Helper functions
function path($path) {
    return "/callcongress/{$path}";
}

function handler($handler) {
    return ['\App\Controllers\CallCongress', $handler];
}
