<?php

namespace App\Providers;

use App\Models\ChatbotSetting;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private static array $aiStartTimes = [];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! app()->isProduction());

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        // Disable modal close-by-clicking-away globally for all Filament actions (covers both pages and tables under Filament v3!)
        Action::configureUsing(fn ($action) => $action->closeModalByClickingAway(false));

        // Dynamic AI Configuration from Database (General Mode)
        try {
            if (class_exists(ChatbotSetting::class)) {
                $settings = ChatbotSetting::current();

                if ($settings->settings) {
                    foreach ($settings->settings as $provider => $values) {
                        if (isset($values['key'])) {
                            config(["ai.providers.{$provider}.key" => $values['key']]);
                        }
                        if (isset($values['url'])) {
                            config(["ai.providers.{$provider}.url" => $values['url']]);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail if DB is not ready
        }

        // Global AI Request Logging
        try {
            \Illuminate\Support\Facades\Event::listen(\Laravel\Ai\Events\PromptingAgent::class, function ($event) {
                self::$aiStartTimes[$event->invocationId] = microtime(true);

                $provider = $event->prompt->provider;
                if ($provider instanceof \BackedEnum) {
                    $provider = $provider->value;
                } elseif (is_object($provider)) {
                    $provider = class_basename($provider);
                }

                \App\Models\ChatbotRequestLog::create([
                    'invocation_id' => $event->invocationId,
                    'user_id' => auth()->id(),
                    'message' => $event->prompt->prompt,
                    'status' => 'failed',
                    'provider' => $provider,
                    'model' => $event->prompt->model,
                    'error_message' => 'AI Provider connection failed, overloaded, or timed out.',
                ]);
            });

            \Illuminate\Support\Facades\Event::listen(\Laravel\Ai\Events\AgentPrompted::class, function ($event) {
                $log = \App\Models\ChatbotRequestLog::where('invocation_id', $event->invocationId)->first();
                if ($log) {
                    $startTime = self::$aiStartTimes[$event->invocationId] ?? null;
                    $latency = $startTime ? (microtime(true) - $startTime) : null;

                    $log->update([
                        'status' => 'success',
                        'response' => $event->response->text,
                        'error_message' => null,
                        'latency_seconds' => $latency ? round($latency, 3) : null,
                    ]);
                }
            });
        } catch (\Exception $e) {
            // Silently fail
        }
    }
}
