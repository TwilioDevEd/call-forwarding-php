<?php

namespace App\Controllers;

use App\QueryBuilder as DB;
use Twilio\Rest\Client;
use Twilio\Twiml;

class CallCongress
{
    /**
     * Verify or collect State information.
     *
     * @param  string  $fromState The state abbreviature.
     * @param  string  $baseUrl The base url path e.g.: http://localhost/callcongress/.
     * @return \Twilio\Twiml
     */
    public static function welcome($fromState, $baseUrl)
    {
        $twiml = new Twiml();

        if ($fromState) {
            $twiml->say(
                'Thank you for calling congress! It looks like ' .
                "you're calling from {$fromState}. " .
                'If this is correct, please press 1. Press 2 if ' .
                'this is not your current state of residence.'
            )->gather([
                'numDigits' => 1,
                'action' => "{$baseUrl}set-state",
                'method' => 'GET',
                'fromState' => $fromState
            ]);
        } else {
            $twiml->say(
                'Thank you for calling Call Congress! If you wish to ' .
                'call your senators, please enter your 5-digit zip code.'
            )->gather([
                'numDigits' => 5,
                'action' => "{$baseUrl}state-lookup",
                'method' => 'POST'
            ]);
        }

        return $twiml;
    }

    /**
     * Look up state from given zipcode, once state is found,
     * redirect to call_senators for forwarding.
     *
     * @param  string  $digits The zipcode digits to find in our DB.
     * @param  int  $baseUrl The base url path e.g.: http://localhost/callcongress/.
     * @return string
     */
    public static function stateLookup($digits, $baseUrl)
    {
        // NB: We don't do any error handling for a missing/erroneous zip code
        // in this sample application. You, gentle reader, should handle that
        // edge case before deploying this code.
        $zipCode = DB::fromTable('zipcodes')
            ->where('zipcode', $digits)
            ->first();

        return "{$baseUrl}call-senators?state_id={$zipCode['state_id']}";
    }

    /**
     * If our state guess is wrong, prompt user for zip code.
     *
     * @param  string  $baseUrl The base url path e.g.: http://localhost/callcongress/.
     * @return \Twilio\Twiml
     */
    public static function collectZip($baseUrl)
    {
        $twiml = new Twiml();

        $twiml->gather([
            'numDigits' => 5,
            'action' => "{$baseUrl}state-lookup",
            'method' => 'POST'
        ])->say(
            'If you wish to call your senators, please ' .
            'enter your 5-digit zip code.'
        );

        return $twiml;
    }

    /**
     * Set user's state from confirmation or user-provided Zip,
     * and redirect to call_senators route.
     *
     * @param  string  $digitsProvided Get the digit pressed by the user.
     * @param  string  $callerState Get the digit pressed by the user.
     * @param  string  $baseUrl The base url path e.g.: http://localhost/callcongress/.
     * @return string
     */
    public static function setState($digitsProvided, $callerState, $baseUrl)
    {
        // Set state if State correct, else prompt for zipcode.
        if ($digitsProvided == 1) {
            $state = DB::fromTable('states')
                ->where('name', $callerState)
                ->first();
            if ($state) {
                return "{$baseUrl}call-senators?state_id={$state['id']}";
            }
        }

        return "{$baseUrl}collect-zip";
    }

    /**
     * Route for connecting caller to both of their senators.
     *
     * @param  string  $stateId State id.
     * @param  string  $baseUrl The base url path e.g.: http://localhost/callcongress/.
     * @return \Twilio\Twiml
     */
    public static function callSenators($stateId, $baseUrl)
    {
        $twiml = new Twiml();

        list($senator_one, $senator_two) = DB::fromTable('senators')
            ->where('state_id', $stateId)
            ->all();

        $twiml->say(
            "Connecting you to {$senator_one['name']}. " .
            'After the senator\'s office ends the call, you will ' .
            "be re-directed to {$senator_two['name']}."
        )->dial($senator_one['phone'], [
            'action' => "{$baseUrl}call-second-senator?" .
                        "senator_id={$senator_two['id']}"
        ]);

        return $twiml;
    }

    /**
     * Forward the caller to their second senator.
     *
     * @param  string  $senatorId Senator's id in our DB.
     * @param  string  $baseUrl The base url path e.g.: http://localhost/callcongress/.
     * @return \Twilio\Twiml
     */
    public static function callSecondSenator($senatorId, $baseUrl)
    {
        $twiml = new Twiml();
        $senator = DB::fromTable('senators')
            ->where('id', $senatorId)
            ->first();

        $twiml
            ->say("Connecting you to {$senator['name']}")
            ->dial($senator['phone'], [
                'action' => "{$baseUrl}end-call"
            ]);

        return $twiml;
    }

    /**
     * Thank user & hang up.
     *
     * @return \Twilio\Twiml
     */
    public static function goodbye()
    {
        $twiml = new Twiml();

        $twiml->say(
            'Thank you for using Call Congress! ' .
            'Your voice makes a difference. Goodbye.'
        )->hangup();

        return $twiml;
    }
}
