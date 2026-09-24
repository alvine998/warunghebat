<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PushSubscriptionController extends Controller
{
    /** Saves (or refreshes) one FCM device token for the signed-in user. */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'min:20', 'max:512'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ], [
            'token.required' => 'Token notifikasi wajib diisi.',
            'token.min' => 'Token notifikasi tidak valid.',
            'token.max' => 'Token notifikasi terlalu panjang.',
            'device_name.max' => 'Nama perangkat maksimal 100 karakter.',
        ]);

        $request->user()->fcmTokens()->updateOrCreate(
            ['token' => $validated['token']],
            ['device_name' => $validated['device_name'] ?? $this->deviceLabel((string) $request->userAgent())],
        );

        return response()->json(['saved' => true]);
    }

    /** Removes one FCM device token; idempotent so logout stays simple. */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'min:20', 'max:512'],
        ], [
            'token.required' => 'Token notifikasi wajib diisi.',
        ]);

        $request->user()->fcmTokens()->where('token', $validated['token'])->delete();

        return response()->json(['removed' => true]);
    }

    /**
     * Public Firebase web config as a tiny JS file for the service worker.
     * importScripts() in sw.js cannot read Blade, so the worker loads this
     * instead. Empty keys render null and the worker stays a plain cache SW.
     */
    public function workerConfig(): Response
    {
        $config = $this->publicConfig();

        $body = $config === null
            ? 'self.__FIREBASE_CONFIG__ = null;'
            : 'self.__FIREBASE_CONFIG__ = '.json_encode($config, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).';';

        return response($body, 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    /**
     * @return array{apiKey: string, authDomain: string, projectId: string, storageBucket: string, messagingSenderId: string, appId: string, vapidKey: string}|null
     */
    public static function publicConfig(): ?array
    {
        $firebase = config('services.firebase', []);

        $config = [
            'apiKey' => (string) ($firebase['api_key'] ?? ''),
            'authDomain' => (string) ($firebase['auth_domain'] ?? ''),
            'projectId' => (string) ($firebase['project_id'] ?? ''),
            'storageBucket' => (string) ($firebase['storage_bucket'] ?? ''),
            'messagingSenderId' => (string) ($firebase['messaging_sender_id'] ?? ''),
            'appId' => (string) ($firebase['app_id'] ?? ''),
            'vapidKey' => (string) ($firebase['vapid_key'] ?? ''),
        ];

        if ($config['apiKey'] === '' || $config['projectId'] === '' || $config['messagingSenderId'] === '' || $config['appId'] === '' || $config['vapidKey'] === '') {
            return null;
        }

        return $config;
    }

    private function deviceLabel(string $userAgent): ?string
    {
        $label = trim(mb_substr($userAgent, 0, 100));

        return $label === '' ? null : $label;
    }
}
