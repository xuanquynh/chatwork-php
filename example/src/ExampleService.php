<?php

namespace Acme;

use Closure;
use stdClass;
use SunAsterisk\Chatwork\Chatwork;
use SunAsterisk\Chatwork\Exceptions\APIException;

class ExampleService
{
    /** @var Closure */
    protected static $createChatworkCallback;

    /** @var stdClass */
    protected $webhook;

    /** @var Chatwork */
    protected $chatwork;

    /**
     * @param Closure $callback
     * @return void
     */
    public static function createChatworkUsing(Closure $callback)
    {
        static::$createChatworkCallback = $callback;
    }

    /**
     * @param stdClass $webhook
     */
    public function __construct($webhook)
    {
        $this->webhook = $webhook;
    }

    /**
     * @param string $token
     * @param string|null $token
     * @return Chatwork
     */
    protected function getChatwork($token, $type = null)
    {
        if ($this->chatwork) {
            return $this->chatwork;
        }

        if (is_null(static::$createChatworkCallback)) {
            static::$createChatworkCallback = function ($token, $type = null) {
                $type = $type ?: 'api';
                if ($type === 'api') {
                    return Chatwork::withAPIToken($token);
                } elseif ($type === 'access') {
                    return Chatwork::withAccessToken($token);
                }
            };
        }

        return $this->chatwork = call_user_func(static::$createChatworkCallback, $token, $type);
    }

    /**
     * @param string[] $messages
     * @return int[]
     *
     * @throws \Exception
     */
    public function sendMessages($messages)
    {
        $botKey = $this->webhook->bot->bot_key;
        $roomId = $this->webhook->room_id;

        $chatwork = $this->getChatwork($botKey);

        $statuses = [];

        foreach ($messages as $message) {
            try {
                $chatwork->room($roomId)->messages()->create($message);
            } catch (APIException $exception) {
                // handle the exception
                $statuses[] = $exception->getStatus();
            }
        }

        return $statuses;
    }
}
