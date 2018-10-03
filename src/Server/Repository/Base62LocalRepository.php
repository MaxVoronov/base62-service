<?php declare(strict_types=1);

namespace App\Server\Repository;

use Amirax\Base62;
use App\Repository\Base62RepositoryInterface;

class Base62LocalRepository implements Base62RepositoryInterface
{
    /** @var Base62 */
    protected $base62;

    /**
     * Base62LocalRepository constructor
     *
     * @param Base62 $base62
     */
    public function __construct(Base62 $base62)
    {
        $this->base62 = $base62;
    }

    /**
     * Encode any string to base62 using library
     *
     * @param string $payload
     * @return string
     */
    public function encode(string $payload): string
    {
        return $this->base62->encode($payload);
    }

    /**
     * Decode base62 to string using library
     *
     * @param string $payload
     * @return string
     */
    public function decode(string $payload): string
    {
        return $this->base62->decode($payload);
    }
}
