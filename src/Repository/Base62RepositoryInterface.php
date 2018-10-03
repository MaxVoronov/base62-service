<?php declare(strict_types=1);

namespace App\Repository;

interface Base62RepositoryInterface
{
    /**
     * @param string $payload
     * @return string
     */
    public function encode(string $payload): string;

    /**
     * @param string $payload
     * @return string
     */
    public function decode(string $payload): string;
}
