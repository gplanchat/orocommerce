<?php

namespace Oro\Bundle\ProductBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\CurrencyBundle\Rounding\RoundingServiceInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('oro_product');

        SettingsBuilder::append(
            $rootNode,
            [
                'unit_rounding_type' => ['value' => RoundingServiceInterface::ROUND_HALF_UP],
                'default_unit' => ['value' => 'each'],
                'default_unit_precision' => ['value' => 0],
                'general_frontend_product_visibility' => [
                    'value' => [
                        Product::INVENTORY_STATUS_IN_STOCK,
                        Product::INVENTORY_STATUS_OUT_OF_STOCK
                    ]
                ]
            ]
        );

        return $treeBuilder;
    }
}
