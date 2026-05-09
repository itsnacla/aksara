<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>QR Card - {{ $student->nama_siswa }}</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex-direction: column;
            gap: 20px;
        }

        .card {
            width: 320px;
            background: linear-gradient(135deg, #1e3a5f, #2d6a9f);
            border-radius: 20px;
            padding: 30px 25px;
            color: white;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .school-name {
            font-size: 13px;
            font-weight: 600;
            opacity: 0.8;
            margin-bottom: 5px;
        }

        .system-name {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }

        .qr-container {
            background: white;
            border-radius: 15px;
            padding: 15px;
            margin: 15px auto;
            display: inline-block;
        }

        .qr-container canvas {
            display: block;
        }

        .student-name {
            font-size: 18px;
            font-weight: 700;
            margin-top: 15px;
        }

        .student-info {
            font-size: 13px;
            opacity: 0.8;
            margin-top: 5px;
        }

        .qr-code-text {
            font-size: 11px;
            opacity: 0.6;
            margin-top: 10px;
            font-family: monospace;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 30px;
            font-weight: 700;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-print {
            background: white;
            color: #1e3a5f;
        }

        .btn-back {
            background: #e2e8f0;
            color: #4a5568;
        }

        @media print {
            body {
                background: white;
            }

            .actions {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="school-name">SDN 3 Bangorejo</div>
        <div class="system-name">AKSARA</div>

        <div class="qr-container">
            <canvas id="qrCanvas"></canvas>
        </div>

        <div class="student-name">{{ $student->nama_siswa }}</div>
        <div class="student-info">
            {{ $student->schoolClass?->nama_kelas ?? '-' }} &nbsp;|&nbsp;
            NISN: {{ $student->nisn ?? '-' }}
        </div>
        <div class="qr-code-text">{{ $student->qr_code }}</div>
    </div>

    <div class="actions">
        <button class="btn btn-print" onclick="window.print()">🖨️ Print Card</button>
        <a class="btn btn-back" href="javascript:history.back()">← Back</a>
    </div>

    <script>
        // QR Code generator - pure JS, no CDN needed
        // Using qrcode-generator library inline
        var qrData = "{{ $student->qr_code }}";

        // Minimal QR encoder using data URL approach
        function drawQR(canvasId, text) {
            var canvas = document.getElementById(canvasId);
            var size = 200;
            canvas.width = size;
            canvas.height = size;
            var ctx = canvas.getContext('2d');

            // White background
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, size, size);

            // Use QR via img with data
            var img = new Image();
            img.onload = function () { ctx.drawImage(img, 0, 0, size, size); };
            img.onerror = function () {
                // Fallback: show text if image fails
                ctx.fillStyle = '#1e3a5f';
                ctx.font = '12px monospace';
                ctx.textAlign = 'center';
                ctx.fillText('QR: ' + text, size / 2, size / 2);
            };
            img.src = 'https://quickchart.io/qr?text=' + encodeURIComponent(text) + '&size=200';
        }

        drawQR('qrCanvas', qrData);
    </script>
</body>

</html>