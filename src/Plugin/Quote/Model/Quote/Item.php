<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Plugin\Quote\Model\Quote;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\Calculation;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Item
{
    /** @var Calculation */
    protected $calculation;

    /** @var Data */
    protected $taxHelper;

    /** @var PriceCurrencyInterface */
    protected $priceCurrency;

    public function __construct(Calculation $calculation, Data $taxHelper, PriceCurrencyInterface $priceCurrency)
    {
        $this->calculation = $calculation;
        $this->taxHelper = $taxHelper;
        $this->priceCurrency = $priceCurrency;
    }

    public function afterCalcRowTotal(
        \Magento\Quote\Model\Quote\Item $subject,
        \Magento\Quote\Model\Quote\Item $result
    ): \Magento\Quote\Model\Quote\Item {
        $qty = $subject->getTotalQty();

        $quote = $subject->getQuote();

        $taxRateRequest = $this->calculation->getRateRequest(
            $quote->getShippingAddress(),
            $quote->getBillingAddress(),
            $quote->getCustomerTaxClassId(),
            $quote->getStoreId(),
            $quote->getCustomerId()
        );

        $product = $subject->getProduct();

        $taxRateRequest->setData(
            'product_class_id',
            $product->getData('tax_class_id')
        );

        $rate = $this->calculation->getRate($taxRateRequest);

        $this->setOffersPrice(
            $result,
            $qty,
            $rate
        );

        $this->setBaseOffersPrice(
            $result,
            $qty,
            $rate
        );

        return $result;
    }

    private function setOffersPrice(\Magento\Quote\Model\Quote\Item $item, float $qty, float $taxRate): void
    {
        $offersPrice = $this->getConvertedOffersPrice($item);

        if ($this->taxHelper->priceIncludesTax()) {
            $taxAmount = $this->calculation->calcTaxAmount(
                $offersPrice,
                $taxRate,
                true
            );

            $item->setData(
                'offers_price',
                $this->priceCurrency->roundPrice($offersPrice - $taxAmount)
            );

            $item->setData(
                'offers_price_incl_tax',
                $offersPrice
            );
        } else {
            $item->setData(
                'offers_price',
                $this->priceCurrency->roundPrice($offersPrice)
            );

            $taxAmount = $this->calculation->calcTaxAmount(
                $offersPrice,
                $taxRate
            );

            $item->setData(
                'offers_price_incl_tax',
                $this->priceCurrency->roundPrice($offersPrice + $taxAmount)
            );
        }

        $rowOffersPrice = $this->priceCurrency->roundPrice($this->priceCurrency->roundPrice($offersPrice) * $qty);

        if ($this->taxHelper->priceIncludesTax()) {
            $taxAmount = $this->calculation->calcTaxAmount(
                $rowOffersPrice,
                $taxRate,
                true
            );

            $item->setData(
                'row_offers_price',
                $this->priceCurrency->roundPrice($rowOffersPrice - $taxAmount)
            );

            $item->setData(
                'row_offers_price_incl_tax',
                $this->priceCurrency->roundPrice($rowOffersPrice)
            );
        } else {
            $taxAmount = $this->calculation->calcTaxAmount(
                $rowOffersPrice,
                $taxRate
            );

            $item->setData(
                'row_offers_price',
                $rowOffersPrice
            );

            $item->setData(
                'row_offers_price_incl_tax',
                $this->priceCurrency->roundPrice($rowOffersPrice + $taxAmount)
            );
        }
    }

    private function getConvertedOffersPrice(\Magento\Quote\Model\Quote\Item $item): float
    {
        $price = $item->getData('converted_offers_price');

        if ($price === null) {
            $product = $item->getProduct();

            $price = $this->priceCurrency->convert(
                $product->getData('offers_price'),
                $item->getStore()
            );

            $item->setData(
                'converted_offers_price',
                $price
            );
        }

        return $price;
    }

    private function setBaseOffersPrice(\Magento\Quote\Model\Quote\Item $item, float $qty, float $taxRate): void
    {
        $product = $item->getProduct();

        $baseOffersPrice = $product->getData('offers_price');

        if ($this->taxHelper->priceIncludesTax()) {
            $taxAmount = $this->calculation->calcTaxAmount(
                $baseOffersPrice,
                $taxRate,
                true
            );

            $item->setData(
                'base_offers_price',
                $this->priceCurrency->roundPrice($baseOffersPrice - $taxAmount)
            );

            $item->setData(
                'base_offers_price_incl_tax',
                $baseOffersPrice
            );
        } else {
            $taxAmount = $this->calculation->calcTaxAmount(
                $baseOffersPrice,
                $taxRate
            );

            $item->setData(
                'base_offers_price',
                $baseOffersPrice
            );

            $item->setData(
                'base_offers_price_incl_tax',
                $this->priceCurrency->roundPrice($baseOffersPrice + $taxAmount)
            );
        }

        $baseRowOffersPrice =
            $this->priceCurrency->roundPrice($this->priceCurrency->roundPrice($baseOffersPrice) * $qty);

        if ($this->taxHelper->priceIncludesTax()) {
            $taxAmount = $this->calculation->calcTaxAmount(
                $baseRowOffersPrice,
                $taxRate,
                true
            );

            $item->setData(
                'base_row_offers_price',
                $this->priceCurrency->roundPrice($baseRowOffersPrice - $taxAmount)
            );

            $item->setData(
                'base_row_offers_price_incl_tax',
                $baseRowOffersPrice
            );
        } else {
            $taxAmount = $this->calculation->calcTaxAmount(
                $baseRowOffersPrice,
                $taxRate
            );

            $item->setData(
                'base_row_offers_price',
                $baseRowOffersPrice
            );

            $item->setData(
                'base_row_offers_price_incl_tax',
                $this->priceCurrency->roundPrice($baseRowOffersPrice + $taxAmount)
            );
        }
    }
}
