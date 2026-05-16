<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Kartu Siswa - A4 PVC Layout</title>
    <style>
        /* A4 Page Setup */
        @page { 
            size: A4 portrait; 
            margin: 10mm; 
        }
        
        body { 
            margin: 0; 
            padding: 0; 
            background: #f0f0f0; 
            font-family: sans-serif;
            -webkit-print-color-adjust: exact;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10mm;
        }

        /* Row for Front and Back side-by-side */
        .card-row {
            display: flex;
            justify-content: center;
            gap: 10mm; /* Space between Front and Back */
            page-break-inside: avoid;
            margin-bottom: 5mm;
        }

        @media print {
            body { background: none; }
            .no-print { display: none; }
            .container { gap: 5mm; }
        }
    </style>
</head>
<body>
    <div class="container">
        @foreach($students as $student)
            <div class="card-row">
                {{-- FRONT SIDE --}}
                <x-student-card :student="$student" :school="$school" side="front" />
                
                {{-- BACK SIDE --}}
                <x-student-card :student="$student" :school="$school" side="back" />
            </div>
        @endforeach
    </div>
    
    <script>
        window.onload = function() {
            // window.print();
        };
    </script>
</body>
</html>
