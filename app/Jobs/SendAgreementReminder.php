<?php

namespace App\Jobs;

use App\Mail\AgreementReminder;
use App\Models\FollowupAgreementDate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendAgreementReminder implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $agreementDateId
    ) {}

    public function handle(): void
    {
        $date = FollowupAgreementDate::with([
            'followupAgreement.visitReport.user'
        ])->find($this->agreementDateId);

        if (!$date) {
            return;
        }

        $agreement = $date->followupAgreement;

        if (!$agreement) {
            return;
        }

        if ((int) $date->estado !== 2) {
            return;
        }

        if ((int) $agreement->status !== 1) {
            return;
        }

        $currentDate = $agreement->dates()
            ->where('estado', 2)
            ->latest('id')
            ->first();

        if (!$currentDate || $currentDate->id !== $date->id) {
            return;
        }

        if ($date->recordatorio_enviado_at !== null) {
            return;
        }

        $user = $agreement->visitReport?->user;

        if (!$user || !$user->email) {
            return;
        }

        Mail::to([
            $user->email,
            'luisangelem.dp@gmail.com',
        ])->send(new AgreementReminder($date));

        $date->update([
            'recordatorio_enviado_at' => now(),
        ]);
    }
}
