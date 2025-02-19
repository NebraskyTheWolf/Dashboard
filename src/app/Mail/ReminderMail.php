<?php

namespace App\Mail;

use App\Models\Event\EventsInteresteds;
use App\Models\SocialMedia;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $event;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct($event, $user)
    {
        $this->event = $event;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Připomenutí zahájení události',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        $currentDate = Carbon::parse($this->event->begin);
        $day = $currentDate->day;
        $month = $currentDate->month;
        $time =  str_pad($currentDate->hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($currentDate->minute, 2, '0', STR_PAD_LEFT);

        return new Content(
            view: 'emails.admin.reminder',
            with: [
                'month' => $month,
                'day' => $day,
                'time' => $time,
                'name' => $this->user->name,
                'dayFull' => $currentDate->dayName,
                'monthFull' => $currentDate->monthName,
                'eventName' => $this->event->name,
                'interested' => $this->getPeoples(),
                'socials' => SocialMedia::all()
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    public function getPeoples(): int {
        $peoples = EventsInteresteds::where('event_id', $this->event->event_id)->all();
        return count($peoples);
    }
}
