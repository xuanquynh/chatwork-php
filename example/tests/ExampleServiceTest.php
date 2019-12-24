<?php

namespace Tests\Acme;

use TestCase;
use Acme\ExampleService;
use SunAsterisk\Chatwork\Chatwork;
use SunAsterisk\Chatwork\Endpoints\Room;
use SunAsterisk\Chatwork\Endpoints\Rooms\Messages;
use Mockery as m;
use SunAsterisk\Chatwork\Exceptions\APIException;

class ExampleServiceTest extends TestCase
{
    public function testBasic()
    {
        ExampleService::createChatworkUsing(function ($token, $type = null) {
            $this->assertSame('a_bot_key', $token);
            $room = new MockRoom();
            $chatwork = m::mock(Chatwork::class);
            $chatwork->shouldReceive('room')->with('12345')->andReturn($room);
            return $chatwork;
        });

        $service = new ExampleService((object) [
            'bot' => (object) [
                'bot_key' => 'a_bot_key',
            ],
            'room_id' => '12345',
        ]);

        $statuses = $service->sendMessages([
            'an_401_error_message',
            'an_403_error_message',
        ]);

        $this->assertEquals([401, 403], $statuses);
    }
}

class MockRoom extends Room
{
    public function __construct()
    {
        //
    }

    public function messages()
    {
        return new MockMessages();
    }
}

class MockMessages extends Messages
{
    public function __construct()
    {
        //
    }

    public function create($message)
    {
        if ($message === 'an_401_error_message') {
            throw new APIException(401, ['errors' => ['an_401_error_message']]);
        }

        if ($message === 'an_403_error_message') {
            throw new APIException(403, ['errors' => ['an_403_error_message']]);
        }
    }
}
