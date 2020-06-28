<?php

namespace Genesis\BehatApiSpec\Extension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Genesis\BehatApiSpec\Command\EndpointGenerate;
use Genesis\BehatApiSpec\Command\SampleRequest;
use Genesis\BehatApiSpec\Command\UpdateSnapshots;
use Genesis\BehatApiSpec\Extension\Initializer\Initializer;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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
                ->scalarNode('baseUrl')->isRequired()->end()
                ->arrayNode('specMappings')
                    ->children()
                        ->scalarNode('endpoint')->isRequired()->end()
                        ->scalarNode('path')->defaultNull()->end()
                        ->scalarNode('schema')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('options')
                    ->children()
                        ->scalarNode('stripSpaces')->defaultValue(false)->end()
                ->end()
            ->end();
    }

    /**
     * Loads extension services into temporary container.
     *
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $container->setParameter('genesis.apispec.config.baseUrl', $config['baseUrl']);

        if (!isset($config['specMappings'])) {
            $config['specMappings'] = [];
        }
        $container->setParameter('genesis.apispec.config.specMappings', $config['specMappings']);

        if (!isset($config['options'])) {
            $config['options'] = [];
        }
        $container->setParameter('genesis.apispec.config.options', $config['options']);

        $definition = new Definition(Initializer::class, [
            '%genesis.apispec.config.baseUrl%',
            '%genesis.apispec.config.specMappings%',
            '%genesis.apispec.config.options%',
        ]);
        $definition->addTag(ContextExtension::INITIALIZER_TAG);
        $container->setDefinition(self::CONTEXT_INITIALISER, $definition);
        $this->addUpdateSnapshotsCommand($container);
        $this->addSampleRequestCommand($container);
        $this->addEndpointGenerateCommand($container, $config['specMappings']);
    }

    private function addUpdateSnapshotsCommand($container)
    {
        $definition = new Definition(
            UpdateSnapshots::class,
            array(new Reference(self::CONTEXT_INITIALISER))
        );
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 1));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.apispec.updateSnapshot', $definition);
    }

    private function addSampleRequestCommand($container)
    {
        $definition = new Definition(
            SampleRequest::class,
            array(new Reference(self::CONTEXT_INITIALISER))
        );
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 1));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.apispec.sampleRequest', $definition);
    }

    private function addEndpointGenerateCommand($container, $specMappings)
    {
        $definition = new Definition(
            EndpointGenerate::class,
            array(new Reference(self::CONTEXT_INITIALISER)),
            array($specMappings)
        );
        $definition->addTag(CliExtension::CONTROLLER_TAG, array('priority' => 1));
        $container->setDefinition(CliExtension::CONTROLLER_TAG . '.apispec.endpointGenerate', $definition);
    }
}
