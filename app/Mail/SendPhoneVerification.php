<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendPhoneVerification extends Mailable
{
	use Queueable, SerializesModels;

	public $user;
	public $code;


	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct($user, $code,)
	{
		$this->user = $user;
		$this->code = $code;
	}

	/**
	 * Get the message envelope.
	 *
	 * @return \Illuminate\Mail\Mailables\Envelope
	 */
	public function envelope()
	{
		return new Envelope(
			subject: "Motoka Phone Verification Code",
		);
	}

	/**
	 * Get the message content definition.
	 *
	 * @return \Illuminate\Mail\Mailables\Content
	 */
	public function content()
	{
		return new Content(
			markdown: 'emails.phone-verification',
		);
	}

	/**
	 * Get the attachments for the message.
	 *
	 * @return array
	 */
	public function attachments()
	{
		return [];
	}
}
