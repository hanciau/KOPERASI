<?php
namespace App\Mail;

use App\Models\MemberRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InterviewInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $request;

    public function __construct(MemberRequest $request)
    {
        $this->request = $request;
    }

    public function build()
    {
        return $this->subject('Undangan Interview Calon Member Koperasi')
                    ->view('emails.interview_invitation');
    }
}
