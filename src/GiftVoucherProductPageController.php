<?php

namespace Sunnysideup\EcommerceGiftvoucher;

use SilverStripe\Core\Convert;
use SilverStripe\Forms\CurrencyField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextField;
use Sunnysideup\Ecommerce\Api\ShoppingCart;
use Sunnysideup\Ecommerce\Pages\CheckoutPage;
use Sunnysideup\Ecommerce\Pages\ProductController;

class GiftVoucherProductPageController extends ProductController
{
    private static $allowed_actions = [
        'AddNewPriceForm',
        'doaddnewpriceform',
        'setamount',
    ];

    public function AddNewPriceForm()
    {
        if ($this->canPurchase()) {
            $requiredFields = [];
            $amount = $this->MinimumAmount;
            if ($newAmount = $this->getRequest()->getSession()->get('GiftVoucherProductPageAmount')) {
                $amount = $newAmount;
            }
            $description = $this->DefaultDescription;
            if ($newDescription = $this->getRequest()->getSession()->get('GiftVoucherProductPageDescription')) {
                $description = $newDescription;
            }
            $fields = FieldList::create();
            if ($this->CanSetDescription) {
                $fields->push(TextField::create('Description', $this->DescriptionFieldLabel, $description));
                $requiredFields[] = 'Description';
            }
            $fields->push(CurrencyField::create('Amount', $this->AmountFieldLabel, $amount));
            $requiredFields[] = 'Amount';

            $actions = FieldList::create(
                FormAction::create('doaddnewpriceform', $this->ActionFieldLabel)
            );
            $requiredFields = RequiredFields::create($requiredFields);

            return Form::create(
                $controller = $this,
                $name = 'AddNewPriceForm',
                $fields,
                $actions,
                $requiredFields
            );
        }
    }

    public function doaddnewpriceform($data, $form)
    {
        //check amount
        $amount = $this->parseFloat($data['Amount']);
        if ($this->MinimumAmount > 0 && ($amount < $this->MinimumAmount)) {
            $form->sessionMessage(_t('GiftVoucherProductPage.ERRORINFORMTOOLOW', 'Please enter a higher amount.'), 'bad');
            $this->redirectBack();

            return;
        } elseif ($this->MaximumAmount > 0 && ($amount > $this->MaximumAmount)) {
            $form->sessionMessage(_t('GiftVoucherProductPage.ERRORINFORMTOOHIGH', 'Please enter a lower amount.'), 'bad');
            $this->redirectBack();

            return;
        }

        //clear settings from URL

        $this->getRequest()->getSession()->clear('GiftVoucherProductPageAmount');
        $this->getRequest()->getSession()->clear('GiftVoucherProductPageDescription');

        //create a description
        $description = '';
        if (isset($data['Description']) && $data['Description']) {
            $description = Convert::raw2sql($data['Description']);
        } elseif ($this->DefaultDescription) {
            $description = $this->DefaultDescription;
        }
        //..

        //create order item and update it ... if needed
        $orderItem = $this->createOrderItem($amount, $description, $data);
        $orderItem = $this->updateOrderItem($orderItem, $data, $form);

        if (! $orderItem) {
            $form->sessionMessage(_t('GiftVoucherProductPage.ERROROTHER', 'Sorry, we could not add your entry.'), 'bad');
            $this->redirectBack();

            return;
        }
        $checkoutPage = CheckoutPage::get()->First();
        if ($checkoutPage) {
            return $this->redirect($checkoutPage->Link());
        }
        return [];
    }

    public function setamount($request)
    {
        if ($amount = floatval($request->param('ID'))) {
            $this->getRequest()->getSession()->set('GiftVoucherProductPageAmount', $amount);
        }
        if ($description = Convert::raw2sql($request->param('OtherID'))) {
            $this->getRequest()->getSession()->set('GiftVoucherProductPageDescription', $description);
        }
        $this->redirect($this->Link());

        return [];
    }

    /**
     * clean up the amount, we may improve this in the future.
     *
     * @return float
     */
    protected function parseFloat($floatString)
    {
        return preg_replace('/([^0-9\\.])/i', '', $floatString);
    }

    /**
     * @param Variation (optional) $amount
     * @return \Sunnysideup\Ecommerce\Model\OrderItem|null
     */
    protected function createOrderItem($amount, $description, $data)
    {
        $shoppingCart = ShoppingCart::singleton();
        $orderItem = $shoppingCart->addBuyable($this->dataRecord);
        $orderItem->setCustomCalculatedTotal($amount);
        $orderItem->setCustomDescription($description);
        return $orderItem;
    }

    /**
     * you can add this method to a class extending
     * GiftVoucherProductPageController so that you can do something with the OrderItem
     *
     * @param \Sunnysideup\Ecommerce\Model\OrderItem $orderItem
     * @param array $data
     * @param Form $form
     *
     * @return \Sunnysideup\Ecommerce\Model\OrderItem
     */
    protected function updateOrderItem($orderItem, $data, $form)
    {
        return $orderItem;
    }
}
