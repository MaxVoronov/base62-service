<?php declare(strict_types=1);

namespace App\Service;

use Amirax\Base62;

class Base62Service
{
    public const MODE_ENCODE = 'encode';
    public const MODE_DECODE = 'decode';

    /** @var Base62 */
    protected $base62;

    /**
     * Base62Service constructor
     *
     * @param Base62 $base62
     */
    public function __construct(Base62 $base62)
    {
        $this->base62 = $base62;
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
        return $this->base62->encode($payload);
    }

    /**
     * Decode base62 to string
     *
     * @param string $payload
     * @return string
     */
    public function decode(string $payload): string
    {
        return $this->base62->decode($payload);
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
