<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Model\ResourceModel\Offer\Option;

use Infrangible\CatalogProductOptionOffer\Model\Offer\Option;
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
            Option::class,
            \Infrangible\CatalogProductOptionOffer\Model\ResourceModel\Offer\Option::class
        );
    }

    public function filterByOffer(int $offerId): void
    {
        $this->addFieldToFilter(
            'offer_id',
            $offerId
        );
    }
}
