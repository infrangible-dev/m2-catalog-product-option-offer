<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Offer extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init(
            'catalog_product_option_offer',
            'id'
        );
    }
}
