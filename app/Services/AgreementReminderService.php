<?php

namespace App\Services;

use App\Jobs\SendAgreementReminder;
use App\Models\FollowupAgreementDate;

class AgreementReminderService
{
    public static function schedule(
        FollowupAgreementDate $date
    ): void {

        if (!$date->fecha_compromiso) {
            return;
        }

        $recordatorio = $date->fecha_compromiso
            ->copy()
            ->subDays(2)
            ->setTime(8, 0, 0);

        if ($recordatorio->isPast()) {

            SendAgreementReminder::dispatch(
                $date->id
            );

            return;
        }

        SendAgreementReminder::dispatch(
            $date->id
        )->delay($recordatorio);
    }
}
