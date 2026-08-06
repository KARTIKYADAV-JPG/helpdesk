<?php

namespace App\Enums;

enum TicketCategory: string
{
    case BILLING = 'Billing';
    case TECHNICAL_SUPPORT = 'Technical Support';
    case LOGIN = 'Login';
    case ACCOUNT = 'Account';
    case ORDERS = 'Orders';
    case SHIPPING = 'Shipping';
    case REFUND = 'Refund';
    case PAYMENT = 'Payment';
    case FEATURE_REQUEST = 'Feature Request';
    case BUG_REPORT = 'Bug Report';
    case GENERAL_QUESTION = 'General Question';

    /**
     * Get all values as a simple array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Explicit label mapping for category values in Title Case.
     */
    public static function labels(): array
    {
        return [
            self::BILLING->value => 'Billing',
            self::TECHNICAL_SUPPORT->value => 'Technical Support',
            self::LOGIN->value => 'Login',
            self::ACCOUNT->value => 'Account',
            self::ORDERS->value => 'Orders',
            self::SHIPPING->value => 'Shipping',
            self::REFUND->value => 'Refund',
            self::PAYMENT->value => 'Payment',
            self::FEATURE_REQUEST->value => 'Feature Request',
            self::BUG_REPORT->value => 'Bug Report',
            self::GENERAL_QUESTION->value => 'General Question',
        ];
    }
}
