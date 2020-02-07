<?php

namespace Genesis\BehatApiSpec\Extension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Genesis\BehatApiSpec\Extension\Initializer\Initializer;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Extension class.
 */
class Extension implements ExtensionInterface
{
    const CONTEXT_INITIALISER = 'genesis.apispec.context_initialiser';

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * Create definition object to handle in the context?
     */
    public function process(ContainerBuilder $container)
    {
        return;
    }

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'GenesisApiSpecExtension';
    }

    /**
     * Initializes other extensions.
     *
     * This method is called immediately after all extensions are activated but
     * before any extension `configure()` method is called. This allows extensions
     * to hook into the configuration of other extensions providing such an
     * extension point.
     *
     */
    public function initialize(ExtensionManager $extensionManager)
    {
        return;
    }

    /**
     * Setups configuration for the extension.
     *
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('endpoint')->isRequired()->end()
                ->arrayNode('specMappings')
                    ->children()
                        ->scalarNode('endpoint')->isRequired()->end()
                        ->scalarNode('schema')->defaultNull()->end()
                ->end()
            ->end()
        ->end();
    }

    /**
     * Loads extension services into temporary container.
     *
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $container->setParameter('genesis.apispec.config.endpoint', $config['endpoint']);

        if (!isset($config['specMappings'])) {
            $config['specMappings'] = [];
        }
        $container->setParameter('genesis.apispec.config.specMappings', $config['specMappings']);

        $definition = new Definition(Initializer::class, [
            '%genesis.apispec.config.specMappings%',
        ]);
        $definition->addTag(ContextExtension::INITIALIZER_TAG);
        $container->setDefinition(self::CONTEXT_INITIALISER, $definition);
    }
}
