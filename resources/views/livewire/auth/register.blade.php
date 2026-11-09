<div class="mx-auto my-8 w-full max-w-md">
    <div class="card p-6 sm:p-8">
        <div class="mb-6 text-center">
            <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-pink-100 text-pink-800 shadow-[0_8px_18px_-8px_rgba(190,24,93,0.6)] ring-4 ring-pink-200" aria-hidden="true">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
            </div>
            <h1 id="register-heading" class="text-2xl font-extrabold text-rose-950">Buat Akun Baru</h1>
            <p class="mt-1 text-sm text-rose-900">Daftar untuk mulai mengelola stok barang Anda</p>
        </div>

        @if ($errors->any())
            <div role="alert" class="mb-5 rounded-xl border-2 border-rose-800 bg-rose-50 p-3 text-sm text-rose-950">
                <p class="font-bold">Mohon periksa {{ $errors->count() }} isian berikut:</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form wire:submit.prevent="register" class="space-y-5" aria-labelledby="register-heading" novalidate>
            <div>
                <label for="register-name" class="field-label">Nama Lengkap</label>
                <input wire:model="name" type="text" id="register-name" name="name" class="field-input"
                       placeholder="Nama Anda" autocomplete="name" required
                       @error('name') aria-invalid="true" aria-describedby="register-name-error" @enderror>
                @error('name') <span id="register-name-error" class="field-error">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="register-username" class="field-label">Username</label>
                <input wire:model="username" type="text" id="register-username" name="username" class="field-input"
                       placeholder="username_anda" autocomplete="username" autocapitalize="none" spellcheck="false" required
                       aria-describedby="register-username-hint @error('username') register-username-error @enderror"
                       @error('username') aria-invalid="true" @enderror>
                <span id="register-username-hint" class="field-hint">Minimal 3 karakter: huruf, angka, strip (-), atau garis bawah (_).</span>
                @error('username') <span id="register-username-error" class="field-error">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="register-email" class="field-label">Email</label>
                <input wire:model="email" type="email" id="register-email" name="email" class="field-input"
                       placeholder="nama@email.com" autocomplete="email" required
                       @error('email') aria-invalid="true" aria-describedby="register-email-error" @enderror>
                @error('email') <span id="register-email-error" class="field-error">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="register-password" class="field-label">Password</label>
                <input wire:model="password" type="password" id="register-password" name="password" class="field-input"
                       autocomplete="new-password" required
                       aria-describedby="register-password-hint @error('password') register-password-error @enderror"
                       @error('password') aria-invalid="true" @enderror>
                <span id="register-password-hint" class="field-hint">Minimal 8 karakter.</span>
                @error('password') <span id="register-password-error" class="field-error">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="register-password-confirmation" class="field-label">Konfirmasi Password</label>
                <input wire:model="password_confirmation" type="password" id="register-password-confirmation" name="password_confirmation"
                       class="field-input" autocomplete="new-password" required>
            </div>

            <button type="submit" id="register-submit" wire:loading.attr="disabled" wire:target="register" class="btn-primary w-full">
                <span wire:loading.remove wire:target="register">Daftar</span>
                <span wire:loading wire:target="register" role="status">Memproses...</span>
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-rose-950">
            Sudah memiliki akun?
            <a href="{{ route('login') }}" class="focus-ring rounded font-bold text-pink-900 underline underline-offset-2 hover:text-pink-950">Masuk di sini</a>
        </p>
    </div>
</div>
