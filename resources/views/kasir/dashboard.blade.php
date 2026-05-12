@extends('kasir.layout')

@section('title', 'Dashboard Kasir - Mora Market')

@section('body')
<div class="page">
    <header class="topbar">
        <div class="container topbar-inner">
            <a class="brand-mark" href="{{ route('kasir.dashboard') }}">
                <div class="brand-icon">M</div>
                <span>Mora Market</span>
            </a>

            <form class="search-form" action="{{ route('kasir.dashboard') }}" method="GET">
                <span>⌕</span>
                <input class="search-input" name="q" value="{{ $query }}" placeholder="Cari kode, nama, atau kategori barang">
            </form>

            <div class="cashier-chip">
                <div class="avatar">{{ strtoupper(substr(session('kasir.nama', 'K'), 0, 1)) }}</div>
                <div>
                    <small>Kasir aktif</small><br>
                    <strong>{{ session('kasir.nama', 'Kasir') }}</strong>
                </div>
                <form method="POST" action="{{ route('kasir.logout') }}">
                    @csrf
                    <button class="btn btn-ghost" type="submit">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <main class="container main-grid">
        <section>
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <section class="promo" aria-label="Promo Mora Market">
                <div class="promo-track">
                    <article class="promo-slide" style="background-image: url('https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=1500&q=80')">
                        <span class="promo-badge">Promo Hari Ini</span>
                        <h2>Belanja cepat, antrean singkat.</h2>
                        <p>Tambahkan barang ke keranjang, atur jumlah, lalu checkout sederhana dari satu layar kasir.</p>
                    </article>
                    <article class="promo-slide" style="background-image: url('https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1500&q=80')">
                        <span class="promo-badge">Mora Fresh</span>
                        <h2>Produk harian siap diproses.</h2>
                        <p>Data produk diambil langsung dari tabel barang sebagai master POS minimarket.</p>
                    </article>
                    <article class="promo-slide" style="background-image: url('https://images.unsplash.com/photo-1601599963565-b7ba29c8e3ff?auto=format&fit=crop&w=1500&q=80')">
                        <span class="promo-badge">Touchscreen Friendly</span>
                        <h2>Kartu besar, tombol jelas.</h2>
                        <p>Layout responsive dengan panel keranjang yang mudah dipantau selama transaksi berjalan.</p>
                    </article>
                </div>
            </section>

            <div class="section-head">
                <div>
                    <h1>Daftar Barang</h1>
                    <p>{{ $barang->total() }} produk tersedia{{ $query ? ' untuk pencarian "' . $query . '"' : '' }}.</p>
                </div>
                @if ($query)
                    <a class="btn btn-ghost" href="{{ route('kasir.dashboard') }}">Reset pencarian</a>
                @endif
            </div>

            @if ($barang->isEmpty())
                <div class="empty-cart">Belum ada barang yang cocok dengan pencarian.</div>
            @else
                <div class="product-grid">
                    @foreach ($barang as $item)
                        <article class="product-card">
                            <div class="product-code">{{ $item->kode_barang }}</div>
                            <h3>{{ $item->nama_barang }}</h3>
                            <span class="category-pill">{{ $item->kategori }}</span>

                            <div class="price-row">
                                <div class="price">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</div>
                                <form method="POST" action="{{ route('kasir.cart.store') }}">
                                    @csrf
                                    <input type="hidden" name="barang_id" value="{{ $item->id }}">
                                    <button class="btn btn-primary" type="submit">Tambah</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="pagination">
                    {{ $barang->links() }}
                </div>
            @endif
        </section>

        <aside class="cart-panel">
            <div class="cart-head">
                <h2>Keranjang</h2>
                <span class="cart-count">{{ $cartSummary['items'] }} item</span>
            </div>

            @if (empty($cart))
                <div class="empty-cart">Keranjang masih kosong. Tambahkan barang dari daftar produk.</div>
            @else
                <div class="cart-list">
                    @foreach ($cart as $item)
                        @php
                            $subtotal = $item['harga_jual'] * $item['qty'];
                        @endphp
                        <div class="cart-item">
                            <div class="cart-item-top">
                                <div>
                                    <h3>{{ $item['nama_barang'] }}</h3>
                                    <small>{{ $item['kode_barang'] }} - Rp {{ number_format($item['harga_jual'], 0, ',', '.') }}</small>
                                </div>
                                <form method="POST" action="{{ route('kasir.cart.destroy', $item['id']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="remove-btn" type="submit">Hapus</button>
                                </form>
                            </div>

                            <div class="cart-controls">
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
                                <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong>Rp {{ number_format($cartSummary['total'], 0, ',', '.') }}</strong>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <strong>Rp {{ number_format($cartSummary['total'], 0, ',', '.') }}</strong>
                    </div>
                    <a class="btn btn-orange" style="width: 100%; text-align: center;" href="{{ route('kasir.checkout') }}">Checkout</a>
                </div>
            @endif
        </aside>
    </main>
</div>
@endsection
