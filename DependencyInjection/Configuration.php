<?php

namespace c33s\SimpleContentBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('c33s_simple_content');
        
        $rootNode
            ->children()
                ->scalarNode('base_template')
                    ->defaultValue('')
                    ->info('Base template to extend inside the content_template')
                ->end()
                ->scalarNode('content_template')
                    ->defaultValue('c33sSimpleContentBundle:Content:show.html.twig')
                    ->info('Template to use for displaying content')
                ->end()
            ->end()
        ;
        
        return $treeBuilder;
    }
}
