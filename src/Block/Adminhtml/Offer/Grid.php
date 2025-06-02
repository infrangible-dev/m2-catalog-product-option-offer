<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Block\Adminhtml\Offer;

use Infrangible\CatalogProductOptionOffer\Block\Adminhtml\Widget\Grid\Column\Renderer\Options;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Grid extends \Infrangible\BackendWidget\Block\Grid
{
    protected function prepareCollection(AbstractDb $collection): void
    {
    }

    /**
     * @throws \Exception
     */
    protected function prepareFields(): void
    {
        $this->addTextColumn(
            'name',
            __('Name')->render()
        );

        $this->addProductNameColumn(
            'product_id',
            __('Product')->render()
        );

        $this->addPriceColumn(
            'price',
            __('Price')->render()
        );

        $this->addWebsiteNameColumn('website_id');

        $this->addYesNoColumn(
            'active',
            __('Active')->render()
        );

        $this->addYesNoColumn(
            'product_view',
            __('Product View')->render()
        );

        $this->addYesNoColumn(
            'cart',
            __('Cart')->render()
        );

        $this->addTextColumnWithRenderer(
            'options',
            __('Options')->render(),
            Options::class
        );
    }

    /**
     * @return string[]
     */
    protected function getHiddenFieldNames(): array
    {
        return ['product_view', 'cart'];
    }
}
