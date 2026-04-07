<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockProductsMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, \App\Models\Product>  $products
     */
    public function __construct(public Collection $products)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Low stock alert — products need restocking'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.low-stock-products',
        );
    }
}
