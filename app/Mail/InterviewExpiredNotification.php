<?php

namespace App\Mail;

use App\Models\MemberRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InterviewExpiredNotification extends Mailable
{
    use Queueable, SerializesModels;

    public MemberRequest $request;

    public function __construct(MemberRequest $request)
    {
        $this->request = $request;
    }

    public function build()
    {
        return $this->subject('Pengajuan Anda Dibatalkan Otomatis')
            ->view('emails.interview_expired')
            ->with([
                'email' => $this->request->email,
                'reason' => $this->request->reason,
            ]);
    }
}
