<?php declare(strict_types=1);

namespace App\Daemon\Runnable;

use App\Config\Config;

interface RunnableInterface
{
    /**
     * Primary job for daemon
     * Can use infinite loop
     */
    public function run(): void;

    /**
     * Event on reload configs
     *
     * @param Config $oldConfig
     * @param Config $newConfig
     */
    public function reloaded(Config $oldConfig, Config $newConfig): void;

    /**
     * Event on daemon stop
     */
    public function stopped(): void;

    /**
     * Event on catch non-signal exceptions
     */
    public function panicked(): void;
}
