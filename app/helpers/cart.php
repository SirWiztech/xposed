<?php
/**
 * XPOSED — Cart (session-backed).
 */

function cart(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_add(array $item): void
{
    $cart = cart();
    $key = $item['product_id'] . ':' . (int)($item['variant_id'] ?? 0);
    if (isset($cart[$key])) {
        $cart[$key]['qty'] += max(1, (int)$item['qty']);
    } else {
        $cart[$key] = $item;
        $cart[$key]['qty'] = max(1, (int)$item['qty']);
    }
    $_SESSION['cart'] = $cart;
}

function cart_update(string $key, int $qty): void
{
    $cart = cart();
    if ($qty <= 0) {
        unset($cart[$key]);
    } elseif (isset($cart[$key])) {
        $cart[$key]['qty'] = $qty;
    }
    $_SESSION['cart'] = $cart;
}

function cart_remove(string $key): void
{
    $cart = cart();
    unset($cart[$key]);
    $_SESSION['cart'] = $cart;
}

function cart_clear(): void
{
    unset($_SESSION['cart']);
}

function cart_count(): int
{
    return array_sum(array_column(cart(), 'qty'));
}

function cart_total_cents(): int
{
    $total = 0;
    foreach (cart() as $item) {
        $total += (int)$item['unit_price'] * (int)$item['qty'];
    }
    return $total;
}
