<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DeployWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $internalToken = (string) config('services.deploy_webhook.internal_token', '');
        if ($internalToken !== '') {
            $receivedToken = (string) $request->header('X-Deploy-Token', '');
            if (!hash_equals($internalToken, $receivedToken)) {
                return response('Unauthorized webhook', 401);
            }
        }

        $event = (string) $request->header('X-GitHub-Event', '');
        if ($event !== '' && $event !== 'push') {
            return response('Event ignored', 202);
        }

        $scriptPath = base_path('deploy.sh');
        if (!is_file($scriptPath) || !is_executable($scriptPath)) {
            return response('deploy.sh is missing or not executable', 500);
        }

        $payloadFile = tempnam(storage_path('app'), 'deploy_payload_');
        if ($payloadFile === false) {
            return response('Failed to prepare payload file', 500);
        }

        file_put_contents($payloadFile, $request->getContent());

        $payloadRef = (string) $request->input('ref', '');
        if ($payloadRef === '') {
            return response('Invalid payload: missing ref', 422);
        }

        $appDir = escapeshellarg(base_path());
        $branch = escapeshellarg((string) config('services.deploy_webhook.branch', 'main'));
        $secret = escapeshellarg((string) config('services.deploy_webhook.github_secret', ''));
        $signature = escapeshellarg((string) $request->header('X-Hub-Signature-256', ''));

        $script = escapeshellarg($scriptPath);
        $payload = escapeshellarg($payloadFile);
        $logFile = escapeshellarg(storage_path('logs/deploy-hook.log'));

        $command = "APP_DIR=$appDir DEPLOY_BRANCH=$branch WEBHOOK_SECRET=$secret "
            . "HTTP_X_HUB_SIGNATURE_256=$signature X_HUB_SIGNATURE_256=$signature "
            . "$script < $payload >> $logFile 2>&1; rm -f $payload";

        exec('nohup sh -c ' . escapeshellarg($command) . ' > /dev/null 2>&1 &');

        Log::info('Deploy webhook accepted', [
            'event' => $event,
            'ref' => $request->input('ref'),
            'repository' => $request->input('repository.full_name'),
        ]);

        return response('Deploy started', 202);
    }
}