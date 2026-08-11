<?php
/**
 * XPOSED — Order model.
 */

class Order
{
    public static function create(int $customerId, array $items, string $currency = 'CAD'): array
    {
        $db = db();
        $ref = strtoupper(bin2hex(random_bytes(4))); // 8-char ref, e.g. 3F2A9C1B

        $st = $db->prepare(
            'INSERT INTO orders (order_ref, customer_id, email, total_cents, currency, status, payment_method)
             VALUES (?, ?, ?, ?, ?, \'pending\', \'pending\')'
        );
        $total = array_reduce($items, fn($sum, $i) => $sum + ((int)$i['unit_price'] * (int)$i['qty']), 0);
        $st->execute([$ref, $customerId, $items[0]['email'] ?? '', $total, $currency]);

        $orderId = (int)$db->lastInsertId();

        $stItem = $db->prepare(
            'INSERT INTO order_items (order_id, product_id, variant_id, product_name, variant_name, qty, unit_price_cents)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($items as $i) {
            $stItem->execute([
                $orderId,
                $i['product_id'],
                $i['variant_id'] ?? null,
                $i['product_name'],
                $i['variant_name'] ?? '',
                (int)$i['qty'],
                (int)$i['unit_price'],
            ]);
        }

        return ['id' => $orderId, 'ref' => $ref, 'total_cents' => $total];
    }

    public static function findByRef(string $ref): ?array
    {
        $st = db()->prepare('SELECT * FROM orders WHERE order_ref = ?');
        $st->execute([$ref]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function items(int $orderId): array
    {
        $st = db()->prepare('SELECT * FROM order_items WHERE order_id = ?');
        $st->execute([$orderId]);
        return $st->fetchAll();
    }

    public static function setStripeSession(int $orderId, string $sessionId): void
    {
        db()->prepare('UPDATE orders SET payment_method = \'stripe\', stripe_session_id = ? WHERE id = ?')
            ->execute([$sessionId, $orderId]);
    }

    public static function setStatus(int $orderId, string $status): void
    {
        db()->prepare('UPDATE orders SET status = ? WHERE id = ?')->execute([$status, $orderId]);
    }

    public static function setPaymentMethod(int $orderId, string $method): void
    {
        db()->prepare('UPDATE orders SET payment_method = ? WHERE id = ?')->execute([$method, $orderId]);
    }

    public static function recent(int $limit = 50): array
    {
        $st = db()->prepare('SELECT * FROM orders ORDER BY id DESC LIMIT ?');
        $st->execute([$limit]);
        return $st->fetchAll();
    }

    public static function count(): int
    {
        return (int)db()->query('SELECT COUNT(*) FROM orders')->fetchColumn();
    }
}
