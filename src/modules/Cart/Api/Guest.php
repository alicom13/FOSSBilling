<?php

/**
 * FOSSBilling.
 *
 * @copyright FOSSBilling (https://www.fossbilling.org)
 * @license   Apache-2.0
 *
 * Copyright FOSSBilling 2022
 * This software may contain code previously used in the BoxBilling project.
 * Copyright BoxBilling, Inc 2011-2021
 *
 * This source file is subject to the Apache-2.0 License that is bundled
 * with this source code in the file LICENSE
 */

namespace Box\Mod\Cart\Api;

/**
 * Shopping cart management.
 */
class Guest extends \Api_Abstract
{
    /**
     * Get the contents of the current shopping cart.
     * 
     * @return array Contents of the shopping cart
     */
    public function get()
    {
        $cart = $this->getService()->getSessionCart();

        return $this->getService()->toApiArray($cart);
    }

    /**
     * Completely remove shopping cart contents.
     *
     * @return bool
     */
    public function reset()
    {
        $cart = $this->getService()->getSessionCart();

        return $this->getService()->resetCart($cart);
    }

    /**
     * Set shopping cart currency.
     *
     * @param string $data['currency'] New currency code to applied to shopping cart
     *
     * @return bool
     */
    public function set_currency($data)
    {
        $required = [
            'currency' => 'Currency code not passed',
        ];
        $this->di['validator']->checkRequiredParamsForArray($required, $data);

        $currencyService = $this->di['mod_service']('currency');
        $currency = $currencyService->getByCode($data['currency']);
        if (!$currency instanceof \Model_Currency) {
            throw new \Box_Exception('Currency not found');
        }
        $cart = $this->getService()->getSessionCart();

        return $this->getService()->changeCartCurrency($cart, $currency);
    }

    /**
     * Retrieve information about currently selected shopping cart currency.
     *
     * @return array Currency details
     */
    public function get_currency()
    {
        $cart = $this->getService()->getSessionCart();

        $currencyService = $this->di['mod_service']('currency');
        $currency = $this->di['db']->load('Currency', $cart->currency_id);
        if (!$currency instanceof \Model_Currency) {
            $currency = $currencyService->getDefault();
        }

        return $currencyService->toApiArray($currency);
    }

    /**
     * Apply Promo code to shopping cart.
     *
     * @param string $promocode Promo code to apply to shopping cart
     *
     * @return bool
     */
    public function apply_promo($data)
    {
        $required = [
            'promocode' => 'Promo code not passed',
        ];
        $this->di['validator']->checkRequiredParamsForArray($required, $data);

        $promo = $this->getService()->findActivePromoByCode($data['promocode']);
        if (!$promo instanceof \Model_Promo) {
            throw new \Box_Exception('Promo code is expired or does not exist');
        }

        if (!$this->getService()->isPromoAvailableForClientGroup($promo)) {
            throw new \Box_Exception('Promo can not be applied to your account');
        }

        if (!$this->getService()->promoCanBeApplied($promo)) {
            throw new \Box_Exception('Promo code is expired or does not exist');
        }

        $cart = $this->getService()->getSessionCart();

        return $this->getService()->applyPromo($cart, $promo);
    }

    /**
     * Removes promo from shopping cart and resets discounted prices if any.
     *
     * @return bool
     */
    public function remove_promo()
    {
        $cart = $this->getService()->getSessionCart();

        return $this->getService()->removePromo($cart);
    }

    /**
     * Removes product from shopping cart.
     *
     * @param int $data['id'] Shopping cart item id
     *
     * @return bool
     */
    public function remove_item($data)
    {
        $required = [
            'id' => 'Cart item id not passed',
        ];
        $this->di['validator']->checkRequiredParamsForArray($required, $data);

        $cart = $this->getService()->getSessionCart();

        return $this->getService()->removeProduct($cart, $data['id'], true);
    }

    /**
     * Add a product to the shopping cart.
     * 
     * @param array $data Product data
     * 
     * @param int $data['id'] ID of the product to add
     * @param bool $data['multiple'] [optional] Allow multiple items in the cart. Default is `false`
     * 
     * @return bool
     */
    public function add_item($data)
    {
        $required = [
            'id' => 'Product id not passed',
        ];
        $this->di['validator']->checkRequiredParamsForArray($required, $data);

        $cart = $this->getService()->getSessionCart();

        $product = $this->di['db']->getExistingModelById('Product', $data['id'], 'Product not found');

        // reset cart by default
        if (!isset($data['multiple']) || !$data['multiple']) {
            $this->reset();
        }

        return $this->getService()->addItem($cart, $product, $data);
    }
}
