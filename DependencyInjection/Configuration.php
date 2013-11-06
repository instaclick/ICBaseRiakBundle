<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\RiakBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * @author Guilherme Blanco <gblanco@nationalfibre.net>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('ic_base_riak');

        $rootNode
            ->children()
                ->scalarNode('default_connection')
                    ->defaultValue('default')
                ->end()
            ->end()
            ->append($this->addConnectionsNode())
            ->append($this->addBucketsNode())
        ;

        return $treeBuilder;
    }

    /**
     * Build connections node configuration definition
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The connections tree builder
     */
    private function addConnectionsNode()
    {
        $builder = new TreeBuilder();
        $node    = $builder->root('connections');

        $node
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('key')
            ->prototype('array')
                ->children()
                    ->scalarNode('host')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('port')
                        ->cannotBeEmpty()
                        ->defaultValue(8087)
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Build buckets node configuration definition
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The buckets tree builder
     */
    private function addBucketsNode()
    {
        $builder = new TreeBuilder();
        $node    = $builder->root('buckets');

        $node
            ->useAttributeAsKey('key')
            ->prototype('array')
                ->children()
                    ->scalarNode('connection')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('name')
                        ->defaultNull()
                    ->end()
                ->end()
                ->append($this->addBucketPropertyListNode())
            ->end()
        ;

        return $node;
    }

    /**
     * Build bucket property list node configuration definition
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The bucket property list tree builder
     */
    private function addBucketPropertyListNode()
    {
        $builder = new TreeBuilder();
        $node    = $builder->root('property_list');

        $node
            ->children()
                ->scalarNode('n_value')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->booleanNode('allow_multiple')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;

        return $node;
    }
}
