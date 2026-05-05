<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DeployWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $internalToken = (string) config('services.deploy_webhook.internal_token', '');
        $receivedToken = (string) $request->header('X-Deploy-Token', '');
        $githubSecret = (string) config('services.deploy_webhook.github_secret', '');
        $githubSignature = (string) $request->header('X-Hub-Signature-256', '');

        $isTrustedGatewayCall = $internalToken !== ''
            && $receivedToken !== ''
            && hash_equals($internalToken, $receivedToken);

        $isDirectGitHubCall = $githubSecret !== '' && $githubSignature !== '';

        if ($internalToken !== '' && !$isTrustedGatewayCall && !$isDirectGitHubCall) {
            return response('Unauthorized webhook', 401);
        }

        $event = (string) $request->header('X-GitHub-Event', '');
        if ($event !== '' && $event !== 'push') {
            return response('Event ignored', 202);
        }

        $payloadRef = (string) $request->input('ref', '');
        if ($payloadRef === '') {
            return response('Invalid payload: missing ref', 422);
        }

        $forwardUrl = (string) config('services.deploy_webhook.forward_url', '');
        if ($forwardUrl === '') {
            return response('Deploy forward URL is not configured', 500);
        }

        $forwardResponse = Http::timeout(10)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-GitHub-Event' => $event !== '' ? $event : 'push',
                'X-Deploy-Token' => $internalToken,
            ])
            ->withBody($request->getContent(), 'application/json')
            ->post($forwardUrl);

        if (!$forwardResponse->successful()) {
            Log::error('Deploy webhook forward failed', [
                'status' => $forwardResponse->status(),
                'body' => $forwardResponse->body(),
                'forward_url' => $forwardUrl,
                'ref' => $payloadRef,
            ]);

            return response('Deploy forward failed', 502);
        }

        Log::info('Deploy webhook accepted', [
            'event' => $event,
            'ref' => $payloadRef,
            'repository' => $request->input('repository.full_name'),
            'forward_url' => $forwardUrl,
        ]);

        return response('Deploy started', 202);
    }
}