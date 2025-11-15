<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmação do Pedido #{{ $order->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .order-details {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .product-item:last-child {
            border-bottom: none;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #333;
        }
        .footer {
            background-color: #f4f4f4;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .status-badge {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pastelaria do Comércio</h1>
        <h2>Confirmação do Pedido #{{ $order->id }}</h2>
    </div>

    <p>Olá <strong>{{ $customer->name }}</strong>,</p>
    
    <p>Seu pedido foi recebido com sucesso! Abaixo estão os detalhes:</p>

    <div class="order-details">
        <h3>Informações do Pedido</h3>
        <p><strong>Número do Pedido:</strong> #{{ $order->id }}</p>
        <p><strong>Data:</strong> {{ $order->order_date->format('d/m/Y H:i') }}</p>
        <p><strong>Status:</strong> <span class="status-badge">{{ ucfirst($order->status) }}</span></p>
        
        @if($order->notes)
        <p><strong>Observações:</strong> {{ $order->notes }}</p>
        @endif

        <h3>Produtos Pedidos</h3>
        @foreach($products as $product)
        <div class="product-item">
            <div>
                <strong>{{ $product->name }}</strong><br>
                <small>Quantidade: {{ $product->pivot->quantity }}</small>
            </div>
            <div>
                <strong>R$ {{ number_format($product->pivot->unit_price * $product->pivot->quantity, 2, ',', '.') }}</strong><br>
                <small>R$ {{ number_format($product->pivot->unit_price, 2, ',', '.') }} cada</small>
            </div>
        </div>
        @endforeach

        <div class="total">
            Total: R$ {{ number_format($total, 2, ',', '.') }}
        </div>
    </div>

    <div class="order-details">
        <h3>Dados de Contato</h3>
        <p><strong>E-mail:</strong> {{ $customer->email }}</p>
        <p><strong>Telefone:</strong> {{ $customer->phone }}</p>
        <p><strong>Endereço:</strong> {{ $customer->address }}
            @if($customer->complement), {{ $customer->complement }}@endif<br>
            {{ $customer->neighborhood }} - CEP: {{ $customer->zip_code }}
        </p>
    </div>

    <p>Em breve entraremos em contato para confirmar a entrega do seu pedido.</p>
    
    <p>Obrigado por escolher a <strong>Pastelaria do Comércio</strong>!</p>

    <div class="footer">
        <p>Este é um e-mail automático. Por favor, não responda.</p>
        <p>Para dúvidas, entre em contato conosco pelo telefone (11) 99999-9999</p>
        <p>&copy; 2025 Pastelaria do Comércio - Todos os direitos reservados</p>
    </div>
</body>
</html>