<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Observer;

use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionOffer\Helper\Data;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote\Item;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class CatalogProductOptionCompositeQuoteItemBundleUpdateItem implements ObserverInterface
{
    /** @var Data */
    protected $helper;

    /** @var Variables */
    protected $variables;

    /** @var Json */
    protected $serializer;

    public function __construct(Data $helper, Variables $variables, Json $serializer)
    {
        $this->helper = $helper;
        $this->variables = $variables;
        $this->serializer = $serializer;
    }

    /**
     * @throws \Exception
     */
    public function execute(Observer $observer): void
    {
        /** @var Http $request */
        $request = $observer->getData('request');

        $requestParams = $request->getParams();

        if (array_key_exists(
            'offer',
            $requestParams
        )) {
            $offerIds = $requestParams[ 'offer' ];

            /** @var Item $item */
            $item = $observer->getData('item');

            $product = $item->getProduct();

            $offerOption = $item->getOptionByCode('offer_ids');

            if ($offerOption) {
                $currentOfferIds = explode(
                    ',',
                    $offerOption->getValue()
                );

                foreach ($offerIds as $offerId) {
                    if (! in_array(
                        $offerId,
                        $currentOfferIds
                    )) {
                        $currentOfferIds[] = $offerId;
                    }
                }

                $offerOption->setValue(
                    implode(
                        ',',
                        $currentOfferIds
                    )
                );
            } else {
                $item->addOption(
                    [
                        'code'  => 'offer_ids',
                        'value' => implode(
                            ',',
                            $offerIds
                        )
                    ]
                );

                $offerOption = $item->getOptionByCode('offer_ids');

                $offerOption->setProduct($product);
            }

            foreach ($offerIds as $offerId) {
                $offerOptionData = $this->helper->getOfferData($this->variables->intValue($offerId));

                $item->addOption(
                    [
                        'code'  => sprintf(
                            'offer_%d',
                            $offerId
                        ),
                        'value' => $this->serializer->serialize($offerOptionData)
                    ]
                );

                $offerOption = $item->getOptionByCode(
                    sprintf(
                        'offer_%d',
                        $offerId
                    )
                );

                $offerOption->setProduct($product);
            }
        }
    }
}
