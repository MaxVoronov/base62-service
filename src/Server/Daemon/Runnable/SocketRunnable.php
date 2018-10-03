<?php declare(strict_types=1);

namespace App\Server\Daemon\Runnable;

use App\Config\Config;
use App\Service\Base62Service;
use Psr\Log\LoggerInterface;

class SocketRunnable extends AbstractRunnable
{
    /** @var Config */
    protected $config;

    /** @var LoggerInterface */
    protected $logger;

    /** @var Base62Service */
    protected $base62Service;

    /** @var resource */
    protected $socket;

    /** @var array */
    protected $clients = [];

    /**
     * SocketRunnable constructor
     *
     * @param Config $config
     * @param LoggerInterface $logger
     * @param Base62Service $base62Service
     */
    public function __construct(Config $config, LoggerInterface $logger, Base62Service $base62Service)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->base62Service = $base62Service;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function run(): void
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket === false) {
            throw new \Exception('Can\'t init socket');
        }

        if (!socket_bind($this->socket, $this->config->getHost(), $this->config->getPort())) {
            $socketError = socket_strerror(socket_last_error($this->socket));
            throw new \Exception(sprintf(
                'Can\'t bind socket to [%s:%s]: %s',
                $this->config->getHost(),
                $this->config->getPort(),
                $socketError
            ));
        }

        if (!socket_listen($this->socket, 2)) {
            throw new \Exception('Can\'t setup socket to listening');
        }

        $this->clients = [$this->socket];
        $this->processingLoop();
    }

    /**
     * Main loop for processing client socket connections
     */
    protected function processingLoop(): void
    {
        while (true) {
            $this->dispatchSignals();
            $readClients = $this->clients;
            $writeClients = $exceptClients = null;

            if (@socket_select($readClients, $writeClients, $exceptClients, null) < 1) {
                continue;
            }

            // Check if there is a client trying to connect
            if (in_array($this->socket, $readClients)) {
                $this->clients[] = $newClient = socket_accept($this->socket);
                $this->onConnecting($newClient);

                $key = array_search($this->socket, $readClients);
                unset($readClients[$key]);
            }

            foreach ($readClients as $client) {
                $buffer = @socket_read($client, 4096, PHP_BINARY_READ);

                // When client was disconnected
                if ($buffer === false) {
                    $key = array_search($client, $this->clients);
                    unset($this->clients[$key]);
                    continue;
                }

                $buffer = trim($buffer);
                $this->onProcessing($buffer, $client);
            }

            usleep(500000);
        }
    }

    /**
     * Event on new client connecting
     *
     * @param resource $client
     */
    protected function onConnecting($client): void
    {
        $welcomeMessage = 'Welcome to Base62 encode/decode service' . PHP_EOL . PHP_EOL .
            'Usage:' . PHP_EOL .
            '  encode <data>     Encode any string to base62' . PHP_EOL .
            '  decode <data>     Decode base62 to string' . PHP_EOL . PHP_EOL;
        socket_write($client, $welcomeMessage, \strlen($welcomeMessage));

        $this->logger->info(sprintf('New client has been connected'));
    }

    /**
     * Event on client send request for processing
     *
     * @param string $input
     * @param resource $client
     */
    protected function onProcessing(string $input, $client): void
    {
        $output = '';
        try {
            if ($input === '') {
                return;
            }

            $this->logger->debug('<< ' . $input);
            $chunks = explode(' ', $input, 2);
            $command = strtolower($chunks[0]);

            if (!\in_array($command, $this->base62Service->getAvailableModes(), true)) {
                throw new \DomainException('Unsupported command');
            }

            $payload = (string)($chunks[1] ?? '');
            if ($payload !== '') {
                $output = $this->base62Service->process($command, $payload) . PHP_EOL;
            }
        } catch (\Exception $e) {
            $output = 'Error: ' . $e->getMessage() . PHP_EOL;
        }

        if ($output !== '') {
            socket_write($client, $output, \strlen($output));
            $this->logger->debug('>> ' . trim($output));
        }
    }

    /**
     * Event on server has been stopped/reloaded
     * Say 'Goodbye' to clients
     *
     * @param string $message
     */
    protected function onClosing(string $message = ''): void
    {
        $message .= PHP_EOL;
        foreach ($this->clients as $client) {
            @socket_write($client, $message, \strlen($message));
        }

        socket_close($this->socket);
        sleep(3);           // Waiting while system close sockets
        $this->logger->info(sprintf('Socket has been closed'));
    }

    /**
     * @inheritdoc
     */
    public function reloaded(Config $oldConfig, Config $newConfig): void
    {
        $this->onClosing(sprintf(
            'Service was restarted. Please reconnect to new port %s',
            $newConfig->getPort()
        ));
    }

    /**
     * @inheritdoc
     */
    public function stopped(): void
    {
        $this->onClosing('Service was stopped. Bye-bye');
    }

    /**
     * @inheritdoc
     */
    public function panicked(): void
    {
        $this->onClosing('Internal Server Error. Please try to reconnect some later');
    }
}
