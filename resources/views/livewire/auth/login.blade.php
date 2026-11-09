<div class="mx-auto my-8 w-full max-w-md">
    <div class="card p-6 sm:p-8">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-pink-100 text-pink-800 shadow-[0_8px_18px_-8px_rgba(190,24,93,0.6)] ring-4 ring-pink-200" aria-hidden="true">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
            </div>
            <h1 id="login-heading" class="text-2xl font-extrabold text-rose-950">Masuk ke Akun Anda</h1>
            <p class="mt-1 text-sm text-rose-900">Kelola stok barang Anda secara teratur dan akurat</p>
        </div>

        <form wire:submit.prevent="authenticate" class="space-y-5" aria-labelledby="login-heading" novalidate>
            @error('login')
                <div id="login-form-error" role="alert" class="rounded-xl border-2 border-rose-800 bg-rose-50 p-3 text-sm font-semibold text-rose-950">
                    <span class="sr-only">Kesalahan: </span>{{ $message }}
                </div>
            @enderror

            <div>
                <label for="login-username" class="field-label">Username atau Email</label>
                <input wire:model="login" type="text" id="login-username" name="login"
                       class="field-input" placeholder="username atau nama@email.com"
                       autocomplete="username" autocapitalize="none" spellcheck="false" required
                       @error('login') aria-invalid="true" aria-describedby="login-form-error" @enderror>
            </div>

            <div>
                <label for="login-password" class="field-label">Password</label>
                <input wire:model="password" type="password" id="login-password" name="password"
                       class="field-input" placeholder="Masukkan password"
                       autocomplete="current-password" required
                       @error('password') aria-invalid="true" aria-describedby="login-password-error" @enderror>
                @error('password') <span id="login-password-error" class="field-error" role="alert">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="login-remember" class="flex min-h-[44px] cursor-pointer items-center gap-3 text-sm text-rose-950">
                    <input wire:model="remember" type="checkbox" id="login-remember" class="focus-ring h-5 w-5 rounded border-pink-600 text-pink-800">
                    <span>Ingat Saya</span>
                </label>
            </div>

            <button type="submit" id="login-submit" wire:loading.attr="disabled" wire:target="authenticate" class="btn-primary w-full">
                <svg wire:loading wire:target="authenticate" class="h-4 w-4 animate-spin" aria-hidden="true" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span wire:loading.remove wire:target="authenticate">Masuk</span>
                <span wire:loading wire:target="authenticate" role="status">Memverifikasi...</span>
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-rose-950">
            Belum memiliki akun?
            <a href="{{ route('register') }}" class="focus-ring rounded font-bold text-pink-900 underline underline-offset-2 hover:text-pink-950">Daftar sekarang</a>
        </p>
    </div>
</div>
