<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class DeployController extends ApiController
{
    /**
     * Handle GitHub Webhook Deployment (Auto-Pull & Migrate)
     */
    public function deploy(Request $request): JsonResponse
    {
        $githubEvent = $request->header('X-GitHub-Event', 'push');
        
        // Handle GitHub Ping event (ketika webhook baru dibuat atau dites)
        if ($githubEvent === 'ping') {
            return $this->ok([
                'event' => 'ping',
                'message' => 'GitHub Webhook successfully connected to SakuraKotoba FIB Backend!',
                'zen' => $request->input('zen'),
                'hook_id' => $request->input('hook_id'),
            ], 'Webhook connected successfully');
        }

        // Secret Verification
        $secret = env('GITHUB_WEBHOOK_SECRET', '45827bfe592c2309e3958b8a7131669fb1e92f56cd83d7e76042a464ffe79f3a');
        if (!empty($secret)) {
            $signature = $request->header('X-Hub-Signature-256');
            if ($signature) {
                $computed = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);
                if (!hash_equals($signature, $computed)) {
                    return $this->fail('Invalid webhook secret signature', 403);
                }
            }
        }

        $basePath = base_path();
        $output = [];

        try {
            // 1. Git pull
            $gitPull = shell_exec("cd /d {$basePath} && git pull origin main 2>&1") 
                     ?? shell_exec("cd {$basePath} && git pull 2>&1");
            $output['git_pull'] = trim($gitPull ?? 'No output');

            // 2. Run migrations
            $migrate = shell_exec("cd /d {$basePath} && php artisan migrate --force 2>&1")
                     ?? shell_exec("cd {$basePath} && php artisan migrate --force 2>&1");
            $output['migrations'] = trim($migrate ?? 'No output');

            // 3. Clear optimize/cache
            $cacheClear = shell_exec("cd /d {$basePath} && php artisan optimize:clear 2>&1")
                        ?? shell_exec("cd {$basePath} && php artisan optimize:clear 2>&1");
            $output['optimize_clear'] = trim($cacheClear ?? 'No output');

            Log::info('GitHub Webhook Auto-Deploy executed successfully', [
                'event' => $githubEvent,
                'output' => $output,
            ]);

            return $this->ok([
                'event' => $githubEvent,
                'deployed_at' => now()->toIso8601String(),
                'output' => $output,
            ], 'Deployment executed successfully');

        } catch (\Throwable $e) {
            Log::error('GitHub Webhook Deploy error: ' . $e->getMessage());
            return $this->fail('Deploy error: ' . $e->getMessage(), 500, ['output' => $output]);
        }
    }
}
