<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Block\Adminhtml\Widget\Grid\Column\Renderer;

use Exception;
use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionOffer\Model\Offer;
use Infrangible\Core\Helper\Product;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\DataObject;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Options extends AbstractRenderer
{
    /** @var Variables */
    protected $variables;

    /** @var Product */
    protected $productHelper;

    public function __construct(Context $context, Variables $variables, Product $productHelper, array $data = [])
    {
        parent::__construct(
            $context,
            $data
        );

        $this->variables = $variables;
        $this->productHelper = $productHelper;
    }

    /**
     * @param Offer $row
     *
     * @throws Exception
     */
    public function render(DataObject $row): string
    {
        $productId = $row->getProductId();

        $product = $this->productHelper->loadProduct($this->variables->intValue($productId));

        $productOptionCollection = $product->getProductOptionsCollection();

        $optionsOutput = [];

        /** @var Offer\Option $option */
        foreach ($row->getOptionsCollection() as $option) {
            /** @var Option $productOption */
            foreach ($productOptionCollection as $productOption) {
                $productOptionValues = $productOption->getValues();

                if ($productOptionValues) {
                    /** @var Option\Value $productOptionValue */
                    foreach ($productOptionValues as $productOptionValue) {
                        if ($option->getOptionId() == $productOptionValue->getOptionId() &&
                            $option->getOptionValueId() == $productOptionValue->getOptionTypeId()) {

                            $optionsOutput[] = sprintf(
                                '%s: %s',
                                $productOption->getTitle(),
                                $productOptionValue->getTitle()
                            );
                        }
                    }
                } else {
                    if ($option->getOptionId() == $productOption->getOptionId()) {
                        $optionsOutput[] = $productOption->getTitle();
                    }
                }
            }
        }

        natcasesort($optionsOutput);

        return implode(
            '<br>',
            $optionsOutput
        );
    }
}
