<?php




class GiftVoucherProductPage_ProductOrderItem extends Product_OrderItem {


    private static $db = array(
        'ValueSet' => 'Currency'
    );

     /* standard SS method.
     *
     * @var array
     */
    private static $api_access = array(
        'view' => array(
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
        ),
    );

    /**
     * Standard SS variable.
     *
     * @var string
     */
    private static $singular_name = 'Order for Gift Item';
    public function i18n_singular_name()
    {
        return $this->Config()->get('singular_name');
    }

    /**
     * Standard SS variable.
     *
     * @var string
     */
    private static $plural_name = 'Orders for Gift Item';
    public function i18n_plural_name()
    {
        return $this->Config()->get('plural_name');
    }

    function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->CalculatedTotal = $this->ValueSet;
    }

    function getUnitPrice($recalculate = false)
    {
        return $this->ValueSet;
    }

    function getTotal($recalculate = false)
    {
        return $this->ValueSet * $this->Quantity;
    }

    function getCalculatedTotal()
    {
        return $this->ValueSet * $this->Quantity;
    }

    public function setCalculatedTotal($total) {
        if(!$this->ValueSet) {
            $this->ValueSet = $total;
            $this->write();
        }
    }

    public function getTableSubTitle()
    {
        return _t('GIFTVOUCHERPRODUCTPAGE.Value', 'Value: ').$this->getTotalAsMoney()->Nice();
    }


}
