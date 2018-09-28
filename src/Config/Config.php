<?php declare(strict_types=1);

namespace App\Config;

use App\Config\Exception\ConfigFileNotFoundException;
use App\Config\Exception\InvalidParamException;
use App\Config\Exception\ParamNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;

class Config
{
    /** @var string */
    protected $sourceFile;

    /** @var LoggerInterface */
    protected $logger;

    /** @var string */
    protected $host;

    /** @var int */
    protected $port;

    /**
     * Config constructor
     *
     * @param LoggerInterface $logger
     * @param string $sourceFile
     * @throws ConfigFileNotFoundException
     * @throws InvalidParamException
     * @throws ParamNotFoundException
     */
    public function __construct(LoggerInterface $logger, string $sourceFile)
    {
        $this->logger = $logger;
        $this->sourceFile = __DIR__ . '/../../' . $sourceFile;

        $this->load();
    }

    /**
     * Load config parameters from file
     *
     * @return Config
     * @throws ConfigFileNotFoundException
     * @throws InvalidParamException
     * @throws ParamNotFoundException
     */
    public function load(): self
    {
        if (!is_file($this->sourceFile) || !is_readable($this->sourceFile)) {
            throw new ConfigFileNotFoundException(sprintf(
                'Config file %s is not exist or not readable',
                $this->sourceFile
            ));
        }

        $config = Yaml::parseFile($this->sourceFile);

        if (empty($config['host'])) {
            throw new ParamNotFoundException('Parameter "host" is required');
        }
        $this->host = trim($config['host']);

        if (empty($config['port'])) {
            throw new ParamNotFoundException('Parameter "port" is required');
        }
        if ($config['port'] < 1 || $config['port'] > 65535) {
            throw new InvalidParamException('Port value should be in range 1-65535');
        }
        $this->port = (int)$config['port'];

        return $this;
    }

    /**
     * Alias for load method
     *
     * @return Config
     * @throws ConfigFileNotFoundException
     * @throws InvalidParamException
     * @throws ParamNotFoundException
     */
    public function reload(): self
    {
        return $this->load();
    }

    /**
     * Return server host
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Return server port
     *
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }
}
