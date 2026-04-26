<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { 
            font-family: 'Helvetica', sans-serif; 
            color: #1f2937; 
            line-height: 1.5;
            margin: 0;
            padding: 40px;
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #4f46e5; 
            padding-bottom: 20px; 
            margin-bottom: 30px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 10px;
        }
        .receipt-title { 
            font-size: 24px; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 40px;
        }
        .info-grid td {
            vertical-align: top;
            width: 50%;
        }
        .details { 
            margin-top: 30px; 
        }
        .details table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        .details th { 
            background-color: #f9fafb;
            text-align: left; 
            padding: 12px; 
            border-bottom: 2px solid #e5e7eb;
            color: #4b5563;
            font-size: 14px;
            text-transform: uppercase;
        }
        .details td { 
            text-align: left; 
            padding: 15px 12px; 
            border-bottom: 1px solid #e5e7eb; 
            color: #111827;
        }
        .total-section { 
            margin-top: 30px; 
            text-align: right; 
        }
        .total-label {
            font-size: 16px;
            color: #4b5563;
        }
        .total-amount { 
            font-size: 24px; 
            font-weight: bold; 
            color: #4f46e5;
            margin-top: 5px;
        }
        .footer { 
            margin-top: 100px; 
            font-size: 12px; 
            text-align: center; 
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">UPPLY</div>
        <div class="receipt-title">Reçu de Paiement</div>
    </div>

    <table class="info-grid">
        <tr>
            <td>
                <strong>Émis pour :</strong><br>
                {{ $provider_name }}<br>
                Paiement de prestation
            </td>
            <td style="text-align: right;">
                <strong>Référence :</strong> #{{ $transaction_id }}<br>
                <strong>Date :</strong> {{ \Carbon\Carbon::parse($date)->format('d/m/Y H:i') }}
            </td>
        </tr>
    </table>

    <div class="details">
        <table>
            <thead>
                <tr>
                    <th>Description de la mission</th>
                    <th style="text-align: right;">Montant</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $task_title }}</td>
                    <td style="text-align: right;">{{ number_format($amount, 0, ',', ' ') }} XOF</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="total-section">
        <div class="total-label">Montant total versé</div>
        <div class="total-amount">{{ number_format($amount, 0, ',', ' ') }} XOF</div>
    </div>

    <div class="footer">
        <p>Ce document confirme le transfert de fonds effectué via la plateforme Upply.</p>
        <p>&copy; {{ date('Y') }} Upply. Tous droits réservés.</p>
    </div>
</body>
</html>
