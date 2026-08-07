@push('scripts')
<script>
    const PURCHASE_PRODUCTS_URL = @json(route('purchase.products'));
    const PURCHASE_SUPPLIERS_URL = @json(route('purchase.suppliers-search'));
    const PURCHASE_OPEN_INVOICES_URL = @json(route('purchase.payments.open-invoices'));

    document.addEventListener('alpine:init', () => {

        // ── Split payment: multiple methods (cash + card, etc.) ────────────
        Alpine.data('splitPayment', (config) => ({
            methods: config.methods || [],
            defaultAmount: config.amount || 0,
            amount: config.amount || 0,
            rows: [],
            total: 0,
            currencySymbol: config.currencySymbol || '৳',

            init() {
                if (!this.rows.length) this.addRow();
                this.$watch('rows', () => this.recalc(), { deep: true });
                this.$watch('amount', (v) => {
                    if (this.rows.length === 1 && !this._manual) {
                        this.rows[0].amount = Number(v) || 0;
                    }
                    this.recalc();
                });
                this.recalc();
            },

            addRow() {
                this._manual = true;
                const remaining = Math.max((Number(this.amount) || 0) - this.total, 0);
                this.rows.push({ method: this.methods[0]?.code || this.methods[0] || '', amount: remaining, reference: '' });
            },

            removeRow(row) {
                if (this.rows.length <= 1) return;
                const i = this.rows.indexOf(row);
                if (i > -1) this.rows.splice(i, 1);
            },

            recalc() {
                this.total = this.rows.reduce((s, r) => s + (Number(r.amount) || 0), 0);
            },

            methodsJson() {
                return JSON.stringify(this.rows
                    .filter(r => r.method && (Number(r.amount) || 0) > 0)
                    .map(r => ({
                        method: r.method,
                        amount: Number(r.amount) || 0,
                        reference: r.reference || '',
                    })));
            },

            fmt(n) {
                return (Number(n) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
        }));

        // ── Parent form state: line items + totals ─────────────────────────
        Alpine.data('purchaseForm', (config) => ({
            items: config.items || [],
            discountType: config.discountType || '',
            discountValue: config.discountValue || 0,
            taxRate: config.taxRate || 0,
            subtotal: 0,
            discountAmount: 0,
            taxAmount: 0,
            total: 0,

            variantModalOpen: false,
            variantProduct: null,
            variantSelections: [],

            init() {
                this.calc();
                this.$watch('items', () => this.calc(), { deep: true });
                this.$watch('discountType', () => this.calc());
                this.$watch('discountValue', () => this.calc());
                this.$watch('taxRate', () => this.calc());

                window.addEventListener('purchase-variant-open', (e) => this.openVariantModal(e.detail?.product));
            },

            calc() {
                this.subtotal = this.items.reduce((s, i) => s + (Number(i.quantity) || 0) * ((Number(i.unit_cost) || 0) - (Number(i.discount) || 0)), 0);
                let d = 0;
                if (this.discountType === 'percent') {
                    d = this.subtotal * ((Number(this.discountValue) || 0) / 100);
                } else if (this.discountType === 'fixed') {
                    d = Math.min(Number(this.discountValue) || 0, this.subtotal);
                }
                this.discountAmount = d;
                const taxable = Math.max(this.subtotal - d, 0);
                this.taxAmount = taxable * ((Number(this.taxRate) || 0) / 100);
                this.total = taxable + this.taxAmount;
            },

            addItem() {
                this.items.push({ product_id: null, variant_id: null, purchase_order_item_id: null, name: '', sku: '', quantity: 1, unit_cost: 0, discount: 0, variants: [] });
            },

            removeItem(row) {
                const i = this.items.indexOf(row);
                if (i > -1) this.items.splice(i, 1);
            },

            openVariantModal(product) {
                if (!product || !product.variants?.length) return;
                this.variantProduct = product;
                this.variantSelections = product.variants.map(v => ({ variant: v, qty: 0 }));
                this.variantModalOpen = true;
            },

            changeVariantQty(index, delta) {
                const s = this.variantSelections[index];
                const next = s.qty + delta;
                if (next < 0) return;
                s.qty = next;
            },

            addVariantSelections() {
                const p = this.variantProduct;
                if (!p) return;
                this.variantSelections.forEach(s => {
                    if (s.qty > 0) {
                        this.items.push({
                            product_id: p.id,
                            variant_id: s.variant.id,
                            purchase_order_item_id: null,
                            name: p.name + ' — ' + s.variant.name,
                            sku: s.variant.sku || p.sku || '',
                            quantity: s.qty,
                            unit_cost: s.variant.cost ?? p.cost ?? 0,
                            discount: 0,
                            variants: [],
                        });
                    }
                });
                this.variantModalOpen = false;
                this.variantProduct = null;
                this.variantSelections = [];
            },

            variantSelectionTotal() {
                return this.variantSelections.reduce((sum, s) => sum + s.qty * (s.variant.cost ?? 0), 0);
            },

            variantSelectionCount() {
                return this.variantSelections.reduce((sum, s) => sum + s.qty, 0);
            },

            itemsJson() {
                return JSON.stringify(this.items
                    .filter(i => i.product_id)
                    .map(i => ({
                        product_id: i.product_id,
                        variant_id: i.variant_id || null,
                        purchase_order_item_id: i.purchase_order_item_id || null,
                        name: i.name || '',
                        sku: i.sku || '',
                        quantity: Number(i.quantity) || 0,
                        unit_cost: Number(i.unit_cost) || 0,
                        discount: Number(i.discount) || 0,
                    })));
            },

            fmt(n) {
                return (Number(n) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
        }));

        // ── Invoice form state: line items + header discount + totals ──────
        Alpine.data('invoiceForm', (config) => ({
            items: config.items || [],
            headerDiscount: config.headerDiscount || 0,
            taxRate: config.taxRate || 0,
            status: config.status || 'awaiting_payment',
            subtotal: 0,
            discountAmount: 0,
            taxAmount: 0,
            total: 0,

            variantModalOpen: false,
            variantProduct: null,
            variantSelections: [],

            init() {
                this.calc();
                this.$watch('items', () => this.calc(), { deep: true });
                this.$watch('headerDiscount', () => this.calc());
                this.$watch('taxRate', () => this.calc());

                window.addEventListener('purchase-variant-open', (e) => this.openVariantModal(e.detail?.product));
            },

            calc() {
                this.subtotal = this.items.reduce((s, i) => s + (Number(i.quantity) || 0) * ((Number(i.unit_cost) || 0) - (Number(i.discount) || 0)), 0);
                this.discountAmount = Math.min(Number(this.headerDiscount) || 0, this.subtotal);
                const taxable = Math.max(this.subtotal - this.discountAmount, 0);
                this.taxAmount = taxable * ((Number(this.taxRate) || 0) / 100);
                this.total = taxable + this.taxAmount;
            },

            addItem() {
                this.items.push({ product_id: null, variant_id: null, purchase_order_item_id: null, name: '', sku: '', quantity: 1, unit_cost: 0, discount: 0, variants: [] });
            },

            removeItem(row) {
                const i = this.items.indexOf(row);
                if (i > -1) this.items.splice(i, 1);
            },

            openVariantModal(product) {
                if (!product || !product.variants?.length) return;
                this.variantProduct = product;
                this.variantSelections = product.variants.map(v => ({ variant: v, qty: 0 }));
                this.variantModalOpen = true;
            },

            changeVariantQty(index, delta) {
                const s = this.variantSelections[index];
                const next = s.qty + delta;
                if (next < 0) return;
                s.qty = next;
            },

            addVariantSelections() {
                const p = this.variantProduct;
                if (!p) return;
                this.variantSelections.forEach(s => {
                    if (s.qty > 0) {
                        this.items.push({
                            product_id: p.id,
                            variant_id: s.variant.id,
                            purchase_order_item_id: null,
                            name: p.name + ' — ' + s.variant.name,
                            sku: s.variant.sku || p.sku || '',
                            quantity: s.qty,
                            unit_cost: s.variant.cost ?? p.cost ?? 0,
                            discount: 0,
                            variants: [],
                        });
                    }
                });
                this.variantModalOpen = false;
                this.variantProduct = null;
                this.variantSelections = [];
            },

            variantSelectionTotal() {
                return this.variantSelections.reduce((sum, s) => sum + s.qty * (s.variant.cost ?? 0), 0);
            },

            variantSelectionCount() {
                return this.variantSelections.reduce((sum, s) => sum + s.qty, 0);
            },

            itemsJson() {
                return JSON.stringify(this.items
                    .filter(i => i.product_id)
                    .map(i => ({
                        product_id: i.product_id,
                        variant_id: i.variant_id || null,
                        purchase_order_item_id: i.purchase_order_item_id || null,
                        name: i.name || '',
                        sku: i.sku || '',
                        quantity: Number(i.quantity) || 0,
                        unit_cost: Number(i.unit_cost) || 0,
                        discount: Number(i.discount) || 0,
                    })));
            },

            fmt(n) {
                return (Number(n) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
        }));

        // ── Per-row product picker (combobox with live search) ─────────────
        Alpine.data('productPicker', (row, remove, variantModal) => ({
            row,
            remove,
            variantModal,
            query: row.name || '',
            open: false,
            loading: false,
            results: [],

            async search() {
                const q = this.query.trim();
                this.loading = true;
                this.open = true;
                try {
                    const res = await fetch(PURCHASE_PRODUCTS_URL + '?search=' + encodeURIComponent(q));
                    const json = await res.json();
                    this.results = json.data || [];
                } catch (e) {
                    this.results = [];
                } finally {
                    this.loading = false;
                }
            },

            select(p) {
                if (this.variantModal && p.has_variants && p.variants && p.variants.length) {
                    if (this.remove && typeof this.remove === 'function') this.remove(this.row);
                    window.dispatchEvent(new CustomEvent('purchase-variant-open', { detail: { product: p } }));
                    return;
                }
                this.row.product_id = p.id;
                this.row.name = p.name;
                this.row.sku = p.sku || '';
                this.row.unit_cost = p.cost || 0;
                this.row.variants = p.variants || [];
                this.row.variant_id = null;
                this.row.purchase_order_item_id = p.purchase_order_item_id || null;
                this.query = p.name;
                this.open = false;
                this.results = [];
            },

            selectVariant() {
                const v = this.row.variants.find(x => String(x.id) === String(this.row.variant_id));
                if (v) {
                    this.row.name = v.name;
                    this.row.sku = v.sku || '';
                    this.row.unit_cost = v.cost || 0;
                }
            },
        }));

        // ── Supplier picker (combobox) ──────────────────────────────────────
        Alpine.data('supplierPicker', (initial) => ({
            query: (initial && initial.name) || '',
            selectedId: (initial && initial.id) || null,
            open: false,
            loading: false,
            results: [],

            async search() {
                const q = this.query.trim();
                this.open = true;
                this.loading = true;
                try {
                    const res = await fetch(PURCHASE_SUPPLIERS_URL + '?search=' + encodeURIComponent(q));
                    const json = await res.json();
                    this.results = json.data || [];
                } catch (e) {
                    this.results = [];
                } finally {
                    this.loading = false;
                }
            },

            select(s) {
                this.selectedId = s.id;
                this.query = s.name;
                this.open = false;
                this.results = [];
            },
        }));

        // ── Supplier payment form: supplier picker + linked open invoices ──
        Alpine.data('paymentForm', (config) => ({
            supplierQuery: (config.supplier && config.supplier.name) || '',
            supplierId: (config.supplier && config.supplier.id) || null,
            supplierOpen: false,
            supplierLoading: false,
            supplierResults: [],
            invoices: [],
            invoiceId: (config.invoiceId || config.invoice && config.invoice.id) || '',
            invoiceLoading: false,

            init() {
                if (this.supplierId) this.loadInvoices();
            },

            async searchSuppliers() {
                const q = this.supplierQuery.trim();
                this.supplierOpen = true;
                if (q.length < 1) { this.supplierResults = []; return; }
                this.supplierLoading = true;
                try {
                    const res = await fetch(PURCHASE_SUPPLIERS_URL + '?search=' + encodeURIComponent(q));
                    const json = await res.json();
                    this.supplierResults = json.data || [];
                } catch (e) {
                    this.supplierResults = [];
                } finally {
                    this.supplierLoading = false;
                }
            },

            selectSupplier(s) {
                this.supplierId = s.id;
                this.supplierQuery = s.name;
                this.supplierOpen = false;
                this.supplierResults = [];
                this.invoiceId = '';
                this.loadInvoices();
            },

            async loadInvoices() {
                this.invoices = [];
                if (!this.supplierId) return;
                this.invoiceLoading = true;
                try {
                    const res = await fetch(PURCHASE_OPEN_INVOICES_URL + '?supplier_id=' + this.supplierId);
                    const json = await res.json();
                    this.invoices = json.data || [];
                } catch (e) {
                    this.invoices = [];
                } finally {
                    this.invoiceLoading = false;
                }
            },
        }));
    });
</script>
@endpush
