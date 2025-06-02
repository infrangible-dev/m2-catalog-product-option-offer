<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Plugin\Quote\Model\Quote\Item;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ToOrderItem
{
    private static $columns = [
        'offers_price',
        'base_offers_price',
        'row_offers_price',
        'base_row_offers_price',
        'offers_price_incl_tax',
        'base_offers_price_incl_tax',
        'row_offers_price_incl_tax',
        'base_row_offers_price_incl_tax'
    ];

    /**
     * @noinspection PhpUnusedParameterInspection
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        AbstractItem $item,
        $additional = []
    ): OrderItemInterface {
        /** @var Item $orderItem */
        $orderItem = $proceed(
            $item,
            $additional
        );

        foreach (static::$columns as $column) {
            $orderItem->setData(
                $column,
                $item->getData($column)
            );
        }

        return $orderItem;
    }
}
