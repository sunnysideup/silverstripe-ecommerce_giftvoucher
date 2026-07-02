<?php

namespace Sunnysideup\EcommerceGiftvoucher;

use SilverStripe\Forms\Validation\RequiredFieldsValidator;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\CurrencyField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\TextField;
use Sunnysideup\Ecommerce\Api\ShoppingCart;
use Sunnysideup\Ecommerce\Interfaces\BuyableModel;
use Sunnysideup\Ecommerce\Model\OrderItem;
use Sunnysideup\Ecommerce\Pages\CheckoutPage;
use Sunnysideup\Ecommerce\Pages\ProductController;

/**
 * Class \Sunnysideup\EcommerceGiftvoucher\GiftVoucherProductPageController
 *
 * @property GiftVoucherProductPage $dataRecord
 * @method GiftVoucherProductPage data()
 * @mixin GiftVoucherProductPage
 */
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
            $fields = FieldList::create();

            $description = $this->DefaultDescription;
            $newDescription = $this->getRequest()->getSession()->get('GiftVoucherProductPageDescription');
            if ($newDescription) {
                $description = $newDescription;
            }

            if ($this->CanSetDescription) {
                $fields->push(TextField::create('Description', $this->DescriptionFieldLabel, $description));
                $requiredFields[] = 'Description';
            }

            $amount = $this->MinimumAmount;
            $newAmount = $this->getRequest()->getSession()->get('GiftVoucherProductPageAmount');
            if ($newAmount) {
                $amount = $newAmount;
            }

            $fields->push(CurrencyField::create('Amount', $this->AmountFieldLabel, $amount));
            $requiredFields[] = 'Amount';

            $actions = FieldList::create(
                FormAction::create('doaddnewpriceform', $this->ActionFieldLabel)
            );
            $requiredFields = RequiredFieldsValidator::create($requiredFields);

            return Form::create(
                $controller = $this,
                $name = 'AddNewPriceForm',
                $fields,
                $actions,
                $requiredFields
            );
        }
    }

    public function doaddnewpriceform(array $data, Form $form)
    {
        //check amount
        $amount = $this->parseFloat($data['Amount']);
        if ($this->MinimumAmount > 0 && ($amount < $this->MinimumAmount)) {
            $form->sessionMessage(
                _t('GiftVoucherProductPage.ERRORINFORMTOOLOW', 'Please enter a higher amount.'),
                'bad'
            );
            $form->setSessionData($data);
            $this->redirectBack();

            return null;
        }

        if ($this->MaximumAmount > 0 && ($amount > $this->MaximumAmount)) {
            $form->sessionMessage(_t('GiftVoucherProductPage.ERRORINFORMTOOHIGH', 'Please enter a lower amount.'), 'bad');
            $form->setSessionData($data);
            $this->redirectBack();

            return null;
        }

        //clear settings from URL

        $this->getRequest()->getSession()->clear('GiftVoucherProductPageAmount');
        $this->getRequest()->getSession()->clear('GiftVoucherProductPageDescription');
        $form->setSessionData([]);

        //create a description
        $description = '';
        if (isset($data['Description']) && $data['Description']) {
            $description = $this->removeNonAlphaNumeric($data['Description']);
        } elseif ($this->DefaultDescription) {
            $description = $this->DefaultDescription;
        }

        //..

        //create order item and update it ... if needed
        $orderItem = $this->createOrderItem($amount, $description, $data);
        $orderItem = $this->updateOrderItem($orderItem, $data, $form);

        if (! $orderItem) {
            $form->sessionMessage(_t('GiftVoucherProductPage.ERROROTHER', 'Sorry, we could not add your entry.'), 'bad');
            $form->setSessionData($data);
            $this->redirectBack();

            return null;
        }

        $checkoutPage = CheckoutPage::get()->First();
        if ($checkoutPage) {
            return $this->redirect($checkoutPage->Link());
        }

        return [];
    }

    public function setamount($request)
    {
        $amount = floatval($request->param('ID'));
        if ($amount !== 0.0) {
            $this->getRequest()->getSession()->set('GiftVoucherProductPageAmount', $amount);
        }

        $description = urldecode(Convert::raw2sql($request->param('OtherID')));
        if ($description !== '' && $description !== '0') {
            $this->getRequest()->getSession()->set('GiftVoucherProductPageDescription', $description);
        }

        if ($amount && $description) {
            $this->doaddnewpriceform(
                [
                    'Amount' => $amount,
                    'Description' => $description,
                ],
                $this->AddNewPriceForm()
            );
        } else {
            $this->redirect($this->Link());
        }

        return [];
    }

    /**
     * clean up the amount, we may improve this in the future.
     *
     * @param mixed $floatString
     *
     * @return float
     */
    protected function parseFloat($floatString)
    {
        return (float) preg_replace('#([^0-9\.])#i', '', (string) $floatString);
    }

    /**
     * @param float  $amount
     * @param string $description
     * @param array  $data
     *
     * @return null|OrderItem
     */
    protected function createOrderItem(float $amount, string $description, ?array $data = [])
    {
        $shoppingCart = ShoppingCart::singleton();
        /** @var BuyableModel|GiftVoucherProductPage $record  */
        $record = $this->dataRecord;
        /** @var OrderItem $orderItem */
        $orderItem = $shoppingCart->addBuyable($record);
        if ($orderItem) {
            $orderItem->setCustomCalculatedTotal($amount);
            $orderItem->setCustomDescription($description);
        }

        return $orderItem;
    }

    /**
     * you can add this method to a class extending
     * GiftVoucherProductPageController so that you can do something with the OrderItem.
     *
     * @param OrderItem $orderItem
     * @param array     $data
     * @param Form      $form
     *
     * @return OrderItem
     */
    protected function updateOrderItem($orderItem, $data, $form)
    {
        return $orderItem;
    }

    protected function removeNonAlphaNumeric(string $text): string
    {
        return preg_replace('/[^a-zA-Z0-9 ]/', '', $text);
    }
}
