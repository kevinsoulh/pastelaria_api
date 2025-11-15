<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmationNotification extends Notification
{
    use Queueable;

    protected Order $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $orderItems = $this->order->products->map(function ($product) {
            return "- {$product->name} x{$product->pivot->quantity} - R$ " . number_format($product->pivot->unit_price, 2, ',', '.');
        })->implode("\n");

        return (new MailMessage)
            ->subject('Pedido Confirmado - #' . $this->order->id)
            ->greeting('Olá ' . $this->order->customer->name . '!')
            ->line('Seu pedido foi confirmado com sucesso!')
            ->line('**Detalhes do Pedido:**')
            ->line('Número: #' . $this->order->id)
            ->line('Data: ' . $this->order->order_date->format('d/m/Y H:i'))
            ->line('Status: ' . $this->getStatusName($this->order->status))
            ->line('')
            ->line('**Itens do Pedido:**')
            ->line($orderItems)
            ->line('')
            ->line('**Total: R$ ' . number_format((float) $this->order->total_amount, 2, ',', '.') . '**')
            ->when($this->order->notes, function ($mail) {
                return $mail->line('**Observações:** ' . $this->order->notes);
            })
            ->action('Acompanhar Pedido', url('/'))
            ->line('Agradecemos pela preferência!')
            ->salutation('Atenciosamente, Equipe da Pastelaria');
    }

    /**
     * Get friendly status name
     */
    private function getStatusName(string $status): string
    {
        $statusMap = [
            'pending' => 'Pendente',
            'confirmed' => 'Confirmado',
            'preparing' => 'Em Preparação',
            'ready' => 'Pronto',
            'delivered' => 'Entregue',
            'cancelled' => 'Cancelado'
        ];

        return $statusMap[$status] ?? $status;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'total_amount' => $this->order->total_amount,
            'status' => $this->order->status,
        ];
    }
}