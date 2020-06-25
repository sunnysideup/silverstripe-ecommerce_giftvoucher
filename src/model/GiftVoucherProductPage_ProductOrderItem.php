<?php




class GiftVoucherProductPage_ProductOrderItem extends ProductOrderItem
{

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * OLD: private static $db (case sensitive)
  * NEW: 
    private static $table_name = '[SEARCH_REPLACE_CLASS_NAME_GOES_HERE]';

    private static $db (COMPLEX)
  * EXP: Check that is class indeed extends DataObject and that it is not a data-extension!
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
    
    private static $table_name = 'GiftVoucherProductPage_ProductOrderItem';

    private static $db = array(
        'ValueSet' => 'Currency',
        'Description' => 'Varchar(40)'
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

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->CalculatedTotal = $this->ValueSet;
    }

    public function getUnitPrice($recalculate = false)
    {
        return $this->ValueSet;
    }

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
     * @return this
     */
    public function setCustomCalculatedTotal($total)
    {
        if (!$this->ValueSet) {
            $this->ValueSet = $total;
            $this->write();
        }

        return $this;
    }

    /**
     * @param string $description
     * @return this
     */
    public function setCustomDescription($description)
    {
        if (! $this->Description) {
            $this->Description = $description;
            $this->write();
        }

        return $this;
    }

    public function getTableSubTitle()
    {
        $array = [];
        if ($this->Description || 1 == 1) {
            $array[] = Convert::raw2xml($this->Description);
        }
        $array[] = _t('GIFTVOUCHERPRODUCTPAGE.Value', 'Value: ').$this->UnitPriceAsMoney()->Nice();
        return implode('<br />', $array);
    }
}

