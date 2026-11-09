@php
    $deletingStock = $confirmingStockDeletionId ? \App\Features\Stock\Models\Stock::find($confirmingStockDeletionId) : null;
@endphp
<div>
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-rose-950">Daftar Stok Saya</h1>
            <p class="mt-1 text-sm text-rose-900">Kelola persediaan barang Anda. Status stok menyesuaikan jumlah dibanding stok minimum.</p>
        </div>

        <div class="flex flex-wrap gap-2" role="group" aria-label="Aksi stok">
            <button type="button" wire:click="openCreateModal" class="btn-primary">
                <svg class="h-4 w-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Stok
            </button>
            <button type="button" wire:click="openAiModal" class="btn-secondary">
                <svg class="h-4 w-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                Generate dengan AI
            </button>
            <button type="button" wire:click="openImportModal" class="btn-secondary">
                <svg class="h-4 w-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M16 8l-4-4m0 0L8 8m4-4v12"/></svg>
                Import Excel
            </button>
            <button type="button" wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel" class="btn-secondary">
                <svg class="h-4 w-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span wire:loading.remove wire:target="exportExcel">Export Excel</span>
                <span wire:loading wire:target="exportExcel" role="status">Menyiapkan berkas...</span>
            </button>
        </div>
    </div>

    <form role="search" aria-label="Cari dan saring stok" wire:submit.prevent class="card mb-6 grid gap-4 p-4 sm:grid-cols-[1fr_16rem]">
        <div>
            <label for="stock-search" class="field-label">Cari nama, SKU, atau deskripsi</label>
            <input wire:model.live.debounce.300ms="search" type="search" id="stock-search" name="search" class="field-input" autocomplete="off" placeholder="Ketik kata kunci">
        </div>
        <div>
            <label for="stock-status-filter" class="field-label">Status stok</label>
            <select wire:model.live="statusFilter" id="stock-status-filter" name="status" class="field-input">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <p class="sr-only" role="status" aria-live="polite">Menampilkan {{ $stocks->count() }} dari {{ $stocks->total() }} stok.</p>

    <div class="card overflow-hidden">
        @if ($stocks->isEmpty())
            <div class="px-6 py-14 text-center">
                <svg class="mx-auto h-12 w-12 text-rose-800" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <h2 class="mt-3 text-base font-bold text-rose-950">Belum ada stok ditemukan</h2>
                <p class="mt-1 text-sm text-rose-900">Tambahkan barang baru, impor dari Excel, atau generate otomatis menggunakan AI.</p>
            </div>
        @else
            <div class="relative overflow-x-auto" tabindex="0" role="region" aria-label="Tabel stok barang (dapat digulir)">
                <table class="min-w-full divide-y divide-pink-200">
                    <caption class="sr-only">Daftar stok barang milik Anda</caption>
                    <thead class="bg-pink-100">
                        <tr>
                            <th scope="col" class="th">Barang</th>
                            <th scope="col" class="th text-center">Jumlah</th>
                            <th scope="col" class="th text-right">Harga</th>
                            <th scope="col" class="th text-right">Stok Min.</th>
                            <th scope="col" class="th">Status</th>
                            <th scope="col" class="th"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-pink-100">
                        @foreach ($stocks as $stock)
                            <tr wire:key="stock-{{ $stock->id }}">
                                <th scope="row" class="td text-left">
                                    <a href="{{ route('stocks.show', $stock->id) }}" class="focus-ring rounded font-bold text-pink-900 underline underline-offset-2 hover:text-pink-950">{{ $stock->name }}</a>
                                    @if ($stock->sku)<span class="block font-mono text-xs font-normal text-rose-900">SKU: {{ $stock->sku }}</span>@endif
                                </th>
                                <td class="td">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" wire:click="adjustQuantity({{ $stock->id }}, -1)" wire:loading.attr="disabled" class="btn-icon"
                                                aria-label="Kurangi 1 jumlah {{ $stock->name }}" @disabled($stock->quantity <= 0)>
                                            <svg class="h-4 w-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                                        </button>
                                        <span class="min-w-[5.5rem] text-center text-sm font-bold tabular-nums">{{ number_format($stock->quantity, 0, ',', '.') }} {{ $stock->unit }}</span>
                                        <button type="button" wire:click="adjustQuantity({{ $stock->id }}, 1)" wire:loading.attr="disabled" class="btn-icon"
                                                aria-label="Tambah 1 jumlah {{ $stock->name }}">
                                            <svg class="h-4 w-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="td text-right tabular-nums">{{ $stock->price !== null ? 'Rp '.number_format((float) $stock->price, 0, ',', '.') : '-' }}</td>
                                <td class="td text-right tabular-nums">{{ number_format($stock->min_stock, 0, ',', '.') }}</td>
                                <td class="td"><x-stock-status-badge :stock="$stock" /></td>
                                <td class="td">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('stocks.show', $stock->id) }}" class="btn-secondary">Detail<span class="sr-only"> {{ $stock->name }}</span></a>
                                        <button type="button" wire:click="openEditModal({{ $stock->id }})" class="btn-secondary">Edit<span class="sr-only"> {{ $stock->name }}</span></button>
                                        <button type="button" wire:click="confirmDelete({{ $stock->id }})" class="btn-danger-outline">Hapus<span class="sr-only"> {{ $stock->name }}</span></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($stocks->hasPages())
            <div class="border-t border-pink-200 p-4">{{ $stocks->links() }}</div>
        @endif
    </div>

    {{-- ===================== Dialog: Tambah / Edit Stok ===================== --}}
    @if ($showFormModal)
        <div class="fixed inset-0 z-[60] flex items-end justify-center p-0 sm:items-center sm:p-4"
             x-data x-trap.noscroll="true" @keydown.escape.window="$wire.closeFormModal()">
            <div class="absolute inset-0 bg-rose-950/60" aria-hidden="true" wire:click="closeFormModal"></div>
            <div role="dialog" aria-modal="true" aria-labelledby="form-modal-title" class="relative max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-t-2xl bg-white shadow-xl sm:rounded-3xl">
                <form wire:submit.prevent="saveStock" novalidate>
                    <div class="flex items-center justify-between border-b border-pink-200 px-6 py-4">
                        <h2 id="form-modal-title" class="text-lg font-extrabold text-rose-950">{{ $editingStockId ? 'Edit Stok' : 'Tambah Stok Baru' }}</h2>
                        <button type="button" wire:click="closeFormModal" class="btn-icon" aria-label="Tutup dialog">
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
                            <label for="form-name" class="field-label">Nama barang <span class="text-rose-800" aria-hidden="true">*</span><span class="sr-only">(wajib)</span></label>
                            <input wire:model="formName" type="text" id="form-name" class="field-input" required autofocus
                                   @error('formName') aria-invalid="true" @enderror>
                            @error('formName') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="form-sku" class="field-label">Kode SKU</label>
                            <input wire:model="formSku" type="text" id="form-sku" class="field-input" autocomplete="off"
                                   @error('formSku') aria-invalid="true" @enderror>
                            @error('formSku') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="form-unit" class="field-label">Satuan <span class="text-rose-800" aria-hidden="true">*</span><span class="sr-only">(wajib)</span></label>
                            <input wire:model="formUnit" type="text" id="form-unit" class="field-input" required aria-describedby="form-unit-hint"
                                   @error('formUnit') aria-invalid="true" @enderror>
                            <span id="form-unit-hint" class="field-hint">Contoh: pcs, kg, box.</span>
                            @error('formUnit') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="form-quantity" class="field-label">Jumlah <span class="text-rose-800" aria-hidden="true">*</span><span class="sr-only">(wajib)</span></label>
                            <input wire:model="formQuantity" type="number" min="0" step="1" inputmode="numeric" id="form-quantity" class="field-input" required
                                   @error('formQuantity') aria-invalid="true" @enderror>
                            @error('formQuantity') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="form-min-stock" class="field-label">Stok minimum <span class="text-rose-800" aria-hidden="true">*</span><span class="sr-only">(wajib)</span></label>
                            <input wire:model="formMinStock" type="number" min="0" step="1" inputmode="numeric" id="form-min-stock" class="field-input" required aria-describedby="form-min-hint"
                                   @error('formMinStock') aria-invalid="true" @enderror>
                            <span id="form-min-hint" class="field-hint">Status menjadi "Stok Menipis" bila jumlah sama dengan atau di bawah angka ini.</span>
                            @error('formMinStock') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="form-price" class="field-label">Harga satuan (Rp)</label>
                            <input wire:model="formPrice" type="number" min="0" step="0.01" inputmode="decimal" id="form-price" class="field-input"
                                   @error('formPrice') aria-invalid="true" @enderror>
                            @error('formPrice') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="form-description" class="field-label">Deskripsi</label>
                            <textarea wire:model="formDescription" id="form-description" rows="4" class="field-input" aria-describedby="form-desc-hint"></textarea>
                            <span id="form-desc-hint" class="field-hint">Boleh memakai format Markdown. Editor lengkap tersedia di halaman detail stok.</span>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-2 border-t border-pink-200 px-6 py-4 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="closeFormModal" class="btn-secondary">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="saveStock" class="btn-primary">
                            <span wire:loading.remove wire:target="saveStock">Simpan</span>
                            <span wire:loading wire:target="saveStock" role="status">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ===================== Dialog: Konfirmasi hapus ===================== --}}
    @if ($confirmingStockDeletionId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4"
             x-data x-trap.noscroll="true" @keydown.escape.window="$wire.cancelDelete()">
            <div class="absolute inset-0 bg-rose-950/60" aria-hidden="true" wire:click="cancelDelete"></div>
            <div role="alertdialog" aria-modal="true" aria-labelledby="delete-modal-title" aria-describedby="delete-modal-desc" class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-xl">
                <h2 id="delete-modal-title" class="text-lg font-extrabold text-rose-950">Hapus stok ini?</h2>
                <p id="delete-modal-desc" class="mt-2 text-sm text-rose-950">
                    @if ($deletingStock)
                        Stok <strong>{{ $deletingStock->name }}</strong> akan dihapus permanen beserta foto sampulnya.
                    @else
                        Stok akan dihapus permanen beserta foto sampulnya.
                    @endif
                    Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="cancelDelete" class="btn-secondary" autofocus>Batal</button>
                    <button type="button" wire:click="deleteStock" wire:loading.attr="disabled" wire:target="deleteStock" class="btn-danger">
                        <span wire:loading.remove wire:target="deleteStock">Ya, hapus</span>
                        <span wire:loading wire:target="deleteStock" role="status">Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===================== Dialog: Import Excel ===================== --}}
    @if ($showImportModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4"
             x-data x-trap.noscroll="true" @keydown.escape.window="$wire.closeImportModal()">
            <div class="absolute inset-0 bg-rose-950/60" aria-hidden="true" wire:click="closeImportModal"></div>
            <div role="dialog" aria-modal="true" aria-labelledby="import-modal-title" class="relative w-full max-w-lg rounded-3xl bg-white shadow-xl">
                <form wire:submit.prevent="importExcel" novalidate>
                    <div class="flex items-center justify-between border-b border-pink-200 px-6 py-4">
                        <h2 id="import-modal-title" class="text-lg font-extrabold text-rose-950">Import Stok dari Excel</h2>
                        <button type="button" wire:click="closeImportModal" class="btn-icon" aria-label="Tutup dialog">
                            <svg class="h-5 w-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="space-y-4 px-6 py-5">
                        <p class="text-sm text-rose-950">
                            Gunakan berkas hasil Export Excel sebagai templat. Urutan kolom: No, Name, SKU, Description, Quantity, Unit, Price, Min Stock. Baris pertama adalah judul kolom.
                        </p>
                        <div>
                            <label for="import-file" class="field-label">Berkas Excel (.xlsx, .xls, .csv; maks. 10 MB)</label>
                            <input wire:model="importFile" type="file" id="import-file" accept=".xlsx,.xls,.csv"
                                   class="focus-ring block w-full rounded-xl border border-pink-600 bg-white text-sm text-rose-950 file:mr-4 file:min-h-[44px] file:cursor-pointer file:border-0 file:bg-pink-100 file:px-4 file:py-2 file:font-semibold file:text-rose-950"
                                   @error('importFile') aria-invalid="true" aria-describedby="import-file-error" @enderror>
                            @error('importFile') <span id="import-file-error" class="field-error" role="alert">{{ $message }}</span> @enderror
                            <p wire:loading wire:target="importFile" class="field-hint" role="status">Mengunggah berkas...</p>
                        </div>
                    </div>
                    <div class="flex flex-col-reverse gap-2 border-t border-pink-200 px-6 py-4 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="closeImportModal" class="btn-secondary">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="importExcel,importFile" class="btn-primary">
                            <span wire:loading.remove wire:target="importExcel">Import</span>
                            <span wire:loading wire:target="importExcel" role="status">Mengimpor...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ===================== Dialog: Generate dengan AI ===================== --}}
    @if ($showAiModal)
        <div class="fixed inset-0 z-[60] flex items-end justify-center p-0 sm:items-center sm:p-4"
             x-data x-trap.noscroll="true" @keydown.escape.window="$wire.closeAiModal()">
            <div class="absolute inset-0 bg-rose-950/60" aria-hidden="true" wire:click="closeAiModal"></div>
            <div role="dialog" aria-modal="true" aria-labelledby="ai-modal-title" class="relative max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-t-2xl bg-white shadow-xl sm:rounded-3xl">
                <div class="flex items-center justify-between border-b border-pink-200 px-6 py-4">
                    <h2 id="ai-modal-title" class="text-lg font-extrabold text-rose-950">Generate Item Stok dengan AI</h2>
                    <button type="button" wire:click="closeAiModal" class="btn-icon" aria-label="Tutup dialog">
                        <svg class="h-5 w-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-4 px-6 py-5">
                    @if ($aiErrorMessage)
                        <div role="alert" class="rounded-xl border-2 border-rose-800 bg-rose-50 p-3 text-sm font-semibold text-rose-950">
                            <span class="sr-only">Kesalahan: </span>{{ $aiErrorMessage }}
                        </div>
                    @endif

                    <form wire:submit.prevent="generateWithAi" class="grid gap-4 sm:grid-cols-[1fr_8rem]" novalidate>
                        <div>
                            <label for="ai-prompt" class="field-label">Deskripsikan kebutuhan inventaris Anda</label>
                            <textarea wire:model="aiPrompt" id="ai-prompt" rows="3" class="field-input" autofocus
                                      placeholder="Contoh: perlengkapan kantor untuk startup kecil"
                                      @error('aiPrompt') aria-invalid="true" aria-describedby="ai-prompt-error" @enderror></textarea>
                            @error('aiPrompt') <span id="ai-prompt-error" class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="ai-count" class="field-label">Jumlah item</label>
                            <input wire:model="aiCount" type="number" min="1" max="10" id="ai-count" class="field-input"
                                   @error('aiCount') aria-invalid="true" aria-describedby="ai-count-error" @enderror>
                            @error('aiCount') <span id="ai-count-error" class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <button type="submit" wire:loading.attr="disabled" wire:target="generateWithAi" class="btn-primary">
                                <span wire:loading.remove wire:target="generateWithAi">Generate</span>
                                <span wire:loading wire:target="generateWithAi" role="status">Sedang membuat item...</span>
                            </button>
                        </div>
                    </form>

                    @if (!empty($aiGeneratedItems))
                        <form wire:submit.prevent="saveAiGenerated">
                            <fieldset>
                                <legend class="field-label">Pilih item yang akan disimpan ({{ count($aiGeneratedItems) }} hasil)</legend>
                                <ul class="mt-2 space-y-2">
                                    @foreach ($aiGeneratedItems as $index => $item)
                                        <li class="rounded-xl border border-pink-500 p-3" wire:key="ai-item-{{ $index }}">
                                            <label for="ai-item-{{ $index }}" class="flex cursor-pointer items-start gap-3">
                                                <input wire:model="aiSelectedIndices" type="checkbox" value="{{ $index }}" id="ai-item-{{ $index }}" class="focus-ring mt-1 h-5 w-5 rounded border-pink-600 text-pink-800">
                                                <span class="text-sm text-rose-950">
                                                    <strong class="block">{{ $item['name'] }}</strong>
                                                    <span class="block text-rose-900">
                                                        {{ number_format($item['quantity'] ?? 0, 0, ',', '.') }} {{ $item['unit'] ?? 'pcs' }}
                                                        &middot; Stok min. {{ $item['min_stock'] ?? 5 }}
                                                        @if (!empty($item['price'])) &middot; Rp {{ number_format((float) $item['price'], 0, ',', '.') }} @endif
                                                        @if (!empty($item['sku'])) &middot; SKU {{ $item['sku'] }} @endif
                                                    </span>
                                                    @if (!empty($item['description']))<span class="mt-1 block text-rose-900">{{ $item['description'] }}</span>@endif
                                                </span>
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                            </fieldset>
                            <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                                <button type="button" wire:click="closeAiModal" class="btn-secondary">Batal</button>
                                <button type="submit" wire:loading.attr="disabled" wire:target="saveAiGenerated" class="btn-primary">
                                    <span wire:loading.remove wire:target="saveAiGenerated">Simpan item terpilih</span>
                                    <span wire:loading wire:target="saveAiGenerated" role="status">Menyimpan...</span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
