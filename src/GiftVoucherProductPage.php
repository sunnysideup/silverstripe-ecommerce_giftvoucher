<?php

namespace Sunnysideup\EcommerceGiftvoucher;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\Security\Member;
use Sunnysideup\Ecommerce\Pages\Product;
use Sunnysideup\EcommerceGiftvoucher\Model\GiftVoucherProductPageProductOrderItem;

/**
 * @author nicolaas [at] sunnysideup.co.nz
 * @requires ecommerce
 * @requires ecommerce_product_variation
 */
class GiftVoucherProductPage extends Product
{
    /**
     * @var string
     */
    protected $defaultClassNameForOrderItem = GiftVoucherProductPageProductOrderItem::class;

    private static $table_name = 'GiftVoucherProductPage';

    private static $db = [
        'DescriptionFieldLabel' => 'Varchar(255)',
        'AmountFieldLabel' => 'Varchar(255)',
        'ActionFieldLabel' => 'Varchar(255)',
        'MinimumAmount' => 'Decimal(9,2)',
        'MaximumAmount' => 'Decimal(9,2)',
        'RecommendedAmounts' => 'Varchar(255)',
        'CanSetDescription' => 'Boolean',
        'DefaultDescription' => 'Varchar(255)',
    ];

    private static $defaults = [
        'DescriptionFieldLabel' => 'Enter Description',
        'AmountFieldLabel' => 'Enter Amount',
        'ActionFieldLabel' => 'Add to cart',
        'MinimumAmount' => 1,
        'MaximumAmount' => 100,
        'AllowPurchase' => false,
        'Price' => 0,
    ];

    private static $field_labels = [
        'DescriptionFieldLabel' => 'Description Label',
        'AmountFieldLabel' => 'Amount Label',
        'ActionFieldLabel' => 'Button Label',
        'MinimumAmount' => 'Minimum Amount',
        'MaximumAmount' => 'Maximum Amount',
        'RecommendedAmounts' => 'Hinted amounts',
        'CanSetDescription' => 'Customer Adds Description',
        'DefaultDescription' => 'Default Description',
    ];

    private static $field_labels_right = [
        'DescriptionFieldLabel' => 'e.g. please enter title for payment',
        'AmountFieldLabel' => 'e.g. please enter amount for payment',
        'ActionFieldLabel' => 'e.g. pay now',
        'MinimumAmount' => 'e.g. 10.00',
        'MaximumAmount' => 'e.g. 100.00',
        'RecommendedAmounts' => 'create a list of recommended payment amounts, separated by a space, e.g. 10.00 12.00 19.00 23.00',
        'CanSetDescription' => 'can the customer add their own description to the payment?',
        'DefaultDescription' => 'e.g. generic product, this field is optional',
    ];

    private static $singular_name = 'Any Price Product';

    private static $plural_name = 'Any Price Products';

    private static $icon = 'ecommerce_giftvoucher/images/treeicons/GiftVoucherProductPage';

    /**
     * @config
     *
     * @var string Description of the class functionality, typically shown to a user
     *             when selecting which page type to create. Translated through {@link provideI18nEntities()}.
     */
    private static $description = 'Generic product that can be used to allow customers to choose a specific amount to pay.';

    public function i18n_singular_name()
    {
        return _t('GiftVoucherProductPage.GIFT_VOUCHER_PRODUCT_PAGE', 'Gift Voucher Page');
    }

    public function i18n_plural_name()
    {
        return _t('GiftVoucherProductPage.GIFT_VOUCHER_PRODUCT_PAGES', 'Gift Voucher Pages');
    }

    public function canCreate($member = null, $context = [])
    {
        return SiteTree::get()->filter(['ClassName' => GiftVoucherProductPage::class])->count() ? false : true;
    }

    public function canPurchase(Member $member = null, $checkPrice = true)
    {
        return parent::canPurchase($member, false);
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fieldLabels = $this->fieldLabels();
        $fieldLabelsRight = Config::inst()->get(GiftVoucherProductPage::class, 'field_labels_right');
        $exampleLink = Director::absoluteURL($this->Link('setamount')) . '/123.45/?description=' . urlencode('test payment only');
        $exampleLinkExplanation = sprintf(_t('GiftVoucherProductPage.EXPLANATION', '<br /><br /><h5>How to preset the amount?</h5><p>The link <a href="%1$s">%1$s</a> will pre-set the amount to 123.45. You can use this link (and vary the amount as needed) to cutomers to receive payments.</p>.'), $exampleLink);
        $fields->addFieldsToTab(
            'Root.Form',
            [
                TextField::create('DescriptionFieldLabel', $fieldLabels['DescriptionFieldLabel'])->setDescription($fieldLabelsRight['DescriptionFieldLabel']),
                TextField::create('AmountFieldLabel', $fieldLabels['AmountFieldLabel'])->setDescription($fieldLabelsRight['AmountFieldLabel']),
                TextField::create('ActionFieldLabel', $fieldLabels['ActionFieldLabel'])->setDescription($fieldLabelsRight['ActionFieldLabel']),

                NumericField::create('MinimumAmount', $fieldLabels['MinimumAmount'])->setScale(2)->setDescription($fieldLabelsRight['MinimumAmount']),
                NumericField::create('MaximumAmount', $fieldLabels['MaximumAmount'])->setScale(2)->setDescription($fieldLabelsRight['MaximumAmount']),
                TextField::create('RecommendedAmounts', $fieldLabels['RecommendedAmounts'])->setDescription($fieldLabelsRight['RecommendedAmounts']),
                CheckboxField::create('CanSetDescription', $fieldLabels['CanSetDescription'])->setDescription($fieldLabelsRight['CanSetDescription']),
                TextField::create('DefaultDescription', $fieldLabels['DefaultDescription'])->setDescription($fieldLabelsRight['DefaultDescription']),
                LiteralField::create('ExampleLinkExplanation', $exampleLinkExplanation),
            ]
        );
        if (! $this->CanSetDescription) {
            $fields->removeByName('DescriptionFieldLabel');
        }
        // Standard product detail fields
        $fields->removeFieldsFromTab(
            'Root.Details',
            [
                'Weight',
                'Price',
                'Model',
            ]
        );

        // Flags for this product which affect it's behaviour on the site
        $fields->removeFieldsFromTab(
            'Root.Details',
            [
                'FeaturedProduct',
            ]
        );

        return $fields;
    }
}
