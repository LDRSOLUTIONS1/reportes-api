<?php

namespace App\Mail;

use App\Models\FollowupAgreementDate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AgreementReminder extends Mailable
{
    use Queueable, SerializesModels;

    public FollowupAgreementDate $agreementDate;

    public function __construct(FollowupAgreementDate $agreementDate)
    {
        $this->agreementDate = $agreementDate;
    }

    public function build()
    {
        return $this
            ->from(
                'notificacion@ldrsolutions.com.mx',
                'LDR Solutions, Foton'
            )
            ->subject('Recordatorio de acuerdo próximo a vencer')
            ->view('mail.agreement-reminder');
    }
}
