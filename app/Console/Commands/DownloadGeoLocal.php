<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class DownloadGeoLocal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geo:download-local';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download and save all region data locally to run 100% offline';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $baseUrl = rtrim(env('TATETA_GEO_URL', 'https://geo.tateta.samastanuswantara.com/api/v1/geo'), '/');
        $token = env('TATETA_GEO_TOKEN');

        if (!$token) {
            $this->error('TATETA_GEO_TOKEN is not set in .env');
            return 1;
        }

        $geoDir = storage_path('app/geo');
        $villagesDir = $geoDir . '/villages';

        if (!File::exists($geoDir)) {
            File::makeDirectory($geoDir, 0755, true);
        }
        if (!File::exists($villagesDir)) {
            File::makeDirectory($villagesDir, 0755, true);
        }

        // 1. Download Provinces
        $this->info('Downloading provinces...');
        $response = Http::withToken($token)->timeout(30)->get("{$baseUrl}/provinces");
        if (!$response->successful()) {
            $this->error('Failed to download provinces: ' . $response->body());
            return 1;
        }
        $provinces = $response->json();
        File::put("{$geoDir}/provinces.json", json_encode($provinces, JSON_PRETTY_PRINT));
        $this->info('Saved provinces.json successfully.');

        // 2. Download Regencies
        $this->info('Downloading regencies...');
        $response = Http::withToken($token)->timeout(30)->get("{$baseUrl}/regencies");
        if (!$response->successful()) {
            $this->error('Failed to download regencies: ' . $response->body());
            return 1;
        }
        $regencies = $response->json();
        File::put("{$geoDir}/regencies.json", json_encode($regencies, JSON_PRETTY_PRINT));
        $this->info('Saved regencies.json successfully.');

        // 3. Download Districts
        $this->info('Downloading districts...');
        $response = Http::withToken($token)->timeout(60)->get("{$baseUrl}/districts");
        if (!$response->successful()) {
            $this->error('Failed to download districts: ' . $response->body());
            return 1;
        }
        $districts = $response->json();
        File::put("{$geoDir}/districts.json", json_encode($districts, JSON_PRETTY_PRINT));
        $this->info('Saved districts.json successfully.');

        // 4. Download Villages
        $this->info('Downloading all villages (approx 4.2 MB)...');
        $response = Http::withToken($token)->timeout(120)->get("{$baseUrl}/villages");
        if (!$response->successful()) {
            $this->error('Failed to download villages: ' . $response->body());
            return 1;
        }
        $villages = $response->json();
        $this->info('Processing and splitting villages by province ID...');

        // Group villages by province ID (first 2 digits of the village ID or district ID)
        $groupedVillages = [];
        foreach ($villages as $village) {
            $villageId = (string) ($village['id'] ?? '');
            if (strlen($villageId) >= 2) {
                $provinceId = substr($villageId, 0, 2);
                $groupedVillages[$provinceId][] = [
                    'id' => $village['id'],
                    'district_id' => $village['district_id'],
                    'name' => $village['name']
                ];
            }
        }

        // Save each province's villages into its own JSON file
        foreach ($groupedVillages as $provinceId => $provVillages) {
            File::put("{$villagesDir}/{$provinceId}.json", json_encode($provVillages, JSON_PRETTY_PRINT));
        }

        $this->info('Saved all village JSON files by province successfully.');
        $this->info('Local geo database setup is complete! The system can now run 100% offline.');

        return 0;
    }
}
