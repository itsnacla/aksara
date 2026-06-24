@props(['student', 'school', 'side' => 'front'])

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    * { box-sizing: border-box; -webkit-print-color-adjust: exact; }
    
    .student-card-container {
        width: 85.6mm;
        height: 53.98mm;
        background: #fff;
        position: relative;
        overflow: hidden;
        border-radius: 3mm;
        display: flex;
        flex-direction: column;
        border: 0.1mm solid #e0e0e0;
        font-family: 'Inter', sans-serif;
    }

    .card-pattern {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background-image: radial-gradient(circle at 1px 1px, rgba(0,77,77,0.03) 1px, transparent 0);
        background-size: 6px 6px;
        z-index: 1;
    }

    /* HEADER */
    .card-header {
        height: 19mm;
        background: linear-gradient(135deg, #004d4d 0%, #003333 100%);
        color: white;
        padding: 0 5mm;
        display: flex;
        align-items: center;
        gap: 4mm;
        position: relative;
        z-index: 5;
        border-bottom: 0.8mm solid #cc0000;
    }
    .header-logo { width: 11mm; height: 11mm; background: #fff; border-radius: 1.5mm; flex-shrink: 0; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .header-logo img { width: 85%; height: 85%; object-fit: contain; }
    
    .header-content { flex: 1; overflow: hidden; }
    .card-title-tag { font-size: 6.5pt; font-weight: 800; color: #4ade80; letter-spacing: 1.2pt; margin-bottom: 0.2mm; text-transform: uppercase; }
    .school-name-text { font-size: 11pt; font-weight: 800; text-transform: uppercase; margin: 0; line-height: 1.1; }
    .school-contact-info { font-size: 5.5pt; opacity: 0.8; margin-top: 0.8mm; line-height: 1.2; }

    /* FRONT BODY */
    .card-body-front {
        flex: 1;
        display: flex;
        padding: 4mm 6mm;
        gap: 6mm;
        z-index: 5;
    }
    .photo-wrapper {
        width: 21mm;
        height: 27mm;
        background: #fff;
        padding: 0.8mm;
        border-radius: 1.5mm;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-top: 0; /* Aligned with info */
        z-index: 10;
        border: 0.1mm solid #ddd;
    }
    .student-photo { width: 100%; height: 100%; border-radius: 0.5mm; overflow: hidden; }
    .student-photo img { width: 100%; height: 100%; object-fit: cover; }
    
    .student-info { flex: 1; display: flex; flex-direction: column; justify-content: center; }
    .display-name { font-size: 11.5pt; font-weight: 800; color: #004d4d; margin-bottom: 3mm; text-transform: uppercase; border-bottom: 1.5pt solid #f0f0f0; padding-bottom: 1mm; }
    .info-value-only { font-size: 9pt; font-weight: 600; color: #444; margin-bottom: 1.2mm; }
    .validity-label { font-size: 6.5pt; font-weight: 800; color: #cc0000; background: #ffeeee; padding: 0.5mm 1.5mm; border-radius: 0.5mm; margin-top: 2.5mm; width: fit-content; border: 0.1mm solid #ffcccc; }

    /* BACK BODY */
    .card-body-back {
        flex: 1;
        display: flex;
        padding: 5mm 7mm;
        gap: 6mm;
        z-index: 5;
        align-items: center;
    }
    .back-left-rules {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    .back-side-title { font-size: 9pt; font-weight: 800; color: #004d4d; margin-bottom: 3mm; border-bottom: 1pt solid #004d4d; padding-bottom: 1mm; text-transform: uppercase; }
    .rules-list { list-style: none; padding: 0; margin: 0; }
    .rules-list li { font-size: 7.2pt; color: #444; margin-bottom: 1.5mm; line-height: 1.3; display: flex; gap: 2.5mm; font-weight: 500; }
    .rules-list li::before { content: "•"; color: #004d4d; font-weight: bold; }

    .back-right-qr {
        width: 26mm;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2mm;
    }
    .qr-frame-modern {
        background: white;
        padding: 1.8mm;
        border-radius: 2mm;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border: 0.1mm solid #eee;
    }
    .qr-nisn-back { font-size: 9pt; font-weight: 800; color: #004d4d; letter-spacing: 1px; }

    .card-footer-strip { height: 1.5mm; background: #004d4d; z-index: 5; position: relative; }
    
    .card-copyright {
        position: absolute;
        bottom: 2.2mm;
        right: 5mm;
        font-size: 4.2pt;
        color: #999;
        font-weight: 500;
        z-index: 10;
        letter-spacing: 0.8pt;
        text-transform: uppercase;
    }
    .card-copyright b { color: #004d4d; font-weight: 800; }
</style>

<div class="student-card-container">
    <div class="card-pattern"></div>
    
    @if($side === 'front')
        {{-- FRONT SIDE --}}
        <div class="card-header">
            <div class="header-logo">
                @if($school->logo)
                    <img src="{{ asset('storage/' . $school->logo) }}" alt="Logo">
                @else
                    <span style="color: #004d4d; font-weight: 800; font-size: 14pt;">{{ substr($school->name, 0, 1) }}</span>
                @endif
            </div>
            <div class="header-content">
                <div class="card-title-tag">Kartu Tanda Pelajar</div>
                <h1 class="school-name-text">{{ $school->name }}</h1>
                <div class="school-contact-info">
                    {{ $school->address }}<br>
                    Telp: {{ $school->phone ?? '-' }} | {{ str_replace(['https://', 'http://'], '', $school->website ?? '-') }}
                </div>
            </div>
        </div>
        
        <div class="card-body-front">
            <div class="photo-wrapper">
                <div class="student-photo">
                    @if($student->user->photo)
                        <img src="{{ asset('storage/' . $student->user->photo) }}" alt="Photo">
                    @else
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #ccc; font-size: 20pt;"></div>
                    @endif
                </div>
            </div>
            
            <div class="student-info">
                <div class="display-name">{{ $student->user->name }}</div>
                
                <div class="info-value-only">{{ $student->nisn }}</div>
                <div class="info-value-only">{{ $student->pob ?? '-' }}, {{ $student->dob ? $student->dob->translatedFormat('d F Y') : '-' }}</div>
                
                <div class="validity-label">BERLAKU SELAMA MENJADI SISWA</div>
            </div>
        </div>
    @else
        {{-- BACK SIDE --}}
        <div class="card-body-back">
            <div class="back-left-rules">
                <div class="back-side-title">Ketentuan</div>
                <ul class="rules-list">
                    <li>Kartu resmi identitas siswa {{ $school->name }}.</li>
                    <li>Wajib dibawa selama di lingkungan sekolah.</li>
                    <li>Digunakan untuk akses presensi dan fasilitas sekolah.</li>
                    <li>Jika menemukan kartu ini, harap lapor administrasi.</li>
                </ul>
            </div>
            
            <div class="back-right-qr">
                <div class="qr-frame-modern">
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(80)->margin(0)->generate($student->nisn) !!}
                </div>
                <div class="qr-nisn-back">{{ $student->nisn }}</div>
            </div>
            
            <div class="card-copyright">Powered by <b>AKSARA</b> | TATETA</div>
        </div>
    @endif
    <div class="card-footer-strip"></div>
</div>
