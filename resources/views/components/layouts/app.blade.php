<!DOCTYPE html>
<html lang="id" class="h-full bg-pink-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'StockApp' }} - PABWE 2026</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: {
            sans: ['Nunito', 'ui-rounded', 'system-ui', 'sans-serif'],
            display: ['Fredoka', 'ui-rounded', 'system-ui', 'sans-serif'],
        } } } };
    </script>
    <style type="text/tailwindcss">
        @layer components {
            /* Fokus keyboard selalu terlihat (WCAG 2.4.7 / 2.4.13): ungu tua kontras tinggi di atas pink & putih */
            .focus-ring { @apply focus-visible:outline focus-visible:outline-[3px] focus-visible:outline-offset-2 focus-visible:outline-purple-900; }

            /* Tombol pil: target minimal 44x44 px (WCAG 2.5.8) dan kontras teks >= 4.5:1 */
            .btn { @apply focus-ring inline-flex min-h-[44px] items-center justify-center gap-2 rounded-full border-2 px-5 py-2 text-sm font-bold transition active:scale-95 disabled:cursor-not-allowed disabled:opacity-60; }
            .btn-primary { @apply btn border-pink-700 bg-pink-700 text-white shadow-[0_6px_14px_-6px_rgba(190,24,93,0.7)] hover:bg-pink-800 hover:border-pink-800; }
            .btn-secondary { @apply btn border-pink-600 bg-white text-rose-950 hover:bg-pink-100; }
            .btn-danger { @apply btn border-rose-800 bg-rose-800 text-white hover:bg-rose-900; }
            .btn-danger-outline { @apply btn border-rose-700 bg-white text-rose-800 hover:bg-rose-50; }
            .btn-icon { @apply focus-ring inline-flex h-11 w-11 items-center justify-center rounded-full border-2 border-pink-600 bg-white text-rose-950 transition active:scale-95 hover:bg-pink-100 disabled:cursor-not-allowed disabled:opacity-60; }

            .field-label { @apply mb-1.5 block text-sm font-bold text-rose-950; }
            .field-hint { @apply mt-1 block text-sm text-rose-900; }
            .field-error { @apply mt-1 block text-sm font-bold text-rose-800; }
            .field-input { @apply focus-ring block min-h-[44px] w-full rounded-2xl border-2 border-pink-600 bg-white px-4 py-2 text-base text-rose-950 placeholder:text-rose-800 sm:text-sm; }
            .field-input[aria-invalid="true"] { @apply border-rose-800 bg-rose-50; }

            .card { @apply rounded-3xl border-2 border-pink-200 bg-white shadow-[0_12px_32px_-14px_rgba(190,24,93,0.35)]; }
            .th { @apply px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-rose-950; }
            .td { @apply px-4 py-3 text-sm text-rose-950 align-middle; }
            .nav-link { @apply focus-ring flex min-h-[44px] items-center gap-3 rounded-full px-4 py-2 text-sm font-bold text-rose-950 transition hover:bg-white/80; }
            .nav-link[aria-current="page"] { @apply bg-white text-pink-900 shadow-[0_6px_14px_-8px_rgba(190,24,93,0.6)]; }

            /* Judul berfont bulat & playful */
            h1, h2, h3 { font-family: 'Fredoka', ui-rounded, system-ui, sans-serif; letter-spacing: 0.01em; }

            /* Tampilan deskripsi Markdown (dirender di server) */
            .markdown-body { @apply text-sm leading-relaxed text-rose-950; }
            .markdown-body h1, .markdown-body h2, .markdown-body h3 { @apply mt-4 mb-2 font-extrabold text-rose-950; }
            .markdown-body h1 { @apply text-xl; }
            .markdown-body h2 { @apply text-lg; }
            .markdown-body p { @apply my-2; }
            .markdown-body ul { @apply my-2 list-disc pl-6; }
            .markdown-body ol { @apply my-2 list-decimal pl-6; }
            .markdown-body a { @apply font-semibold text-pink-900 underline underline-offset-2; }
            .markdown-body code { @apply rounded bg-pink-100 px-1 py-0.5 font-mono text-xs text-rose-950; }
            .markdown-body pre { @apply my-2 overflow-x-auto rounded-xl bg-rose-950 p-3 text-pink-50; }
            .markdown-body pre code { @apply bg-transparent p-0 text-pink-50; }
            .markdown-body blockquote { @apply my-2 border-l-4 border-pink-600 pl-4 italic text-rose-900; }
        }
    </style>
    <style>
        [x-cloak] { display: none !important; }
        html {
            background-color: #fff1f2;
            background-image:
                radial-gradient(circle at 8% 4%, #fbcfe8 0, transparent 38%),
                radial-gradient(circle at 96% 96%, #fed7aa 0, transparent 34%),
                radial-gradient(circle at 92% 8%, #e9d5ff 0, transparent 26%);
            background-attachment: fixed;
        }
        .sr-only-focusable:not(:focus):not(:focus-within) {
            position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
            overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;
        }
        /* Hormati preferensi pengguna yang meminimalkan animasi (WCAG 2.3.3) */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
    @livewireStyles
</head>
<body class="h-full font-sans text-rose-950 antialiased"
      x-data="{
          sidebarOpen: false,
          mobile: window.matchMedia('(max-width: 767px)').matches,
          init() {
              const mq = window.matchMedia('(max-width: 767px)');
              mq.addEventListener('change', (e) => { this.mobile = e.matches; if (!e.matches) this.sidebarOpen = false; });
          }
      }"
      @keydown.escape.window="if (sidebarOpen) { sidebarOpen = false; $nextTick(() => $refs.menuButton?.focus()); }">

    <a href="#main-content" class="sr-only-focusable focus-ring absolute left-4 top-4 z-[100] rounded-xl bg-pink-700 px-4 py-3 text-sm font-bold text-white">
        Lewati ke konten utama
    </a>

    @auth
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" aria-hidden="true" class="fixed inset-0 z-40 bg-rose-950/60 md:hidden"></div>

        <aside id="sidebar"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               :inert="mobile && !sidebarOpen"
               class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col rounded-r-[2rem] border-r-2 border-pink-200 bg-pink-100 transition-transform duration-300 ease-in-out md:translate-x-0">

            <div class="flex h-16 items-center justify-between border-b border-pink-100 px-4">
                <a href="{{ auth()->user()->isAdmin() ? route('admin.users') : route('stocks.index') }}" class="focus-ring flex min-h-[44px] items-center gap-2.5 rounded-xl text-lg font-extrabold text-rose-950">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-pink-700 text-white" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                    <span>StockApp</span>
                </a>
                <button type="button" class="btn-icon md:hidden" @click="sidebarOpen = false; $nextTick(() => $refs.menuButton?.focus())" aria-label="Tutup menu navigasi">
                    <svg class="h-5 w-5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <section aria-label="Akun Anda" class="mx-3 my-4 rounded-xl border border-pink-200 bg-pink-50 p-4">
                <div class="flex items-center gap-3">
                    @if (auth()->user()->profile_photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists(auth()->user()->profile_photo_path))
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->profile_photo_path) }}" alt="Foto profil {{ auth()->user()->name }}" class="h-10 w-10 flex-shrink-0 rounded-full border border-pink-200 object-cover">
                    @else
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full {{ auth()->user()->isAdmin() ? 'bg-purple-800' : 'bg-pink-700' }} text-sm font-bold text-white" aria-hidden="true">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-rose-950">{{ auth()->user()->name }}</p>
                        <p class="truncate font-mono text-xs text-rose-900">&#64;{{ auth()->user()->username }}</p>
                    </div>
                </div>
                <p class="mt-3 flex items-center justify-between border-t border-pink-200 pt-2 text-xs text-rose-900">
                    <span>Peran akun:</span>
                    <strong class="rounded border px-2 py-0.5 {{ auth()->user()->isAdmin() ? 'border-purple-800 bg-purple-100 text-purple-900' : 'border-emerald-800 bg-emerald-100 text-emerald-900' }}">{{ ucfirst(auth()->user()->role) }}</strong>
                </p>
            </section>

            <nav aria-label="Navigasi utama" class="flex-1 overflow-y-auto px-3">
                <p class="px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-rose-900" id="menu-heading">Menu Utama</p>
                <ul class="space-y-1" aria-labelledby="menu-heading">
                    @if(auth()->user()->isAdmin())
                        <li>
                            <a href="{{ route('admin.users') }}" class="nav-link" @if(request()->routeIs('admin.*')) aria-current="page" @endif>
                                <svg class="h-5 w-5 flex-shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <span>Manajemen Pengguna</span>
                            </a>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('stocks.index') }}" class="nav-link" @if(request()->routeIs('stocks.*')) aria-current="page" @endif>
                                <svg class="h-5 w-5 flex-shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span>Stok Saya</span>
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="{{ route('profile') }}" class="nav-link" @if(request()->routeIs('profile')) aria-current="page" @endif>
                            <svg class="h-5 w-5 flex-shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Profil Saya</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="border-t border-pink-200 p-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="focus-ring flex min-h-[44px] w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold text-rose-800 hover:bg-rose-50">
                        <svg class="h-5 w-5 flex-shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Keluar (Logout)</span>
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-h-screen flex-col md:pl-64">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-pink-200 bg-white px-4 md:hidden">
                <button type="button" x-ref="menuButton" class="btn-icon" @click="sidebarOpen = true; $nextTick(() => document.querySelector('#sidebar a')?.focus())"
                        aria-label="Buka menu navigasi" aria-controls="sidebar" :aria-expanded="sidebarOpen.toString()">
                    <svg class="h-6 w-6" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <span class="text-base font-extrabold text-rose-950">StockApp</span>
                <span class="rounded border px-2 py-0.5 text-xs font-bold {{ auth()->user()->isAdmin() ? 'border-purple-800 bg-purple-100 text-purple-900' : 'border-emerald-800 bg-emerald-100 text-emerald-900' }}">{{ ucfirst(auth()->user()->role) }}</span>
            </header>

            <main id="main-content" tabindex="-1" class="mx-auto w-full max-w-7xl flex-1 p-4 focus:outline-none sm:p-6 lg:p-8">
                {{-- Wilayah pengumuman untuk pembaca layar (WCAG 4.1.3) --}}
                <div aria-live="polite" aria-atomic="true">
                    @if (session()->has('success'))
                        <div role="status" class="mb-6 flex items-start gap-3 rounded-xl border-2 border-emerald-800 bg-emerald-50 p-4 text-emerald-950">
                            <svg class="mt-0.5 h-5 w-5 flex-shrink-0" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <p class="text-sm font-semibold"><span class="sr-only">Berhasil: </span>{{ session('success') }}</p>
                        </div>
                    @endif
                </div>
                @if (session()->has('error'))
                    <div role="alert" class="mb-6 flex items-start gap-3 rounded-xl border-2 border-rose-800 bg-rose-50 p-4 text-rose-950">
                        <svg class="mt-0.5 h-5 w-5 flex-shrink-0" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <p class="text-sm font-semibold"><span class="sr-only">Kesalahan: </span>{{ session('error') }}</p>
                    </div>
                @endif

                {{ $slot }}
            </main>

            <footer class="mt-auto border-t border-pink-200 bg-white py-4 text-center text-xs text-rose-900">
                &copy; 2026 Delcom. All rights reserved.
            </footer>
        </div>
    @else
        <div class="flex min-h-screen flex-col">
            <header class="border-b border-pink-200 bg-white">
                <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <a href="{{ route('login') }}" class="focus-ring flex min-h-[44px] items-center gap-2.5 rounded-xl text-lg font-extrabold text-rose-950">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-pink-700 text-white" aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </span>
                        <span>StockApp</span>
                    </a>
                    <nav aria-label="Autentikasi">
                        <ul class="flex items-center gap-2">
                            <li><a href="{{ route('login') }}" class="btn-secondary" @if(request()->routeIs('login')) aria-current="page" @endif>Login</a></li>
                            <li><a href="{{ route('register') }}" class="btn-primary" @if(request()->routeIs('register')) aria-current="page" @endif>Register</a></li>
                        </ul>
                    </nav>
                </div>
            </header>

            <main id="main-content" tabindex="-1" class="flex flex-1 items-center justify-center p-4 focus:outline-none">
                {{ $slot }}
            </main>

            <footer class="border-t border-pink-200 bg-white py-4 text-center text-xs text-rose-900">
                &copy; 2026 Delcom. All rights reserved.
            </footer>
        </div>
    @endauth

    @livewireScripts
</body>
</html>
