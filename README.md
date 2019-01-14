# php-daemon
Experimental php daemon implementation.
I made this out of curiosity and self education

Rough example:
```php
<?php

require(__DIR__ . '/common/components/daemon/BaseDaemon.php');
require(__DIR__ . '/common/components/daemon/SignalHandler.php');
require(__DIR__ . '/common/components/daemon/MessageQueue.php');

use common\components\daemon\BaseDaemon;
use common\components\daemon\MessageQueue;
use common\components\daemon\SignalHandler;

class TestDaemon extends BaseDaemon
{
    /**
     * @var MessageQueue $queue 
     */
    protected $queue;

    protected $loop = 60;

    public function init()
    {
        //ideally this instance should be created outside of this daemon and be access by reference.
        $this->queue = MessageQueue::getInstance();
        $this->queue->allocate('seg_demo', 256000);
        $this->queue->registerChannel('ch_kono_dio_da');
    }

    public function signals()
    {
        parent::signals();

        $handler = new SignalHandler;
        $handler->register(SIGUSR1, [$this, 'nothing']);
    }

    public function nothing()
    {
    }

    public function stop()
    {
        $this->loop = 0;
        parent::stop();
    }

    public function run()
    {
        if ($this->isChild()) {
            $this->detach();
            $this->start();

            while ($this->started) {
                pcntl_signal_dispatch();

                if (!($message = $this->queue->next('ch_kono_dio_da'))) {
                    time_nanosleep($this->loop, 0);
                } else {
                    //do smth with message
                    //when adding message to queue don't forget to invoke posix_kill($pid, SIGUSR1) to wakeup daemon
                }
            }
        }

        exit();
    }
}

$daemon = new TestDaemon;
if ($daemon->fork()) {
    $daemon->init();
    $daemon->signals();
    $daemon->run();
}
```
