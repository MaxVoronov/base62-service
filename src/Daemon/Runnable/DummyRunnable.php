<?php declare(strict_types=1);

namespace App\Daemon\Runnable;

use App\Config\Config;
use Psr\Log\LoggerInterface;

class DummyRunnable extends AbstractRunnable
{
    /** @var Config */
    protected $config;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * DummyRunnable constructor
     *
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(Config $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function run(): void
    {
        $i = 0;
        while (true) {
            $this->dispatchSignals();
            sleep(5);
            $this->logger->info('Iteration #' . (++$i) . ' [' . $this->config->getHost() . ':' . $this->config->getPort() .']');

            if ($i >= 10) {
                throw new \Exception('Fatal error! OMG! Alarm! FIX ASAP!!!');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function reloaded(Config $oldConfig, Config $newConfig): void
    {
        $this->logger->info('Started service reload');
        $this->logger->info('Old config: ' . $oldConfig->getHost() . ':' . $oldConfig->getPort());
        $this->logger->info('New config: ' . $newConfig->getHost() . ':' . $newConfig->getPort());
    }

    /**
     * @inheritdoc
     */
    public function stopped(): void
    {
        $this->logger->info('Stopped by signal');
    }

    /**
     * @inheritdoc
     */
    public function panicked(): void
    {
        $this->logger->error('Panic! Fatal Error');
    }
}
