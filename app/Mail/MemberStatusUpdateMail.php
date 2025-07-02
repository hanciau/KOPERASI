<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\MemberRequest;

class MemberStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $request;
    public $messageText;

    public function __construct(MemberRequest $request, string $messageText)
    {
        $this->request = $request;
        $this->messageText = $messageText;
    }

    public function build()
    {
        return $this->subject('Status Pengajuan Anda Telah Diperbarui')
                    ->view('emails.status_update');
    }
}
