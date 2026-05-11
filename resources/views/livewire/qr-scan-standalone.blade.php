<div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col">
@if(!$isEmbedded)
    <!-- Top Bar / Header -->
    <header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 px-6 py-3">
        <div class="max-w-[1400px] mx-auto flex items-center justify-between">
            <div class="flex items-center gap-4">
                @if($school->logo)
                    <img src="{{ asset('storage/' . $school->logo) }}" alt="Logo" class="h-10 w-10 object-contain">
                @else
                    <div class="h-10 w-10 bg-primary-600 rounded-lg flex items-center justify-center text-white font-bold text-xl shadow-sm">
                        {{ substr($school->name, 0, 1) }}
                    </div>
                @endif
                <div>
                    <h1 class="text-sm font-bold text-gray-950 dark:text-white uppercase tracking-tight">{{ $school->name }}</h1>
                    <p class="text-[10px] font-medium text-gray-500 uppercase tracking-widest">{{ $school->motto ?? 'Sistem Presensi Digital' }}</p>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="hidden sm:flex flex-col items-end">
                    <span id="live-clock" class="text-sm font-bold text-gray-900 dark:text-white">--:--:--</span>
                    <span class="text-[10px] font-bold text-primary-600 uppercase tracking-widest">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</span>
                </div>
                <div class="h-10 w-[1px] bg-gray-200 dark:bg-gray-800"></div>
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">System Online</span>
                </div>
            </div>
        </div>
    </header>
@endif

    <!-- Main Content Area -->
    <main class="flex-grow flex items-center justify-center {{ $isEmbedded ? 'p-0' : 'p-6 sm:p-10' }}">
        <div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left: Scanner (Main Action) -->
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 bg-primary-50 dark:bg-primary-400/10 rounded-lg flex items-center justify-center text-primary-600 dark:text-primary-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                            </div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white uppercase tracking-wider">Pemindaian Kartu</h2>
                        </div>
                        <!-- Use wire:ignore here to prevent flickering on Livewire re-renders -->
                        <div id="scanner-status" wire:ignore class="flex items-center gap-2">
                            <div class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Initializing...</span>
                        </div>
                    </div>

                    <!-- Scanner Container with wire:ignore -->
                    <div wire:ignore class="relative aspect-[16/9] rounded-xl bg-black overflow-hidden ring-1 ring-gray-950/10 dark:ring-white/5 shadow-inner">
                        <div id="reader" class="w-full h-full"></div>
                        
                        <!-- Fixed Overlay UI -->
                        <div class="absolute inset-0 z-10 pointer-events-none flex items-center justify-center">
                            <div class="absolute inset-0 bg-black/40"></div>
                            <div class="relative w-64 h-64 sm:w-80 sm:h-80 bg-transparent ring-[1000px] ring-black/40 rounded-lg">
                                <div class="absolute -top-1 -left-1 w-8 h-8 border-t-4 border-l-4 border-primary-500 rounded-tl-sm"></div>
                                <div class="absolute -top-1 -right-1 w-8 h-8 border-t-4 border-r-4 border-primary-500 rounded-tr-sm"></div>
                                <div class="absolute -bottom-1 -left-1 w-8 h-8 border-b-4 border-l-4 border-primary-500 rounded-bl-sm"></div>
                                <div class="absolute -bottom-1 -right-1 w-8 h-8 border-b-4 border-r-4 border-primary-500 rounded-br-sm"></div>
                                <div class="absolute top-0 left-0 w-full h-0.5 bg-primary-500/50 shadow-[0_0_15px_rgba(59,130,246,0.5)] animate-scan"></div>
                            </div>
                        </div>
                    </div>

                    <input type="text" id="qr_input" class="absolute opacity-0 pointer-events-none" autofocus>
                    
                    <div class="mt-6 flex items-center justify-between text-gray-400 dark:text-gray-500">
                        <p class="text-xs font-medium italic">Kamera akan memindai secara otomatis setelah kartu terdeteksi.</p>
                        <div class="flex gap-1.5">
                            <div class="h-1.5 w-4 bg-primary-500 rounded-full"></div>
                            <div class="h-1.5 w-1.5 bg-gray-200 dark:bg-gray-800 rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Identity & Results -->
            <div class="lg:col-span-5 space-y-6">
                <!-- Status & Identification Card -->
                <div @class([
                    'ring-1 rounded-2xl p-8 min-h-[160px] flex flex-col justify-center transition-all duration-500 shadow-sm',
                    'bg-white dark:bg-gray-900 ring-gray-950/5 dark:ring-white/10' => !$status_message,
                    'bg-green-50 ring-green-600/20 dark:bg-green-950/10 dark:ring-green-500/20' => $status_type == 'success',
                    'bg-red-50 ring-red-600/20 dark:bg-red-950/10 dark:ring-red-500/20' => $status_type == 'error',
                    'bg-amber-50 ring-amber-600/20 dark:bg-amber-950/10 dark:ring-amber-500/20' => $status_type == 'warning',
                ])>
                    @if($status_message)
                        <div class="flex items-center gap-6">
                            <div @class([
                                'h-16 w-16 rounded-2xl flex items-center justify-center shrink-0 shadow-sm ring-1 ring-inset',
                                'bg-green-600 text-white ring-green-700/10' => $status_type == 'success',
                                'bg-red-600 text-white ring-red-700/10' => $status_type == 'error',
                                'bg-amber-500 text-white ring-amber-600/10' => $status_type == 'warning',
                            ])>
                                @if($status_type == 'success')
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                @else
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h3 @class([
                                    'font-bold uppercase text-[10px] tracking-[0.3em] mb-2',
                                    'text-green-800 dark:text-green-400' => $status_type == 'success',
                                    'text-red-800 dark:text-red-400' => $status_type == 'error',
                                    'text-amber-800 dark:text-amber-400' => $status_type == 'warning',
                                ])>
                                    {{ $status_type == 'success' ? 'Identifikasi Berhasil' : 'Sistem Warning' }}
                                </h3>
                                <p @class([
                                    'text-lg font-bold leading-tight break-words',
                                    'text-green-950 dark:text-green-200' => $status_type == 'success',
                                    'text-red-950 dark:text-red-200' => $status_type == 'error',
                                    'text-amber-950 dark:text-amber-200' => $status_type == 'warning',
                                ])>{{ $status_message }}</p>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center text-center">
                            <div class="w-12 h-12 border-4 border-gray-100 dark:border-gray-800 border-t-primary-600 rounded-full animate-spin mb-4"></div>
                            <p class="text-gray-400 dark:text-gray-500 text-xs font-bold uppercase tracking-[0.2em]">Menunggu Input Kartu Siswa...</p>
                        </div>
                    @endif
                </div>

                <!-- Profile Result Card -->
                <div @class([
                    'bg-white dark:bg-gray-900 ring-1 ring-gray-950/5 dark:ring-white/10 rounded-2xl p-6 shadow-sm transition-all duration-700',
                    'opacity-100 translate-x-0' => $last_scanned,
                    'opacity-40 translate-x-4 grayscale' => !$last_scanned,
                ])>
                    <div class="flex items-center justify-between mb-6">
                        <h4 class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em]">Profil Siswa Terakhir</h4>
                        <div class="px-2 py-0.5 bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 text-[9px] font-bold rounded uppercase">Auto Sync</div>
                    </div>

                    <div class="flex items-center gap-6">
                        <div class="relative shrink-0">
                            @if($last_scanned && $last_scanned['avatar'])
                                <img src="{{ $last_scanned['avatar'] }}" class="h-24 w-24 rounded-2xl object-cover ring-2 ring-primary-600/20 shadow-md">
                                <div class="absolute -top-2 -right-2 h-6 w-6 bg-green-500 rounded-lg border-4 border-white dark:border-gray-900 flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                </div>
                            @else
                                <div class="h-24 w-24 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-300 dark:text-gray-700 border-2 border-dashed border-gray-200 dark:border-gray-700">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-xl font-bold text-gray-950 dark:text-white truncate leading-tight">{{ $last_scanned['name'] ?? '--- Nama Siswa ---' }}</h2>
                            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs font-medium mt-2">
                                <div class="h-5 w-5 rounded bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <span id="last-scanned-time">{{ $last_scanned['time'] ?? '--:--' }} WIB</span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400 text-xs font-medium mt-2">
                                <div class="h-5 w-5 rounded bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                </div>
                                <span>PRESENSI HARIAN</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technical Specs / Help -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-100 dark:bg-gray-800/50 rounded-xl p-4 text-center">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Device Status</span>
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-300">Connected</span>
                    </div>
                    <div class="bg-gray-100 dark:bg-gray-800/50 rounded-xl p-4 text-center">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Latency</span>
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-300">12ms</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

@if(!$isEmbedded)
    <!-- SaaS Footer -->
    <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 px-8 py-4">
        <div class="max-w-[1400px] mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Powered by</span>
                <div class="flex items-center gap-1.5 px-2 py-1 bg-gray-50 dark:bg-gray-800 rounded-md border border-gray-200 dark:border-gray-700">
                    <div class="h-4 w-4 bg-primary-600 rounded flex items-center justify-center text-[10px] font-bold text-white">A</div>
                    <span class="text-[11px] font-bold text-gray-950 dark:text-white tracking-tighter">AKSARA <span class="text-primary-600">SYSTEM</span></span>
                </div>
                <div class="h-4 w-[1px] bg-gray-200 dark:bg-gray-800 mx-2"></div>
                <div class="flex items-center gap-2 text-[9px] font-bold text-gray-400 uppercase tracking-widest">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04M12 21.355r7.106-7.106a10.046 10.046 0 000-14.212L12 7.143l-7.106 7.106a10.046 10.046 0 000 14.212L12 21.355z"></path></svg>
                    <span>Dual-Mode Scanner Active (HID/Camera)</span>
                </div>
            </div>
            
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.4em]">
                &copy; 2026 TATETA TECH INDONESIA
            </div>
            
            <div class="flex items-center gap-4 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                <a href="#" class="hover:text-primary-600 transition-colors">Privacy</a>
                <a href="#" class="hover:text-primary-600 transition-colors">Support</a>
                <a href="#" class="hover:text-primary-600 transition-colors">v2.4.1</a>
            </div>
        </div>
    </footer>
@endif

    <style>
        @keyframes scan {
            0% { top: 0%; }
            100% { top: 100%; }
        }
        .animate-scan {
            animation: scan 2s linear infinite;
        }
        #reader video {
            object-fit: cover !important;
            width: 100% !important;
            height: 100% !important;
        }
    </style>

    <script>
        document.addEventListener('livewire:init', () => {
            const qrInput = document.getElementById('qr_input');
            const clockEl = document.getElementById('live-clock');
            const statusEl = document.getElementById('scanner-status');

            // Sync with Server Time (Initial Offset)
            const serverTimeStr = "{{ now()->format('Y-m-d H:i:s') }}";
            let serverDate = new Date(serverTimeStr.replace(/-/g, "/"));
            const clientDate = new Date();
            const timeDiff = serverDate.getTime() - clientDate.getTime();

            const timeOptions = { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit', 
                hour12: false
            };

            // Live Clock with Offset
            if (clockEl) {
                setInterval(() => {
                    const now = new Date(new Date().getTime() + timeDiff);
                    clockEl.textContent = now.toLocaleTimeString('id-ID', timeOptions) + ' WIB';
                }, 1000);
            }
            
            // Auto focus for physical scanner (HID Mode)
            const keepFocus = () => {
                if (document.activeElement !== qrInput) {
                    qrInput.focus();
                }
            };
            
            document.addEventListener('click', keepFocus);
            setInterval(keepFocus, 1000); 

            qrInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    @this.set('scanned_id', qrInput.value);
                    qrInput.value = '';
                }
            });

            let html5QrCode = null;

            let lastToken = '';
            let lastTokenTime = 0;

            async function initScanner() {
                if (window.scannerInitialized) return;
                
                if (typeof Html5Qrcode === 'undefined') {
                    setTimeout(initScanner, 200);
                    return;
                }

                html5QrCode = new Html5Qrcode("reader");
                window.scannerInitialized = true;
                
                const config = { 
                    fps: 20, 
                    qrbox: { width: 300, height: 300 },
                    aspectRatio: 1.777778
                };

                try {
                    const devices = await Html5Qrcode.getCameras();
                    if (devices && devices.length > 0) {
                        const cameraId = devices[0].id; 
                        await html5QrCode.start(
                            cameraId, 
                            config,
                            (decodedText) => {
                                // JS-side cooldown: 5 seconds
                                const now = Date.now();
                                if (decodedText === lastToken && (now - lastTokenTime) < 5000) {
                                    return;
                                }
                                lastToken = decodedText;
                                lastTokenTime = now;

                                @this.call('processScan', decodedText);
                                if (navigator.vibrate) navigator.vibrate(100);
                                
                                if (statusEl) {
                                    statusEl.innerHTML = `
                                        <div class="h-2 w-2 rounded-full bg-green-500"></div>
                                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Scanned Successfully</span>
                                    `;
                                    setTimeout(() => {
                                        statusEl.innerHTML = `
                                            <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Scanner Ready</span>
                                        `;
                                    }, 2000);
                                }
                            },
                            (errorMessage) => {}
                        );
                        
                        if (statusEl) {
                            statusEl.innerHTML = `
                                <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Scanner Ready</span>
                            `;
                        }
                    }
                } catch (err) {
                    console.error("Unable to start scanning", err);
                    window.scannerInitialized = false;
                    if (statusEl) {
                        statusEl.innerHTML = `
                            <div class="h-2 w-2 rounded-full bg-red-500"></div>
                            <span class="text-[10px] font-bold text-red-500 uppercase tracking-widest">Camera Error</span>
                        `;
                    }
                }
            }

            initScanner();
        });
    </script>
</div>