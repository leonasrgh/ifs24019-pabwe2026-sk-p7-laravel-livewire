@php
    $coverUrl = $stock->coverImageUrl();
    $descriptionHtml = filled($stock->description)
        ? \Illuminate\Support\Str::markdown($stock->description, ['html_input' => 'strip', 'allow_unsafe_links' => false])
        : null;
@endphp
<div>
    <nav aria-label="Breadcrumb" class="mb-4 text-sm">
        <ol class="flex flex-wrap items-center gap-2 text-rose-900">
            <li><a href="{{ route('stocks.index') }}" class="focus-ring rounded font-semibold text-pink-900 underline underline-offset-2">Stok Saya</a></li>
            <li aria-hidden="true">/</li>
            <li aria-current="page" class="font-semibold text-rose-950">{{ $stock->name }}</li>
        </ol>
    </nav>

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-rose-950">{{ $stock->name }}</h1>
            <p class="mt-2 flex flex-wrap items-center gap-3 text-sm text-rose-900">
                <x-stock-status-badge :stock="$stock" />
                @if ($stock->sku)<span class="font-mono">SKU: {{ $stock->sku }}</span>@endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2" role="group" aria-label="Aksi stok">
            <button type="button" wire:click="openEditModal" class="btn-primary">Edit Informasi</button>
            <button type="button" wire:click="confirmDeleteStock" class="btn-danger-outline">Hapus Stok</button>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Jumlah stok --}}
            <section aria-labelledby="qty-heading" class="card p-6">
                <h2 id="qty-heading" class="text-base font-extrabold text-rose-950">Jumlah Stok</h2>
                <div class="mt-4 flex flex-wrap items-center gap-4">
                    <button type="button" wire:click="adjustQuantity(-10)" wire:loading.attr="disabled" class="btn-secondary" aria-label="Kurangi 10 jumlah stok" @disabled($stock->quantity <= 0)>-10</button>
                    <button type="button" wire:click="adjustQuantity(-1)" wire:loading.attr="disabled" class="btn-icon" aria-label="Kurangi 1 jumlah stok" @disabled($stock->quantity <= 0)>
                        <svg class="h-4 w-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                    </button>
                    <p class="min-w-[8rem] text-center" aria-live="polite" aria-atomic="true">
                        <span class="text-4xl font-extrabold tabular-nums text-rose-950">{{ number_format($stock->quantity, 0, ',', '.') }}</span>
                        <span class="ml-1 text-base font-semibold text-rose-900">{{ $stock->unit }}</span>
                    </p>
                    <button type="button" wire:click="adjustQuantity(1)" wire:loading.attr="disabled" class="btn-icon" aria-label="Tambah 1 jumlah stok">
                        <svg class="h-4 w-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    </button>
                    <button type="button" wire:click="adjustQuantity(10)" wire:loading.attr="disabled" class="btn-secondary" aria-label="Tambah 10 jumlah stok">+10</button>
                </div>
                <p class="mt-3 text-sm text-rose-900">Status otomatis menjadi "Stok Menipis" saat jumlah &le; {{ $stock->min_stock }} dan "Habis" saat jumlah 0.</p>
            </section>

            {{-- Deskripsi --}}
            <section aria-labelledby="desc-heading" class="card p-6">
                <div class="flex items-center justify-between gap-4">
                    <h2 id="desc-heading" class="text-base font-extrabold text-rose-950">Deskripsi</h2>
                    <button type="button" wire:click="openEditModal" class="btn-secondary">Edit Deskripsi</button>
                </div>
                @if ($descriptionHtml)
                    <div class="markdown-body mt-4">{!! $descriptionHtml !!}</div>
                @else
                    <p class="mt-4 text-sm text-rose-900">Belum ada deskripsi. Pilih "Edit Deskripsi" untuk menambahkan catatan dalam format Markdown.</p>
                @endif
            </section>
        </div>

        <div class="space-y-6">
            {{-- Informasi --}}
            <section aria-labelledby="info-heading" class="card p-6">
                <h2 id="info-heading" class="text-base font-extrabold text-rose-950">Informasi Barang</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-rose-900">Harga satuan</dt><dd class="font-bold tabular-nums text-rose-950">{{ $stock->price !== null ? 'Rp '.number_format((float) $stock->price, 0, ',', '.') : '-' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-rose-900">Stok minimum</dt><dd class="font-bold tabular-nums text-rose-950">{{ number_format($stock->min_stock, 0, ',', '.') }} {{ $stock->unit }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-rose-900">Dibuat</dt><dd class="font-semibold text-rose-950"><time datetime="{{ $stock->created_at?->toIso8601String() }}">{{ $stock->created_at?->format('d M Y H:i') }}</time></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-rose-900">Diperbarui</dt><dd class="font-semibold text-rose-950"><time datetime="{{ $stock->updated_at?->toIso8601String() }}">{{ $stock->updated_at?->format('d M Y H:i') }}</time></dd></div>
                </dl>
            </section>

            {{-- Foto sampul --}}
            <section aria-labelledby="cover-heading" class="card p-6">
                <h2 id="cover-heading" class="text-base font-extrabold text-rose-950">Foto Sampul</h2>
                <div class="mt-4 overflow-hidden rounded-xl border border-pink-200 bg-pink-100">
                    @if ($coverUrl)
                        <img src="{{ $coverUrl }}" alt="Foto sampul {{ $stock->name }}" class="aspect-video w-full object-cover">
                    @else
                        <div class="flex aspect-video flex-col items-center justify-center p-4 text-center text-sm text-rose-900">
                            <svg class="mb-2 h-10 w-10 text-rose-800" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.6-4.6a2 2 0 012.8 0L16 16m-2-2l1.6-1.6a2 2 0 012.8 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Belum ada foto sampul
                        </div>
                    @endif
                </div>

                <div class="mt-4 space-y-3">
                    <div>
                        <label for="cover-photo" class="field-label">{{ $coverUrl ? 'Ganti foto sampul' : 'Unggah foto sampul' }}</label>
                        <input wire:model="coverPhoto" type="file" id="cover-photo" accept="image/*"
                               class="focus-ring block w-full rounded-xl border border-pink-600 bg-white text-sm text-rose-950 file:mr-4 file:min-h-[44px] file:cursor-pointer file:border-0 file:bg-pink-100 file:px-4 file:py-2 file:font-semibold file:text-rose-950"
                               aria-describedby="cover-hint @error('coverPhoto') cover-error @enderror" @error('coverPhoto') aria-invalid="true" @enderror>
                        <span id="cover-hint" class="field-hint">Format gambar, maksimal 5 MB.</span>
                        @error('coverPhoto') <span id="cover-error" class="field-error" role="alert">{{ $message }}</span> @enderror
                        <p wire:loading wire:target="coverPhoto" class="field-hint" role="status">Mengunggah foto...</p>
                    </div>
                    @if ($coverUrl)
                        <button type="button" wire:click="confirmDeleteCover" class="btn-danger-outline w-full">Hapus foto sampul</button>
                    @endif
                </div>
            </section>
        </div>
    </div>

    {{-- ===================== Dialog: Edit informasi ===================== --}}
    @if ($showEditModal)
        <div class="fixed inset-0 z-[60] flex items-end justify-center p-0 sm:items-center sm:p-4"
             x-data="{
                 rich: false,
                 editor: null,
                 notice: '',
                 loadScript(src) {
                     return new Promise((resolve, reject) => {
                         const s = document.createElement('script');
                         s.src = src; s.onload = resolve; s.onerror = reject;
                         document.head.appendChild(s);
                     });
                 },
                 async enableEditor() {
                     this.notice = '';
                     try {
                         if (!window.toastui) {
                             if (!document.getElementById('toastui-css')) {
                                 const l = document.createElement('link');
                                 l.id = 'toastui-css'; l.rel = 'stylesheet';
                                 l.href = 'https://uicdn.toast.com/editor/3.2.2/toastui-editor.min.css';
                                 document.head.appendChild(l);
                             }
                             await this.loadScript('https://uicdn.toast.com/editor/3.2.2/toastui-editor-all.min.js');
                         }
                         this.rich = true;
                         await this.$nextTick();
                         this.editor = new toastui.Editor({
                             el: this.$refs.editorHost,
                             height: '320px',
                             initialEditType: 'wysiwyg',
                             previewStyle: 'tab',
                             usageStatistics: false,
                             initialValue: this.$wire.description || '',
                             events: { change: () => { this.$wire.$set('description', this.editor.getMarkdown(), false); } }
                         });
                     } catch (e) {
                         this.rich = false;
                         this.notice = 'Editor Toast UI tidak dapat dimuat. Gunakan kolom teks Markdown di atas.';
                     }
                 },
                 disableEditor() {
                     if (this.editor) {
                         this.$wire.$set('description', this.editor.getMarkdown(), false);
                         this.editor.destroy();
                         this.editor = null;
                     }
                     this.rich = false;
                 }
             }"
             x-trap.noscroll="true" @keydown.escape.window="$wire.closeEditModal()">
            <div class="absolute inset-0 bg-rose-950/60" aria-hidden="true" wire:click="closeEditModal"></div>
            <div role="dialog" aria-modal="true" aria-labelledby="edit-modal-title" class="relative max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-t-2xl bg-white shadow-xl sm:rounded-3xl">
                <form wire:submit.prevent="saveStock" novalidate>
                    <div class="flex items-center justify-between border-b border-pink-200 px-6 py-4">
                        <h2 id="edit-modal-title" class="text-lg font-extrabold text-rose-950">Edit Informasi Stok</h2>
                        <button type="button" wire:click="closeEditModal" class="btn-icon" aria-label="Tutup dialog">
                            <svg class="h-5 w-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    @if ($errors->any())
                        <div role="alert" class="mx-6 mt-4 rounded-xl border-2 border-rose-800 bg-rose-50 p-3 text-sm text-rose-950">
                            <p class="font-bold">Mohon perbaiki {{ $errors->count() }} isian berikut:</p>
                            <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <div class="grid gap-4 px-6 py-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="edit-name" class="field-label">Nama barang <span class="text-rose-800" aria-hidden="true">*</span><span class="sr-only">(wajib)</span></label>
                            <input wire:model="name" type="text" id="edit-name" class="field-input" required autofocus @error('name') aria-invalid="true" @enderror>
                            @error('name') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="edit-sku" class="field-label">Kode SKU</label>
                            <input wire:model="sku" type="text" id="edit-sku" class="field-input" autocomplete="off" @error('sku') aria-invalid="true" @enderror>
                            @error('sku') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="edit-unit" class="field-label">Satuan <span class="text-rose-800" aria-hidden="true">*</span><span class="sr-only">(wajib)</span></label>
                            <input wire:model="unit" type="text" id="edit-unit" class="field-input" required @error('unit') aria-invalid="true" @enderror>
                            @error('unit') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="edit-quantity" class="field-label">Jumlah <span class="text-rose-800" aria-hidden="true">*</span><span class="sr-only">(wajib)</span></label>
                            <input wire:model="quantity" type="number" min="0" step="1" inputmode="numeric" id="edit-quantity" class="field-input" required @error('quantity') aria-invalid="true" @enderror>
                            @error('quantity') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="edit-min-stock" class="field-label">Stok minimum <span class="text-rose-800" aria-hidden="true">*</span><span class="sr-only">(wajib)</span></label>
                            <input wire:model="minStock" type="number" min="0" step="1" inputmode="numeric" id="edit-min-stock" class="field-input" required @error('minStock') aria-invalid="true" @enderror>
                            @error('minStock') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="edit-price" class="field-label">Harga satuan (Rp)</label>
                            <input wire:model="price" type="number" min="0" step="0.01" inputmode="decimal" id="edit-price" class="field-input" @error('price') aria-invalid="true" @enderror>
                            @error('price') <span class="field-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <div x-show="!rich">
                                <label for="edit-description" class="field-label">Deskripsi (Markdown)</label>
                                <textarea wire:model="description" id="edit-description" rows="6" class="field-input" aria-describedby="edit-desc-hint"></textarea>
                                <span id="edit-desc-hint" class="field-hint">Tulis dengan Markdown, misalnya <code>**tebal**</code> atau daftar berawalan <code>-</code>.</span>
                            </div>
                            <div x-show="rich" x-cloak>
                                <span class="field-label" id="edit-rich-label">Deskripsi (Toast UI Editor)</span>
                                <div wire:ignore x-ref="editorHost" role="group" aria-labelledby="edit-rich-label"></div>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-3">
                                <button type="button" class="btn-secondary" x-show="!rich" @click="enableEditor()">Gunakan Toast UI Editor</button>
                                <button type="button" class="btn-secondary" x-show="rich" x-cloak @click="disableEditor()">Kembali ke kolom teks biasa</button>
                                <p class="text-sm font-medium text-rose-800" role="alert" x-show="notice" x-text="notice" x-cloak></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-2 border-t border-pink-200 px-6 py-4 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="closeEditModal" class="btn-secondary">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveStock" class="btn-primary" @click="if (rich && editor) { $wire.$set('description', editor.getMarkdown(), false); }">
                            <span wire:loading.remove wire:target="saveStock">Simpan</span>
                            <span wire:loading wire:target="saveStock" role="status">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ===================== Dialog: Hapus stok ===================== --}}
    @if ($confirmingStockDeletion)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-data x-trap.noscroll="true" @keydown.escape.window="$wire.cancelDeleteStock()">
            <div class="absolute inset-0 bg-rose-950/60" aria-hidden="true" wire:click="cancelDeleteStock"></div>
            <div role="alertdialog" aria-modal="true" aria-labelledby="delete-stock-title" aria-describedby="delete-stock-desc" class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-xl">
                <h2 id="delete-stock-title" class="text-lg font-extrabold text-rose-950">Hapus stok ini?</h2>
                <p id="delete-stock-desc" class="mt-2 text-sm text-rose-950">Stok <strong>{{ $stock->name }}</strong> akan dihapus permanen beserta foto sampulnya. Tindakan ini tidak dapat dibatalkan.</p>
                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="cancelDeleteStock" class="btn-secondary" autofocus>Batal</button>
                    <button type="button" wire:click="deleteStock" wire:loading.attr="disabled" wire:target="deleteStock" class="btn-danger">
                        <span wire:loading.remove wire:target="deleteStock">Ya, hapus</span>
                        <span wire:loading wire:target="deleteStock" role="status">Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===================== Dialog: Hapus foto sampul ===================== --}}
    @if ($confirmingCoverDeletion)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-data x-trap.noscroll="true" @keydown.escape.window="$wire.cancelDeleteCover()">
            <div class="absolute inset-0 bg-rose-950/60" aria-hidden="true" wire:click="cancelDeleteCover"></div>
            <div role="alertdialog" aria-modal="true" aria-labelledby="delete-cover-title" aria-describedby="delete-cover-desc" class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-xl">
                <h2 id="delete-cover-title" class="text-lg font-extrabold text-rose-950">Hapus foto sampul?</h2>
                <p id="delete-cover-desc" class="mt-2 text-sm text-rose-950">Foto sampul stok ini akan dihapus. Data stok lainnya tidak berubah.</p>
                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="cancelDeleteCover" class="btn-secondary" autofocus>Batal</button>
                    <button type="button" wire:click="deleteCoverPhoto" wire:loading.attr="disabled" wire:target="deleteCoverPhoto" class="btn-danger">
                        <span wire:loading.remove wire:target="deleteCoverPhoto">Ya, hapus foto</span>
                        <span wire:loading wire:target="deleteCoverPhoto" role="status">Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
