<?php

namespace C33s\SimpleContentBundle\DependencyInjection;

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
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('default_base_template')
                    ->defaultValue(null)
                    ->info('Base template to extend for full pages')
                ->end()
                ->scalarNode('default_renderer_template')
                    ->defaultValue('C33sSimpleContentBundle:Renderer:markdown.html.twig')
                    ->info('Template to use for rendering/filtering content')
                ->end()
                ->booleanNode('use_locale_fallback')
                    ->defaultValue(false)
                    ->info('Set to true to use content block locale fallback by default.')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
