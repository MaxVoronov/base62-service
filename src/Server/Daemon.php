<?php declare(strict_types=1);

namespace App\Server;

use App\Config\Config;
use App\Server\Daemon\Exception\ReloadException;
use App\Server\Daemon\Exception\StopException;
use App\Server\Daemon\PidManager;
use App\Server\Daemon\Runnable\RunnableInterface;
use Psr\Log\LoggerInterface;

class Daemon
{
    /** @var Config */
    protected $config;

    /** @var PidManager */
    protected $pidManager;

    /** @var LoggerInterface */
    protected $logger;

    /** @var RunnableInterface */
    protected $runnable;

    /**
     * Daemon constructor
     *
     * @param Config $config
     * @param PidManager $pidManager
     * @param LoggerInterface $logger
     */
    public function __construct(Config $config, PidManager $pidManager, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->pidManager = $pidManager;
        $this->logger = $logger;
    }

    /**
     * Start service in foreground/background mode
     *
     * @param RunnableInterface $runnableInstance
     * @param bool $isDaemonMode
     * @throws \App\Server\Daemon\Exception\Pid\AlreadyExistsException
     * @throws \App\Server\Daemon\Exception\Pid\NotWritableException
     */
    public function start(RunnableInterface $runnableInstance, bool $isDaemonMode = true): void
    {
        if ($isDaemonMode) {
            $pid = pcntl_fork();
            if ($pid === -1) {
                throw new \Exception('Can not unfork process');
            }
            if ($pid) {
                exit(0);       // Exit from parent process
            }

            if (posix_setsid() === -1) {
                throw new \Exception('Can not set as background process');
            }

            // Close file descriptors
            fclose(STDIN);
            fclose(STDOUT);
            fclose(STDERR);
        }

        pcntl_signal(SIGINT, [$this, 'signalHandler']);
        pcntl_signal(SIGHUP, [$this, 'signalHandler']);
        pcntl_signal(SIGTERM, [$this, 'signalHandler']);

        $this->pidManager->create();

        // ToDo: Try..catch?
        $this->runnable = $runnableInstance;
        $this->runnableHandler();
    }

    /**
     * Send reload signal to daemon
     *
     * @throws \App\Server\Daemon\Exception\Pid\NotReadableExtension
     */
    public function reload()
    {
        $pid = $this->pidManager->getPid();
        posix_kill($pid, SIGHUP);
    }

    /**
     * Send stop signal to daemon
     *
     * @throws \App\Server\Daemon\Exception\Pid\NotReadableExtension
     */
    public function stop(): void
    {
        $pid = $this->pidManager->getPid();
        posix_kill($pid, SIGTERM);
    }

    /**
     * Runnable handler used in inner loop
     */
    protected function runnableHandler(): void
    {
        try {
            $this->runnable->run();
        } catch (ReloadException $e) {
            $this->onReloadSignal();
        } catch (StopException $e) {
            $this->onStopSignal();
        } catch (\Exception $e) {
            $this->onPanic($e);
        }
    }

    /**
     * System signals handlers
     *
     * @param int $sigNo
     * @throws \App\Server\Daemon\Exception\ReloadException
     * @throws \App\Server\Daemon\Exception\StopException
     */
    public function signalHandler(int $sigNo): void
    {
        switch ($sigNo) {
            case SIGINT:
            case SIGTERM:
                throw new StopException(sprintf('Daemon has been stopped by signal #%s', $sigNo));
                break;
            case SIGHUP:
                throw new ReloadException('Interrupt for reload');
                break;
        }
    }

    /**
     * Processor on reload signal
     *
     * @throws \App\Config\Exception\ConfigFileNotFoundException
     * @throws \App\Config\Exception\InvalidParamException
     * @throws \App\Config\Exception\ParamNotFoundException
     */
    protected function onReloadSignal(): void
    {
        // Prepare old and new config for current worker
        $oldConfig = clone $this->config;
        $this->config->reload();
        $this->runnable->reloaded($oldConfig, $this->config);

        // Clean up memory and restart job
        unset($oldConfig);
        $this->runnableHandler();
    }

    /**
     * Processor on stop signal
     *
     * @throws \App\Server\Daemon\Exception\Pid\NotWritableException
     */
    protected function onStopSignal(): void
    {
        $this->runnable->stopped();
        $this->pidManager->close();
    }

    /**
     * Processor for non-signal exceptions
     *
     * @param \Exception $e
     * @throws \App\Server\Daemon\Exception\Pid\NotWritableException
     * @todo Auto reload with sleep
     */
    protected function onPanic(\Exception $e): void
    {
        $this->logger->error('Panic in worker', [$e]);
        $this->runnable->panicked();
        $this->pidManager->close();
    }
}
