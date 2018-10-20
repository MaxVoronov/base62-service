<?php declare(strict_types=1);

namespace App\Server\Daemon;

use App\Server\Daemon\Exception\Pid\AlreadyExistsException;
use App\Server\Daemon\Exception\Pid\NotReadableExtension;
use App\Server\Daemon\Exception\Pid\NotWritableException;

class PidManager
{
    /** @var $pidFile string */
    protected $pidFile;

    /**
     * PidManager constructor
     *
     * @param string $pidFile
     */
    public function __construct(string $pidFile)
    {
        $this->pidFile = \dirname(__FILE__, 3) . $pidFile;
    }

    /**
     * Create new PID file
     *
     * @return PidManager
     * @throws AlreadyExistsException
     * @throws NotWritableException
     */
    public function create(): self
    {
        if (is_file($this->pidFile)) {
            throw new AlreadyExistsException(sprintf('File %s already exists', $this->pidFile));
        }

        if (!file_put_contents($this->pidFile, getmypid(), LOCK_EX)) {
            throw new NotWritableException(sprintf('Can\'t create PID file %s', $this->pidFile));
        }

        return $this;
    }

    /**
     * Remove current PID file
     *
     * @return PidManager
     * @throws NotWritableException
     */
    public function close(): self
    {
        if (!is_file($this->pidFile) && !is_writable($this->pidFile)) {
            throw new NotWritableException(sprintf('File %s is not writable or not exists', $this->pidFile));
        }

        unlink($this->pidFile);

        return $this;
    }

    /**
     * Return daemon PID
     *
     * @return int
     * @throws NotReadableExtension
     */
    public function getPid(): int
    {
        if (!is_file($this->pidFile) || !is_readable($this->pidFile)) {
            throw new NotReadableExtension(sprintf('File %s is not exist or not readable', $this->pidFile));
        }

        return (int)file_get_contents($this->pidFile);
    }
}
