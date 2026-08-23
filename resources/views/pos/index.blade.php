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
            <button id="checkout-btn" type="button"
                    class="touch-btn w-full rounded-xl bg-emerald-700 text-white font-extrabold text-xl py-4 mt-2 hover:bg-emerald-800 active:bg-emerald-900 disabled:opacity-40">
                إتمام البيع وطباعة الفاتورة
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

        <div id="search-results" class="flex-1 overflow-y-auto grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 content-start pb-4">
        </div>

        <p id="search-message" class="text-center text-slate-400 hidden"></p>

        <button type="button" id="unknown-product-btn"
                class="touch-btn w-full rounded-xl bg-amber-500 text-white font-bold py-4 text-lg hover:bg-amber-600">
            + بيع صنف بدون باركود (سعر يدوي)
        </button>
    </section>
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
    const cart = new Map();

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

        const newQty = line.quantity + delta;

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

        let qty = parseInt(value, 10);
        if (isNaN(qty) || qty < 1) qty = 1;

        line.quantity = qty;
        renderCart();
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
                    <div class="font-semibold text-slate-800 truncate">${line.name}</div>
                    <div class="text-xs text-slate-500">${money(line.price)} × ${line.quantity} = ${money(line.price * line.quantity)}</div>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <button type="button" data-action="dec" class="touch-btn h-11 w-11 rounded-lg bg-slate-200 text-xl font-bold hover:bg-slate-300">−</button>
                    <input type="number" min="1" value="${line.quantity}" data-action="set"
                           class="h-11 w-14 rounded-lg border border-slate-300 text-center text-lg">
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

        if (cart.size === 0) {
            broadcastIdle();
        } else {
            broadcastCart(subtotal, discount, total);
        }
    }

    function renderSearchResults(products) {
        searchResults.innerHTML = '';

        if (products.length === 0) {
            searchMessage.textContent = 'لا توجد نتائج';
            searchMessage.classList.remove('hidden');
            return;
        }

        searchMessage.classList.add('hidden');

        products.forEach((product) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'touch-btn rounded-xl bg-white border border-slate-200 shadow-sm p-4 text-right hover:border-emerald-500 hover:shadow flex flex-col gap-1 min-h-[96px]';
            btn.innerHTML = `
                <span class="font-bold text-slate-800 leading-snug">${product.name}</span>
                <span class="text-emerald-700 font-extrabold text-lg">${money(product.price)}</span>
                <span class="text-xs text-slate-400">متوفر: ${product.quantity} ${product.unit || ''}</span>
            `;
            btn.addEventListener('click', () => {
                addProductToCart({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    quantity_available: product.quantity,
                });
            });
            searchResults.appendChild(btn);
        });
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
                });
            } else {
                alert(data.message || 'لا يوجد منتج بهذا الباركود');
            }
        } catch (e) {
            alert('حدث خطأ أثناء البحث عن المنتج');
        } finally {
            barcodeInput.value = '';
            focusBarcode();
        }
    }

    let searchTimer = null;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const term = searchInput.value.trim();

        if (!term) {
            searchResults.innerHTML = '';
            searchMessage.classList.add('hidden');
            return;
        }

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

    checkoutBtn.addEventListener('click', async () => {
        if (cart.size === 0) return;

        checkoutBtn.disabled = true;
        checkoutBtn.textContent = 'جارٍ الحفظ...';

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

            checkoutBtn.textContent = 'جارٍ الطباعة...';
            try {
                const printRes = await fetch(`/sales/${data.sale_id}/print-thermal`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                const printData = await printRes.json();
                if (printRes.ok) {
                    showToast('تم البيع وطباعة الفاتورة بنجاح ✓', false);
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
            checkoutBtn.disabled = false;
            checkoutBtn.textContent = 'إتمام البيع وطباعة الفاتورة';
        }
    });

    // Keep the barcode field focused for scanner input, without blocking mouse/keyboard use elsewhere.
    document.addEventListener('click', (e) => {
        const isFormField = ['INPUT', 'BUTTON', 'SELECT', 'TEXTAREA', 'A'].includes(e.target.tagName);
        if (!isFormField) {
            focusBarcode();
        }
    });

    renderCart();
    focusBarcode();
})();
</script>
@endsection
