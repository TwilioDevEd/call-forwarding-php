<?php

use \App\Controllers\CallCongress;

$k = new \Klein\Klein();
$gp = ['GET', 'POST'];

$k->respond('GET', '/', function($request, $response, $service) {
    return $service->render(__DIR__ . '/views/index.html');
});

$k->respond('POST', path('welcome'), function($request, $response) {
    $fromState = $request->param('FromState');
    $twiml = CallCongress::welcome($fromState, base_url($request));

    $response->header('Content-Type', 'application/xml');
    return $twiml;
});

$k->respond($gp, path('state-lookup'), function($request, $response) {
    $digits = $request->param('Digits');
    $url = CallCongress::stateLookup($digits, base_url($request));

    return $response->redirect($url);
});

$k->respond($gp, path('collect-zip'), function($request, $response) {
    $twiml = CallCongress::collectZip(base_url($request));

    $response->header('Content-Type', 'application/xml');
    return $twiml;
});

$k->respond($gp, path('set-state'), function() {
    $digitsProvided = $request->param('Digits');
    $callerState = $request->param('CallerState');
    $url = CallCongress::setState($digitsProvided, $callerState, base_url($request));

    return $response->redirect($url);
});

$k->respond($gp, path('call-senators'), function() {
    $twiml = CallCongress::callSenators($stateId, $baseUrl);

    $response->header('Content-Type', 'application/xml');
    return $twiml;
});

$k->respond($gp, path('call-second-senator'), function() {
    $senatorId = $request->param('senator_id');
    $twiml = CallCongress::callSecondSenator($senatorId, $baseUrl);

    $response->header('Content-Type', 'application/xml');
    return $twiml;
});

$k->respond($gp, path('goodbye'), function() {
    return CallCongress::goodbye();
});

$k->dispatch();

// Helper functions
function path($path)
{
    return "/callcongress/{$path}";
}

function base_url($request)
{
    $protocol = $request->isSecure() ? 'https://' : 'http://';
    $host = $request->headers()->get('Host');
    return $protocol . $host . path('');
}
