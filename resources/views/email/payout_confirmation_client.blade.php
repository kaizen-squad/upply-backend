<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 40px 20px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.025em; }
        .content { padding: 40px; }
        .info-card { background: #f0f9ff; border: 1px solid #e0f2fe; border-radius: 12px; padding: 24px; margin-bottom: 32px; }
        .info-title { font-size: 14px; color: #0369a1; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-bottom: 12px; }
        .details { border-top: 1px solid #e2e8f0; padding-top: 24px; }
        .detail-item { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; }
        .detail-label { color: #64748b; }
        .detail-value { font-weight: 600; color: #1e293b; }
        .footer { padding: 24px; text-align: center; background: #f1f5f9; color: #64748b; font-size: 13px; }
        .btn { display: inline-block; padding: 12px 24px; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Mission Terminée !</h1>
        </div>
        <div class="content">
            <p>Bonjour,</p>
            <p>Nous vous confirmons que le paiement pour votre mission a été finalisé. Le prestataire a bien reçu ses fonds.</p>
            
            <div class="info-card">
                <div class="info-title">Récapitulatif de la mission</div>
                <p style="margin: 0; font-size: 18px; font-weight: 700; color: #0c4a6e;">{{ $taskTitle }}</p>
                <p style="margin: 4px 0 0; color: #334155;">{{ $providerName }} a été payé pour son intervention.</p>
            </div>

            <div class="details">
                <div class="detail-item">
                    <span class="detail-label">Montant total</span>
                    <span class="detail-value">{{ number_format($amountGross, 0, ',', ' ') }} XOF</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Statut</span>
                    <span class="detail-value" style="color: #059669;">Libéré</span>
                </div>
            </div>

            <p style="margin-top: 32px;">Merci d'avoir utilisé Upply pour vos besoins !</p>
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}" class="btn">Laisser un avis</a>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Upply. Tous droits réservés.
        </div>
    </div>
</body>
</html>
