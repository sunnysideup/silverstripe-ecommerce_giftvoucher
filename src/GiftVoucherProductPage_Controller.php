<?php

namespace Sunnysideup\EcommerceGiftvoucher;

use SilverStripe\Control\Controller;
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

class GiftVoucherProductPage_Controller extends ProductController
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

            /**
             * ### @@@@ START REPLACEMENT @@@@ ###
             * WHY: automated upgrade
             * OLD: Session:: (case sensitive)
             * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
             * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
             * ### @@@@ STOP REPLACEMENT @@@@ ###
             */
            if ($newAmount = Controller::curr()->getRequest()->getSession()->get('GiftVoucherProductPageAmount')) {
                $amount = $newAmount;
            }
            $description = $this->DefaultDescription;

            /**
             * ### @@@@ START REPLACEMENT @@@@ ###
             * WHY: automated upgrade
             * OLD: Session:: (case sensitive)
             * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
             * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
             * ### @@@@ STOP REPLACEMENT @@@@ ###
             */
            if ($newDescription = Controller::curr()->getRequest()->getSession()->get('GiftVoucherProductPageDescription')) {
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

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: Session:: (case sensitive)
         * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
         * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        Controller::curr()->getRequest()->getSession()->clear('GiftVoucherProductPageAmount');

        /**
         * ### @@@@ START REPLACEMENT @@@@ ###
         * WHY: automated upgrade
         * OLD: Session:: (case sensitive)
         * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
         * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
         * ### @@@@ STOP REPLACEMENT @@@@ ###
         */
        Controller::curr()->getRequest()->getSession()->clear('GiftVoucherProductPageDescription');

        //create a description
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

            /**
             * ### @@@@ START REPLACEMENT @@@@ ###
             * WHY: automated upgrade
             * OLD: Session:: (case sensitive)
             * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
             * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
             * ### @@@@ STOP REPLACEMENT @@@@ ###
             */
            Controller::curr()->getRequest()->getSession()->set('GiftVoucherProductPageAmount', $amount);
        }
        if ($description = Convert::raw2sql($request->param('OtherID'))) {

            /**
             * ### @@@@ START REPLACEMENT @@@@ ###
             * WHY: automated upgrade
             * OLD: Session:: (case sensitive)
             * NEW: Controller::curr()->getRequest()->getSession()-> (COMPLEX)
             * EXP: If THIS is a controller than you can write: $this->getRequest(). You can also try to access the HTTPRequest directly.
             * ### @@@@ STOP REPLACEMENT @@@@ ###
             */
            Controller::curr()->getRequest()->getSession()->set('GiftVoucherProductPageDescription', $_GET['description']);
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
     * @return OrderItem | null
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
     * GiftVoucherProductPage_Controller so that you can do something with the OrderItem
     *
     * @param OrderItem $orderItem
     * @param array $data
     * @param Form $form
     *
     * @return OrderItem
     */
    protected function updateOrderItem($orderItem, $data, $form)
    {
        return $orderItem;
    }
}
