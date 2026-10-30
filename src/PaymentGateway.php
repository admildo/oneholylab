<?php
declare(strict_types=1);

namespace Holy;

/**
 * Anything that can take a card payment. OrderService only knows this interface,
 * so a real provider (Stripe, PayFast, Yoco...) is added by writing one new class
 * and setting PAYMENT_PROVIDER. Card details must go straight to the provider and
 * are never stored in our database.
 */
interface PaymentGateway
{
    /** @return array{ok: bool, reference: ?string, message: string} */
    public function charge(float $amount, string $orderNo, array $card): array;
}
