<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Siswa</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page { 
            size: A4 portrait; 
            margin: 0; 
        }
        
        @media print {
            body { 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
                background-color: white !important;
            }
            .no-print { display: none !important; }
            .container { 
                box-shadow: none !important; 
                padding: 0 !important; 
                margin: 0 !important;
            }
        }

        body { 
            font-family: sans-serif;
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
            gap: 10mm;
            page-break-inside: avoid;
            margin-bottom: 5mm;
        }
    </style>
</head>
<body class="bg-gray-200 text-black p-8">
    
    <div class="max-w-7xl mx-auto bg-white p-8 shadow-lg">
        
        <!-- Action Buttons (No Print) -->
        <div class="mb-8 flex justify-end gap-3 no-print border-b pb-4">
            <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded inline-flex items-center shadow-md">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Tutup Tab
            </button>
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded inline-flex items-center shadow-md">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak / Simpan PDF
            </button>
        </div>

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

    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function savePDF() {
            const element = document.querySelector('.container');
            const opt = {
                margin: 10,
                filename: 'kartu-siswa.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>
