<style>
    .import-preview-wrapper {
        font-family: inherit;
        margin-top: 12px;
        line-height: 1.5;
    }
    .import-warning-banner {
        background-color: #fffbeb;
        color: #b45309;
        border: 1px solid #fef3c7;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 13px;
        margin-bottom: 14px;
    }
    .import-success-banner {
        background-color: #ecfdf5;
        color: #047857;
        border: 1px solid #d1fae5;
        padding: 14px 16px;
        border-radius: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 8px;
    }
    .import-table-container {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        overflow-x: auto;
        background: #ffffff;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        margin-bottom: 8px;
    }
    .import-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13px;
    }
    .import-table th {
        background-color: #f9fafb;
        padding: 12px 16px;
        font-weight: 600;
        color: #111827;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
    }
    .import-table td {
        padding: 12px 16px;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }
    .import-table tr:last-child td {
        border-bottom: none;
    }
    .import-row-duplicate {
        background-color: #fff5f5;
    }
    .import-row-duplicate td {
        color: #991b1b;
    }
    .import-badge-danger {
        background-color: #fee2e2;
        color: #991b1b;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        border: 1px solid #fca5a5;
        display: inline-block;
    }
    .import-badge-normal {
        background-color: #f3f4f6;
        color: #374151;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid #e5e7eb;
        display: inline-block;
    }
    .import-badge-warning {
        background-color: #fef3c7;
        color: #d97706;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 700;
        border: 1px solid #fde68a;
    }
    .import-badge-success {
        background-color: #ffffff;
        color: #047857;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid #a7f3d0;
        box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
    }
    .import-footer-note {
        font-size: 12px;
        color: #6b7280;
        font-style: italic;
        padding-left: 4px;
        margin-top: 8px;
    }
    
    /* Dark Mode overrides support matching Filament dark theme */
    .dark .import-warning-banner {
        background-color: rgba(180, 83, 9, 0.15);
        color: #fcd34d;
        border-color: rgba(180, 83, 9, 0.3);
    }
    .dark .import-success-banner {
        background-color: rgba(4, 120, 87, 0.15);
        color: #6ee7b7;
        border-color: rgba(4, 120, 87, 0.3);
    }
    .dark .import-badge-success {
        background-color: #064e3b;
        color: #6ee7b7;
        border-color: #047857;
    }
    .dark .import-table-container {
        background-color: #18181b;
        border-color: #27272a;
    }
    .dark .import-table th {
        background-color: #27272a;
        color: #f4f4f5;
        border-color: #3f3f46;
    }
    .dark .import-table td {
        color: #d4d4d8;
        border-color: #27272a;
    }
    .dark .import-row-duplicate {
        background-color: rgba(153, 27, 27, 0.15);
    }
    .dark .import-row-duplicate td {
        color: #fca5a5;
    }
    .dark .import-badge-danger {
        background-color: rgba(153, 27, 27, 0.3);
        color: #fca5a5;
        border-color: rgba(153, 27, 27, 0.5);
    }
    .dark .import-badge-normal {
        background-color: #27272a;
        color: #d4d4d8;
        border-color: #3f3f46;
    }
</style>

<div class="import-preview-wrapper">
    {{-- Banner Pemberitahuan Duplikat --}}
    @if(($duplicateCount ?? 0) > 0)
        <div class="import-warning-banner">
            ⚠️ Ditemukan <b>{{ $duplicateCount }}</b> baris dengan identitas yang sudah terdaftar. Sistem akan <b>melewati (mengabaikan)</b> baris tersebut secara otomatis saat proses impor untuk mencegah duplikasi data.
        </div>
    @endif

    <div class="import-success-banner">
        <div>
            <span style="font-weight: 700;">Hasil Parsing Sukses!</span> Terdeteksi <span style="font-weight: 800; text-decoration: underline;">{{ $validCount ?? 0 }}</span> baris data siap diproses.
        </div>
        <div class="import-badge-success">
            Sandi Default: password
        </div>
    </div>

    <div class="import-table-container">
        <table class="import-table">
            <thead>
                <tr>
                    @if($type === 'teacher')
                        <th>Nama Lengkap</th>
                        <th>NIP</th>
                        <th>Status</th>
                        <th>Username (Auto)</th>
                        <th>Email (Auto)</th>
                        <th>Mapel (Array)</th>
                    @elseif($type === 'student')
                        <th>Nama Siswa</th>
                        <th>NISN</th>
                        <th>Status</th>
                        <th>Username</th>
                        <th>Orang Tua/Wali</th>
                        <th>Rombel</th>
                    @elseif($type === 'staff')
                        <th>Nama Staff</th>
                        <th>Jabatan</th>
                        <th>Status</th>
                        <th>Username (Auto)</th>
                        <th>Email (Auto)</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $p)
                    @php
                        $isDuplicate = !empty($p['is_duplicate']);
                    @endphp
                    <tr class="{{ $isDuplicate ? 'import-row-duplicate' : '' }}">
                        @if($type === 'teacher')
                            <td style="font-weight: 500;">{{ $p['name'] ?? '-' }}</td>
                            <td>
                                @if(!empty($p['is_auto_nip']))
                                    <span class="import-badge-warning" title="Dihasilkan otomatis karena kosong">{{ $p['nip'] ?? '-' }}</span>
                                @else
                                    <span style="font-family: monospace;">{{ $p['nip'] ?? '-' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($isDuplicate)
                                    <span class="import-badge-danger">Sudah Terdaftar</span>
                                @else
                                    <span class="import-badge-normal">{{ $p['status'] ?? 'Aktif' }}</span>
                                @endif
                            </td>
                            <td style="font-family: monospace; opacity: 0.8;">{{ $p['username'] ?? '-' }}</td>
                            <td style="font-family: monospace; opacity: 0.8;">{{ $p['email'] ?? '-' }}</td>
                            <td>{{ $p['mapel'] ?? '-' }}</td>
                        @elseif($type === 'student')
                            <td style="font-weight: 500;">{{ $p['name'] ?? '-' }}</td>
                            <td style="font-family: monospace;">{{ $p['nisn'] ?? '-' }}</td>
                            <td>
                                @if($isDuplicate)
                                    <span class="import-badge-danger">Sudah Terdaftar</span>
                                @else
                                    <span class="import-badge-normal">Aktif</span>
                                @endif
                            </td>
                            <td style="font-family: monospace; opacity: 0.8;">{{ $p['username'] ?? '-' }}</td>
                            <td>{{ $p['parent'] ?? '-' }}</td>
                            <td>{{ $p['rombel'] ?? '-' }}</td>
                        @elseif($type === 'staff')
                            <td style="font-weight: 500;">{{ $p['name'] ?? '-' }}</td>
                            <td>{{ $p['jabatan'] ?? '-' }}</td>
                            <td>
                                @if($isDuplicate)
                                    <span class="import-badge-danger">Sudah Terdaftar</span>
                                @else
                                    <span class="import-badge-normal">Aktif</span>
                                @endif
                            </td>
                            <td style="font-family: monospace; opacity: 0.8;">{{ $p['username'] ?? '-' }}</td>
                            <td style="font-family: monospace; opacity: 0.8;">{{ $p['email'] ?? '-' }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if(($validCount ?? 0) > count($rows))
            <div style="padding: 10px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; background-color: #f9fafb;" class="dark:bg-zinc-900 dark:border-zinc-800">
                ... menampilkan {{ count($rows) }} baris pertama dari total {{ $validCount }} baris hasil pemindaian
            </div>
        @endif
    </div>

    <div class="import-footer-note">
        * Keterangan: Kolom kosong/esensial akan diisi otomatis. Baris berstatus "Sudah Terdaftar" akan diabaikan secara aman oleh sistem.
    </div>
</div>
