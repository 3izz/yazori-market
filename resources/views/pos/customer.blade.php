<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>شاشة الزبون - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen w-screen overflow-hidden bg-emerald-950 text-white select-none">
    <button id="fullscreen-btn" type="button"
            class="touch-btn fixed top-3 left-3 z-50 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs px-3 py-2">
        ملء الشاشة
    </button>

    {{-- Idle screen --}}
    <div id="idle-screen" class="h-screen w-screen flex flex-col items-center justify-center gap-6">
        <img src="{{ asset('images/logo.png') }}" alt="اليزوري ماركت" class="h-32 w-32 rounded-full shadow-2xl object-cover">
        <div class="text-center">
            <h1 class="text-4xl font-extrabold">مرحباً بكم في اليزوري ماركت</h1>
            <p class="text-emerald-300 text-xl mt-2">Welcome to Al-Yazori Market</p>
        </div>
        <div id="clock" class="text-emerald-400 text-2xl font-mono mt-6"></div>
    </div>

    {{-- Active sale screen --}}
    <div id="sale-screen" class="hidden h-screen w-screen flex-col">
        <div id="items-list" class="flex-1 overflow-y-auto px-8 py-6 space-y-3">
        </div>
        <div class="shrink-0 bg-emerald-900 border-t-4 border-emerald-600 px-8 py-6">
            <div class="flex items-center justify-between text-2xl text-emerald-200">
                <span>المجموع الفرعي</span>
                <span id="subtotal-value">0.00</span>
            </div>
            <div id="discount-row" class="hidden flex items-center justify-between text-xl text-emerald-300">
                <span>الخصم</span>
                <span id="discount-value">0.00</span>
            </div>
            <div class="flex items-center justify-between mt-2">
                <span class="text-3xl font-bold">الإجمالي</span>
                <span id="total-value" class="text-6xl font-extrabold tabular-nums">0.00</span>
            </div>
        </div>
    </div>

    {{-- Thank-you screen --}}
    <div id="thanks-screen" class="hidden h-screen w-screen flex-col items-center justify-center gap-4">
        <div class="text-3xl font-semibold text-emerald-300">الإجمالي المستحق</div>
        <div id="thanks-total" class="text-8xl font-extrabold tabular-nums">0.00</div>
        <div class="text-4xl font-bold mt-8">شكراً لتسوقكم معنا 🙏</div>
    </div>

<script>
(function () {
    const idleScreen = document.getElementById('idle-screen');
    const saleScreen = document.getElementById('sale-screen');
    const thanksScreen = document.getElementById('thanks-screen');
    const itemsList = document.getElementById('items-list');
    const subtotalValue = document.getElementById('subtotal-value');
    const discountRow = document.getElementById('discount-row');
    const discountValue = document.getElementById('discount-value');
    const totalValue = document.getElementById('total-value');
    const thanksTotal = document.getElementById('thanks-total');
    const clockEl = document.getElementById('clock');
    const fullscreenBtn = document.getElementById('fullscreen-btn');

    function money(n) {
        return (Math.round((n || 0) * 100) / 100).toFixed(2);
    }

    function showScreen(name) {
        idleScreen.classList.toggle('hidden', name !== 'idle');
        idleScreen.classList.toggle('flex', name === 'idle');
        saleScreen.classList.toggle('hidden', name !== 'sale');
        saleScreen.classList.toggle('flex', name === 'sale');
        thanksScreen.classList.toggle('hidden', name !== 'thanks');
        thanksScreen.classList.toggle('flex', name === 'thanks');
    }

    function renderCart(data) {
        const items = data.items || [];

        if (items.length === 0) {
            showScreen('idle');
            return;
        }

        itemsList.innerHTML = '';
        items.forEach((item) => {
            const row = document.createElement('div');
            row.className = 'flex items-center justify-between text-2xl bg-white/5 rounded-xl px-5 py-4';
            row.innerHTML = `
                <div class="flex-1 truncate">${item.name}</div>
                <div class="text-emerald-300 mx-4 shrink-0">${item.quantity} × ${money(item.price)}</div>
                <div class="font-bold shrink-0">${money(item.price * item.quantity)}</div>
            `;
            itemsList.appendChild(row);
        });
        itemsList.scrollTop = itemsList.scrollHeight;

        subtotalValue.textContent = money(data.subtotal);

        if (data.discount > 0) {
            discountRow.classList.remove('hidden');
            discountRow.classList.add('flex');
            discountValue.textContent = money(data.discount);
        } else {
            discountRow.classList.add('hidden');
            discountRow.classList.remove('flex');
        }

        totalValue.textContent = money(data.total);
        showScreen('sale');
    }

    function showThanks(total) {
        thanksTotal.textContent = money(total);
        showScreen('thanks');
        setTimeout(() => showScreen('idle'), 6000);
    }

    function tickClock() {
        const now = new Date();
        clockEl.textContent = now.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit' });
    }
    tickClock();
    setInterval(tickClock, 1000 * 15);

    fullscreenBtn.addEventListener('click', () => {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(() => {});
        } else {
            document.exitFullscreen().catch(() => {});
        }
    });

    if ('BroadcastChannel' in window) {
        const channel = new BroadcastChannel('alyazori-pos-display');
        channel.onmessage = (event) => {
            const msg = event.data || {};
            if (msg.type === 'cart') {
                renderCart(msg);
            } else if (msg.type === 'completed') {
                showThanks(msg.total);
            } else if (msg.type === 'idle') {
                showScreen('idle');
            }
        };
    }

    showScreen('idle');
})();
</script>
</body>
</html>
