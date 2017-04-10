<?php

namespace App\Controllers;

use App\QueryBuilder as DB;
use Twilio\Rest\Client;
use Twilio\Twiml;

class CallCongress
{
    /**
     * Render the index page.
     */
    public static function index($request, $response, $service)
    {
        return $service->render(__DIR__ . '/../views/index.html');
    }

    /**
     * Verify or collect State information.
     */
    public static function welcome($request, $response)
    {
        $fromState = $request->param('FromState');
        $twiml = new Twiml();

        if ($fromState) {
            $twiml->gather([
                'numDigits' => 1,
                'action' => url_for($request, 'set-state'),
                'method' => 'GET',
                'fromState' => $fromState
            ])->say(
                'Thank you for calling congress! It looks like ' .
                "you're calling from {$fromState}. " .
                'If this is correct, please press 1. Press 2 if ' .
                'this is not your current state of residence.'
            );
        } else {
            $twiml->gather([
                'numDigits' => 5,
                'action' => url_for($request, 'state-lookup'),
                'method' => 'POST'
            ])->say(
                'Thank you for calling Call Congress! If you wish to ' .
                'call your senators, please enter your 5-digit zip code.'
            );
        }

        $response->header('Content-Type', 'application/xml');

        return $twiml;
    }

    /**
     * Look up state from given zipcode.
     *
     * Once state is found, redirect to call_senators for forwarding.
     */
    public static function stateLookup($request, $response)
    {
        // NB: We don't do any error handling for a missing/erroneous zip code
        // in this sample application. You, gentle reader, should handle that
        // edge case before deploying this code.
        $zipCode = DB::fromTable('zipcodes')
            ->where('zipcode', '=', $request->param('Digits'))
            ->first();

        $url = url_for($request,
            "call-senators?state_id={$zipCode['state_id']}");

        return $response->redirect($url);
    }

    /**
     * If our state guess is wrong, prompt user for zip code.
     */
    public static function collectZip($request, $response)
    {
        $twiml = new Twiml();

        $twiml->gather([
            'numDigits' => 5,
            'action' => url_for($request, 'state-lookup'),
            'method' => 'POST'
        ])->say(
            'If you wish to call your senators, please ' .
            'enter your 5-digit zip code.'
        );

        $response->header('Content-Type', 'application/xml');

        return $twiml;
    }

    /**
     * Set state for senator call list.
     *
     * Set user's state from confirmation or user-provided Zip.
     * Redirect to call_senators route.
     */
    public static function setState($request, $response)
    {
        // By default we set the path in case the conditions were false
        $url = url_for($request, "collect-zip");

        // Get the digit pressed by the user
        $digitsProvided = $request->param('Digits');

        // Set state if State correct, else prompt for zipcode.
        if ($digitsProvided == 1) {
            $state = DB::fromTable('states')
                ->where('name', '=', $request->param('CallerState'))
                ->first();
            if ($state) {
                $url = url_for($request,
                    "call-senators?state_id={$state['id']}");
            }
        }

        return $response->redirect($url);
    }

    /**
     * Route for connecting caller to both of their senators.
     */
    public static function callSenators($request, $response)
    {
        list($senator_one, $senator_two) = DB::fromTable('senators')
            ->where('state_id', '=', $request->param('state_id'))
            ->all();

        $twiml = new Twiml();

        $twiml->say(
            "Connecting you to {$senator_one['name']}. " .
            'After the senator\'s office ends the call, you will ' .
            "be re-directed to {$senator_two['name']}."
        )->dial($senator_one['phone'], [
            'action' => url_for($request,
                "call-second-senator?senator_id={$senator_two['id']}")
          ]);

        $response->header('Content-type', 'application/xml');

        return $twiml;
    }

    /**
     * Forward the caller to their second senator.
     */
    public static function callSecondSenator($request, $response)
    {
        $senator = DB::fromTable('senators')
            ->where('id', '=', $request->param('senator_id'))
            ->first();

        $twiml = new Twiml();

        $twiml
          ->say("Connecting you to {$senator['name']}")
          ->dial($senator['phone'], [
              'action' => url_for($request, 'end-call')
          ]);

        $response->header('Content-type', 'application/xml');

        return $twiml;
    }

    /**
     * Thank user & hang up.
     */
    public static function goodbye($request, $response)
    {
        $twiml = new Twiml();
        $twiml->say(
            'Thank you for using Call Congress! ' .
            'Your voice makes a difference. Goodbye.'
        )->hangup();

        $response->header('Content-type', 'application/xml');

        return $twiml;
    }
}

function url_for($request, $path) {
    $protocol = $request->isSecure() ? 'https://' : 'http://';
    $host = $request->headers()->get('Host');
    return $protocol . $host . '/callcongress/' . $path;
}
