<?php declare(strict_types=1);

namespace App;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DI
{
    /**
     * Initialize DI container builder using settings from yaml config
     *
     * @param string $config
     * @return ContainerBuilder
     * @throws \Exception
     */
    public static function init(string $config): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder;
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../config/'));
        $loader->load($config);
        $containerBuilder->compile();

        return $containerBuilder;
    }
}
