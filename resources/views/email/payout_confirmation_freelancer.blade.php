<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 40px 20px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.025em; }
        .content { padding: 40px; }
        .amount-card { background: #f0fdf4; border: 1px solid #dcfce7; border-radius: 12px; padding: 24px; text-align: center; margin-bottom: 32px; }
        .amount-label { font-size: 14px; color: #166534; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }
        .amount-value { font-size: 36px; font-weight: 800; color: #065f46; margin: 8px 0; }
        .details { border-top: 1px solid #e2e8f0; padding-top: 24px; }
        .detail-item { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; }
        .detail-label { color: #64748b; }
        .detail-value { font-weight: 600; color: #1e293b; }
        .footer { padding: 24px; text-align: center; background: #f1f5f9; color: #64748b; font-size: 13px; }
        .btn { display: inline-block; padding: 12px 24px; background: #10b981; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Paiement Reçu !</h1>
        </div>
        <div class="content">
            <p>Bonjour,</p>
            <p>Bonne nouvelle ! Le paiement pour votre mission a été débloqué et transféré vers votre compte.</p>
            
            <div class="amount-card">
                <div class="amount-label">Montant versé</div>
                <div class="amount-value">{{ number_format($amount, 0, ',', ' ') }} XOF</div>
            </div>

            <div class="details">
                <div class="detail-item">
                    <span class="detail-label">Mission</span>
                    <span class="detail-value">{{ $taskTitle }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Date du transfert</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($date)->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <p style="margin-top: 32px;">Merci pour votre excellent travail sur Upply !</p>
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}" class="btn">Accéder à mon tableau de bord</a>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Upply. Tous droits réservés.
        </div>
    </div>
</body>
</html>
