<?php
declare(strict_types=1);

namespace Holy;

/** Sends order emails. In development it only writes them to storage/mail.log. */
final class Mailer
{
    public static function send(string $to, string $subject, string $body): void
    {
        if (config('mail_transport') === 'mail') {
            @mail($to, $subject, $body, 'From: ' . config('mail_from') . "\r\nContent-Type: text/plain; charset=utf-8");
            return;
        }
        @file_put_contents(
            ROOT . '/storage/mail.log',
            '[' . date('c') . "] To: $to\nSubject: $subject\n$body\n\n",
            FILE_APPEND
        );
    }

    public static function orderConfirmation(array $user, array $order): void
    {
        self::send($user['email'], "Order {$order['order_no']} confirmed",
            "Hi {$user['first_name']},\n\nThank you for your order {$order['order_no']}. We received your payment of "
            . money($order['total']) . " and will email you when it ships.\n\noneholylab");
    }

    public static function statusChanged(array $user, array $order): void
    {
        $extra = $order['tracking_no'] ? "\nTracking number: {$order['tracking_no']}" : '';
        self::send($user['email'], "Order {$order['order_no']} is now {$order['status']}",
            "Hi {$user['first_name']},\n\nYour order {$order['order_no']} is now: {$order['status']}.$extra\n\noneholylab");
    }
}
