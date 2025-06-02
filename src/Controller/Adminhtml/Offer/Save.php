<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Controller\Adminhtml\Offer;

use Infrangible\BackendWidget\Model\Backend\Session;
use Infrangible\CatalogProductOptionOffer\Traits\Offer;
use Infrangible\Core\Helper\Cache;
use Infrangible\Core\Helper\Instances;
use Infrangible\Core\Helper\Registry;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Model\AbstractModel;
use Psr\Log\LoggerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Save extends \Infrangible\BackendWidget\Controller\Backend\Object\Save
{
    use Offer;

    /** @var Cache */
    protected $cacheHelper;

    public function __construct(
        Registry $registryHelper,
        Instances $instanceHelper,
        Context $context,
        LoggerInterface $logging,
        Session $session,
        Cache $cacheHelper
    ) {
        parent::__construct(
            $registryHelper,
            $instanceHelper,
            $context,
            $logging,
            $session
        );

        $this->cacheHelper = $cacheHelper;
    }

    /**
     * @param \Infrangible\CatalogProductOptionOffer\Model\Offer $object
     */
    protected function beforeSave(AbstractModel $object): void
    {
        parent::beforeSave($object);

        /** @var Http $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $postData = $request->getPost()->toArray();

            $object->setData(
                'option_ids',
                array_key_exists(
                    'option_ids',
                    $postData
                ) ? array_keys($postData[ 'option_ids' ]) : []
            );

            $optionValueIds = [];

            if (array_key_exists(
                'option_value_ids',
                $postData
            )) {
                foreach ($postData[ 'option_value_ids' ] as $optionId => $optionIdValues) {
                    $optionValueIds[ $optionId ] = array_keys($optionIdValues);
                }
            }

            $object->setData(
                'option_value_ids',
                $optionValueIds
            );
        }
    }

    protected function afterSave(AbstractModel $object): void
    {
        parent::afterSave($object);

        $this->cacheHelper->invalidateFullPageCache();
    }
}
