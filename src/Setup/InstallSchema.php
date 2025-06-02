<?php

declare(strict_types=1);

namespace Infrangible\CatalogProductOptionOffer\Setup;

use Exception;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class InstallSchema implements InstallSchemaInterface
{
    private static $tables = [
        'quote_item',
        'sales_order_item'
    ];

    private static $columns = [
        'offers_price'                   => 'Offers price',
        'base_offers_price'              => 'Base offers price',
        'row_offers_price'               => 'Row offers price',
        'base_row_offers_price'          => 'Base row offers price',
        'offers_price_incl_tax'          => 'Offers price incl tax',
        'base_offers_price_incl_tax'     => 'Base offers price incl tax',
        'row_offers_price_incl_tax'      => 'Row offers price incl tax',
        'base_row_offers_price_incl_tax' => 'Base row offers price incl tax'
    ];

    /**
     * @throws Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $connection = $setup->getConnection();

        $this->createOfferTable($connection);
        $this->createOfferOptionTable($connection);
        $this->createTableColumns($connection);

        $setup->endSetup();
    }

    /**
     * @throws Exception
     */
    private function createOfferTable(AdapterInterface $connection)
    {
        $productOptionOfferTableName = $connection->getTableName('catalog_product_option_offer');

        if (! $connection->isTableExists($productOptionOfferTableName)) {
            $productEntityTableName = $connection->getTableName('catalog_product_entity');
            $websiteTableName = $connection->getTableName('store_website');

            $productOptionOfferTable = $connection->newTable($productOptionOfferTableName);

            $productOptionOfferTable->addColumn(
                'id',
                Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            );
            $productOptionOfferTable->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            );
            $productOptionOfferTable->addColumn(
                'description',
                Table::TYPE_TEXT,
                24576,
                ['nullable' => true]
            );
            $productOptionOfferTable->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false]
            );
            $productOptionOfferTable->addColumn(
                'price',
                Table::TYPE_DECIMAL,
                '20,6',
                ['unsigned' => false, 'nullable' => false]
            );
            $productOptionOfferTable->addColumn(
                'website_id',
                Table::TYPE_SMALLINT,
                5,
                ['nullable' => false, 'unsigned' => true, 'default' => 0]
            );
            $productOptionOfferTable->addColumn(
                'active',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'unsigned' => true, 'default' => 0]
            );
            $productOptionOfferTable->addColumn(
                'product_view',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'unsigned' => true, 'default' => 1]
            );
            $productOptionOfferTable->addColumn(
                'cart',
                Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'unsigned' => true, 'default' => 1]
            );
            $productOptionOfferTable->addColumn(
                'created_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false, 'default' => '0000-00-00 00:00:00']
            );
            $productOptionOfferTable->addColumn(
                'updated_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false, 'default' => '0000-00-00 00:00:00']
            );

            $productOptionOfferTable->addForeignKey(
                $connection->getForeignKeyName(
                    $productOptionOfferTableName,
                    'product_id',
                    $productEntityTableName,
                    'entity_id'
                ),
                'product_id',
                $productEntityTableName,
                'entity_id',
                Table::ACTION_CASCADE
            );

            $productOptionOfferTable->addForeignKey(
                $connection->getForeignKeyName(
                    $productOptionOfferTableName,
                    'website_id',
                    $websiteTableName,
                    'website_id'
                ),
                'website_id',
                $websiteTableName,
                'website_id',
                Table::ACTION_CASCADE
            );

            $connection->createTable($productOptionOfferTable);
        }
    }

    /**
     * @throws Exception
     */
    private function createOfferOptionTable(AdapterInterface $connection)
    {
        $productOptionOfferOptionTableName = $connection->getTableName('catalog_product_option_offer_option');

        if (! $connection->isTableExists($productOptionOfferOptionTableName)) {
            $productOptionOfferTableName = $connection->getTableName('catalog_product_option_offer');
            $productOptionTableName = $connection->getTableName('catalog_product_option');
            $productOptionValueTableName = $connection->getTableName('catalog_product_option_type_value');

            $productOptionOfferOptionTable = $connection->newTable($productOptionOfferOptionTableName);

            $productOptionOfferOptionTable->addColumn(
                'id',
                Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            );
            $productOptionOfferOptionTable->addColumn(
                'offer_id',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false]
            );
            $productOptionOfferOptionTable->addColumn(
                'option_id',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => true]
            );
            $productOptionOfferOptionTable->addColumn(
                'option_value_id',
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => true]
            );
            $productOptionOfferOptionTable->addColumn(
                'created_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false, 'default' => '0000-00-00 00:00:00']
            );
            $productOptionOfferOptionTable->addColumn(
                'updated_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false, 'default' => '0000-00-00 00:00:00']
            );

            $productOptionOfferOptionTable->addForeignKey(
                $connection->getForeignKeyName(
                    $productOptionOfferOptionTableName,
                    'offer_id',
                    $productOptionOfferTableName,
                    'id'
                ),
                'offer_id',
                $productOptionOfferTableName,
                'id',
                Table::ACTION_CASCADE
            );

            $productOptionOfferOptionTable->addForeignKey(
                $connection->getForeignKeyName(
                    $productOptionOfferOptionTableName,
                    'option_id',
                    $productOptionTableName,
                    'option_id'
                ),
                'option_id',
                $productOptionTableName,
                'option_id',
                Table::ACTION_CASCADE
            );

            $productOptionOfferOptionTable->addForeignKey(
                $connection->getForeignKeyName(
                    $productOptionOfferOptionTableName,
                    'option_value_id',
                    $productOptionValueTableName,
                    'option_type_id'
                ),
                'option_value_id',
                $productOptionValueTableName,
                'option_type_id',
                Table::ACTION_CASCADE
            );

            $connection->createTable($productOptionOfferOptionTable);
        }
    }

    private function createTableColumns(AdapterInterface $connection)
    {
        foreach (self::$tables as $tableName) {
            $tableName = $connection->getTableName($tableName);

            foreach (static::$columns as $column => $comment) {
                if (! $connection->tableColumnExists(
                    $tableName,
                    $column
                )) {
                    $connection->addColumn(
                        $tableName,
                        $column,
                        $this->getColumnDefinition($comment)
                    );
                }
            }
        }
    }

    private function getColumnDefinition(string $comment): array
    {
        return [
            'type'     => Table::TYPE_DECIMAL,
            'length'   => '20,4',
            'nullable' => true,
            'default'  => '0.0000',
            'comment'  => $comment
        ];
    }
}
