<div>
    <div class="mb-6">
        <h1 class="text-2xl font-extrabold text-rose-950">Manajemen Pengguna</h1>
        <p class="mt-1 text-sm text-rose-900">Daftar semua pengguna terdaftar beserta jumlah stok barang mereka (hanya baca).</p>
    </div>

    <form role="search" aria-label="Cari pengguna" wire:submit.prevent class="card mb-6 p-4">
        <label for="user-search" class="field-label">Cari pengguna</label>
        <input wire:model.live.debounce.300ms="search" type="search" id="user-search" name="search" class="field-input sm:max-w-md"
               placeholder="Nama, username, atau email" autocomplete="off">
    </form>

    <p class="sr-only" role="status" aria-live="polite">Menampilkan {{ $users->count() }} dari {{ $users->total() }} pengguna.</p>

    <div class="card overflow-hidden">
        <div class="relative overflow-x-auto" tabindex="0" role="region" aria-label="Tabel daftar pengguna (dapat digulir)">
            <table class="min-w-full divide-y divide-pink-200">
                <caption class="sr-only">Daftar pengguna beserta peran dan jumlah stok</caption>
                <thead class="bg-pink-100">
                    <tr>
                        <th scope="col" class="th">No</th>
                        <th scope="col" class="th">Pengguna</th>
                        <th scope="col" class="th">Email</th>
                        <th scope="col" class="th">Peran</th>
                        <th scope="col" class="th text-center">Jumlah Stok</th>
                        <th scope="col" class="th"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-pink-100">
                    @forelse ($users as $index => $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td class="td">{{ $users->firstItem() + $index }}</td>
                            <th scope="row" class="td text-left font-semibold">
                                {{ $user->name }}
                                <span class="block font-mono text-xs font-normal text-rose-900">&#64;{{ $user->username }}</span>
                            </th>
                            <td class="td">{{ $user->email }}</td>
                            <td class="td">
                                <span class="inline-flex rounded border px-2 py-0.5 text-xs font-bold {{ $user->isAdmin() ? 'border-purple-800 bg-purple-100 text-purple-900' : 'border-emerald-800 bg-emerald-100 text-emerald-900' }}">{{ ucfirst($user->role) }}</span>
                            </td>
                            <td class="td text-center font-bold">{{ $user->stocks_count }}</td>
                            <td class="td text-right">
                                <a href="{{ route('admin.users.stocks', $user->id) }}" class="btn-secondary">
                                    Lihat Stok<span class="sr-only"> milik {{ $user->name }}</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="td py-10 text-center text-rose-900">Tidak ada pengguna yang cocok dengan pencarian Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-pink-200 p-4">{{ $users->links() }}</div>
        @endif
    </div>
</div>
