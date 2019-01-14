<?php

namespace common\components\daemon;

/**
 * Class SignalHandler
 * @package common\components\daemon
 * @version 1.0.0
 * @author Shin1gami
 */
class SignalHandler
{
    /**
     * List of registered signals to handle
     * @var array $signals
     */
    protected $signals = [];

    /**
     * Registers signal event to custom handler.
     * @param int $signo
     * @param array|\Closure $handler
     */
    public function register($signo, $handler)
    {
        $this->signals[$signo] = $handler;

        pcntl_signal($signo, function ($signo) {
            $this->invoke($signo);
        });
    }

    /**
     * Invokes registered handler to signal event.
     * Separate method for easier debugging
     * @param int $signo
     */
    protected function invoke($signo)
    {
        if (!isset($this->signals[$signo])) {
            return;
        }

        $handler = $this->signals[$signo];
        if (is_callable($handler)) {
            $handler();
        }

        if (is_array($handler)) {
            list($caller, $method) = $handler;
            $caller->{$method}();
        }
    }
}