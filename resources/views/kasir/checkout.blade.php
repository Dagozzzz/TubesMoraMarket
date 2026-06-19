@extends('kasir.layout')

@section('title', 'Checkout - Mora Market')

@section('body')
<div class="page checkout-page">
    <header class="topbar">
        <div class="container topbar-inner checkout-topbar">
            <a class="brand-mark" href="{{ route('kasir.dashboard') }}">
                <div class="brand-icon">M</div>
                <span>Mora Market</span>
            </a>

            <div class="checkout-title">
                <h1>Checkout</h1>
                <p>Selesaikan transaksi dengan Midtrans Snap sandbox.</p>
            </div>

            <div class="cashier-chip">
                <div class="avatar">{{ strtoupper(substr(session('kasir.nama', 'K'), 0, 1)) }}</div>
                <div>
                    <small>Kasir aktif</small><br>
                    <strong>{{ session('kasir.nama', 'Kasir') }}</strong>
                </div>
            </div>
        </div>
    </header>

    <main class="container checkout-grid">
        <section>
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <div class="checkout-card">
                <div class="section-head compact-head">
                    <div>
                        <h1>Item Transaksi</h1>
                        <p>{{ $cartSummary['items'] }} barang dalam keranjang.</p>
                    </div>
                    <a class="btn btn-ghost" href="{{ route('kasir.dashboard') }}">Tambah Barang</a>
                </div>

                <div class="checkout-items">
                    @foreach ($cart as $item)
                        @php
                            $subtotal = $item['harga_jual'] * $item['qty'];
                        @endphp
                        <article class="checkout-item">
                            <div>
                                <div class="product-code">{{ $item['kode_barang'] }}</div>
                                <h3>{{ $item['nama_barang'] }}</h3>
                                <p>Rp {{ number_format($item['harga_jual'], 0, ',', '.') }} per item</p>
                            </div>

                            <div class="checkout-actions">
                                <div class="qty-controls">
                                    <form method="POST" action="{{ route('kasir.cart.update', $item['id']) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="action" value="decrease">
                                        <button class="btn-icon" type="submit">-</button>
                                    </form>
                                    <span class="qty">{{ $item['qty'] }}</span>
                                    <form method="POST" action="{{ route('kasir.cart.update', $item['id']) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="action" value="increase">
                                        <button class="btn-icon" type="submit">+</button>
                                    </form>
                                </div>

                                <div class="checkout-subtotal">
                                    <span>Subtotal</span>
                                    <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                                </div>

                                <form method="POST" action="{{ route('kasir.cart.destroy', $item['id']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="remove-btn" type="submit">Hapus</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <aside class="payment-panel">
            <div class="cart-head">
                <h2>Ringkasan Bayar</h2>
                <span class="cart-count">Sandbox</span>
            </div>

            <div class="customer-box">
                <label for="id_customer">Customer</label>
                <select id="id_customer" name="id_customer" form="midtrans-payment-form" required>
                    <option value="">Pilih customer</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id_customer }}" @selected(old('id_customer', $selectedCustomerId) === $customer->id_customer)>
                            {{ $customer->id_customer }} - {{ $customer->nama_customer }}
                        </option>
                    @endforeach
                </select>
                @error('id_customer')
                    <div class="error-text">{{ $message }}</div>
                @enderror
                @if ($customers->isEmpty())
                    <div class="error-text">Belum ada master data customer. Tambahkan customer dulu dari admin.</div>
                @endif
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Total item</span>
                    <strong>{{ $cartSummary['items'] }}</strong>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <strong>Rp {{ number_format($cartSummary['total'], 0, ',', '.') }}</strong>
                </div>
            </div>

            @if ($orderId)
                <div class="order-box">
                    <span>Order ID</span>
                    <strong>{{ $orderId }}</strong>
                </div>
            @endif

            @if ($snapToken)
                <button class="btn btn-orange" id="pay-button" type="button" style="width: 100%; margin-top: 18px;">Bayar Sekarang</button>
                <form id="midtrans-payment-form" method="POST" action="{{ route('kasir.checkout.pay') }}">
                    @csrf
                    <button class="btn btn-ghost" type="submit" style="width: 100%; margin-top: 10px;">Buat Ulang Pembayaran</button>
                </form>
                @if ($snapRedirectUrl)
                    <a class="btn btn-ghost" style="width: 100%; text-align: center; margin-top: 10px;" href="{{ $snapRedirectUrl }}" target="_blank" rel="noopener">Buka Halaman Midtrans</a>
                @endif
            @else
                <form id="midtrans-payment-form" method="POST" action="{{ route('kasir.checkout.pay') }}">
                    @csrf
                    <button class="btn btn-orange" type="submit" style="width: 100%; margin-top: 18px;">Buat Pembayaran Midtrans</button>
                </form>
            @endif

            <p class="payment-note">
                Gunakan metode pembayaran sandbox di popup Midtrans. Untuk VA, QRIS, atau convenience store, lanjutkan dari
                <a href="https://simulator.sandbox.midtrans.com/" target="_blank" rel="noopener">Midtrans Payment Simulator</a>.
            </p>
        </aside>
    </main>
</div>

@if ($snapToken && $midtransClientKey)
    <form id="finish-payment-form" method="POST" action="{{ route('kasir.checkout.finish') }}">
        @csrf
        <input type="hidden" name="payment_status" id="payment-status" value="pending">
    </form>

    <script src="{{ $midtransSnapUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
    <script>
        const payButton = document.getElementById('pay-button');
        const finishForm = document.getElementById('finish-payment-form');
        const paymentStatus = document.getElementById('payment-status');

        payButton?.addEventListener('click', function () {
            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function () {
                    paymentStatus.value = 'success';
                    finishForm.submit();
                },
                onPending: function () {
                    paymentStatus.value = 'pending';
                    finishForm.submit();
                },
                onError: function () {
                    paymentStatus.value = 'error';
                    finishForm.submit();
                },
                onClose: function () {
                    paymentStatus.value = 'closed';
                }
            });
        });
    </script>
@endif
@endsection
