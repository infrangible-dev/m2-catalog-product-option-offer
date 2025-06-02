<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Model\ResourceModel\Offer;

use Infrangible\CatalogProductOptionOffer\Model\Offer;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(
            Offer::class,
            \Infrangible\CatalogProductOptionOffer\Model\ResourceModel\Offer::class
        );
    }

    public function filterByProduct(int $productId): void
    {
        $this->addFieldToFilter(
            'product_id',
            $productId
        );
    }

    public function filterByProductView(): void
    {
        $this->addFieldToFilter(
            'product_view',
            1
        );
    }

    public function filterByCart(): void
    {
        $this->addFieldToFilter(
            'cart',
            1
        );
    }
}
