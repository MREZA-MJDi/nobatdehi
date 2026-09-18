<?php

namespace App\Http\Controllers;

use App\Services\Booking\SmsBookingReplyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsWebhookController extends Controller
{
    public function bookingReply(
        Request $request,
        SmsBookingReplyService $replies
    ): JsonResponse {
        $this->verifySignature($request);

        $from = (string) (
            $request->input('from')
            ?? $request->input('from_phone')
            ?? $request->input('sender')
            ?? ''
        );

        $body = (string) (
            $request->input('text')
            ?? $request->input('message')
            ?? $request->input('body')
            ?? ''
        );

        $messageId = $request->input(
            'message_id'
        ) ?? $request->input('id');

        if ($from === '' || trim($body) === '') {
            return response()->json([
                'ok' => false,
                'message' => 'پارامترهای پیامک ناقص است.',
            ], 422);
        }

        try {
            $result = $replies->handle(
                $from,
                $body,
                $messageId ? (string) $messageId : null,
                $request->all()
            );
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'ok' => false,
                'message' => 'پردازش پیامک انجام نشد.',
            ], 500);
        }

        return response()->json(
            $result,
            200
        );
    }

    private function verifySignature(
        Request $request
    ): void {
        $secret = (string) config(
            'services.nobat_sms.webhook_secret'
        );

        abort_unless(
            $secret !== '',
            503,
            'SMS webhook secret is not configured.'
        );

        $provided = trim((string) $request->header(
            'X-NOBAT-SIGNATURE'
        ));

        $provided = str_starts_with(
            $provided,
            'sha256='
        )
            ? substr($provided, 7)
            : $provided;

        $expected = hash_hmac(
            'sha256',
            $request->getContent(),
            $secret
        );

        abort_unless(
            $provided !== '' &&
            hash_equals($expected, $provided),
            401
        );
    }
}
