<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Customer;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use App\Services\MidtransSnapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use RuntimeException;

class KasirController extends Controller
{
    public function login(): View|RedirectResponse
    {
        if (Auth::check() && session()->has('kasir')) {
            return redirect()->route('kasir.dashboard');
        }

        return view('kasir.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username_email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', $validated['username_email'])
            ->orWhere('name', $validated['username_email'])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return back()
                ->withInput($request->only('username_email'))
                ->with('error', 'Username, email, atau password tidak sesuai.');
        }

        Auth::login($user);
        $request->session()->regenerate();

        session([
            'kasir' => [
                'id' => $user->id,
                'nama' => $user->name,
                'email' => $user->email,
            ],
        ]);

        return redirect()->route('kasir.dashboard')
            ->with('success', 'Selamat datang, ' . $user->name . '.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->forget(['kasir', 'cart']);
        $request->session()->regenerateToken();

        return redirect()->route('kasir.login')
            ->with('success', 'Anda berhasil logout.');
    }

    public function dashboard(Request $request): View|RedirectResponse
    {
        if (! Auth::check() || ! session()->has('kasir')) {
            return redirect()->route('kasir.login')
                ->with('error', 'Silakan login sebagai kasir terlebih dahulu.');
        }

        $query = trim((string) $request->query('q'));

        $barang = Barang::query()
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($subQuery) use ($query) {
                    $subQuery->where('kode_barang', 'like', "%{$query}%")
                        ->orWhere('nama_barang', 'like', "%{$query}%")
                        ->orWhere('kategori', 'like', "%{$query}%");
                });
            })
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('kasir.dashboard', [
            'barang' => $barang,
            'cart' => $this->cart(),
            'cartSummary' => $this->cartSummary(),
            'query' => $query,
        ]);
    }

    public function addToCart(Request $request): RedirectResponse
    {
        if (! Auth::check() || ! session()->has('kasir')) {
            return redirect()->route('kasir.login');
        }

        $validated = $request->validate([
            'barang_id' => ['required', 'integer', 'exists:barang,id'],
        ]);

        $barang = Barang::findOrFail($validated['barang_id']);
        $cart = $this->cart();
        $key = (string) $barang->id;

        if (isset($cart[$key])) {
            $cart[$key]['qty']++;
        } else {
            $cart[$key] = [
                'id' => $barang->id,
                'kode_barang' => $barang->kode_barang,
                'nama_barang' => $barang->nama_barang,
                'kategori' => $barang->kategori,
                'satuan' => $barang->satuan,
                'harga_jual' => (int) $barang->harga_jual,
                'qty' => 1,
            ];
        }

        session(['cart' => $cart]);
        session()->forget('midtrans');

        return back()->with('success', $barang->nama_barang . ' masuk ke keranjang.');
    }

    public function updateCart(Request $request, int $barang): RedirectResponse
    {
        if (! Auth::check() || ! session()->has('kasir')) {
            return redirect()->route('kasir.login');
        }

        $validated = $request->validate([
            'action' => ['required', 'in:increase,decrease'],
        ]);

        $cart = $this->cart();
        $key = (string) $barang;

        if (! isset($cart[$key])) {
            return back()->with('error', 'Barang tidak ditemukan di keranjang.');
        }

        if ($validated['action'] === 'increase') {
            $cart[$key]['qty']++;
        }

        if ($validated['action'] === 'decrease') {
            $cart[$key]['qty']--;
        }

        if ($cart[$key]['qty'] < 1) {
            unset($cart[$key]);
        }

        session(['cart' => $cart]);
        session()->forget('midtrans');

        return back();
    }

    public function removeFromCart(int $barang): RedirectResponse
    {
        if (! Auth::check() || ! session()->has('kasir')) {
            return redirect()->route('kasir.login');
        }

        $cart = $this->cart();
        unset($cart[(string) $barang]);

        session(['cart' => $cart]);
        session()->forget('midtrans');

        return back()->with('success', 'Barang dihapus dari keranjang.');
    }

    public function checkout(): View|RedirectResponse
    {
        if (! Auth::check() || ! session()->has('kasir')) {
            return redirect()->route('kasir.login');
        }

        if ($this->cartSummary()['items'] === 0) {
            return redirect()->route('kasir.dashboard')
                ->with('error', 'Keranjang masih kosong.');
        }

        return view('kasir.checkout', [
            'cart' => $this->cart(),
            'cartSummary' => $this->cartSummary(),
            'customer' => Customer::orderBy('nama_customer')->get(),
            'selectedCustomerId' => session('checkout.id_customer'),
            'midtransClientKey' => config('services.midtrans.client_key'),
            'midtransSnapUrl' => rtrim(config('services.midtrans.snap_url'), '/') . '/snap/snap.js',
            'snapToken' => session('midtrans.snap_token'),
            'snapRedirectUrl' => session('midtrans.redirect_url'),
            'orderId' => session('midtrans.order_id'),
        ]);
    }

    public function pay(MidtransSnapService $midtrans): RedirectResponse
    {
        if (! Auth::check() || ! session()->has('kasir')) {
            return redirect()->route('kasir.login');
        }

        $validated = request()->validate([
            'id_customer' => ['required', 'string', 'exists:customer,id_customer'],
        ]);

        if ($this->cartSummary()['items'] === 0) {
            return redirect()->route('kasir.dashboard')
                ->with('error', 'Keranjang masih kosong.');
        }

        $customer = Customer::findOrFail($validated['id_customer']);
        $kodePenjualan = TransaksiPenjualan::generateKodePenjualan();

        try {
            $transaction = $midtrans->createTransaction(
                $this->cart(),
                $this->cartSummary(),
                [
                    'nama' => $customer->nama_customer,
                    'email' => $customer->email,
                ],
                $kodePenjualan
            );
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $penjualan = DB::transaction(function () use ($customer, $kodePenjualan, $transaction) {
            $penjualan = TransaksiPenjualan::create([
                'kode_penjualan' => $kodePenjualan,
                'id_customer' => $customer->id_customer,
                'id_kasir' => Auth::id(),
                'tanggal_penjualan' => now(),
                'status_pembayaran' => 'pending',
                'metode_pembayaran' => 'midtrans',
                'midtrans_order_id' => $transaction['order_id'],
                'midtrans_snap_token' => $transaction['token'],
                'midtrans_redirect_url' => $transaction['redirect_url'],
                'total_harga' => $this->cartSummary()['total'],
            ]);

            foreach ($this->cart() as $item) {
                $penjualan->detailTransaksi()->create([
                    'id_barang' => $item['id'],
                    'kode_barang' => $item['kode_barang'],
                    'nama_barang' => $item['nama_barang'],
                    'kategori' => $item['kategori'] ?? null,
                    'satuan' => $item['satuan'] ?? 'pcs',
                    'jumlah' => $item['qty'],
                    'harga_satuan' => $item['harga_jual'],
                    'subtotal' => $item['harga_jual'] * $item['qty'],
                ]);
            }

            return $penjualan;
        });

        session([
            'checkout' => [
                'id_customer' => $customer->id_customer,
            ],
            'midtrans' => [
                'transaksi_penjualan_id' => $penjualan->id,
                'order_id' => $transaction['order_id'],
                'snap_token' => $transaction['token'],
                'redirect_url' => $transaction['redirect_url'],
            ],
        ]);

        return redirect()->route('kasir.checkout')
            ->with('success', 'Transaksi Midtrans siap dibayar.');
    }

    public function finishPayment(Request $request): RedirectResponse
    {
        if (! Auth::check() || ! session()->has('kasir')) {
            return redirect()->route('kasir.login');
        }

        $request->validate([
            'payment_status' => ['nullable', 'string', 'max:40'],
        ]);

        $status = $request->input('payment_status', 'pending');
        $penjualan = TransaksiPenjualan::find(session('midtrans.transaksi_penjualan_id'));

        if (in_array($status, ['success', 'pending'], true)) {
            if ($penjualan) {
                $penjualan->update([
                    'status_pembayaran' => $status === 'success' ? 'lunas' : 'pending',
                ]);
            }

            session()->forget(['cart', 'midtrans', 'checkout']);

            return redirect()->route('kasir.dashboard')
                ->with('success', 'Transaksi Midtrans berhasil diproses dengan status ' . $status . '.');
        }

        if ($penjualan && $status === 'error') {
            $penjualan->update(['status_pembayaran' => 'gagal']);
        }

        return redirect()->route('kasir.checkout')
            ->with('error', 'Pembayaran belum selesai. Silakan coba lagi.');
    }

    private function cart(): array
    {
        return session('cart', []);
    }

    private function cartSummary(): array
    {
        $cart = collect($this->cart());

        return [
            'items' => $cart->sum('qty'),
            'total' => $cart->sum(fn ($item) => $item['harga_jual'] * $item['qty']),
        ];
    }
}
