<?php

namespace App\Console\Commands;

use App\Services\WAService;
use Illuminate\Console\Command;

class TestWACommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wa:test {phone} {message=Test message from Aksara System}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the WhatsApp Gateway configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->argument('phone');
        $message = $this->argument('message');

        $this->info("Sending message to $phone...");

        $result = WAService::sendMessage($phone, $message);

        if ($result) {
            $this->info('Message sent successfully!');
        } else {
            $this->error('Failed to send message. Check storage/logs/laravel.log for details.');
            $this->warn("Make sure 'is_wa_enabled' is true and 'wa_gateway_token' is filled in School Settings.");
        }
    }
}
