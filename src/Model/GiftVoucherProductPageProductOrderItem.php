<?php

namespace Sunnysideup\EcommerceGiftvoucher\Model;

use Override;
use Sunnysideup\Ecommerce\Model\ProductOrderItem;

/**
 * Class \Sunnysideup\EcommerceGiftvoucher\Model\GiftVoucherProductPageProductOrderItem
 *
 * @property float $ValueSet
 * @property string $Description
 */
class GiftVoucherProductPageProductOrderItem extends ProductOrderItem
{
    private static $table_name = 'GiftVoucherProductPageProductOrderItem';

    private static $db = [
        'ValueSet' => 'Currency',
        'Description' => 'Varchar(200)',
    ];

    /* standard SS method.
    *
    * @var array
    */
    private static $api_access = [
        'view' => [
            'CalculatedTotal',
            'TableTitle',
            'TableSubTitleNOHTML',
            'Name',
            'TableValue',
            'Quantity',
            'BuyableID',
            'BuyableClassName',
            'Version',
            'UnitPrice',
            'Total',
            'Order',
            'InternalItemID',
        ],
    ];

    /**
     * Standard SS variable.
     *
     * @var string
     */
    private static $singular_name = 'Order for Gift Item';

    /**
     * Standard SS variable.
     *
     * @var string
     */
    private static $plural_name = 'Orders for Gift Item';

    #[Override]
    public function i18n_singular_name()
    {
        return $this->Config()->get('singular_name');
    }

    #[Override]
    public function plural_name()
    {
        return $this->Config()->get('plural_name');
    }

    #[Override]
    public function getUnitPrice($recalculate = false)
    {
        return $this->ValueSet;
    }

    #[Override]
    public function getTotal($recalculate = false)
    {
        return $this->ValueSet * $this->Quantity;
    }

    public function getCalculatedTotal()
    {
        return $this->ValueSet * $this->Quantity;
    }

    /**
     * @param float $total
     *
     * @return $this
     */
    public function setCustomCalculatedTotal($total)
    {
        if (! $this->ValueSet) {
            $this->ValueSet = $total;
            $this->write();
        }

        return $this;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setCustomDescription($description)
    {
        if (! $this->Description) {
            $this->Description = $description;
            $this->write();
        }

        return $this;
    }

    #[Override]
    public function getTableSubTitle(): string
    {
        if ($this->priceHasBeenFixed()) {
            return (string) $this->TableSubTitleFixed;
        }

        return (string) $this->Description;
    }

    #[Override]
    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->CalculatedTotal = $this->ValueSet;
    }
}
