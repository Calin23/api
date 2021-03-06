<?php

declare(strict_types=1);

namespace Api\App\Doctrine;

use Api\App\Doctrine\Factory\EntityListenerResolverFactory;
use Api\App\Doctrine\Service\EntityListenerResolver;

/**
 * Class ConfigProvider
 * @package Api\App\Doctrine
 */
class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'doctrine' => $this->getDoctrineConfig(),
        ];
    }

    /**
     * @return array
     */
    public function getDependencies() : array
    {
        return [
            'delegators' => [
            ],
            'factories' => [
                EntityListenerResolver::class => EntityListenerResolverFactory::class
            ],
            'aliases' => [
            ]
        ];
    }

    /**
     * @return array
     */
    public function getDoctrineConfig()
    {
        return [
            'configuration' => [
                'orm_default' => [
                    'entity_listener_resolver' => EntityListenerResolver::class,
                ]
            ]
        ];
    }
}
