<?php

namespace common\components\daemon;

/**
 * Class BaseDaemon
 * @package common\components\daemon
 * @version 1.0.0
 * @author Shin1gami
 * @property int $pid
 * @property bool $started
 */
abstract class BaseDaemon
{
    /**
     * Current daemon process id
     * @var int $pid
     */
    public $pid;

    /**
     * Daemon activity status.
     * @var bool $started
     */
    protected $started = false;

    /**
     * If you are running PHP as CLI and as a "daemon" (i.e. in a loop),
     * [[pcntl_signal_dispatch()]] must be called in each loop  to check if new signals are waiting dispatching.
     * Required PHP >= 5.3.0
     * @example
     * public function run()
     * {
     *     if ($this->isChild()) {
     *         $this->detach();
     *         $this->start();
     *
     *         while ($this->started) {
     *             pcntl_signal_dispatch();
     *             ...
     *         }
     *     }
     *
     *     exit();
     * }
     *
     * @void
     */
    abstract public function run();

    /**
     * Method registers signals that should be obtained by [[SignalHandler()]] class.
     * It may be useful when you need to perform specific actions after some signal had been triggered.
     * You may also register SIGUSR1|SIGUSR2 signals to interrupt while loop sleeps.
     * In that case your script will sleep on idle until signal received.
     * @void
     */
    public function signals()
    {
        $handler = new SignalHandler;
        $handler->register(SIGTERM, [$this, 'stop']);
    }

    /**
     * Method forks current daemon process
     * @return bool
     */
    public function fork()
    {
        return ($this->pid = pcntl_fork()) !== -1;
    }

    /**
     * Starts daemon loop inside [[run()]] method
     * @void
     */
    public function start()
    {
        $this->started = true;
    }

    /**
     * Stops daemon loop (in some case entire daemon will be terminated) inside [[run()]] method.
     * Depends on [[run()]] method implementation
     * @void
     */
    public function stop()
    {
        $this->started = false;
    }

    /**
     * Do not read from CLI
     * @param string $destination
     */
    public function forwardStdin($destination = '/dev/null')
    {
        fclose(STDIN);
        fopen($destination, 'rb');
    }

    /**
     * Forward output from stdout to somewhere else
     * @param string $destination
     */
    public function forwardStdout($destination)
    {
        fclose(STDOUT);
        fopen($destination, 'ab');
    }

    /**
     * Forward errors from stderr to somewhere else
     * @param string $destination
     */
    public function forwardStderr($destination)
    {
        fclose(STDERR);
        fopen($destination, 'ab');
    }

    /**
     * Method detaches daemon process from CLI
     * @void
     */
    protected function detach()
    {
        posix_setsid();
    }

    /**
     * Is current daemon process is child
     * @return bool
     */
    protected function isChild()
    {
        return $this->pid === 0;
    }
}