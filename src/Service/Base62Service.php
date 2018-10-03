<?php declare(strict_types=1);

namespace App\Service;

use App\Repository\Base62RepositoryInterface;

class Base62Service
{
    public const MODE_ENCODE = 'encode';
    public const MODE_DECODE = 'decode';

    /** @var Base62RepositoryInterface */
    protected $base62Repository;

    /**
     * Base62Service constructor
     *
     * @param Base62RepositoryInterface $base62Repository
     */
    public function __construct(Base62RepositoryInterface $base62Repository)
    {
        $this->base62Repository = $base62Repository;
    }

    /**
     * Process payload by mode
     *
     * @param string $mode
     * @param string $payload
     * @return string
     */
    public function process(string $mode, string $payload): string
    {
        switch ($mode) {
            case self::MODE_ENCODE:
                return $this->encode($payload);
            case self::MODE_DECODE:
                return $this->decode($payload);
        }

        return '';
    }

    /**
     * Encode any string to base62
     *
     * @param string $payload
     * @return string
     */
    public function encode(string $payload): string
    {
        return $this->base62Repository->encode($payload);
    }

    /**
     * Decode base62 to string
     *
     * @param string $payload
     * @return string
     */
    public function decode(string $payload): string
    {
        return $this->base62Repository->decode($payload);
    }

    /**
     * Return list of available modes
     *
     * @return array
     */
    public function getAvailableModes(): array
    {
        return [self::MODE_ENCODE, self::MODE_DECODE];
    }
}
