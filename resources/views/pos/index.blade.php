@extends('layouts.pos')

@section('content')
<div id="toast" class="hidden fixed top-4 left-1/2 -translate-x-1/2 z-50 rounded-xl px-6 py-4 text-white font-bold text-lg shadow-lg"></div>

<div class="flex flex-1 min-h-0 flex-col md:flex-row-reverse">

    {{-- Cart panel --}}
    <aside class="flex w-full md:w-[420px] shrink-0 flex-col bg-white border-s border-slate-200 min-h-0">
        <div class="p-4 border-b">
            <input type="text" id="customer-name" placeholder="اسم الزبون (اختياري)"
                   class="w-full rounded-lg border border-slate-300 px-4 py-3 text-base">
        </div>

        <div id="cart-list" class="flex-1 overflow-y-auto divide-y">
            <p id="cart-empty" class="p-6 text-center text-slate-400">السلة فارغة — امسح باركود أو ابحث عن منتج</p>
        </div>

        <div class="border-t p-4 space-y-2 bg-slate-50">
            <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500">المجموع الفرعي</span>
                <span id="subtotal-value" class="font-semibold">0.00</span>
            </div>
            <div class="flex items-center justify-between text-sm gap-3">
                <label for="discount-input" class="text-slate-500 shrink-0">الخصم</label>
                <input type="number" id="discount-input" min="0" step="0.01" value="0"
                       class="w-28 rounded-lg border border-slate-300 px-3 py-2 text-left">
            </div>
            <div class="flex items-center justify-between text-xl font-extrabold text-emerald-800 pt-1">
                <span>الإجمالي</span>
                <span id="total-value">0.00</span>
            </div>
            <div class="flex items-center justify-between text-sm gap-3 pt-1">
                <label for="paid-input" class="text-slate-500 shrink-0">المبلغ المدفوع</label>
                <input type="number" id="paid-input" min="0" step="0.01" placeholder="0.00"
                       class="w-28 rounded-lg border border-slate-300 px-3 py-2 text-left">
            </div>
            <div class="flex items-center justify-between text-lg font-bold text-slate-700">
                <span>الباقي للزبون</span>
                <span id="change-value">0.00</span>
            </div>
            <button id="checkout-btn" type="button" data-print="1"
                    class="touch-btn w-full rounded-xl bg-emerald-700 text-white font-extrabold text-xl py-4 mt-2 hover:bg-emerald-800 active:bg-emerald-900 disabled:opacity-40">
                إتمام البيع وطباعة الفاتورة
            </button>
            <button id="checkout-noprint-btn" type="button" data-print="0"
                    class="touch-btn w-full rounded-xl bg-emerald-700 text-white font-extrabold text-xl py-4 hover:bg-emerald-800 active:bg-emerald-900 disabled:opacity-40">
                إتمام البيع بدون طباعة الفاتورة
            </button>
            <button id="clear-btn" type="button"
                    class="touch-btn w-full rounded-xl bg-slate-200 text-slate-600 font-semibold py-3 hover:bg-slate-300">
                تفريغ السلة
            </button>
        </div>
    </aside>

    {{-- Search / scan panel --}}
    <section class="flex flex-1 flex-col min-h-0 p-4 gap-3">
        <input type="text" id="barcode-input" autofocus placeholder="امسح الباركود هنا..." autocomplete="off" inputmode="numeric"
               class="w-full rounded-2xl border-2 border-emerald-600 px-5 py-5 text-2xl text-center font-bold focus:outline-none focus:ring-4 focus:ring-emerald-200">

        <input type="text" id="search-input" placeholder="أو ابحث باسم المنتج..." autocomplete="off"
               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-lg">

        <div id="search-results" class="hidden flex-1 overflow-y-auto grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 content-start pb-4">
        </div>

        <p id="search-message" class="text-center text-slate-400 hidden"></p>

        <div id="quick-panel" class="flex-1 overflow-y-auto space-y-3 pb-4">
            <div>
                <h3 class="text-xs font-bold text-slate-500 mb-1">أسعار سريعة</h3>
                <div id="quick-prices-row" class="flex flex-wrap gap-2"></div>
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-500 mb-1">الأكثر مبيعاً</h3>
                <div id="best-sellers-row" class="flex flex-wrap gap-2"></div>
            </div>
            <div id="category-rows" class="space-y-3"></div>
        </div>

        <button type="button" id="unknown-product-btn"
                class="touch-btn w-full rounded-xl bg-amber-500 text-white font-bold py-4 text-lg hover:bg-amber-600">
            + بيع صنف بدون باركود (سعر يدوي)
        </button>
    </section>
</div>

<div id="return-invoice-modal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm space-y-4">
        <h3 class="text-lg font-bold text-slate-800">استرجاع فاتورة</h3>

        <div id="return-lookup-step" class="space-y-3">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">رقم الفاتورة</label>
                <input type="text" id="return-invoice-input" placeholder="مثال: INV-000123"
                       class="w-full rounded-lg border border-slate-300 px-4 py-3 text-lg" dir="ltr">
            </div>
            <p id="return-lookup-message" class="text-sm text-red-600 hidden"></p>
            <div class="flex gap-3 pt-2">
                <button type="button" id="return-lookup-btn"
                        class="flex-1 rounded-xl bg-amber-600 text-white font-bold py-3 hover:bg-amber-700">
                    بحث
                </button>
                <button type="button" id="return-cancel-btn"
                        class="flex-1 rounded-xl bg-slate-200 text-slate-700 font-bold py-3 hover:bg-slate-300">
                    إلغاء
                </button>
            </div>
        </div>

        <div id="return-confirm-step" class="hidden space-y-3">
            <div class="text-sm bg-slate-50 rounded-lg p-3">
                <div class="font-bold mb-1">فاتورة <span id="return-sale-invoice"></span></div>
                <div id="return-sale-items" class="text-slate-600 space-y-0.5"></div>
                <div class="font-bold text-emerald-700 mt-2">الإجمالي: <span id="return-sale-total"></span></div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" id="return-confirm-btn"
                        class="flex-1 rounded-xl bg-red-600 text-white font-bold py-3 hover:bg-red-700">
                    تأكيد الاسترجاع
                </button>
                <button type="button" id="return-back-btn"
                        class="flex-1 rounded-xl bg-slate-200 text-slate-700 font-bold py-3 hover:bg-slate-300">
                    رجوع
                </button>
            </div>
        </div>
    </div>
</div>

<div id="unknown-product-modal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm space-y-4">
        <h3 class="text-lg font-bold text-slate-800">بيع صنف بدون باركود</h3>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">اسم الصنف (اختياري)</label>
            <input type="text" id="unknown-name" placeholder="صنف بدون اسم"
                   class="w-full rounded-lg border border-slate-300 px-4 py-3 text-lg">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">السعر</label>
            <input type="number" id="unknown-price" min="0" step="0.01" inputmode="decimal"
                   class="w-full rounded-lg border border-slate-300 px-4 py-3 text-lg">
        </div>
        <div class="flex gap-3 pt-2">
            <button type="button" id="unknown-add-btn"
                    class="touch-btn flex-1 rounded-xl bg-emerald-700 text-white font-bold py-3 text-lg hover:bg-emerald-800">
                إضافة للسلة
            </button>
            <button type="button" id="unknown-cancel-btn"
                    class="touch-btn flex-1 rounded-xl bg-slate-200 text-slate-700 font-bold py-3 text-lg hover:bg-slate-300">
                إلغاء
            </button>
        </div>
    </div>
</div>

<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const isAdminSession = @json(auth()->check());
    const cart = new Map();
    let todayTotal = {{ (float) ($todayTotal ?? 0) }};

    // Web Audio oscillator beeps - no audio files needed, works fully offline.
    let audioCtx = null;
    function beep(success) {
        try {
            audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
            const tones = success ? [1600] : [300, 300];
            let time = audioCtx.currentTime;
            tones.forEach((freq, i) => {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.frequency.value = freq;
                gain.gain.value = 0.15;
                osc.connect(gain).connect(audioCtx.destination);
                const start = time + i * 0.16;
                osc.start(start);
                osc.stop(start + 0.12);
            });
        } catch (e) {
            // Audio isn't essential to completing a sale; ignore if unsupported/blocked.
        }
    }

    const barcodeInput = document.getElementById('barcode-input');
    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    const searchMessage = document.getElementById('search-message');
    const cartList = document.getElementById('cart-list');
    const cartEmpty = document.getElementById('cart-empty');
    const subtotalValue = document.getElementById('subtotal-value');
    const totalValue = document.getElementById('total-value');
    const discountInput = document.getElementById('discount-input');
    const paidInput = document.getElementById('paid-input');
    const changeValue = document.getElementById('change-value');
    const checkoutBtn = document.getElementById('checkout-btn');
    const checkoutNoPrintBtn = document.getElementById('checkout-noprint-btn');
    const clearBtn = document.getElementById('clear-btn');
    const customerNameInput = document.getElementById('customer-name');
    const openCustomerDisplayBtn = document.getElementById('open-customer-display-btn');
    const unknownProductBtn = document.getElementById('unknown-product-btn');
    const unknownProductModal = document.getElementById('unknown-product-modal');
    const unknownNameInput = document.getElementById('unknown-name');
    const unknownPriceInput = document.getElementById('unknown-price');
    const unknownAddBtn = document.getElementById('unknown-add-btn');
    const unknownCancelBtn = document.getElementById('unknown-cancel-btn');

    const customerChannel = 'BroadcastChannel' in window ? new BroadcastChannel('alyazori-pos-display') : null;

    function broadcastCart(subtotal, discount, total) {
        if (!customerChannel) return;
        customerChannel.postMessage({
            type: 'cart',
            items: Array.from(cart.values()).map((line) => ({
                name: line.name,
                price: line.price,
                quantity: line.quantity,
            })),
            subtotal,
            discount,
            total,
        });
    }

    function broadcastIdle() {
        if (!customerChannel) return;
        customerChannel.postMessage({ type: 'idle' });
    }

    function broadcastCompleted(total) {
        if (!customerChannel) return;
        customerChannel.postMessage({ type: 'completed', total });
    }

    openCustomerDisplayBtn.addEventListener('click', async () => {
        const url = '{{ route('pos.customer') }}';

        if ('getScreenDetails' in window) {
            try {
                const details = await window.getScreenDetails();

                if (details.screens.length > 1) {
                    const target = details.screens.find((s) => !s.isPrimary) || details.screens[details.screens.length - 1];
                    const features = `left=${target.availLeft},top=${target.availTop},width=${target.availWidth},height=${target.availHeight}`;
                    window.open(url, 'alyazoriCustomerDisplay', features);
                    return;
                }
            } catch (e) {
                // Permission denied or unsupported: fall back to manual placement below.
            }
        }

        window.open(url, 'alyazoriCustomerDisplay', 'width=1024,height=768');
        alert('اسحب النافذة الجديدة إلى شاشة الزبون، ثم اضغط زر "ملء الشاشة" الموجود داخلها.');
    });

    function money(n) {
        return (Math.round(n * 100) / 100).toFixed(2);
    }

    function updateChange(total) {
        const paid = parseFloat(paidInput.value);
        const change = (isNaN(paid) ? total : paid) - total;
        changeValue.textContent = money(change);
        changeValue.className = change < 0 ? 'text-red-600' : '';
    }

    paidInput.addEventListener('input', () => updateChange(parseFloat(totalValue.textContent) || 0));

    // Selecting the existing value on focus means tapping a numeric field lets
    // the cashier just type over it, instead of having to position the cursor
    // and delete each digit by hand.
    function selectAllOnFocus(el) {
        el.addEventListener('focus', () => el.select());
    }

    [discountInput, paidInput, unknownPriceInput].forEach(selectAllOnFocus);

    const toastEl = document.getElementById('toast');
    let toastTimer = null;

    function showToast(message, isError) {
        clearTimeout(toastTimer);
        toastEl.textContent = message;
        toastEl.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-50 rounded-xl px-6 py-4 text-white font-bold text-lg shadow-lg '
            + (isError ? 'bg-red-600' : 'bg-emerald-700');
        toastTimer = setTimeout(() => toastEl.classList.add('hidden'), 4000);
    }

    function resetSaleForm() {
        cart.clear();
        customerNameInput.value = '';
        discountInput.value = 0;
        paidInput.value = '';
        renderCart();
        focusBarcode();
    }

    function focusBarcode() {
        barcodeInput.focus();
    }

    function addProductToCart(product) {
        const existing = cart.get(product.id);

        if (existing) {
            existing.quantity += 1;
        } else {
            cart.set(product.id, { ...product, quantity: 1 });
        }

        renderCart();
        // Tapping a quick-item button doesn't leave the barcode field focused,
        // so scanning right after would otherwise silently go nowhere.
        focusBarcode();
    }

    unknownProductBtn.addEventListener('click', () => {
        unknownNameInput.value = '';
        unknownPriceInput.value = '';
        unknownProductModal.classList.remove('hidden');
        unknownProductModal.classList.add('flex');
        unknownPriceInput.focus();
    });

    function closeUnknownProductModal() {
        unknownProductModal.classList.add('hidden');
        unknownProductModal.classList.remove('flex');
        focusBarcode();
    }

    unknownCancelBtn.addEventListener('click', closeUnknownProductModal);

    unknownAddBtn.addEventListener('click', () => {
        const price = parseFloat(unknownPriceInput.value);

        if (isNaN(price) || price < 0) {
            alert('الرجاء إدخال سعر صحيح');
            unknownPriceInput.focus();
            return;
        }

        const name = unknownNameInput.value.trim() || 'صنف بدون اسم';
        const id = `custom-${Date.now()}-${Math.random().toString(36).slice(2)}`;

        cart.set(id, { id, name, price, quantity: 1, isCustom: true });
        renderCart();
        closeUnknownProductModal();
    });

    unknownPriceInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            unknownAddBtn.click();
        }
    });

    function changeQuantity(id, delta) {
        const line = cart.get(id);
        if (!line) return;

        // Weighted products (e.g. produce sold by kg) move in 0.1 steps since
        // a delta of a whole "1" doesn't make sense for fractional quantities.
        const step = line.is_weighted ? 0.1 : 1;
        const newQty = Math.round((line.quantity + (delta * step)) * 1000) / 1000;

        if (newQty <= 0) {
            cart.delete(id);
        } else {
            line.quantity = newQty;
        }

        renderCart();
    }

    function setQuantity(id, value) {
        const line = cart.get(id);
        if (!line) return;

        let qty = line.is_weighted ? parseFloat(value) : parseInt(value, 10);
        const min = line.is_weighted ? 0.001 : 1;
        if (isNaN(qty) || qty < min) qty = min;

        line.quantity = Math.round(qty * 1000) / 1000;
        renderCart();
    }

    function formatQty(line) {
        return line.is_weighted ? (Math.round(line.quantity * 1000) / 1000).toString() : line.quantity;
    }

    function removeLine(id) {
        cart.delete(id);
        renderCart();
    }

    function renderCart() {
        cartList.innerHTML = '';

        if (cart.size === 0) {
            cartEmpty.classList.remove('hidden');
            cartList.appendChild(cartEmpty);
        } else {
            cartEmpty.classList.add('hidden');
        }

        let subtotal = 0;

        cart.forEach((line) => {
            subtotal += line.price * line.quantity;

            const row = document.createElement('div');
            row.className = 'p-3 flex items-center gap-3';
            row.innerHTML = `
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-slate-800 truncate">${line.name}${line.is_weighted ? ' <span class="text-xs text-slate-400">(بالوزن)</span>' : ''}</div>
                    <div class="text-xs text-slate-500">${money(line.price)} × ${formatQty(line)} = ${money(line.price * line.quantity)}</div>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <button type="button" data-action="dec" class="touch-btn h-11 w-11 rounded-lg bg-slate-200 text-xl font-bold hover:bg-slate-300">−</button>
                    <input type="number" min="${line.is_weighted ? '0.001' : '1'}" step="${line.is_weighted ? '0.001' : '1'}" value="${formatQty(line)}" data-action="set"
                           class="h-11 w-16 rounded-lg border border-slate-300 text-center text-lg">
                    <button type="button" data-action="inc" class="touch-btn h-11 w-11 rounded-lg bg-slate-200 text-xl font-bold hover:bg-slate-300">+</button>
                    <button type="button" data-action="remove" class="touch-btn h-11 w-11 rounded-lg bg-red-100 text-red-600 text-lg font-bold hover:bg-red-200">×</button>
                </div>
            `;

            row.querySelector('[data-action="dec"]').addEventListener('click', () => changeQuantity(line.id, -1));
            row.querySelector('[data-action="inc"]').addEventListener('click', () => changeQuantity(line.id, 1));
            row.querySelector('[data-action="remove"]').addEventListener('click', () => removeLine(line.id));
            row.querySelector('[data-action="set"]').addEventListener('change', (e) => setQuantity(line.id, e.target.value));

            cartList.appendChild(row);
        });

        const discount = parseFloat(discountInput.value) || 0;
        const total = Math.max(subtotal - discount, 0);

        subtotalValue.textContent = money(subtotal);
        totalValue.textContent = money(total);
        updateChange(total);
        checkoutBtn.disabled = cart.size === 0;
        checkoutNoPrintBtn.disabled = cart.size === 0;

        if (cart.size === 0) {
            broadcastIdle();
        } else {
            broadcastCart(subtotal, discount, total);
        }
    }

    function makeProductButton(product) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'touch-btn w-24 h-24 shrink-0 rounded-lg bg-white border border-slate-200 shadow-sm p-1.5 text-center hover:border-emerald-500 hover:shadow flex flex-col items-center justify-center gap-0.5';
        btn.innerHTML = `
            <span class="font-bold text-slate-800 text-xs leading-tight line-clamp-2">${product.name}</span>
            <span class="text-emerald-700 font-extrabold text-sm">${money(product.price)}</span>
        `;
        btn.addEventListener('click', () => {
            addProductToCart({
                id: product.id,
                name: product.name,
                price: product.price,
                quantity_available: product.quantity,
                is_weighted: product.is_weighted,
            });
            beep(true);
        });
        return btn;
    }

    function renderSearchResults(products) {
        searchResults.innerHTML = '';

        if (products.length === 0) {
            searchMessage.textContent = 'لا توجد نتائج';
            searchMessage.classList.remove('hidden');
            return;
        }

        searchMessage.classList.add('hidden');
        products.forEach((product) => searchResults.appendChild(makeProductButton(product)));
    }

    const QUICK_PRICES = [
        { price: 0.05, color: 'bg-pink-400' },
        { price: 0.10, color: 'bg-amber-400' },
        { price: 0.15, color: 'bg-sky-400' },
        { price: 0.25, color: 'bg-emerald-400' },
        { price: 0.50, color: 'bg-purple-400' },
        { price: 1.00, color: 'bg-orange-400' },
    ];

    function renderQuickPrices() {
        const row = document.getElementById('quick-prices-row');
        QUICK_PRICES.forEach(({ price, color }) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `touch-btn w-16 h-16 shrink-0 rounded-lg ${color} text-white font-extrabold text-sm shadow-sm hover:brightness-110`;
            btn.textContent = money(price);
            btn.addEventListener('click', () => {
                const id = `candy-${price}`;
                const existing = cart.get(id);
                if (existing) {
                    existing.quantity += 1;
                } else {
                    cart.set(id, { id, name: 'سكاكر', price, quantity: 1, isCustom: true });
                }
                renderCart();
                focusBarcode();
            });
            row.appendChild(btn);
        });
    }

    async function loadQuickItems() {
        try {
            const res = await fetch('{{ route('pos.quickItems') }}');
            const data = await res.json();

            const bestSellersRow = document.getElementById('best-sellers-row');
            (data.bestSellers || []).forEach((product) => bestSellersRow.appendChild(makeProductButton(product)));

            const categoryRows = document.getElementById('category-rows');
            (data.categories || []).forEach((category) => {
                const section = document.createElement('div');
                const heading = document.createElement('h3');
                heading.className = 'text-xs font-bold text-slate-500 mb-1';
                heading.textContent = category.name;
                const row = document.createElement('div');
                row.className = 'flex flex-wrap gap-2';
                category.products.forEach((product) => row.appendChild(makeProductButton(product)));
                section.appendChild(heading);
                section.appendChild(row);
                categoryRows.appendChild(section);
            });
        } catch (e) {
            // Quick-access grid is a convenience; scanning/search still works if it fails to load.
        }
    }

    async function lookupBarcode(code) {
        if (!code) return;

        try {
            const res = await fetch(`{{ route('pos.search') }}?barcode=${encodeURIComponent(code)}`);
            const data = await res.json();

            if (data.found) {
                addProductToCart({
                    id: data.product.id,
                    name: data.product.name,
                    price: data.product.price,
                    quantity_available: data.product.quantity,
                    is_weighted: data.product.is_weighted,
                });
                beep(true);
            } else {
                beep(false);
                alert(data.message || 'لا يوجد منتج بهذا الباركود');
            }
        } catch (e) {
            beep(false);
            alert('حدث خطأ أثناء البحث عن المنتج');
        } finally {
            barcodeInput.value = '';
            focusBarcode();
        }
    }

    const quickPanel = document.getElementById('quick-panel');

    let searchTimer = null;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const term = searchInput.value.trim();

        if (!term) {
            searchResults.innerHTML = '';
            searchResults.classList.add('hidden');
            quickPanel.classList.remove('hidden');
            searchMessage.classList.add('hidden');
            return;
        }

        quickPanel.classList.add('hidden');
        searchResults.classList.remove('hidden');

        searchTimer = setTimeout(async () => {
            const res = await fetch(`{{ route('pos.search') }}?q=${encodeURIComponent(term)}`);
            const data = await res.json();
            renderSearchResults(data.products || []);
        }, 250);
    });

    barcodeInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            lookupBarcode(barcodeInput.value.trim());
        }
    });

    discountInput.addEventListener('input', renderCart);

    clearBtn.addEventListener('click', () => {
        if (cart.size === 0) return;
        if (confirm('تفريغ السلة بالكامل؟')) {
            cart.clear();
            renderCart();
        }
    });

    const CHECKOUT_LABELS = {
        [checkoutBtn.id]: 'إتمام البيع وطباعة الفاتورة',
        [checkoutNoPrintBtn.id]: 'إتمام البيع بدون طباعة الفاتورة',
    };

    async function handleCheckout(clickedBtn) {
        if (cart.size === 0) return;

        const shouldPrint = clickedBtn.dataset.print === '1';

        checkoutBtn.disabled = true;
        checkoutNoPrintBtn.disabled = true;
        clickedBtn.textContent = 'جارٍ الحفظ...';

        try {
            const res = await fetch(`{{ route('pos.checkout') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    customer_name: customerNameInput.value.trim() || null,
                    discount: parseFloat(discountInput.value) || 0,
                    paid_amount: paidInput.value.trim() === '' ? null : parseFloat(paidInput.value),
                    items: Array.from(cart.values()).map((line) => (
                        line.isCustom
                            ? { name: line.name, price: line.price, quantity: line.quantity }
                            : { product_id: line.id, quantity: line.quantity }
                    )),
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                alert(data.message || 'تعذر إتمام عملية البيع');
                return;
            }

            broadcastCompleted(parseFloat(totalValue.textContent) || 0);

            todayTotal += data.total || 0;
            document.getElementById('header-today-total').textContent = money(todayTotal);
            document.getElementById('header-last-invoice').textContent = data.invoice_number;

            clickedBtn.textContent = shouldPrint ? 'جارٍ الطباعة...' : 'جارٍ فتح الدرج...';
            try {
                const url = shouldPrint ? `/sales/${data.sale_id}/print-thermal` : `{{ route('pos.openDrawer') }}`;
                const printRes = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                const printData = await printRes.json();
                if (printRes.ok) {
                    showToast(shouldPrint ? 'تم البيع وطباعة الفاتورة بنجاح ✓' : 'تم البيع بنجاح ✓', false);
                } else {
                    showToast('تم حفظ البيع، لكن تعذرت الطباعة: ' + (printData.message || ''), true);
                }
            } catch (e) {
                showToast('تم حفظ البيع، لكن تعذر الاتصال بالطابعة.', true);
            }

            resetSaleForm();
            broadcastIdle();
        } catch (e) {
            alert('حدث خطأ أثناء إتمام عملية البيع');
        } finally {
            checkoutBtn.disabled = cart.size === 0;
            checkoutNoPrintBtn.disabled = cart.size === 0;
            checkoutBtn.textContent = CHECKOUT_LABELS[checkoutBtn.id];
            checkoutNoPrintBtn.textContent = CHECKOUT_LABELS[checkoutNoPrintBtn.id];
        }
    }

    checkoutBtn.addEventListener('click', () => handleCheckout(checkoutBtn));
    checkoutNoPrintBtn.addEventListener('click', () => handleCheckout(checkoutNoPrintBtn));

    // Keep the barcode field focused for scanner input, without blocking mouse/keyboard use elsewhere.
    document.addEventListener('click', (e) => {
        const isFormField = ['INPUT', 'BUTTON', 'SELECT', 'TEXTAREA', 'A'].includes(e.target.tagName);
        if (!isFormField) {
            focusBarcode();
        }
    });

    // Return / refund flow
    const returnBtn = document.getElementById('return-invoice-btn');
    const returnModal = document.getElementById('return-invoice-modal');
    const returnLookupStep = document.getElementById('return-lookup-step');
    const returnConfirmStep = document.getElementById('return-confirm-step');
    const returnInvoiceInput = document.getElementById('return-invoice-input');
    const returnLookupMessage = document.getElementById('return-lookup-message');
    const returnLookupBtn = document.getElementById('return-lookup-btn');
    const returnCancelBtn = document.getElementById('return-cancel-btn');
    const returnBackBtn = document.getElementById('return-back-btn');
    const returnConfirmBtn = document.getElementById('return-confirm-btn');
    let foundReturnSale = null;

    function closeReturnModal() {
        returnModal.classList.add('hidden');
        returnModal.classList.remove('flex');
        returnInvoiceInput.value = '';
        returnLookupMessage.classList.add('hidden');
        returnLookupStep.classList.remove('hidden');
        returnConfirmStep.classList.add('hidden');
        foundReturnSale = null;
        focusBarcode();
    }

    returnBtn.addEventListener('click', () => {
        returnModal.classList.remove('hidden');
        returnModal.classList.add('flex');
        returnInvoiceInput.focus();
    });

    returnCancelBtn.addEventListener('click', closeReturnModal);
    returnBackBtn.addEventListener('click', () => {
        returnConfirmStep.classList.add('hidden');
        returnLookupStep.classList.remove('hidden');
    });

    async function doReturnLookup() {
        const invoice = returnInvoiceInput.value.trim();
        if (!invoice) return;

        returnLookupMessage.classList.add('hidden');

        try {
            const res = await fetch(`{{ route('pos.returns.lookup') }}?invoice_number=${encodeURIComponent(invoice)}`);
            const data = await res.json();

            if (!res.ok || !data.found) {
                returnLookupMessage.textContent = data.message || 'لم يتم العثور على الفاتورة';
                returnLookupMessage.classList.remove('hidden');
                return;
            }

            foundReturnSale = data.sale;
            document.getElementById('return-sale-invoice').textContent = data.sale.invoice_number;
            document.getElementById('return-sale-total').textContent = money(data.sale.total);
            document.getElementById('return-sale-items').innerHTML = data.sale.items
                .map((item) => `<div>${item.name} × ${item.quantity}</div>`)
                .join('');

            returnLookupStep.classList.add('hidden');
            returnConfirmStep.classList.remove('hidden');
        } catch (e) {
            returnLookupMessage.textContent = 'حدث خطأ أثناء البحث';
            returnLookupMessage.classList.remove('hidden');
        }
    }

    returnLookupBtn.addEventListener('click', doReturnLookup);
    returnInvoiceInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            doReturnLookup();
        }
    });

    returnConfirmBtn.addEventListener('click', async () => {
        if (!foundReturnSale) return;

        returnConfirmBtn.disabled = true;
        returnConfirmBtn.textContent = 'جارٍ الاسترجاع...';

        try {
            const res = await fetch(`{{ route('pos.returns.process') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ sale_id: foundReturnSale.id }),
            });
            const data = await res.json();

            if (!res.ok) {
                alert(data.message || 'تعذر استرجاع الفاتورة');
                return;
            }

            todayTotal = Math.max(todayTotal - foundReturnSale.total, 0);
            document.getElementById('header-today-total').textContent = money(todayTotal);

            showToast('تم استرجاع الفاتورة بنجاح ✓', false);
            closeReturnModal();
        } catch (e) {
            alert('حدث خطأ أثناء الاسترجاع');
        } finally {
            returnConfirmBtn.disabled = false;
            returnConfirmBtn.textContent = 'تأكيد الاسترجاع';
        }
    });

    // Idle auto-lock: only for PIN-only (cashier) sessions - a logged-in admin
    // already passed the strongest gate and locking them mid-task would just
    // be an annoyance with no added safety.
    const lockPosBtn = document.getElementById('lock-pos-btn');

    async function lockPos() {
        try {
            const res = await fetch(`{{ route('pos.lock') }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();
            window.location.href = data.redirect || '{{ route('pos.unlock') }}';
        } catch (e) {
            window.location.href = '{{ route('pos.unlock') }}';
        }
    }

    if (lockPosBtn) {
        lockPosBtn.addEventListener('click', lockPos);
    }

    if (!isAdminSession) {
        const IDLE_LIMIT_MS = 5 * 60 * 1000;
        let idleTimer = setTimeout(lockPos, IDLE_LIMIT_MS);

        ['click', 'keydown', 'touchstart', 'input'].forEach((eventName) => {
            document.addEventListener(eventName, () => {
                clearTimeout(idleTimer);
                idleTimer = setTimeout(lockPos, IDLE_LIMIT_MS);
            });
        });
    }

    renderCart();
    renderQuickPrices();
    loadQuickItems();
    focusBarcode();
})();
</script>
@endsection
