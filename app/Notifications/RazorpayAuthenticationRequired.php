<?php

namespace App\Notifications;

use App\Models\Invoice\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RazorpayAuthenticationRequired extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The invoice requiring authentication.
     *
     * @var \App\Models\Invoice\Invoice
     */
    protected $invoice;

    /**
     * The payment authentication link.
     *
     * @var string
     */
    protected $paymentLink;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice, string $paymentLink)
    {
        $this->invoice = $invoice;
        $this->paymentLink = $paymentLink;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $plan = $this->invoice->subscription ? $this->invoice->subscription->plan->name : null;
        $amount = $this->invoice->formattedTotal();

        return (new MailMessage)
            ->subject(t('authenticate_recurring_payment'))
            ->greeting(t('hello_name', ['name' => $notifiable->firstname ?? 'there']))
            ->line(t('razorpay_authentication_email_intro'))
            ->line(t('invoice_number').': '.$this->invoice->invoice_number)
            ->line(t('amount').': '.$amount)
            ->when($plan, function ($mail) use ($plan) {
                return $mail->line(t('plan').': '.$plan);
            })
            ->line(t('razorpay_rbi_requirement'))
            ->action(t('authenticate_payment'), $this->paymentLink)
            ->line(t('authentication_link_expires'))
            ->line(t('thank_you_for_using_our_application'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => t('payment_authentication_required'),
            'message' => t('action_required_authenticate_payment_for', ['invoice' => $this->invoice->invoice_number]),
            'action_url' => $this->paymentLink,
            'action_text' => t('authenticate_now'),
            'invoice_id' => $this->invoice->id,
            'type' => 'razorpay_authentication',
        ];
    }
}
