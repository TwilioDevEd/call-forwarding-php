<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class CallCongressTest extends TestCase
{
    private $http;

    public function setUp()
    {
        $this->http = new \GuzzleHttp\Client([
            'base_uri' => 'http://localhost:8080',
            'request.options' => [ 'exceptions' => false ]
        ]);
    }

    public function tearDown()
    {
        $this->http = null;
    }

    public function testIndex()
    {
        $resp = $this->http->request('GET', '/');
        $content = $resp->getBody(true)->getContents();

        $this->assertEquals(200, $resp->getStatusCode());

        $this->assertContains(
            'text/html; charset=UTF-8',
            $resp->getHeader('Content-type'));
        $this->assertContains('Call 312-997-5372', $content);
    }

    public function testWelcome()
    {
        // Test Post welcome With State
        $respWithState = $this->http->request(
            'POST',
            '/callcongress/welcome',
            [ 'form_params' => ['FromState' => 'AK'] ]);
        $contentWithState = $respWithState->getBody(true)->getContents();

        $this->assertEquals(200, $respWithState->getStatusCode());
        $this->assertContains(
            'application/xml',
            $respWithState->getHeader('Content-type'));
        $this->assertContains(
            'If this is correct, please press 1.', $contentWithState);

        // Test Post welcome Without State
        $respWithoutState = $this->http->request(
            'POST',
            '/callcongress/welcome');
        $contentWithoutState = $respWithoutState->getBody(true)->getContents();

        $this->assertEquals(200, $respWithoutState->getStatusCode());
        $this->assertContains(
            'application/xml',
            $respWithoutState->getHeader('Content-type'));
        $this->assertContains(
            'If you wish to call your senators', $contentWithoutState);
    }

    public function testStateLookup()
    {
        $resp = $this->http->request('POST', '/callcongress/state-lookup', [
            'form_params' => ['Digits' => 99551]
        ]);
        $content = $resp->getBody(true)->getContents();

        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertContains('call-second-senator?senator_id=2', $content);
        $this->assertContains(
            'Connecting you to Mark Begich. ' .
            'After the senator\'s office ends the call, you will ' .
            'be re-directed to Lisa Murkowski.', $content);
    }

    public function testCollectZip()
    {
        $resp = $this->http->request('POST', '/callcongress/collect-zip', [
            'form_params' => ['Digits' => 99551]
        ]);
        $content = $resp->getBody(true)->getContents();

        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertContains(
            'application/xml',
            $resp->getHeader('Content-type'));
        $this->assertContains('If you wish to call your senators', $content);
    }

    public function testSetState()
    {
        // Test Post set-state With State
        $respWithState = $this->http->request(
            'POST',
            '/callcongress/set-state',
            [
                'form_params' => [
                    'Digits' => 1,
                    'CallerState' => 'AK'
                ]
            ]);
        $contentWithState = $respWithState->getBody(true)->getContents();

        $this->assertEquals(200, $respWithState->getStatusCode());
        $this->assertContains(
            'application/xml',
            $respWithState->getHeader('Content-type'));
        $this->assertContains(
            'call-second-senator?senator_id=2',
            $contentWithState);
        $this->assertContains(
            'Connecting you to Mark Begich. ' .
            'After the senator\'s office ends the call, you will ' .
            'be re-directed to Lisa Murkowski.', $contentWithState);

        // Test Post set-state Without State
        $respWithoutState = $this->http->request(
            'POST',
            '/callcongress/set-state');
        $contentWithoutState = $respWithoutState->getBody(true)->getContents();

        $this->assertEquals(200, $respWithoutState->getStatusCode());
        $this->assertContains(
            'application/xml',
            $respWithoutState->getHeader('Content-type'));
        $this->assertContains(
            'If you wish to call your senators',
            $contentWithoutState);
    }

    public function testCallSenators()
    {
        $resp = $this->http->request('POST', '/callcongress/call-senators', [
            'form_params' => ['state_id' => 2]
        ]);
        $content = $resp->getBody(true)->getContents();

        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertContains(
            'application/xml',
            $resp->getHeader('Content-type'));
        $this->assertContains('call-second-senator?senator_id=2', $content);
        $this->assertContains(
            'Connecting you to Mark Begich. ' .
            'After the senator\'s office ends the call, you will ' .
            'be re-directed to Lisa Murkowski.', $content);
    }

    public function testCallSecondSenator()
    {
        $resp = $this->http->request(
            'POST',
            '/callcongress/call-second-senator',
            [ 'form_params' => ['senator_id' => 2] ]);
        $content = $resp->getBody(true)->getContents();

        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertContains(
            'application/xml',
            $resp->getHeader('Content-type'));
        $this->assertContains('Connecting you to Lisa Murkowski', $content);
        $this->assertContains('end-call', $content);
    }

    public function testGoodbye()
    {
        $resp = $this->http->request('POST', '/callcongress/goodbye');
        $content = $resp->getBody(true)->getContents();

        $this->assertEquals(200, $resp->getStatusCode());
        $this->assertContains(
            'application/xml',
            $resp->getHeader('Content-type'));
        $this->assertContains(
            'Thank you for using Call Congress! ' .
            'Your voice makes a difference. Goodbye.', $content);
    }
}
