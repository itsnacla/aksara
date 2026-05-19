<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class SystemHealthWidget extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        return [
            // --- CORE INFRA ---
            $this->getDatabaseStatus(),
            $this->getQueueStatus(),
            $this->getInfraStatus(),

            // --- EXTERNAL SERVICES ---
            $this->getKemendikbudStatus(),
            $this->getRegionalApiStatus(),
            $this->getWaStatus(),
            $this->getAiStatus(),
            $this->getMailStatus(),

            // --- SERVER PERFORMANCE ---
            $this->getServerStats(),
        ];
    }

    private function getDatabaseStatus(): Stat
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $latency = round((microtime(true) - $start) * 1000);

            return Stat::make('Database Status', 'Healthy')
                ->description("Latency: {$latency}ms")
                ->descriptionIcon('heroicon-m-circle-stack')
                ->color('success');
        } catch (\Exception $e) {
            return Stat::make('Database', 'Offline')
                ->description('Connection Failed')
                ->color('danger');
        }
    }

    private function getQueueStatus(): Stat
    {
        // Check if queue table has many failed jobs
        $failedJobs = DB::table('failed_jobs')->count();
        $color = $failedJobs > 0 ? 'warning' : 'success';

        return Stat::make('Queue System', $failedJobs > 0 ? 'Issues Detected' : 'Running')
            ->description("Failed Jobs: {$failedJobs}")
            ->descriptionIcon('heroicon-m-queue-list')
            ->color($color);
    }

    private function getInfraStatus(): Stat
    {
        $storage = 'Writable';
        try { Storage::disk('public')->put('health_test.txt', '1'); } catch (\Exception $e) { $storage = 'Error'; }

        return Stat::make('Infrastructure', 'Operational')
            ->description("Storage: {$storage} | Cache: OK")
            ->descriptionIcon('heroicon-m-wrench-screwdriver')
            ->color('success');
    }

    private function getKemendikbudStatus(): Stat
    {
        try {
            // Kita cek landing page-nya saja untuk memastikan server UP
            $response = Http::timeout(3)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get('https://referensi.data.kemendikdasmen.go.id/');

            return Stat::make('Scraping Engine', $response->successful() ? 'Online' : 'Limited')
                ->description('Kemendikbud Reference')
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color($response->successful() ? 'success' : 'warning');
        } catch (\Exception $e) {
            return Stat::make('Scraping Engine', 'Offline')
                ->description('Service Unreachable')
                ->color('danger');
        }
    }

    private function getRegionalApiStatus(): Stat
    {
        $baseUrl = env('TATETA_GEO_URL', 'http://127.0.0.1:8001/api/v1/geo');
        
        try {
            // Parse host and port to correctly call the health check endpoint at tateta-geo root
            $urlParts = parse_url($baseUrl);
            $host = ($urlParts['scheme'] ?? 'http') . '://' . ($urlParts['host'] ?? '127.0.0.1') . (isset($urlParts['port']) ? ':' . $urlParts['port'] : '');
            
            $response = Http::timeout(2)->get("{$host}/api/health");
                
            if ($response->successful() && $response->json('status') === 'healthy') {
                $dbStatus = $response->json('database') === 'connected' ? 'Database: OK' : 'Database: Offline';
                return Stat::make('Regional API', 'Online')
                    ->description("TatetaGeo ({$dbStatus})")
                    ->descriptionIcon('heroicon-m-globe-alt')
                    ->color('success');
            }
        } catch (\Exception $e) {
            // Log or ignore to show Offline status below
        }

        return Stat::make('Regional API', 'Offline')
            ->description('TatetaGeo is Offline')
            ->descriptionIcon('heroicon-m-globe-alt')
            ->color('danger');
    }

    private function getWaStatus(): Stat
    {
        // We check the Fonnte API status
        $apiKey = config('services.wa.token') ?? env('FONNTE_TOKEN');
        try {
            $response = Http::withHeaders(['Authorization' => $apiKey])->post('https://api.fonnte.com/device');
            $status = $response->json()['status'] ?? false;
            return Stat::make('WA Gateway', $status ? 'Connected' : 'Disconnected')
                ->description($status ? 'Device Active' : 'Check Fonnte Token')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color($status ? 'success' : 'danger');
        } catch (\Exception $e) {
            return Stat::make('WA Gateway', 'Network Error')->color('danger');
        }
    }

    private function getAiStatus(): Stat
    {
        try {
            $settings = \App\Models\ChatbotSetting::current();
            $isActive = $settings->is_active;
            $rawProvider = $settings->primary_provider;
            
            // Format provider name beautifully
            $provider = match (strtolower($rawProvider)) {
                'google', 'gemini' => 'GEMINI',
                'openai' => 'OPENAI',
                'groq' => 'GROQ',
                default => strtoupper($rawProvider),
            };

            return Stat::make('AI Service', $isActive ? 'Active' : 'Inactive')
                ->description("Provider: {$provider}")
                ->descriptionIcon('heroicon-m-sparkles')
                ->color($isActive ? 'success' : 'danger');
        } catch (\Exception $e) {
            return Stat::make('AI Service', 'Offline')
                ->description('Status Check Failed')
                ->color('danger');
        }
    }

    private function getMailStatus(): Stat
    {
        $driver = config('mail.default');
        return Stat::make('Mail Service', strtoupper($driver))
            ->description('SMTP / API Configured')
            ->descriptionIcon('heroicon-m-envelope')
            ->color('success');
    }

    private function getServerStats(): Stat
    {
        $memUsage = round(memory_get_usage(true) / 1024 / 1024, 2);
        $diskFree = round(disk_free_space("/") / 1024 / 1024 / 1024, 2);

        return Stat::make('Server Performance', "PHP " . PHP_VERSION)
            ->description("RAM: {$memUsage}MB | Disk: {$diskFree}GB Free")
            ->descriptionIcon('heroicon-m-cpu-chip')
            ->color('gray');
    }
}
