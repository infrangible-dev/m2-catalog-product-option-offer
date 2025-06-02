<?php /** @noinspection PhpDeprecationInspection */

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Block\Adminhtml\Offer;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Variables;
use Infrangible\CatalogProductOptionOffer\Model\Offer;
use Infrangible\Core\Helper\Product;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Form extends \Infrangible\BackendWidget\Block\Form
{
    /** @var Variables */
    protected $variables;

    /** @var Product */
    protected $productHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Arrays $arrays,
        \Infrangible\Core\Helper\Registry $registryHelper,
        \Infrangible\BackendWidget\Helper\Form $formHelper,
        Variables $variables,
        Product $productHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $arrays,
            $registryHelper,
            $formHelper,
            $data
        );

        $this->variables = $variables;
        $this->productHelper = $productHelper;
    }

    /**
     * @throws \Exception
     */
    protected function prepareFields(\Magento\Framework\Data\Form $form): void
    {
        $fieldSet = $form->addFieldset(
            'general',
            ['legend' => __('General')]
        );

        $this->addTextField(
            $fieldSet,
            'name',
            __('Name')->render()
        );

        $this->addTextareaField(
            $fieldSet,
            'description',
            __('Description')->render()
        );

        $this->addProductNameField(
            $fieldSet,
            'product_id',
            __('Product')->render(),
            true
        );

        $this->addPriceField(
            $fieldSet,
            'price',
            __('Price')->render()
        );

        $this->addWebsiteSelectField(
            $fieldSet,
            'website_id'
        );

        $this->addYesNoWithDefaultField(
            $fieldSet,
            'active',
            __('Active')->render(),
            1
        );

        $this->addYesNoWithDefaultField(
            $fieldSet,
            'product_view',
            __('Product View')->render(),
            1
        );

        $this->addYesNoWithDefaultField(
            $fieldSet,
            'cart',
            __('Cart')->render(),
            1
        );

        /** @var Offer $offer */
        $offer = $this->getObject();

        if ($offer->getId()) {
            $offerOptionsCollection = $offer->getOptionsCollection();

            $optionsFieldSet = $form->addFieldset(
                'options',
                ['legend' => __('Options')]
            );

            $productId = $offer->getProductId();

            $product = $this->productHelper->loadProduct($this->variables->intValue($productId));

            $productOptionCollection = $product->getProductOptionsCollection();

            /** @var Option $productOption */
            foreach ($productOptionCollection as $productOption) {
                $optionsFieldSet->addField(
                    sprintf(
                        'option_id_%d_title',
                        $productOption->getId()
                    ),
                    'label',
                    ['value' => $productOption->getTitle()]
                );

                $checked = false;

                /** @var Offer\Option $offerOption */
                foreach ($offerOptionsCollection as $offerOption) {
                    if ($offerOption->getOptionId() == $productOption->getId() &&
                        ! $offerOption->getOptionValueId()) {

                        $checked = true;
                    }
                }

                $this->formHelper->addSimpleCheckboxField(
                    $optionsFieldSet,
                    sprintf(
                        'option_ids[%d]',
                        $productOption->getId()
                    ),
                    $productOption->getTitle(),
                    1,
                    $checked
                );

                $productOptionValues = $productOption->getValues();

                if ($productOptionValues) {
                    /** @var Option\Value $productOptionValue */
                    foreach ($productOptionValues as $productOptionValue) {
                        $checked = false;

                        /** @var Offer\Option $offerOption */
                        foreach ($offerOptionsCollection as $offerOption) {
                            if ($offerOption->getOptionId() == $productOption->getId() &&
                                $offerOption->getOptionValueId() == $productOptionValue->getId()) {

                                $checked = true;
                            }
                        }

                        $this->formHelper->addSimpleCheckboxField(
                            $optionsFieldSet,
                            sprintf(
                                'option_value_ids[%d][%d]',
                                $productOption->getId(),
                                $productOptionValue->getId()
                            ),
                            $productOptionValue->getTitle(),
                            1,
                            $checked
                        );
                    }
                }
            }
        }
    }
}
