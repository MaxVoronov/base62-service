<?php declare(strict_types=1);

namespace App\Server\Daemon\Runnable;

abstract class AbstractRunnable implements RunnableInterface
{
    /**
     * Dispatch and process system signals
     */
    protected function dispatchSignals(): void
    {
        pcntl_signal_dispatch();
    }
}
