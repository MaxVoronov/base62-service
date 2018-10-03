<?php declare(strict_types=1);

namespace App\Client\Repository;

use App\Config\Config;
use App\Repository\Base62RepositoryInterface;

class Base62SocketRepository implements Base62RepositoryInterface
{
    /** @var Config */
    protected $config;

    /**
     * Base62SocketRepository constructor
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Encode any string to base62 via service
     *
     * @param string $payload
     * @return string
     * @throws \Exception
     */
    public function encode(string $payload): string
    {
        return $this->sendCommand('encode', $payload);
    }

    /**
     * Decode base62 to string via service
     *
     * @param string $payload
     * @return string
     * @throws \Exception
     */
    public function decode(string $payload): string
    {
        return $this->sendCommand('decode', $payload);
    }

    /**
     * Connect and send command to service using sockets
     *
     * @param string $command
     * @param string $payload
     * @return string
     * @throws \Exception
     */
    protected function sendCommand(string $command, string $payload): string
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            throw new \Exception('Can\'t init socket');
        }

        if (!@socket_connect($socket, $this->config->getHost(), $this->config->getPort())) {
            $socketError = socket_strerror(socket_last_error($socket));
            throw new \Exception(sprintf(
                'Can\'t connect socket to [%s:%s]: %s',
                $this->config->getHost(),
                $this->config->getPort(),
                $socketError
            ));
        }
        socket_read($socket, 4096);     // Read welcome message into void

        $inputCommand = sprintf('%s %s', $command, $payload);
        socket_write($socket, $inputCommand, \strlen($inputCommand));
        $result = trim(socket_read($socket, 4096));
        socket_close($socket);

        return $result;
    }
}
