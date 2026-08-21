@extends('layouts.app')

@section('title', 'فاتورة شراء جديدة')

@section('content')
    <h1 class="text-xl font-bold text-slate-800 mb-5">تسجيل فاتورة شراء جديدة</h1>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <label class="block text-sm font-semibold text-slate-700 mb-1">اسم المورد (اختياري)</label>
                <input type="text" id="supplier-name" class="w-full rounded-lg border border-slate-300 px-4 py-3">
            </div>

            <div class="bg-white rounded-xl shadow-sm p-4">
                <label class="block text-sm font-semibold text-slate-700 mb-1">ابحث عن منتج بالاسم أو الباركود لإضافته</label>
                <input type="text" id="search-input" autofocus autocomplete="off"
                       class="w-full rounded-lg border border-slate-300 px-4 py-3 text-lg">
                <div id="search-results" class="grid sm:grid-cols-2 gap-2 mt-3"></div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
                <table class="w-full text-sm min-w-[600px]">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="text-right px-4 py-3">المنتج</th>
                            <th class="text-right px-4 py-3">سعر الشراء</th>
                            <th class="text-right px-4 py-3">الكمية</th>
                            <th class="text-right px-4 py-3">الإجمالي</th>
                            <th class="text-right px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body">
                        <tr id="items-empty">
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">لم تتم إضافة أصناف بعد</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-4 h-fit space-y-3">
            <div class="flex items-center justify-between text-xl font-extrabold text-emerald-800">
                <span>الإجمالي</span>
                <span id="total-value">0.00</span>
            </div>
            <button id="save-btn" type="button" disabled
                    class="w-full rounded-xl bg-emerald-700 text-white font-extrabold text-lg py-4 hover:bg-emerald-800 disabled:opacity-40">
                حفظ فاتورة الشراء
            </button>
            <a href="{{ route('purchases.index') }}" class="block text-center text-slate-500 font-semibold py-2">إلغاء</a>
        </div>
    </div>

<script>
(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const items = new Map();

    const searchInput = document.getElementById('search-input');
    const searchResults = document.getElementById('search-results');
    const itemsBody = document.getElementById('items-body');
    const itemsEmpty = document.getElementById('items-empty');
    const totalValue = document.getElementById('total-value');
    const saveBtn = document.getElementById('save-btn');
    const supplierInput = document.getElementById('supplier-name');

    function money(n) {
        return (Math.round(n * 100) / 100).toFixed(2);
    }

    function addItem(product) {
        if (!items.has(product.id)) {
            items.set(product.id, {
                id: product.id,
                name: product.name,
                price: product.price,
                quantity: 1,
            });
        }
        render();
    }

    function removeItem(id) {
        items.delete(id);
        render();
    }

    function render() {
        itemsBody.innerHTML = '';

        if (items.size === 0) {
            itemsBody.appendChild(itemsEmpty);
        }

        let total = 0;

        items.forEach((item) => {
            const subtotal = item.price * item.quantity;
            total += subtotal;

            const row = document.createElement('tr');
            row.className = 'border-t';
            row.innerHTML = `
                <td class="px-4 py-2 font-medium">${item.name}</td>
                <td class="px-4 py-2">
                    <input type="number" min="0" step="0.01" value="${item.price}" data-action="price"
                           class="w-24 rounded-lg border border-slate-300 px-2 py-2">
                </td>
                <td class="px-4 py-2">
                    <input type="number" min="1" value="${item.quantity}" data-action="qty"
                           class="w-20 rounded-lg border border-slate-300 px-2 py-2">
                </td>
                <td class="px-4 py-2 font-semibold" data-role="subtotal">${money(subtotal)}</td>
                <td class="px-4 py-2">
                    <button type="button" data-action="remove" class="text-red-600 font-semibold">حذف</button>
                </td>
            `;

            row.querySelector('[data-action="price"]').addEventListener('input', (e) => {
                item.price = parseFloat(e.target.value) || 0;
                render();
            });
            row.querySelector('[data-action="qty"]').addEventListener('input', (e) => {
                item.quantity = Math.max(parseInt(e.target.value, 10) || 1, 1);
                render();
            });
            row.querySelector('[data-action="remove"]').addEventListener('click', () => removeItem(item.id));

            itemsBody.appendChild(row);
        });

        totalValue.textContent = money(total);
        saveBtn.disabled = items.size === 0;
    }

    let searchTimer = null;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const term = searchInput.value.trim();
        searchResults.innerHTML = '';
        if (!term) return;

        searchTimer = setTimeout(async () => {
            const res = await fetch(`{{ route('pos.search') }}?q=${encodeURIComponent(term)}`);
            const data = await res.json();

            (data.products || []).forEach((product) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'text-right rounded-lg border border-slate-200 p-3 hover:border-emerald-500';
                btn.innerHTML = `<div class="font-semibold">${product.name}</div><div class="text-xs text-slate-400">متوفر حالياً: ${product.quantity}</div>`;
                btn.addEventListener('click', () => {
                    addItem({ id: product.id, name: product.name, price: product.price });
                    searchInput.value = '';
                    searchResults.innerHTML = '';
                });
                searchResults.appendChild(btn);
            });
        }, 250);
    });

    saveBtn.addEventListener('click', async () => {
        saveBtn.disabled = true;
        saveBtn.textContent = 'جارٍ الحفظ...';

        try {
            const res = await fetch(`{{ route('purchases.store') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    supplier_name: supplierInput.value.trim() || null,
                    items: Array.from(items.values()).map((item) => ({
                        product_id: item.id,
                        quantity: item.quantity,
                        price: item.price,
                    })),
                }),
            });

            const data = await res.json();

            if (!res.ok) {
                alert(data.message || 'تعذر حفظ فاتورة الشراء');
                saveBtn.disabled = false;
                saveBtn.textContent = 'حفظ فاتورة الشراء';
                return;
            }

            window.location.href = data.redirect;
        } catch (e) {
            alert('حدث خطأ أثناء الحفظ');
            saveBtn.disabled = false;
            saveBtn.textContent = 'حفظ فاتورة الشراء';
        }
    });

    render();
})();
</script>
@endsection
