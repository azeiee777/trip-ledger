<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TripLedger — Split trip expenses, effortlessly</title>
    <meta name="description" content="Track group expenses on any trip. Split costs fairly, settle debts instantly, and travel without the money awkwardness.">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; background: #fafafe; color: #111827; }

        /* NAV */
        .nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 40px;
            background: rgba(255,255,255,.85); backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(99,102,241,.08);
        }
        .nav-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-logo-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg,#6366f1,#8b5cf6);
            display: flex; align-items: center; justify-content: center;
        }
        .nav-logo-text { font-size: 18px; font-weight: 800; color: #1e1b4b; }
        .nav-actions { display: flex; align-items: center; gap: 10px; }
        .btn-ghost {
            padding: 8px 18px; border-radius: 10px; font-size: 13px; font-weight: 600;
            color: #374151; text-decoration: none; border: 1.5px solid #e5e7eb; background: #fff;
            transition: all .15s;
        }
        .btn-ghost:hover { border-color: #c7d2fe; color: #4f46e5; }
        .btn-primary {
            padding: 8px 20px; border-radius: 10px; font-size: 13px; font-weight: 700;
            color: #fff; text-decoration: none;
            background: linear-gradient(135deg,#6366f1,#8b5cf6);
            box-shadow: 0 4px 14px rgba(99,102,241,.35); transition: all .2s;
        }
        .btn-primary:hover { box-shadow: 0 6px 20px rgba(99,102,241,.5); transform: translateY(-1px); }

        /* HERO */
        .hero { padding: 140px 40px 80px; text-align: center; position: relative; overflow: hidden; }
        .hero-bg { position: absolute; inset: 0; background: linear-gradient(160deg,#f0f0ff 0%,#fafafe 40%,#fff7f0 100%); }
        .hero-blob-1 { position: absolute; top:-120px; left:-100px; width:600px; height:600px; border-radius:50%; background:radial-gradient(circle,rgba(99,102,241,.12) 0%,transparent 70%); pointer-events:none; }
        .hero-blob-2 { position: absolute; bottom:-80px; right:-60px; width:500px; height:500px; border-radius:50%; background:radial-gradient(circle,rgba(139,92,246,.1) 0%,transparent 70%); pointer-events:none; }
        .hero-inner { position: relative; z-index: 1; max-width: 760px; margin: 0 auto; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 99px;
            padding: 5px 14px; font-size: 12px; font-weight: 700; color: #4f46e5; margin-bottom: 24px;
        }
        .hero-badge-dot { width: 6px; height: 6px; border-radius: 50%; background: #6366f1; animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.6;transform:scale(1.3)} }
        .hero-title { font-size: clamp(36px,5vw,64px); font-weight:900; line-height:1.1; color:#1e1b4b; margin-bottom:20px; letter-spacing:-.02em; }
        .hero-title span { background: linear-gradient(135deg,#6366f1,#8b5cf6,#ec4899); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        .hero-sub { font-size:clamp(15px,2vw,18px); color:#6b7280; line-height:1.7; max-width:560px; margin:0 auto 36px; }
        .hero-ctas { display:flex; align-items:center; justify-content:center; gap:12px; flex-wrap:wrap; }
        .btn-hero-primary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 28px; border-radius: 14px; font-size: 15px; font-weight: 700;
            color: #fff; text-decoration: none;
            background: linear-gradient(135deg,#6366f1,#8b5cf6);
            box-shadow: 0 6px 24px rgba(99,102,241,.4); transition: all .2s;
        }
        .btn-hero-primary:hover { transform:translateY(-2px); box-shadow:0 10px 32px rgba(99,102,241,.5); }
        .btn-hero-secondary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 28px; border-radius: 14px; font-size: 15px; font-weight: 700;
            color: #374151; text-decoration: none; background: #fff;
            border: 1.5px solid #e5e7eb; box-shadow: 0 2px 8px rgba(0,0,0,.05); transition: all .2s;
        }
        .btn-hero-secondary:hover { border-color:#c7d2fe; color:#4f46e5; }
        .hero-trust { margin-top:28px; font-size:12px; color:#9ca3af; display:flex; align-items:center; justify-content:center; gap:8px; flex-wrap:wrap; }
        .hero-trust-dot { width:4px; height:4px; border-radius:50%; background:#d1d5db; }

        /* MOCK CARD */
        .hero-visual { max-width: 860px; margin: 60px auto 0; position: relative; z-index: 1; }
        .mock-card { background:#fff; border-radius:24px; border:1px solid #e8e6ff; box-shadow:0 24px 80px rgba(99,102,241,.15),0 4px 16px rgba(0,0,0,.05); overflow:hidden; }
        .mock-topbar { background:linear-gradient(135deg,#1e1b4b,#4338ca); padding:20px 24px; display:flex; align-items:center; justify-content:space-between; }
        .mock-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; padding:20px 24px; }
        .mock-stat { border-radius:12px; padding:14px; }
        .mock-stat-val { font-size:18px; font-weight:900; }
        .mock-stat-lbl { font-size:10px; font-weight:600; margin-top:3px; text-transform:uppercase; letter-spacing:.05em; }
        .mock-row { padding:0 24px 20px; display:flex; flex-direction:column; gap:8px; }
        .mock-expense { display:flex; align-items:center; gap:10px; background:#fafafe; border-radius:10px; padding:10px 12px; }
        .mock-exp-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }

        /* FEATURES */
        .features { padding:80px 40px; max-width:1100px; margin:0 auto; }
        .section-label { font-size:11px; font-weight:700; color:#6366f1; letter-spacing:.1em; text-transform:uppercase; margin-bottom:10px; }
        .section-title { font-size:clamp(26px,3vw,38px); font-weight:900; color:#1e1b4b; line-height:1.2; margin-bottom:12px; }
        .features-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(300px,1fr)); gap:20px; margin-top:48px; }
        .feature-card { border-radius:20px; padding:28px; border:1.5px solid; transition:transform .2s,box-shadow .2s; }
        .feature-card:hover { transform:translateY(-3px); box-shadow:0 12px 32px rgba(0,0,0,.08); }
        .feature-icon { width:48px; height:48px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:22px; margin-bottom:18px; }
        .feature-title { font-size:15px; font-weight:800; color:#1e1b4b; margin-bottom:8px; }
        .feature-desc { font-size:13px; color:#6b7280; line-height:1.7; }

        /* HOW IT WORKS */
        .how { padding:70px 40px; background:linear-gradient(135deg,#1e1b4b,#312e81,#4338ca); }
        .how-inner { max-width:960px; margin:0 auto; text-align:center; }
        .how-title { font-size:clamp(24px,3vw,36px); font-weight:900; color:#fff; margin-bottom:12px; }
        .how-sub { font-size:15px; color:rgba(255,255,255,.6); margin-bottom:48px; }
        .steps { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:32px; }
        .step { text-align:center; }
        .step-num { width:52px; height:52px; border-radius:50%; margin:0 auto 16px; background:rgba(255,255,255,.1); border:2px solid rgba(255,255,255,.2); display:flex; align-items:center; justify-content:center; font-size:20px; font-weight:900; color:#fff; }
        .step-title { font-size:14px; font-weight:800; color:#fff; margin-bottom:8px; }
        .step-desc { font-size:12px; color:rgba(255,255,255,.55); line-height:1.7; }

        /* TRIP TYPE CHIPS */
        .trips-row { padding:70px 40px; max-width:1100px; margin:0 auto; text-align:center; }
        .trip-chips { display:flex; flex-wrap:wrap; justify-content:center; gap:12px; margin-top:36px; }
        .trip-chip { display:flex; align-items:center; gap:8px; padding:10px 20px; border-radius:99px; font-size:13px; font-weight:700; border:1.5px solid; transition:transform .15s; cursor:default; }
        .trip-chip:hover { transform:scale(1.05); }

        /* CTA BANNER */
        .cta-banner { margin:0 40px 80px; border-radius:28px; overflow:hidden; background:linear-gradient(135deg,#6366f1,#8b5cf6,#ec4899); padding:70px 40px; text-align:center; position:relative; }
        .cta-blob { position:absolute; width:300px; height:300px; border-radius:50%; background:rgba(255,255,255,.06); pointer-events:none; }
        .cta-banner h2 { font-size:clamp(24px,3vw,42px); font-weight:900; color:#fff; margin-bottom:12px; position:relative; z-index:1; }
        .cta-banner p { font-size:15px; color:rgba(255,255,255,.7); margin-bottom:32px; position:relative; z-index:1; }
        .cta-btns { display:flex; align-items:center; justify-content:center; gap:12px; flex-wrap:wrap; position:relative; z-index:1; }
        .btn-cta-white { padding:14px 32px; border-radius:14px; font-size:15px; font-weight:700; color:#6366f1; background:#fff; text-decoration:none; box-shadow:0 4px 16px rgba(0,0,0,.15); transition:all .2s; }
        .btn-cta-white:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,.2); }
        .btn-cta-outline { padding:14px 32px; border-radius:14px; font-size:15px; font-weight:700; color:#fff; text-decoration:none; border:2px solid rgba(255,255,255,.45); transition:all .2s; }
        .btn-cta-outline:hover { background:rgba(255,255,255,.1); border-color:#fff; }

        /* FOOTER */
        .footer { border-top:1px solid #f1f5f9; padding:24px 40px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; }
        .footer-links a { font-size:12px; color:#9ca3af; text-decoration:none; margin-left:16px; }
        .footer-links a:hover { color:#6366f1; }

        @media(max-width:640px){
            .nav{padding:14px 20px;}
            .hero{padding:120px 20px 60px;}
            .features,.trips-row{padding:60px 20px;}
            .how{padding:50px 20px;}
            .cta-banner{margin:0 16px 60px;padding:48px 24px;}
            .footer{padding:20px;}
            .mock-grid{grid-template-columns:repeat(2,1fr);}
        }
    </style>
</head>
<body>

{{-- NAV --}}
<nav class="nav">
    <a href="{{ route('home') }}" class="nav-logo">
        <div class="nav-logo-icon">
            <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
        </div>
        <span class="nav-logo-text">TripLedger</span>
    </a>
    <div class="nav-actions">
        <a href="{{ route('login') }}" class="btn-ghost">Sign In</a>
        <a href="{{ route('register') }}" class="btn-primary">Get Started Free</a>
    </div>
</nav>

{{-- HERO --}}
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-blob-1"></div>
    <div class="hero-blob-2"></div>
    <div class="hero-inner">
        <div class="hero-badge">
            <div class="hero-badge-dot"></div>
            No more awkward money conversations on trips
        </div>
        <h1 class="hero-title">
            Split trip expenses<br><span>without the drama</span>
        </h1>
        <p class="hero-sub">
            Track every rupee on group trips. Split costs fairly, see who owes what, and settle up in seconds — so you can focus on making memories.
        </p>
        <div class="hero-ctas">
            <a href="{{ route('register') }}" class="btn-hero-primary">
                <svg width="15" height="15" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Start a Trip Free
            </a>
            <a href="{{ route('login') }}" class="btn-hero-secondary">
                Sign in to my trips →
            </a>
        </div>
        <div class="hero-trust">
            <span>✓ No credit card needed</span>
            <div class="hero-trust-dot"></div>
            <span>✓ Free for small groups</span>
            <div class="hero-trust-dot"></div>
            <span>✓ Invite anyone via email</span>
        </div>
    </div>

    {{-- Mock dashboard preview --}}
    <div class="hero-visual">
        <div class="mock-card">
            <div class="mock-topbar">
                <div>
                    <div style="font-size:11px;color:rgba(255,255,255,.5);font-weight:600;letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px;">Active Trip</div>
                    <div style="font-size:16px;font-weight:800;color:#fff;">✈️ Goa Weekend Getaway</div>
                </div>
                <span style="font-size:11px;font-weight:700;background:#dcfce7;color:#16a34a;padding:5px 12px;border-radius:99px;">● Ongoing</span>
            </div>
            <div class="mock-grid">
                <div class="mock-stat" style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
                    <div class="mock-stat-val" style="color:#065f46;">₹12,400</div>
                    <div class="mock-stat-lbl" style="color:#059669;">Group Spend</div>
                </div>
                <div class="mock-stat" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);">
                    <div class="mock-stat-val" style="color:#1e40af;">₹3,100</div>
                    <div class="mock-stat-lbl" style="color:#2563eb;">My Share</div>
                </div>
                <div class="mock-stat" style="background:linear-gradient(135deg,#fff7ed,#fed7aa);">
                    <div class="mock-stat-val" style="color:#9a3412;">₹850</div>
                    <div class="mock-stat-lbl" style="color:#ea580c;">I Owe</div>
                </div>
                <div class="mock-stat" style="background:linear-gradient(135deg,#f5f3ff,#ede9fe);">
                    <div class="mock-stat-val" style="color:#1e1b4b;">4</div>
                    <div class="mock-stat-lbl" style="color:#7c3aed;">Members</div>
                </div>
            </div>
            <div class="mock-row">
                <div style="font-size:11px;font-weight:700;color:#9ca3af;letter-spacing:.06em;text-transform:uppercase;padding:0 4px;margin-bottom:4px;">Recent Expenses</div>
                <div class="mock-expense">
                    <div class="mock-exp-icon" style="background:#fef3c7;">🍜</div>
                    <div style="flex:1;">
                        <div style="font-size:12px;font-weight:700;color:#111827;">Dinner at Fisherman's Wharf</div>
                        <div style="font-size:10px;color:#9ca3af;margin-top:1px;">Paid by Arjun · Split equally · 4 people</div>
                    </div>
                    <div style="font-size:13px;font-weight:800;color:#6366f1;">₹2,800</div>
                </div>
                <div class="mock-expense">
                    <div class="mock-exp-icon" style="background:#dbeafe;">🏨</div>
                    <div style="flex:1;">
                        <div style="font-size:12px;font-weight:700;color:#111827;">Hotel Booking — 2 nights</div>
                        <div style="font-size:10px;color:#9ca3af;margin-top:1px;">Paid by Priya · Split equally · 4 people</div>
                    </div>
                    <div style="font-size:13px;font-weight:800;color:#6366f1;">₹6,400</div>
                </div>
                <div class="mock-expense">
                    <div class="mock-exp-icon" style="background:#dcfce7;">🚗</div>
                    <div style="flex:1;">
                        <div style="font-size:12px;font-weight:700;color:#111827;">Taxi from Airport</div>
                        <div style="font-size:10px;color:#9ca3af;margin-top:1px;">Paid by Rahul · Per car group</div>
                    </div>
                    <div style="font-size:13px;font-weight:800;color:#6366f1;">₹1,200</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FEATURES --}}
<section class="features">
    <div style="text-align:center;">
        <div class="section-label">Everything you need</div>
        <h2 class="section-title">Built for real group travel</h2>
        <p style="font-size:15px;color:#6b7280;max-width:500px;margin:0 auto;line-height:1.7;">Not just a calculator — a full trip finance hub your whole group can use together.</p>
    </div>
    <div class="features-grid">
        <div class="feature-card" style="background:#f5f3ff;border-color:#ddd6fe;">
            <div class="feature-icon" style="background:#ede9fe;">💸</div>
            <div class="feature-title">Smart Expense Splitting</div>
            <div class="feature-desc">Equal splits, custom amounts, percentages, or per-car-group — whatever your trip needs. Every rupee accounted for.</div>
        </div>
        <div class="feature-card" style="background:#f0fdf4;border-color:#bbf7d0;">
            <div class="feature-icon" style="background:#dcfce7;">⚖️</div>
            <div class="feature-title">Automatic Debt Simplification</div>
            <div class="feature-desc">Our algorithm minimises transactions. Instead of 10 awkward payments, settle everything in 3. Pure math, zero arguments.</div>
        </div>
        <div class="feature-card" style="background:#eff6ff;border-color:#bfdbfe;">
            <div class="feature-icon" style="background:#dbeafe;">📧</div>
            <div class="feature-title">Email Invites + OTP Access</div>
            <div class="feature-desc">Invite anyone by email — even if they don't have an account yet. They join instantly with a one-time code.</div>
        </div>
        <div class="feature-card" style="background:#fff7ed;border-color:#fed7aa;">
            <div class="feature-icon" style="background:#ffedd5;">✅</div>
            <div class="feature-title">Expense Approval Workflow</div>
            <div class="feature-desc">Trip admin approves member-submitted expenses before they affect balances. No surprise costs, full transparency.</div>
        </div>
        <div class="feature-card" style="background:#fdf2f8;border-color:#fbcfe8;">
            <div class="feature-icon" style="background:#fce7f3;">📊</div>
            <div class="feature-title">Rich Analytics Dashboard</div>
            <div class="feature-desc">Monthly spend trends, category breakdowns, top trips, travel partner stats — know exactly where money goes.</div>
        </div>
        <div class="feature-card" style="background:#ecfdf5;border-color:#a7f3d0;">
            <div class="feature-icon" style="background:#d1fae5;">🗺️</div>
            <div class="feature-title">Trip Itinerary & Stops</div>
            <div class="feature-desc">Plan your route with named stops, attach expenses to locations, and keep a living itinerary as you travel.</div>
        </div>
    </div>
</section>

{{-- HOW IT WORKS --}}
<section class="how">
    <div class="how-inner">
        <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.45);letter-spacing:.1em;text-transform:uppercase;margin-bottom:10px;">Simple by design</div>
        <h2 class="how-title">Up and running in 60 seconds</h2>
        <p class="how-sub">No training. No spreadsheets. No awkward group chats about money.</p>
        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-title">Create a Trip</div>
                <div class="step-desc">Name it, set a destination and dates. Takes 10 seconds flat.</div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-title">Invite Your Group</div>
                <div class="step-desc">Share a link or email invites. Friends verify with one OTP and they're in.</div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-title">Log Expenses</div>
                <div class="step-desc">Anyone adds expenses as you spend. Splits calculate automatically in real time.</div>
            </div>
            <div class="step">
                <div class="step-num">4</div>
                <div class="step-title">Settle Up</div>
                <div class="step-desc">See exactly who pays whom. Record payments via UPI, cash, or bank transfer.</div>
            </div>
        </div>
    </div>
</section>

{{-- TRIP TYPES --}}
<section class="trips-row">
    <div class="section-label">Works for every kind of trip</div>
    <h2 class="section-title">Wherever you go, we track it</h2>
    <div class="trip-chips">
        <div class="trip-chip" style="background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8;">✈️ International</div>
        <div class="trip-chip" style="background:#f5f3ff;border-color:#ddd6fe;color:#5b21b6;">🚗 Road Trips</div>
        <div class="trip-chip" style="background:#f0fdf4;border-color:#bbf7d0;color:#166534;">🏙️ Local Outings</div>
        <div class="trip-chip" style="background:#fff7ed;border-color:#fed7aa;color:#9a3412;">🙏 Pilgrimages</div>
        <div class="trip-chip" style="background:#fdf2f8;border-color:#fbcfe8;color:#831843;">👨‍👩‍👧 Family Trips</div>
        <div class="trip-chip" style="background:#ecfdf5;border-color:#a7f3d0;color:#065f46;">🏕️ Adventure</div>
    </div>
</section>

{{-- CTA BANNER --}}
<div class="cta-banner">
    <div class="cta-blob" style="top:-80px;left:-80px;"></div>
    <div class="cta-blob" style="bottom:-60px;right:-40px;"></div>
    <h2>Your next trip starts here</h2>
    <p>Create your first trip free. No credit card required. No limits on who can join.</p>
    <div class="cta-btns">
        <a href="{{ route('register') }}" class="btn-cta-white">Create Free Account →</a>
        <a href="{{ route('login') }}" class="btn-cta-outline">I already have an account</a>
    </div>
</div>

{{-- FOOTER --}}
<footer class="footer">
    <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:8px;text-decoration:none;">
        <div style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;">
            <svg width="13" height="13" fill="none" stroke="#fff" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
        </div>
        <span style="font-size:14px;font-weight:800;color:#1e1b4b;">TripLedger</span>
    </a>
    <span style="font-size:12px;color:#9ca3af;">© {{ date('Y') }} TripLedger. Built for groups who travel together.</span>
    <div class="footer-links">
        <a href="{{ route('login') }}">Sign In</a>
        <a href="{{ route('register') }}">Register</a>
    </div>
</footer>

</body>
</html>
