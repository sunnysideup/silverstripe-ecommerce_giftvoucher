2020-06-25 03:29

# running php upgrade upgrade see: https://github.com/silverstripe/silverstripe-upgrader
cd /var/www/upgrades/ecommerce_giftvoucher
php /var/www/ss3/upgrader/vendor/silverstripe/upgrader/bin/upgrade-code upgrade /var/www/upgrades/ecommerce_giftvoucher/ecommerce_giftvoucher  --root-dir=/var/www/upgrades/ecommerce_giftvoucher --write -vvv
Writing changes for 4 files
Running upgrades on "/var/www/upgrades/ecommerce_giftvoucher/ecommerce_giftvoucher"
[2020-06-25 15:29:42] Applying RenameClasses to _config.php...
[2020-06-25 15:29:42] Applying ClassToTraitRule to _config.php...
[2020-06-25 15:29:42] Applying RenameClasses to GiftVoucherTest.php...
[2020-06-25 15:29:42] Applying ClassToTraitRule to GiftVoucherTest.php...
[2020-06-25 15:29:42] Applying RenameClasses to GiftVoucherProductPage_Controller.php...
[2020-06-25 15:29:42] Applying ClassToTraitRule to GiftVoucherProductPage_Controller.php...
[2020-06-25 15:29:42] Applying RenameClasses to GiftVoucherProductPage_ProductOrderItem.php...
[2020-06-25 15:29:42] Applying ClassToTraitRule to GiftVoucherProductPage_ProductOrderItem.php...
[2020-06-25 15:29:42] Applying RenameClasses to GiftVoucherProductPage.php...
[2020-06-25 15:29:42] Applying ClassToTraitRule to GiftVoucherProductPage.php...
modified:	tests/GiftVoucherTest.php
@@ -1,4 +1,6 @@
 <?php
+
+use SilverStripe\Dev\SapphireTest;

 class GiftVoucherTest extends SapphireTest
 {

modified:	src/GiftVoucherProductPage_Controller.php
@@ -2,17 +2,29 @@

 namespace Sunnysideup\EcommerceGiftvoucher;

-use ProductController;
-use Controller;
-use FieldList;
-use TextField;
-use CurrencyField;
-use FormAction;
-use RequiredFields;
-use Form;
-use Convert;
-use CheckoutPage;
-use ShoppingCart;
+
+
+
+
+
+
+
+
+
+
+
+use SilverStripe\Control\Controller;
+use SilverStripe\Forms\FieldList;
+use SilverStripe\Forms\TextField;
+use SilverStripe\Forms\CurrencyField;
+use SilverStripe\Forms\FormAction;
+use SilverStripe\Forms\RequiredFields;
+use SilverStripe\Forms\Form;
+use SilverStripe\Core\Convert;
+use Sunnysideup\Ecommerce\Pages\CheckoutPage;
+use Sunnysideup\Ecommerce\Api\ShoppingCart;
+use Sunnysideup\Ecommerce\Pages\ProductController;
+


 class GiftVoucherProductPage_Controller extends ProductController

modified:	src/Model/GiftVoucherProductPage_ProductOrderItem.php
@@ -2,8 +2,12 @@

 namespace Sunnysideup\EcommerceGiftvoucher\Model;

-use ProductOrderItem;
-use Convert;
+
+
+use Sunnysideup\Ecommerce\Model\Order;
+use SilverStripe\Core\Convert;
+use Sunnysideup\Ecommerce\Model\ProductOrderItem;
+



@@ -47,7 +51,7 @@
             'Version',
             'UnitPrice',
             'Total',
-            'Order',
+            Order::class,
             'InternalItemID',
         ),
     );

modified:	src/GiftVoucherProductPage.php
@@ -2,15 +2,27 @@

 namespace Sunnysideup\EcommerceGiftvoucher;

-use Product;
-use SiteTree;
-use Member;
-use Config;
-use Director;
-use TextField;
-use NumericField;
-use CheckboxField;
-use LiteralField;
+
+
+
+
+
+
+
+
+
+use SilverStripe\CMS\Model\SiteTree;
+use Sunnysideup\EcommerceGiftvoucher\GiftVoucherProductPage;
+use SilverStripe\Security\Member;
+use Sunnysideup\EcommerceGiftvoucher\Model\GiftVoucherProductPage_ProductOrderItem;
+use SilverStripe\Core\Config\Config;
+use SilverStripe\Control\Director;
+use SilverStripe\Forms\TextField;
+use SilverStripe\Forms\NumericField;
+use SilverStripe\Forms\CheckboxField;
+use SilverStripe\Forms\LiteralField;
+use Sunnysideup\Ecommerce\Pages\Product;
+

 /**
  * @author nicolaas [at] sunnysideup.co.nz
@@ -100,7 +112,7 @@

     public function canCreate($member = null, $context = [])
     {
-        return SiteTree::get()->filter(array('ClassName' => 'GiftVoucherProductPage'))->count() ? false : true;
+        return SiteTree::get()->filter(array('ClassName' => GiftVoucherProductPage::class))->count() ? false : true;
     }


@@ -114,13 +126,13 @@
     /**
      * @var string
      */
-    protected $defaultClassNameForOrderItem = 'GiftVoucherProductPage_ProductOrderItem';
+    protected $defaultClassNameForOrderItem = GiftVoucherProductPage_ProductOrderItem::class;

     public function getCMSFields()
     {
         $fields = parent::getCMSFields();
         $fieldLabels = $this->fieldLabels();
-        $fieldLabelsRight = Config::inst()->get('GiftVoucherProductPage', 'field_labels_right');
+        $fieldLabelsRight = Config::inst()->get(GiftVoucherProductPage::class, 'field_labels_right');
         $exampleLink = Director::absoluteURL($this->Link('setamount')).'/123.45/?description='.urlencode('test payment only');
         $exampleLinkExplanation = sprintf(_t('GiftVoucherProductPage.EXPLANATION', '<br /><br /><h5>How to preset the amount?</h5><p>The link <a href="%1$s">%1$s</a> will pre-set the amount to 123.45. You can use this link (and vary the amount as needed) to cutomers to receive payments.</p>.'), $exampleLink);
         $fields->addFieldsToTab(

Writing changes for 4 files
✔✔✔