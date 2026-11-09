@php
    $hasPhoto = $user->profile_photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile_photo_path);
@endphp
<div>
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-rose-950">Pengaturan Profil</h1>
        <p class="mt-1 text-sm text-rose-900">Kelola identitas diri, foto profil rasio 1:1, dan keamanan akun Anda.</p>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        {{-- Foto profil --}}
        <section aria-labelledby="photo-heading" class="card p-6">
            <h2 id="photo-heading" class="mb-4 text-base font-extrabold text-rose-950">Foto Profil (1:1)</h2>

            <div class="mx-auto mb-4 flex h-36 w-36 items-center justify-center overflow-hidden rounded-2xl border-2 border-pink-500 bg-pink-100">
                @if ($hasPhoto)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_photo_path) }}?v={{ time() }}" alt="Foto profil {{ $user->name }}" class="h-full w-full object-cover">
                @else
                    <span class="text-4xl font-extrabold text-rose-900" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                    <span class="sr-only">Belum ada foto profil</span>
                @endif
            </div>

            <div class="space-y-3">
                <div>
                    <label for="profile-photo" class="field-label">{{ $hasPhoto ? 'Ganti foto profil' : 'Unggah foto profil' }}</label>
                    <input wire:model="photo" type="file" id="profile-photo" accept="image/*"
                           class="focus-ring block w-full rounded-xl border border-pink-600 bg-white text-sm text-rose-950 file:mr-4 file:min-h-[44px] file:cursor-pointer file:border-0 file:bg-pink-100 file:px-4 file:py-2 file:font-semibold file:text-rose-950"
                           aria-describedby="profile-photo-hint @error('photo') profile-photo-error @enderror" @error('photo') aria-invalid="true" @enderror>
                    <span id="profile-photo-hint" class="field-hint">Format gambar, maksimal 5 MB.</span>
                    @error('photo') <span id="profile-photo-error" class="field-error" role="alert">{{ $message }}</span> @enderror
                    <p wire:loading wire:target="photo" class="field-hint" role="status">Mengunggah foto...</p>
                </div>

                @if ($hasPhoto)
                    <button type="button" wire:click="confirmDeletePhoto" class="btn-danger-outline w-full">Hapus foto profil</button>
                @endif
            </div>

            <p class="mt-4 text-sm text-rose-900">Setelah memilih foto, Anda dapat memotongnya ke rasio 1:1 atau memakainya apa adanya.</p>
        </section>

        <div class="space-y-8 lg:col-span-2">
            {{-- Informasi akun --}}
            <section aria-labelledby="info-heading" class="card p-6">
                <h2 id="info-heading" class="text-base font-extrabold text-rose-950">Informasi Akun</h2>
                <form wire:submit.prevent="updateProfileInformation" class="mt-4 space-y-4" novalidate>
                    <div>
                        <label for="profile-name" class="field-label">Nama Lengkap</label>
                        <input wire:model="name" type="text" id="profile-name" class="field-input" autocomplete="name" required
                               @error('name') aria-invalid="true" aria-describedby="profile-name-error" @enderror>
                        @error('name') <span id="profile-name-error" class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="profile-username" class="field-label">Username</label>
                            <input wire:model="username" type="text" id="profile-username" class="field-input" autocomplete="username" autocapitalize="none" required
                                   @error('username') aria-invalid="true" aria-describedby="profile-username-error" @enderror>
                            @error('username') <span id="profile-username-error" class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="profile-email" class="field-label">Email</label>
                            <input wire:model="email" type="email" id="profile-email" class="field-input" autocomplete="email" required
                                   @error('email') aria-invalid="true" aria-describedby="profile-email-error" @enderror>
                            @error('email') <span id="profile-email-error" class="field-error">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" wire:target="updateProfileInformation" class="btn-primary">
                            <span wire:loading.remove wire:target="updateProfileInformation">Simpan Perubahan</span>
                            <span wire:loading wire:target="updateProfileInformation" role="status">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </section>

            {{-- Keamanan --}}
            <section aria-labelledby="password-heading" class="card p-6">
                <h2 id="password-heading" class="text-base font-extrabold text-rose-950">Ubah Password</h2>
                <form wire:submit.prevent="updatePassword" class="mt-4 space-y-4" novalidate>
                    <div>
                        <label for="profile-current-password" class="field-label">Password Saat Ini</label>
                        <input wire:model="current_password" type="password" id="profile-current-password" class="field-input" autocomplete="current-password" required
                               @error('current_password') aria-invalid="true" aria-describedby="profile-current-password-error" @enderror>
                        @error('current_password') <span id="profile-current-password-error" class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="profile-new-password" class="field-label">Password Baru</label>
                            <input wire:model="password" type="password" id="profile-new-password" class="field-input" autocomplete="new-password" required aria-describedby="profile-new-password-hint @error('password') profile-new-password-error @enderror"
                                   @error('password') aria-invalid="true" @enderror>
                            <span id="profile-new-password-hint" class="field-hint">Minimal 8 karakter.</span>
                            @error('password') <span id="profile-new-password-error" class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="profile-password-confirmation" class="field-label">Konfirmasi Password Baru</label>
                            <input wire:model="password_confirmation" type="password" id="profile-password-confirmation" class="field-input" autocomplete="new-password" required>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" wire:loading.attr="disabled" wire:target="updatePassword" class="btn-primary">
                            <span wire:loading.remove wire:target="updatePassword">Perbarui Password</span>
                            <span wire:loading wire:target="updatePassword" role="status">Memperbarui...</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    {{-- ===================== Dialog: Potong foto ===================== --}}
    @if ($showCropModal && $photo)
        <div class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto p-4"
             wire:ignore.self
             x-data="{
                cropper: null,
                loadError: '',
                loadScript(src) {
                    return new Promise((resolve, reject) => {
                        const s = document.createElement('script');
                        s.src = src; s.onload = resolve; s.onerror = reject;
                        document.head.appendChild(s);
                    });
                },
                async initCropper() {
                    const img = document.getElementById('imageToCrop');
                    if (!img) return;
                    try {
                        if (!window.Cropper) {
                            if (!document.getElementById('cropper-css')) {
                                const l = document.createElement('link');
                                l.id = 'cropper-css'; l.rel = 'stylesheet';
                                l.href = 'https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css';
                                document.head.appendChild(l);
                            }
                            await this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js');
                        }
                        if (this.cropper) { this.cropper.destroy(); }
                        this.cropper = new Cropper(img, { aspectRatio: 1, viewMode: 1, autoCropArea: 0.9, responsive: true, background: false });
                    } catch (e) {
                        this.loadError = 'Alat potong foto tidak dapat dimuat. Gunakan tombol \'Pakai foto apa adanya\'.';
                    }
                },
                async applyCrop() {
                    if (!this.cropper) return;
                    const canvas = this.cropper.getCroppedCanvas({ width: 400, height: 400 });
                    await $wire.set('croppedPhoto', canvas.toDataURL('image/png'));
                    $wire.saveCroppedPhoto();
                }
             }"
             x-init="$nextTick(() => initCropper())"
             x-trap.noscroll="true" @keydown.escape.window="$wire.closeCropModal()">
            <div class="absolute inset-0 bg-rose-950/60" aria-hidden="true" wire:click="closeCropModal"></div>
            <div role="dialog" aria-modal="true" aria-labelledby="crop-title" aria-describedby="crop-desc" class="relative w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-pink-200 px-6 py-4">
                    <h2 id="crop-title" class="text-lg font-extrabold text-rose-950">Sesuaikan Foto Profil</h2>
                    <button type="button" wire:click="closeCropModal" class="btn-icon" aria-label="Tutup dialog">
                        <svg class="h-5 w-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-6 py-5">
                    <p id="crop-desc" class="mb-3 text-sm text-rose-950">Geser kotak potongan dengan penunjuk (mouse atau sentuh) untuk memotong ke rasio 1:1. Pengguna keyboard dapat memilih "Pakai foto apa adanya".</p>
                    <div class="flex max-h-80 items-center justify-center overflow-hidden rounded-xl bg-rose-950">
                        <img id="imageToCrop" src="{{ $photo->temporaryUrl() }}" alt="Pratinjau foto yang akan dipotong" class="block max-w-full">
                    </div>
                    <p class="mt-2 text-sm font-medium text-rose-800" role="alert" x-show="loadError" x-text="loadError" x-cloak></p>
                </div>
                <div class="flex flex-col-reverse gap-2 border-t border-pink-200 px-6 py-4 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="closeCropModal" class="btn-secondary">Batal</button>
                    <button type="button" wire:click="saveCroppedPhoto" wire:loading.attr="disabled" wire:target="saveCroppedPhoto" class="btn-secondary">Pakai foto apa adanya</button>
                    <button type="button" @click="applyCrop()" wire:loading.attr="disabled" wire:target="saveCroppedPhoto" class="btn-primary">
                        <span wire:loading.remove wire:target="saveCroppedPhoto">Terapkan &amp; Simpan</span>
                        <span wire:loading wire:target="saveCroppedPhoto" role="status">Menyimpan foto...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===================== Dialog: Hapus foto profil ===================== --}}
    @if ($confirmingPhotoDeletion)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-data x-trap.noscroll="true" @keydown.escape.window="$wire.cancelDeletePhoto()">
            <div class="absolute inset-0 bg-rose-950/60" aria-hidden="true" wire:click="cancelDeletePhoto"></div>
            <div role="alertdialog" aria-modal="true" aria-labelledby="delete-photo-title" aria-describedby="delete-photo-desc" class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-xl">
                <h2 id="delete-photo-title" class="text-lg font-extrabold text-rose-950">Hapus foto profil?</h2>
                <p id="delete-photo-desc" class="mt-2 text-sm text-rose-950">Foto akan dihapus dan avatar kembali ke inisial nama Anda.</p>
                <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="cancelDeletePhoto" class="btn-secondary" autofocus>Batal</button>
                    <button type="button" wire:click="deletePhoto" wire:loading.attr="disabled" wire:target="deletePhoto" class="btn-danger">
                        <span wire:loading.remove wire:target="deletePhoto">Ya, hapus foto</span>
                        <span wire:loading wire:target="deletePhoto" role="status">Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
