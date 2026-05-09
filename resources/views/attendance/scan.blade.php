<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QR Attendance Scanner - AKSARA</title>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #1e3a5f, #2d6a9f);
            color: white;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .header h1 {
            font-size: 24px;
            font-weight: 700;
        }

        .header p {
            font-size: 13px;
            opacity: 0.8;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .card h2 {
            font-size: 18px;
            color: #1e3a5f;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }

        select,
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 15px;
            transition: border-color 0.2s;
        }

        select:focus,
        input:focus {
            outline: none;
            border-color: #2d6a9f;
        }

        label {
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 5px;
            display: block;
        }

        #qr-reader {
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
        }

        .result-box {
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            margin-top: 20px;
            display: none;
        }

        .result-success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }

        .result-error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }

        .result-warning {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffeeba;
        }

        .student-info {
            margin-top: 10px;
            font-size: 14px;
            font-weight: 400;
        }

        .manual-input {
            display: flex;
            gap: 10px;
        }

        .manual-input input {
            margin-bottom: 0;
            flex: 1;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #2d6a9f;
            color: white;
        }

        .btn-primary:hover {
            background: #1e3a5f;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        .scanner-toggle {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .today-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .attendance-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        .attendance-item:last-child {
            border-bottom: none;
        }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: #d4edda;
            color: #155724;
        }

        .time-display {
            font-size: 36px;
            font-weight: 700;
            color: #1e3a5f;
            text-align: center;
            margin-bottom: 5px;
        }

        .date-display {
            text-align: center;
            color: #718096;
            font-size: 14px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div>
            <h1>📋 AKSARA Attendance Scanner</h1>
            <p>Scan student QR code to mark attendance</p>
        </div>
    </div>

    <div class="container">
        <!-- Clock -->
        <div class="card">
            <div class="time-display" id="clock">--:--:--</div>
            <div class="date-display" id="date"></div>
        </div>

        <!-- Setup -->
        <div class="card">
            <h2>📅 Session Setup</h2>
            <label>Select Schedule (optional)</label>
            <select id="schedule-select">
                <option value="">-- No specific schedule --</option>
                @foreach($schedules as $schedule)
                    <option value="{{ $schedule->id }}">
                        {{ $schedule->hari }} | {{ $schedule->subject?->nama_mapel }} |
                        {{ $schedule->schoolClass?->nama_kelas }}
                        ({{ \Carbon\Carbon::parse($schedule->jam_mulai)->format('H:i') }} -
                        {{ \Carbon\Carbon::parse($schedule->jam_selesai)->format('H:i') }})
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Scanner -->
        <div class="card">
            <h2>📷 QR Scanner</h2>

            <div class="scanner-toggle">
                <button class="btn btn-primary" onclick="startCamera()">Start Camera</button>
                <button class="btn btn-secondary" onclick="stopCamera()">Stop Camera</button>
            </div>

            <div id="qr-reader"></div>

            <div style="margin-top: 20px;">
                <label>Or enter QR code manually:</label>
                <div class="manual-input">
                    <input type="text" id="manual-qr" placeholder="Type or paste QR code here..." />
                    <button class="btn btn-primary" onclick="processManual()">Submit</button>
                </div>
            </div>

            <div class="result-box" id="result-box">
                <div id="result-message"></div>
                <div class="student-info" id="result-detail"></div>
            </div>
        </div>

        <!-- Today's attendance -->
        <div class="card">
            <h2>✅ Today's Attendance (<span id="count">0</span>)</h2>
            <div class="today-list" id="today-list">
                <p style="text-align:center; color: #a0aec0; padding: 20px;">No attendance recorded yet.</p>
            </div>
        </div>
    </div>

    <script>
        let html5QrCode = null;
        let scannedList = [];

        // Clock
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent = now.toLocaleTimeString('en-US', { hour12: false });
            document.getElementById('date').textContent = now.toLocaleDateString('en-US', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });
        }
        setInterval(updateClock, 1000);
        updateClock();

        function startCamera() {
            html5QrCode = new Html5Qrcode("qr-reader");
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 300, height: 300 } },
                (decodedText) => {
                    stopCamera();
                    processQr(decodedText);
                },
                (error) => { }
            ).catch(err => {
                alert('Camera error: ' + err);
            });
        }

        function stopCamera() {
            if (html5QrCode) {
                html5QrCode.stop().catch(() => { });
            }
        }

        function processManual() {
            const code = document.getElementById('manual-qr').value.trim();
            if (!code) return alert('Please enter a QR code.');
            processQr(code);
            document.getElementById('manual-qr').value = '';
        }

        function processQr(qrCode) {
            const scheduleId = document.getElementById('schedule-select').value;

            fetch('{{ route("attendance.process") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ qr_code: qrCode, schedule_id: scheduleId || null }),
            })
                .then(r => r.json())
                .then(data => {
                    showResult(data);
                    if (data.success) addToList(data);
                })
                .catch(() => showResult({ success: false, message: 'Connection error.' }));
        }

        function showResult(data) {
            const box = document.getElementById('result-box');
            const msg = document.getElementById('result-message');
            const detail = document.getElementById('result-detail');

            box.style.display = 'block';
            box.className = 'result-box ' + (data.success ? 'result-success' : (data.message?.includes('already') ? 'result-warning' : 'result-error'));
            msg.textContent = data.message;
            detail.textContent = data.class ? 'Class: ' + data.class : '';

            setTimeout(() => { box.style.display = 'none'; }, 4000);
        }

        function addToList(data) {
            scannedList.unshift(data);
            document.getElementById('count').textContent = scannedList.length;

            const list = document.getElementById('today-list');
            const now = new Date().toLocaleTimeString('en-US', { hour12: false });

            const item = document.createElement('div');
            item.className = 'attendance-item';
            item.innerHTML = `
            <div>
                <strong>${data.student}</strong>
                <div style="font-size:12px; color:#718096">${data.class ?? ''}</div>
            </div>
            <div style="display:flex; align-items:center; gap:10px">
                <span style="font-size:12px; color:#718096">${now}</span>
                <span class="badge">Present</span>
            </div>
        `;

            if (list.children.length === 1 && list.children[0].tagName === 'P') {
                list.innerHTML = '';
            }
            list.prepend(item);
        }
    </script>

</body>

</html>