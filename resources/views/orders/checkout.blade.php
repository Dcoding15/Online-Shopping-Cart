<!DOCTYPE html>
<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/market.css') }}">
    <title>Checkout & Payment</title>
</head>
<body>
    @include('partials.app-header')

    <fieldset class="page-navigation">
        <legend>Buyer navigation</legend>
        <nav class="page-nav" aria-label="Buyer navigation">
            <a href="{{ route('cart.index') }}">← Back to Cart</a>
            <a href="{{ route('products.index') }}">Continue Shopping</a>
        </nav>
    </fieldset>

    <hr>

    <h1>Order Checkout</h1>

    @if (session('error'))
        <p class="notice-error">{{ session('error') }}</p>
    @endif

    <h2>Order Summary</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Seller</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cartItems as $item)
                <tr>
                    <td>{{ $item->product->product_name }}</td>
                    <td>{{ $item->product->seller->user_name ?? 'N/A' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>₹{{ number_format($item->product->product_price, 2) }}</td>
                    <td>₹{{ number_format($item->quantity * $item->product->product_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Total Quantity:</th>
                <td>{{ $totalQuantity }} item(s)</td>
            </tr>
            <tr>
                <th colspan="4">Order Total:</th>
                <td><strong>₹{{ number_format($totalPrice, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <h2>Secure Online Payment</h2>
    <p>Pay securely with a credit or debit card, UPI app, or UPI QR code using Razorpay Checkout.</p>

    <form id="payment-form" action="{{ route('orders.store') }}" method="POST">
        @csrf
        <label for="shipping_address">Delivery Address:</label><br>
        <textarea id="shipping_address" name="shipping_address" rows="3" cols="50" maxlength="255" required>{{ old('shipping_address', $buyer->user_addr) }}</textarea>
        <br><br>
        <button id="pay-button" type="submit">Continue to Razorpay Payment</button>
    </form>

    <p id="payment-message" class="notice-error" role="alert" hidden></p>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        const paymentForm = document.getElementById('payment-form');
        const payButton = document.getElementById('pay-button');
        const paymentMessage = document.getElementById('payment-message');

        function showPaymentMessage(message) {
            paymentMessage.textContent = message;
            paymentMessage.hidden = false;
        }

        paymentForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            paymentMessage.hidden = true;
            payButton.disabled = true;

            try {
                const orderResponse = await fetch(paymentForm.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': paymentForm.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify({
                        shipping_address: document.getElementById('shipping_address').value,
                    }),
                });
                const orderData = await orderResponse.json();

                if (!orderResponse.ok) {
                    throw new Error(orderData.message || 'Unable to start the payment.');
                }

                const checkout = new Razorpay({
                    key: orderData.key,
                    amount: orderData.amount,
                    currency: orderData.currency,
                    name: orderData.name,
                    description: orderData.description,
                    order_id: orderData.razorpay_order_id,
                    prefill: orderData.prefill,
                    theme: { color: '#176044' },
                    handler: async (paymentResponse) => {
                        try {
                            const verifyResponse = await fetch(orderData.verify_url, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': paymentForm.querySelector('input[name="_token"]').value,
                                },
                                body: JSON.stringify(paymentResponse),
                            });
                            const verifyData = await verifyResponse.json();

                            if (verifyData.redirect_url) {
                                window.location.assign(verifyData.redirect_url);
                                return;
                            }

                            if (!verifyResponse.ok) {
                                throw new Error(verifyData.message || 'Payment verification failed.');
                            }

                            throw new Error('Payment could not be verified. Please contact support.');
                        } catch (error) {
                            showPaymentMessage(error.message);
                            payButton.disabled = false;
                        }
                    },
                    modal: {
                        ondismiss: () => {
                            payButton.disabled = false;
                        },
                    },
                });

                checkout.on('payment.failed', (failure) => {
                    showPaymentMessage(failure.error.description || 'Payment failed. Please try again.');
                    payButton.disabled = false;
                });

                checkout.open();
            } catch (error) {
                showPaymentMessage(error.message);
                payButton.disabled = false;
            }
        });
    </script>
</body>
</html>
