<?php

namespace OroB2B\Bundle\AccountBundle\Visibility\Cache\Product;

use OroB2B\Bundle\AccountBundle\Entity\Visibility\AccountGroupProductVisibility;
use OroB2B\Bundle\AccountBundle\Entity\Visibility\VisibilityInterface;
use OroB2B\Bundle\AccountBundle\Visibility\Cache\ProductCaseCacheBuilderInterface;
use OroB2B\Bundle\ProductBundle\Entity\Product;
use OroB2B\Bundle\WebsiteBundle\Entity\Website;

class AccountGroupProductResolvedCacheBuilder implements ProductCaseCacheBuilderInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolveVisibilitySettings(VisibilityInterface $visibilitySettings)
    {
        // TODO: Implement resolveVisibilitySettings() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isVisibilitySettingsSupported(VisibilityInterface$visibilitySettings)
    {
        return $visibilitySettings instanceof AccountGroupProductVisibility;
    }

    /**
     * {@inheritdoc}
     */
    public function productCategoryChanged(Product $product)
    {
        // TODO: Implement updateProductResolvedVisibility() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildCache(Website $website = null)
    {
        // TODO: Implement buildCache() method.
    }
}
