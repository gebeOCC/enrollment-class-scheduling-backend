<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FacultyCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $userId;
    public $password;

    public function __construct($userId, $password)
    {
        $this->userId = $userId;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject('Your Faculty Account Credentials')
            ->view('emails.faculty_created');
    }
}
