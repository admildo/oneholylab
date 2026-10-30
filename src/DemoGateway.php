<?php
declare(strict_types=1);

namespace Holy;

/** Test gateway: approves any valid-looking card, declines 4000 0000 0000 0002. Charges nothing. */
final class DemoGateway implements PaymentGateway
{
    public function charge(float $amount, string $orderNo, array $card): array
    {
        $number = preg_replace('/\D/', '', $card['number'] ?? '');
        if (!preg_match('/^\d{13,19}$/', $number) || !self::luhn($number)) {
            return ['ok' => false, 'reference' => null, 'message' => 'That card number is not valid.'];
        }
        if (!preg_match('#^(0[1-9]|1[0-2])\s*/\s*(\d{2})$#', $card['expiry'] ?? '', $m)
            || mktime(0, 0, 0, (int) $m[1] + 1, 1, 2000 + (int) $m[2]) < time()) {
            return ['ok' => false, 'reference' => null, 'message' => 'The card has expired or the date is not valid (MM / YY).'];
        }
        if (!preg_match('/^\d{3,4}$/', $card['cvc'] ?? '')) {
            return ['ok' => false, 'reference' => null, 'message' => 'Enter the 3 digit security code.'];
        }
        if ($number === '4000000000000002') {
            return ['ok' => false, 'reference' => null, 'message' => 'The card was declined.'];
        }
        return ['ok' => true, 'reference' => 'demo_' . bin2hex(random_bytes(6)), 'message' => 'Approved'];
    }

    private static function luhn(string $n): bool
    {
        $sum = 0;
        foreach (array_reverse(str_split($n)) as $i => $d) {
            $d = (int) $d;
            if ($i % 2 === 1) {
                $d *= 2;
                $d -= $d > 9 ? 9 : 0;
            }
            $sum += $d;
        }
        return $sum % 10 === 0;
    }
}
