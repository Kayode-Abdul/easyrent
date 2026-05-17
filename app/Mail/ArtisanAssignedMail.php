<?php

namespace App\Mail;

use App\Models\ArtisanTask;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ArtisanAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $task;
    public $code;

    /**
     * Create a new message instance.
     */
    public function __construct(ArtisanTask $task, string $code)
    {
        $this->task = $task;
        $this->code = $code;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('You have been assigned to task: ' . optional($this->task->complaint)->title)
                    ->view('emails.artisan-assigned')
                    ->with([
                        'task' => $this->task,
                        'code' => $this->code,
                    ]);
    }
}
