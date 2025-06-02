<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Model\Offer;

use Magento\Framework\Model\AbstractModel;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @method string getOfferId()
 * @method void setOfferId(string $offerId)
 * @method string getOptionId()
 * @method void setOptionId(string $optionId)
 * @method string getOptionValueId()
 * @method void setOptionValueId(string $optionValueId)
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class Option extends AbstractModel
{
    protected function _construct(): void
    {
        $this->_init(\Infrangible\CatalogProductOptionOffer\Model\ResourceModel\Offer\Option::class);
    }

    public function beforeSave(): AbstractModel
    {
        if ($this->isObjectNew()) {
            $this->setCreatedAt(gmdate('Y-m-d H:i:s'));
        }

        $this->setUpdatedAt(gmdate('Y-m-d H:i:s'));

        return parent::beforeSave();
    }
}
