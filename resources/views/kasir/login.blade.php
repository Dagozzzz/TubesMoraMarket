@extends('kasir.layout')

@section('title', 'Login Kasir - Mora Market')

@section('body')
<main class="login-shell">
    <section class="login-hero">
        <div class="brand-mark">
            <div class="brand-icon">M</div>
            <span>Mora Market</span>
        </div>

        <div class="login-copy">
            <h1>POS Kasir Modern</h1>
            <p>Dashboard kasir cepat untuk memilih produk, menyusun keranjang, dan checkout transaksi minimarket dengan alur MVC sederhana.</p>
        </div>
    </section>

    <section class="login-panel">
        <form class="login-card" method="POST" action="{{ route('kasir.login.store') }}">
            @csrf
            <h2>Masuk Kasir</h2>
            <p>Masuk menggunakan akun user yang sudah terdaftar di database.</p>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <div class="field">
                <label for="username_email">Username atau email</label>
                <input id="username_email" name="username_email" value="{{ old('username_email') }}" placeholder="Contoh: kasir@mora.test atau Sinta" autofocus>
                @error('username_email')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <div class="field" style="margin-top: 16px;">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Masukkan password">
                @error('password')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn btn-primary" style="width: 100%; margin-top: 22px;" type="submit">Masuk Dashboard</button>
        </form>
    </section>
</main>
@endsection
