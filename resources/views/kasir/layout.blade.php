<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mora Market POS')</title>
    <style>
        :root {
            --green: #0A7C6E;
            --yellow: #F59E0B;
            --orange: #FF6B35;
            --white: #FAFAFA;
            --ink: #17312d;
            --muted: #6b7d78;
            --line: #e5eeeb;
            --soft: #f1f7f5;
            --shadow: 0 18px 45px rgba(10, 124, 110, .12);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(10, 124, 110, .14), transparent 32rem),
                linear-gradient(135deg, #f7fbfa 0%, var(--white) 50%, #fff8ef 100%);
            min-height: 100vh;
        }

        a { color: inherit; text-decoration: none; }
        strong { font-weight: 400; }
        button, input { font: inherit; }
        .page { min-height: 100vh; }
        .container { width: min(1440px, calc(100% - 32px)); margin: 0 auto; }
        .btn {
            border: 0;
            border-radius: 16px;
            padding: 12px 18px;
            cursor: pointer;
            font-weight: 400;
            min-height: 46px;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary { background: var(--green); color: #fff; box-shadow: 0 12px 28px rgba(10, 124, 110, .24); }
        .btn-orange { background: var(--orange); color: #fff; box-shadow: 0 12px 28px rgba(255, 107, 53, .2); }
        .btn-ghost { background: #fff; color: var(--green); border: 1px solid var(--line); }
        .btn-icon {
            width: 38px;
            height: 38px;
            border-radius: 13px;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--green);
            cursor: pointer;
            font-weight: 400;
        }
        .alert {
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 16px;
            font-weight: 400;
            box-shadow: 0 12px 28px rgba(23, 49, 45, .08);
        }
        .alert-success { background: #e8f8f4; color: var(--green); }
        .alert-error { background: #fff1ed; color: #b63816; }

        .login-shell { min-height: 100vh; display: grid; grid-template-columns: 1.15fr .85fr; }
        .login-hero {
            padding: 56px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background:
                linear-gradient(135deg, rgba(10, 124, 110, .92), rgba(10, 124, 110, .76)),
                url("https://images.unsplash.com/photo-1604719312566-8912e9227c6a?auto=format&fit=crop&w=1600&q=80") center/cover;
            color: #fff;
        }
        .brand-mark { display: flex; align-items: center; gap: 12px; font-weight: 900; font-size: 24px; }
        .brand-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: #fff;
            color: var(--green);
            box-shadow: 0 16px 36px rgba(0, 0, 0, .16);
        }
        .login-copy h1 { font-size: clamp(42px, 7vw, 88px); line-height: .92; margin: 0 0 22px; letter-spacing: 0; }
        .login-copy p { max-width: 620px; font-size: 18px; line-height: 1.7; opacity: .92; }
        .login-panel { display: grid; place-items: center; padding: 32px; }
        .login-card {
            width: min(100%, 430px);
            background: rgba(255, 255, 255, .86);
            border: 1px solid rgba(255, 255, 255, .75);
            border-radius: 28px;
            padding: 32px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
        }
        .login-card h2 { margin: 0 0 8px; font-size: 30px; }
        .login-card p { color: var(--muted); margin: 0 0 28px; }
        .field label { display: block; font-weight: 400; margin-bottom: 10px; }
        .field input, .search-input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 16px;
            min-height: 50px;
            padding: 0 16px;
            background: #fff;
            color: var(--ink);
            outline: none;
        }
        .field input:focus, .search-input:focus { border-color: var(--green); box-shadow: 0 0 0 4px rgba(10, 124, 110, .1); }
        .error-text { color: #b63816; font-size: 13px; font-weight: 400; margin-top: 8px; }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(250, 250, 250, .86);
            border-bottom: 1px solid rgba(229, 238, 235, .9);
            backdrop-filter: blur(18px);
        }
        .topbar-inner {
            min-height: 82px;
            display: grid;
            grid-template-columns: auto minmax(240px, 1fr) auto;
            gap: 18px;
            align-items: center;
        }
        .search-form { position: relative; }
        .search-form span { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--muted); }
        .search-form .search-input { padding-left: 44px; }
        .cashier-chip { display: flex; align-items: center; gap: 10px; color: var(--muted); font-weight: 400; }
        .cashier-chip strong { color: var(--ink); }
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: #e8f8f4;
            color: var(--green);
            font-weight: 400;
        }
        .main-grid { display: grid; grid-template-columns: minmax(0, 1fr) 390px; gap: 24px; padding: 24px 0; align-items: start; }
        .promo {
            overflow: hidden;
            border-radius: 28px;
            min-height: 210px;
            box-shadow: var(--shadow);
            position: relative;
            background: var(--green);
        }
        .promo-track { display: flex; width: 300%; animation: slidePromo 15s infinite ease-in-out; }
        .promo-slide {
            width: 100%;
            min-height: 210px;
            padding: 32px;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .promo-slide::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(10, 124, 110, .94), rgba(10, 124, 110, .45));
        }
        .promo-slide > * { position: relative; max-width: 560px; }
        .promo-slide h2 { margin: 0 0 10px; font-size: clamp(28px, 4vw, 46px); letter-spacing: 0; }
        .promo-slide p { margin: 0; line-height: 1.6; opacity: .94; font-weight: 400; }
        .promo-badge {
            width: max-content;
            padding: 7px 12px;
            border-radius: 999px;
            background: var(--yellow);
            color: #fff;
            font-size: 13px;
            font-weight: 400;
            margin-bottom: 14px;
        }
        @keyframes slidePromo {
            0%, 27% { transform: translateX(0); }
            33%, 60% { transform: translateX(-33.333%); }
            66%, 93% { transform: translateX(-66.666%); }
            100% { transform: translateX(0); }
        }

        .section-head { display: flex; justify-content: space-between; align-items: end; gap: 16px; margin: 26px 0 16px; }
        .section-head h1 { margin: 0; font-size: 28px; }
        .section-head p { margin: 6px 0 0; color: var(--muted); font-weight: 400; }
        .product-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .product-card {
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 18px;
            background: rgba(255, 255, 255, .92);
            box-shadow: 0 12px 30px rgba(23, 49, 45, .06);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .product-card:hover { transform: translateY(-3px); border-color: rgba(10, 124, 110, .34); box-shadow: var(--shadow); }
        .product-code { color: var(--green); font-weight: 400; font-size: 13px; }
        .product-card h3 { margin: 10px 0 8px; font-size: 18px; line-height: 1.25; min-height: 45px; }
        .category-pill {
            display: inline-flex;
            border-radius: 999px;
            padding: 6px 10px;
            background: #fff7e8;
            color: #9a5c05;
            font-weight: 400;
            font-size: 12px;
        }
        .price-row { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-top: 18px; }
        .price { font-size: 20px; font-weight: 400; color: var(--ink); }
        .cart-panel {
            position: sticky;
            top: 106px;
            background: rgba(255, 255, 255, .94);
            border: 1px solid var(--line);
            border-radius: 28px;
            padding: 20px;
            box-shadow: var(--shadow);
        }
        .cart-head { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 16px; }
        .cart-head h2 { margin: 0; font-size: 22px; }
        .cart-count { border-radius: 999px; background: #e8f8f4; color: var(--green); padding: 7px 11px; font-weight: 400; }
        .cart-list { display: grid; gap: 12px; max-height: 48vh; overflow: auto; padding-right: 4px; }
        .cart-item { border: 1px solid var(--line); border-radius: 20px; padding: 14px; background: #fff; }
        .cart-item-top { display: flex; justify-content: space-between; gap: 12px; align-items: start; }
        .cart-item h3 { margin: 0 0 5px; font-size: 15px; line-height: 1.3; }
        .cart-item small { color: var(--muted); font-weight: 400; }
        .cart-controls { margin-top: 12px; display: flex; justify-content: space-between; align-items: center; gap: 10px; }
        .qty-controls { display: flex; align-items: center; gap: 8px; }
        .qty { min-width: 34px; text-align: center; font-weight: 400; }
        .remove-btn { border: 0; background: transparent; color: #c2410c; cursor: pointer; font-weight: 400; padding: 6px; }
        .cart-summary { border-top: 1px dashed var(--line); margin-top: 18px; padding-top: 18px; display: grid; gap: 10px; }
        .summary-row { display: flex; justify-content: space-between; color: var(--muted); font-weight: 400; }
        .summary-row.total { color: var(--ink); font-size: 24px; font-weight: 400; }
        .empty-cart {
            border: 1px dashed #bdd4ce;
            border-radius: 22px;
            padding: 26px 16px;
            text-align: center;
            color: var(--muted);
            background: var(--soft);
            font-weight: 400;
        }
        .pagination { margin-top: 22px; }
        .checkout-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 390px;
            gap: 24px;
            padding: 24px 0;
            align-items: start;
        }
        .checkout-topbar {
            grid-template-columns: auto minmax(220px, 1fr) auto;
        }
        .checkout-title h1 {
            margin: 0;
            font-size: 26px;
        }
        .checkout-title p {
            margin: 4px 0 0;
            color: var(--muted);
        }
        .checkout-card,
        .payment-panel {
            background: rgba(255, 255, 255, .94);
            border: 1px solid var(--line);
            border-radius: 28px;
            padding: 20px;
            box-shadow: var(--shadow);
        }
        .payment-panel {
            position: sticky;
            top: 106px;
        }
        .compact-head {
            margin-top: 0;
        }
        .checkout-items {
            display: grid;
            gap: 14px;
        }
        .checkout-item {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 18px;
            align-items: center;
            border: 1px solid var(--line);
            border-radius: 22px;
            padding: 16px;
            background: #fff;
        }
        .checkout-item h3 {
            margin: 8px 0 6px;
            font-size: 18px;
        }
        .checkout-item p {
            margin: 0;
            color: var(--muted);
        }
        .checkout-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .checkout-subtotal {
            min-width: 160px;
            text-align: right;
        }
        .checkout-subtotal span,
        .order-box span,
        .payment-note {
            color: var(--muted);
        }
        .checkout-subtotal strong,
        .order-box strong {
            display: block;
            margin-top: 4px;
        }
        .order-box {
            border: 1px dashed #bdd4ce;
            border-radius: 18px;
            padding: 14px;
            margin-top: 16px;
            background: var(--soft);
            word-break: break-word;
        }
        .payment-note {
            margin: 14px 0 0;
            line-height: 1.55;
            font-size: 14px;
        }
        .payment-note a {
            color: var(--green);
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        @media (max-width: 1180px) {
            .main-grid { grid-template-columns: 1fr; }
            .checkout-grid { grid-template-columns: 1fr; }
            .cart-panel { position: static; }
            .payment-panel { position: static; }
            .product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 780px) {
            .container { width: min(100% - 20px, 1440px); }
            .login-shell { grid-template-columns: 1fr; }
            .login-hero { min-height: 38vh; padding: 28px; }
            .login-panel { padding: 20px 10px; }
            .topbar-inner { grid-template-columns: 1fr; padding: 14px 0; }
            .cashier-chip { justify-content: space-between; }
            .product-grid { grid-template-columns: 1fr; }
            .checkout-item { grid-template-columns: 1fr; }
            .checkout-actions { align-items: stretch; flex-direction: column; }
            .checkout-subtotal { min-width: 0; text-align: left; }
            .section-head { align-items: start; flex-direction: column; }
            .promo-slide { padding: 24px; }
        }
    </style>
</head>
<body>
    @yield('body')
</body>
</html>
