<div>
    <nav aria-label="Breadcrumb" class="mb-4 text-sm">
        <ol class="flex flex-wrap items-center gap-2 text-rose-900">
            <li><a href="{{ route('admin.users') }}" class="focus-ring rounded font-semibold text-pink-900 underline underline-offset-2">Manajemen Pengguna</a></li>
            <li aria-hidden="true">/</li>
            <li aria-current="page" class="font-semibold text-rose-950">Stok {{ $user->name }}</li>
        </ol>
    </nav>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-rose-950">Stok Pengguna: {{ $user->name }}</h1>
            <p class="mt-1 text-sm text-rose-900">
                <span class="font-mono">&#64;{{ $user->username }}</span> &middot; Tampilan hanya baca. Admin tidak dapat mengubah atau menghapus stok pengguna lain.
            </p>
        </div>
        <button type="button" wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel" class="btn-primary">
            <svg class="h-4 w-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            <span wire:loading.remove wire:target="exportExcel">Export Excel</span>
            <span wire:loading wire:target="exportExcel" role="status">Menyiapkan berkas...</span>
        </button>
    </div>

    <form role="search" aria-label="Cari dan saring stok pengguna" wire:submit.prevent class="card mb-6 grid gap-4 p-4 sm:grid-cols-[1fr_16rem]">
        <div>
            <label for="admin-stock-search" class="field-label">Cari nama, SKU, atau deskripsi</label>
            <input wire:model.live.debounce.300ms="search" type="search" id="admin-stock-search" class="field-input" autocomplete="off" placeholder="Ketik kata kunci">
        </div>
        <div>
            <label for="admin-stock-status" class="field-label">Status stok</label>
            <select wire:model.live="statusFilter" id="admin-stock-status" class="field-input">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <p class="sr-only" role="status" aria-live="polite">Menampilkan {{ $stocks->count() }} dari {{ $stocks->total() }} stok.</p>

    <div class="card overflow-hidden">
        <div class="relative overflow-x-auto" tabindex="0" role="region" aria-label="Tabel stok pengguna (dapat digulir)">
            <table class="min-w-full divide-y divide-pink-200">
                <caption class="sr-only">Daftar stok milik {{ $user->name }} (hanya baca)</caption>
                <thead class="bg-pink-100">
                    <tr>
                        <th scope="col" class="th">No</th>
                        <th scope="col" class="th">Barang</th>
                        <th scope="col" class="th text-right">Jumlah</th>
                        <th scope="col" class="th text-right">Harga</th>
                        <th scope="col" class="th text-right">Stok Min.</th>
                        <th scope="col" class="th">Status</th>
                        <th scope="col" class="th">Dibuat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-pink-100">
                    @forelse ($stocks as $index => $stock)
                        <tr wire:key="admin-stock-{{ $stock->id }}">
                            <td class="td">{{ $stocks->firstItem() + $index }}</td>
                            <th scope="row" class="td text-left font-semibold">
                                {{ $stock->name }}
                                @if ($stock->sku)<span class="block font-mono text-xs font-normal text-rose-900">SKU: {{ $stock->sku }}</span>@endif
                            </th>
                            <td class="td text-right font-bold">{{ number_format($stock->quantity, 0, ',', '.') }} {{ $stock->unit }}</td>
                            <td class="td text-right">{{ $stock->price !== null ? 'Rp '.number_format((float) $stock->price, 0, ',', '.') : '-' }}</td>
                            <td class="td text-right">{{ number_format($stock->min_stock, 0, ',', '.') }}</td>
                            <td class="td"><x-stock-status-badge :stock="$stock" /></td>
                            <td class="td"><time datetime="{{ $stock->created_at?->toIso8601String() }}">{{ $stock->created_at ? $stock->created_at->format('d M Y H:i') : '-' }}</time></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="td py-10 text-center text-rose-900">Pengguna ini belum memiliki stok barang.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($stocks->hasPages())
            <div class="border-t border-pink-200 p-4">{{ $stocks->links() }}</div>
        @endif
    </div>
</div>
