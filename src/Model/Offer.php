<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Model;

use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionOffer\Model\Offer\OptionFactory;
use Infrangible\CatalogProductOptionOffer\Model\ResourceModel\Offer\Option\Collection;
use Infrangible\CatalogProductOptionOffer\Model\ResourceModel\Offer\Option\CollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 *
 * @method string getName()
 * @method string getDescription()
 * @method string getProductId()
 * @method string getPrice()
 * @method string getCreatedAt()
 * @method void setCreatedAt(string $createdAt)
 * @method string getUpdatedAt()
 * @method void setUpdatedAt(string $updatedAt)
 */
class Offer extends AbstractModel
{
    /** @var Variables */
    protected $variables;

    /** @var OptionFactory */
    protected $offerOptionFactory;

    /** @var ResourceModel\Offer\OptionFactory */
    protected $offerOptionResourceFactory;

    /** @var CollectionFactory */
    protected $offerOptionsCollectionFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        Variables $variables,
        OptionFactory $offerOptionFactory,
        ResourceModel\Offer\OptionFactory $offerOptionResourceFactory,
        CollectionFactory $offerOptionsCollectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

        $this->variables = $variables;
        $this->offerOptionFactory = $offerOptionFactory;
        $this->offerOptionResourceFactory = $offerOptionResourceFactory;
        $this->offerOptionsCollectionFactory = $offerOptionsCollectionFactory;
    }

    protected function _construct(): void
    {
        $this->_init(ResourceModel\Offer::class);
    }

    public function beforeSave(): AbstractModel
    {
        if ($this->isObjectNew()) {
            $this->setCreatedAt(gmdate('Y-m-d H:i:s'));
        }

        $this->setUpdatedAt(gmdate('Y-m-d H:i:s'));

        return parent::beforeSave();
    }

    /**
     * @throws \Exception
     */
    public function afterSave(): AbstractModel
    {
        parent::afterSave();

        $optionIdData = $this->getData('option_ids');
        $optionValueIdData = $this->getData('option_value_ids');

        /** @var Offer\Option $offerOption */
        foreach ($this->getOptionsCollection() as $offerOption) {
            $optionId = $offerOption->getOptionId();

            if ($optionId && ! $offerOption->getOptionValueId()) {
                $optionKey = array_search(
                    $optionId,
                    $optionIdData
                );

                if ($optionKey === false) {
                    $offerOption->delete();
                } else {
                    unset($optionIdData[ $optionKey ]);
                }
            }

            if ($optionId && $offerOption->getOptionValueId()) {
                if (array_key_exists(
                    $optionId,
                    $optionValueIdData
                )) {
                    $optionValueKey = array_search(
                        $offerOption->getOptionValueId(),
                        $optionValueIdData[ $optionId ]
                    );

                    if ($optionValueKey === false) {
                        $offerOption->delete();
                    } else {
                        unset($optionValueIdData[ $optionId ][ $optionValueKey ]);
                    }
                } else {
                    $offerOption->delete();
                }
            }
        }

        $offerOptionResource = $this->offerOptionResourceFactory->create();

        foreach ($optionIdData as $optionId) {
            $offerOption = $this->offerOptionFactory->create();

            $offerOption->setOfferId($this->getId());
            $offerOption->setOptionId($optionId);

            $offerOptionResource->save($offerOption);
        }

        foreach ($optionValueIdData as $optionId => $optionValueIds) {
            foreach ($optionValueIds as $optionValueId) {
                $offerOption = $this->offerOptionFactory->create();

                $offerOption->setOfferId($this->getId());
                $offerOption->setOptionId($optionId);
                $offerOption->setOptionValueId($optionValueId);

                $offerOptionResource->save($offerOption);
            }
        }

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function getOptionsCollection(): Collection
    {
        $collection = $this->offerOptionsCollectionFactory->create();

        $collection->filterByOffer($this->variables->intValue($this->getId()));

        return $collection;
    }
}
