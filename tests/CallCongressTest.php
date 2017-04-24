<?php

namespace App\Tests;

use App\Controllers\CallCongress;
use App\QueryBuilder as DB;
use PHPUnit\Framework\TestCase;

class CallCongressTest extends TestCase
{
    private $stateName = 'AK';
    private $zipCode = 99551;
    private $baseUrl = 'https://localhost:8080/callcongress/';

    public function testWelcome()
    {
        // Test Post welcome With State
        $this->assertContains(
            'Thank you for calling congress! It looks like you\'re calling ' .
            "from {$this->stateName}. If this is correct, please press 1. " .
            'Press 2 if this is not your current state of residence.',
            CallCongress::welcome($this->stateName, $this->baseUrl)->__toString()
        );

        // Test Post welcome Without State
        $this->assertContains(
            'Thank you for calling Call Congress! If you wish to call your ' .
            'senators, please enter your 5-digit zip code.',
            CallCongress::welcome('', $this->baseUrl)->__toString()
        );
    }

    public function testStateLookup()
    {
        $zipCode = DB::fromTable('zipcodes')
            ->where('zipcode', $this->zipCode)
            ->first();

        $this->assertEquals(
            "{$this->baseUrl}call-senators?state_id={$zipCode['state_id']}",
            CallCongress::stateLookup($this->zipCode, $this->baseUrl)
        );
    }

    public function testCollectZip()
    {
        $this->assertContains(
            'If you wish to call your senators, please enter your ' .
            '5-digit zip code.',
            CallCongress::collectZip($this->zipCode, $this->baseUrl)->__toString()
        );
    }

    public function testSetState()
    {
        $zipCode = DB::fromTable('zipcodes')
            ->where('zipcode', $this->zipCode)
            ->first();

        $this->assertEquals(
            "{$this->baseUrl}call-senators?state_id={$zipCode['state_id']}",
            CallCongress::setState(1, $this->stateName, $this->baseUrl)
        );

        $this->assertEquals(
            "{$this->baseUrl}collect-zip",
            CallCongress::setState(0, $this->stateName, $this->baseUrl)
        );
    }

    public function testCallSenators()
    {
        $stateId = 2;
        list($senator_one, $senator_two) = DB::fromTable('senators')
            ->where('state_id', $stateId)
            ->all();

        $this->assertContains(
            "call-second-senator?senator_id={$senator_two['id']}",
            CallCongress::callSenators($stateId, $this->baseUrl)->__toString()
        );

        $this->assertContains(
            "Connecting you to {$senator_one['name']}. " .
            'After the senator\'s office ends the call, you will ' .
            'be re-directed to Lisa Murkowski.',
            CallCongress::callSenators($stateId, $this->baseUrl)->__toString()
        );
    }

    public function testCallSecondSenator()
    {
        $this->assertContains(
            'Connecting you to Lisa Murkowski',
            CallCongress::callSecondSenator(2, $this->baseUrl)->__toString()
        );

        $this->assertContains(
            'end-call',
            CallCongress::callSecondSenator(2, $this->baseUrl)->__toString()
        );
    }

    public function testGoodbye()
    {
        $this->assertContains(
            'Thank you for using Call Congress! ' .
            'Your voice makes a difference. Goodbye.',
            CallCongress::goodbye()->__toString()
        );
    }
}
