<?php
/**
 * XPOSED — Checkout
 * Creates the order record, then either redirects to Stripe hosted checkout
 * (when STRIPE_SECRET_KEY is configured) or falls back to a manual
 * email-order flow so the MVP works with zero payment config.
 */

require __DIR__ . '/app/bootstrap.php';

$cart = cart();
$error = '';

if (empty($cart)) {
    redirect('cart.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $name  = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address — we send your order confirmation and digital downloads there.';
    } elseif ($name === '') {
        $error = 'Add your name so we know who to address the receipt to.';
    } else {
        // Normalize cart items into order payload.
        $items = [];
        foreach ($cart as $item) {
            $items[] = [
                'product_id'    => (int)$item['product_id'],
                'variant_id'    => $item['variant_id'] ?? null,
                'product_name'  => $item['product_name'],
                'variant_name'  => $item['variant_name'] ?? '',
                'qty'           => (int)$item['qty'],
                'unit_price'    => (int)$item['unit_price'],
                'email'         => $email,
            ];
        }

        $customerId = Customer::findOrCreate($email, $name);
        $order = Order::create($customerId, $items, config('stripe.currency'));

        if (config('stripe.secret_key') !== '') {
            $sessionUrl = createStripeSession($order['ref'], $items, $email);
            if ($sessionUrl) {
                Order::setStripeSession($order['id'], $sessionUrl['session_id']);
                cart_clear();
                redirect(str_replace('http://', 'https://', $sessionUrl['url']));
            }
            $error = 'Stripe checkout could not be created — please try again or email ' . config('business_email') . '.';
        } else {
            // Email-order fallback (no payment keys configured).
            Order::setStatus($order['id'], 'email-order');
            Order::setPaymentMethod($order['id'], 'email');
            sendOrderEmail($order['ref'], $email, $name);
            cart_clear();
            redirect('order-confirmation.php?ref=' . urlencode($order['ref']));
        }
    }
}

$pageTitle       = 'Checkout — Xposed Store';
$metaDescription = 'Complete your Xposed order.';
$active = 'store';
include __DIR__ . '/app/views/partials/header.php';
?>

<main>
  <section class="page-hero wrap">
    <p class="eyebrow">The Store</p>
    <h1>Check<span class="accent">out</span></h1>
  </section>

  <section class="section-pad wrap" style="padding-top:20px;">
    <?php if ($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?>

    <div class="cart-layout">
      <form class="form-card" method="post" action="<?= e(url('checkout.php')) ?>">
        <?= csrf_field() ?>
        <h2>Your details</h2>
        <div class="field">
          <label for="name">Name</label>
          <input type="text" id="name" name="name" value="<?= e($_POST['name'] ?? '') ?>" required autocomplete="name">
        </div>
        <div class="field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required autocomplete="email">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Place Order</button>
        <p class="notice-sm" style="margin-top:16px;">
          <?= config('stripe.publishable_key') !== ''
              ? 'You’ll be sent to a secure Stripe checkout to pay.'
              : 'Demo mode — payment keys not configured. You’ll get an email-order confirmation instead.' ?>
        </p>
      </form>

      <aside class="cart-summary">
        <h3>Order summary</h3>
        <?php foreach ($cart as $item): ?>
        <div class="sum-row">
          <span><?= e($item['product_name']) ?><?= !empty($item['variant_name']) ? ' — ' . e($item['variant_name']) : '' ?> × <?= (int)$item['qty'] ?></span>
          <span class="tab-nums"><?= e(money((int)$item['unit_price'] * (int)$item['qty'])) ?></span>
        </div>
        <?php endforeach; ?>
        <div class="sum-row total"><span>Total</span><span class="tab-nums"><?= e(money(cart_total_cents())) ?></span></div>
      </aside>
    </div>
  </section>
</main>

<?php include __DIR__ . '/app/views/partials/footer.php'; ?>

<?php
/**
 * Create a Stripe Checkout Session via the REST API (no SDK needed).
 * Returns ['url' => ..., 'session_id' => ...] or null on failure.
 */
function createStripeSession(string $orderRef, array $items, string $email): ?array
{
    $secret = config('stripe.secret_key');
    $base   = rtrim(config('site_url') ?: '', '/');
    if ($base === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim(config('app.base_url'), '/');
    }

    $params = [
        'mode'              => 'payment',
        'client_reference_id' => $orderRef,
        'customer_email'    => $email,
        'success_url'       => $base . '/order-confirmation.php?ref=' . urlencode($orderRef) . '&session_id={CHECKOUT_SESSION_ID}',
        'cancel_url'        => $base . '/cart.php',
    ];

    foreach ($items as $i) {
        $params['line_items'][] = [
            'quantity'   => (int)$i['qty'],
            'price_data' => [
                'currency'     => config('stripe.currency'),
                'unit_amount'  => (int)$i['unit_price'],
                'product_data' => ['name' => $i['product_name'] . ($i['variant_name'] ? ' — ' . $i['variant_name'] : '')],
            ],
        ];
    }

    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($params),
        CURLOPT_USERPWD        => $secret . ':',
        CURLOPT_TIMEOUT        => 20,
    ]);
    $body = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode((string)$body, true);
    if ($code !== 200 || empty($json['url']) || empty($json['id'])) {
        return null;
    }
    return ['url' => $json['url'], 'session_id' => $json['id']];
}

function sendOrderEmail(string $orderRef, string $email, string $name): void
{
    $subject = 'Xposed Store — Order ' . $orderRef . ' received';
    $lines = [
        "Hi " . ($name !== '' ? $name : 'there') . ",",
        "",
        "We've received your order " . $orderRef . " from the Xposed store.",
        "It's on our side now — you'll hear from us shortly at " . config('business_email') . ".",
        "",
        "If this was a digital product, it's on its way to this inbox.",
        "",
        "— Xposed HQ",
    ];
    $headers = [
        'From: Xposed Store <' . config('business_email') . '>',
        'Reply-To: ' . config('business_email'),
        'Content-Type: text/plain; charset=UTF-8',
        'MIME-Version: 1.0',
    ];
    @mail($email, $subject, implode("\r\n", $lines), implode("\r\n", $headers));
}
