<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Plugin\Catalog\Model\Product\Type;

use Infrangible\CatalogProductOptionOffer\Model\OfferFactory;
use Magento\Catalog\Model\Product;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Price
{
    /** @var OfferFactory */
    protected $offerFactory;

    /** @var \Infrangible\CatalogProductOptionOffer\Model\ResourceModel\OfferFactory */
    protected $offerResourceFactory;

    public function __construct(
        OfferFactory $offerFactory,
        \Infrangible\CatalogProductOptionOffer\Model\ResourceModel\OfferFactory $offerResourceFactory
    ) {
        $this->offerFactory = $offerFactory;
        $this->offerResourceFactory = $offerResourceFactory;
    }

    /**
     * @noinspection PhpUnusedParameterInspection
     */
    public function afterGetFinalPrice(
        Product\Type\Price $subject,
        float $finalPrice,
        ?float $qty,
        Product $product
    ): float {
        $finalPrice = $this->applyOfferPrices(
            $product,
            $finalPrice
        );

        $finalPrice = max(
            0,
            $finalPrice
        );

        $product->setFinalPrice($finalPrice);

        return $finalPrice;
    }

    protected function applyOfferPrices(Product $product, float $finalPrice): float
    {
        $offerIdsOption = $product->getCustomOption('offer_ids');

        $offersPrice = 0;

        if ($offerIdsOption) {
            $offerIds = explode(
                ',',
                $offerIdsOption->getValue()
            );

            $offerResource = $this->offerResourceFactory->create();

            foreach ($offerIds as $offerId) {
                $offer = $this->offerFactory->create();

                $offerResource->load(
                    $offer,
                    $offerId
                );

                if ($offer->getId()) {
                    $offersPrice += (float)$offer->getPrice();
                }
            }
        }

        $product->setData(
            'offers_price',
            $offersPrice
        );

        return $finalPrice + $offersPrice;
    }
}
