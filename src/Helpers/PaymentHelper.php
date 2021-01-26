<?php

namespace Bjerke\Ecommerce\Helpers;

use Bjerke\Ecommerce\Enums\OrderStatus;
use Bjerke\Ecommerce\Enums\PaidStatus;
use Bjerke\Ecommerce\Enums\PaymentLogType;
use Bjerke\Ecommerce\Enums\PaymentStatus;
use Bjerke\Ecommerce\Exceptions\MethodNotImplemented;
use Bjerke\Ecommerce\Exceptions\MissingBillingOrShipping;
use Bjerke\Ecommerce\Exceptions\OrderNotInAPayableState;
use Bjerke\Ecommerce\Exceptions\PaymentFailed;
use Bjerke\Ecommerce\Models\Order;
use Bjerke\Ecommerce\Models\OrderItem;
use Bjerke\Ecommerce\Models\Payment;
use Illuminate\Support\Facades\Request;
use Omnipay\Common\CreditCard;
use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Omnipay;

class PaymentHelper
{
    public static function validateOrderCanBePaid(Order $order)
    {
        if (
            (!$order->billing_address || empty($order->billing_address)) &&
            (!$order->shipping_address || empty($order->shipping_address))
        ) {
            throw new MissingBillingOrShipping();
        }

        if ($order->status !== OrderStatus::DRAFT || $order->paid_status === PaidStatus::PAID) {
            throw new OrderNotInAPayableState();
        }
    }

    public static function initializeGateway(): GatewayInterface
    {
        $gateway = Omnipay::create(config('ecommerce.payments.gateway'));
        $gateway->initialize(config('ecommerce.payments.gateway_options'));
        return $gateway;
    }

    public static function getLineItems(Order $order): array
    {
        return $order->orderItems()->get()->map(fn (OrderItem $orderItem) => [
            'name' => $orderItem->name,
            'price' => $orderItem->value,
            'quantity' => $orderItem->quantity
        ])->toArray();
    }

    public static function authorizePayment(
        GatewayInterface $gateway,
        Order $order,
        Payment $payment,
        ?string $token = null,
        ?array $additionalAuthorizeData = null,
        ?array $additionalCardData = null
    ): ResponseInterface {
        $authorizeData = [
            'transactionId' => $payment->id,
            'amount' => $payment->value,
            'returnUrl' => config('ecommerce.payments.return_url'),
            'cancelUrl' => config('ecommerce.payments.cancel_url'),
            'notifyUrl' => config('ecommerce.payments.notify_url')
        ];

        if ($token) {
            $authorizeData['token'] = $token;
        } else {
            $authorizeData['card'] = self::setupCardData($order, $additionalCardData);
        }

        return $gateway->authorize(array_merge($authorizeData, $additionalAuthorizeData))
                       ->setItems(self::getLineItems($order))
                       ->send();
    }

    public static function parseAuthorizeResponse(
        ResponseInterface $authorizeResponse,
        Payment $payment
    ): array {
        $payment->reference = $authorizeResponse->getTransactionReference();

        if (!$authorizeResponse->isSuccessful() && !$authorizeResponse->isRedirect()) {
            self::onPaymentFailed($payment, $authorizeResponse->getMessage());
        }

        $payment->status = PaymentStatus::PENDING;
        $payment->save();

        if ($authorizeResponse->isRedirect()) {
            return [
                'redirect_method' => $authorizeResponse->getRedirectMethod(),
                'redirect_url' => $authorizeResponse->getRedirectUrl(),
                'redirect_data' => $authorizeResponse->getRedirectData()
            ];
        }

        return $authorizeResponse->getData();
    }

    public static function setupCardData(Order $order, ?array $additionalCardData = null): CreditCard
    {
        $cardData = [
            'firstName' => $order->first_name,
            'lastName' => $order->last_name,
            'company' => $order->company,
            'email' => $order->email
        ];

        if ($order->billing_address && !empty($order->billing_address)) {
            $cardData['billingAddress1'] = $order->billing_address['address_1'];
            $cardData['billingAddress2'] = $order->billing_address['address_2'] ?: null;
            $cardData['billingCity'] = $order->billing_address['city'];
            $cardData['billingPostcode'] = $order->billing_address['postcode'];
            $cardData['billingState'] = $order->billing_address['state'] ?: null;
            $cardData['billingCountry'] = $order->billing_address['country'];
            $cardData['billingPhone'] = $order->billing_address['phone'];
        }

        if ($order->shipping_address && !empty($order->shipping_address)) {
            $cardData['shippingAddress1'] = $order->shipping_address['address_1'];
            $cardData['shippingAddress2'] = $order->shipping_address['address_2'] ?: null;
            $cardData['shippingCity'] = $order->shipping_address['city'];
            $cardData['shippingPostcode'] = $order->shipping_address['postcode'];
            $cardData['shippingState'] = $order->shipping_address['state'] ?: null;
            $cardData['shippingCountry'] = $order->shipping_address['country'];
            $cardData['shippingPhone'] = $order->shipping_address['phone'];
        }

        if ($additionalCardData) {
            $cardData = array_merge($cardData, $additionalCardData);
        }

        return new CreditCard($cardData);
    }

    public static function checkout(
        Order $order,
        ?string $token = null,
        ?array $additionalAuthorizeData = null,
        ?array $additionalCardData = null
    ): array {
        self::validateOrderCanBePaid($order);

        $order->status = OrderStatus::PENDING;
        $order->save();

        $payment = Payment::create([
            'currency' => $order->currency,
            'value' => $order->order_value,
            'status' => PaymentStatus::PENDING,
            'order_id' => $order->id
        ]);
        $payment->setRelation('order', $order);

        $payment->paymentLogs()->create([
            'type' => PaymentLogType::CREATED
        ]);

        $authorizeResponse = self::authorizePayment(
            self::initializeGateway(),
            $order,
            $payment,
            $token,
            $additionalAuthorizeData,
            $additionalCardData
        );

        return self::parseAuthorizeResponse($authorizeResponse, $payment);
    }

    public static function confirm(Request $request): array
    {
        throw new MethodNotImplemented();
    }

    public static function cancel(Request $request): array
    {
        throw new MethodNotImplemented();
    }

    public static function refund(Request $request, Order $order): array
    {
        throw new MethodNotImplemented();
    }

    public static function onPaymentConfirmed(Payment $payment): void
    {
        $payment->status = PaymentStatus::PAID;
        $payment->save();

        $payment->paymentLogs()->create([
            'type' => PaymentLogType::COMPLETED
        ]);

        if ($payment->order->payments()->sum('value') >= $payment->order->order_value) {
            if (config('ecommerce.orders.confirm_on_paid')) {
                $payment->order->status = OrderStatus::CONFIRMED;
            }

            $payment->order->paid_status = PaidStatus::PAID;
            $payment->order->save();
        }
    }

    public static function onPaymentCancelled(Payment $payment): void
    {
        $payment->status = PaymentStatus::CANCELLED;
        $payment->save();

        $payment->paymentLogs()->create([
            'type' => PaymentLogType::CANCELLED
        ]);

        $payment->order->status = OrderStatus::DRAFT;
        $payment->order->save();
    }

    public static function onPaymentFailed(Payment $payment, string $errorMessage): void
    {
        $payment->paymentLogs()->create([
            'type' => PaymentLogType::FAILED,
            'meta' => [
                'error' => $errorMessage
            ]
        ]);

        $payment->status = PaymentStatus::FAILED;
        $payment->save();

        $payment->order->status = OrderStatus::DRAFT;
        $payment->order->save();

        throw new PaymentFailed($errorMessage);
    }
}
