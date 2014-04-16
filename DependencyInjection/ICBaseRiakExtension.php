<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\RiakBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @author Kinn Coelho Julião <kinnj@nationalfibre.net>
 * @author Guilherme Blanco <gblanco@nationalfibre.net>
 * @author Anthon Pang <anthonp@nationalfibre.net>
 * @author Fabio B.Silva <fabios@nationalfibre.net>
 */
class ICBaseRiakExtension extends Extension
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->container = $container;

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->createConnectionDefinition($config['connections'], $config['default_connection']);
        $this->createBucketDefinition($config['buckets'], $config['default_namespace']);
    }

    /**
     * Create list of Connection definition
     *
     * @param array  $connectionList
     * @param string $defaultConnectionName
     */
    private function createConnectionDefinition(array $connectionList, $defaultConnectionName)
    {
        $bundleAlias            = $this->getAlias();
        $connectionServiceClass = $this->container->getParameter(sprintf('%s.class.connection', $bundleAlias));

        foreach ($connectionList as $connectionKey => $connectionConfig) {
            $connectionServiceId  = sprintf('%s.connection.%s', $bundleAlias, $connectionKey);
            $connectionDefinition = new Definition(
                $connectionServiceClass,
                array(
                    $connectionConfig['host'],
                    $connectionConfig['port']
                )
            );

            $this->container->setDefinition($connectionServiceId, $connectionDefinition);
        }

        // Assign default connection
        $this->container->setAlias(
            sprintf('%s.default_connection', $bundleAlias),
            sprintf('%s.connection.%s', $bundleAlias, $defaultConnectionName)
        );
    }

    /**
     * Create list of Bucket definition
     *
     * @param array  $bucketList
     * @param string $prefix
     */
    private function createBucketDefinition(array $bucketList, $prefix)
    {
        $bundleAlias        = $this->getAlias();
        $bucketServiceClass = $this->container->getParameter(sprintf('%s.class.bucket', $bundleAlias));

        foreach ($bucketList as $bucketKey => $bucketConfig) {
            // Connection
            $bucketConfig['name'] = $bucketConfig['name'] ?: $bucketKey;
            $connectionName       = ($bucketConfig['connection'])
                ? sprintf('%s.connection.%s', $bundleAlias, $bucketConfig['connection'])
                : sprintf('%s.default_connection', $bundleAlias);
            $connectionReference = new Reference($connectionName);

            // Bucket
            $bucketServiceId  = sprintf('%s.bucket.%s', $bundleAlias, $bucketKey);
            $bucketDefinition = new Definition(
                $bucketServiceClass,
                array(
                    $connectionReference,
                    $bucketConfig['name'],
                )
            );

            if ($prefix) {
                $bucketDefinition->addMethodCall('setPrefix', array($prefix));
            }

            $bucketDefinition->addTag("ic_base_riak.bucket");

            $this->container->setDefinition($bucketServiceId, $bucketDefinition);

            // Bucket Property list
            $this->createBucketPropertyListDefinition($bucketDefinition, $bucketConfig);
        }
    }

    /**
     * Create Bucket Property List definition and assign to corresponding Bucket automatically.
     *
     * @param \Symfony\Component\DependencyInjection\Definition $bucketDefinition
     * @param array                                             $bucketConfig
     */
    private function createBucketPropertyListDefinition(Definition $bucketDefinition, array $bucketConfig)
    {
        $bundleAlias                    = $this->getAlias();
        $bucketPropertyListServiceClass = $this->container->getParameter(sprintf('%s.class.bucket_property_list', $bundleAlias));

        // Bucket Property list
        if (isset($bucketConfig['property_list'])) {
            $bucketPropertyListConfig     = $bucketConfig['property_list'];
            $bucketPropertyListServiceId  = sprintf('%s.property_list.%s', $bundleAlias, $bucketConfig['name']);
            $bucketPropertyListReference  = new Reference($bucketPropertyListServiceId);
            $bucketPropertyListDefinition = new Definition(
                $bucketPropertyListServiceClass,
                array(
                    $bucketPropertyListConfig['n_value'],
                    $bucketPropertyListConfig['allow_multiple']
                )
            );

            if (isset($bucketConfig['property_list']['backend'])) {
                $bucketPropertyListDefinition->addMethodCall('setBackend', array($bucketConfig['property_list']['backend']));
            }

            if (isset($bucketConfig['property_list']['last_write_wins'])) {
                $bucketPropertyListDefinition->addMethodCall('setLastWriteWins', array($bucketConfig['property_list']['last_write_wins']));
            }

            if (isset($bucketConfig['property_list']['not_found_ok'])) {
                $bucketPropertyListDefinition->addMethodCall('setNotFoundOk', array($bucketConfig['property_list']['not_found_ok']));
            }

            $this->container->setDefinition($bucketPropertyListServiceId, $bucketPropertyListDefinition);

            // Scheduling call to Bucket
            $bucketDefinition->addMethodCall('setPropertyList', array($bucketPropertyListReference));
        }
    }
}
