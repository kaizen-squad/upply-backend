<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; }
        .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #059669 0%, #10b981 100%); padding: 40px 20px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; letter-spacing: -0.025em; }
        .content { padding: 40px; }
        .info-card { background: #ecfdf5; border: 1px solid #d1fae5; border-radius: 12px; padding: 24px; margin-bottom: 32px; }
        .info-title { font-size: 14px; color: #047857; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; margin-bottom: 12px; }
        .details { border-top: 1px solid #e2e8f0; padding-top: 24px; }
        .detail-item { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; }
        .detail-label { color: #64748b; }
        .detail-value { font-weight: 600; color: #1e293b; }
        .footer { padding: 24px; text-align: center; background: #f1f5f9; color: #64748b; font-size: 13px; }
        .btn { display: inline-block; padding: 12px 24px; background: #059669; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bonne nouvelle : Paiement sécurisé !</h1>
        </div>
        <div class="content">
            <p>Bonjour,</p>
            <p>Nous vous informons que le client <strong>{{ $clientName }}</strong> a effectué le paiement pour la mission suivante. Les fonds sont maintenant sécurisés dans notre système d'entiercement (escrow).</p>
            
            <div class="info-card">
                <div class="info-title">Récapitulatif de la mission</div>
                <p style="margin: 0; font-size: 18px; font-weight: 700; color: #064e3b;">{{ $taskTitle }}</p>
                <p style="margin: 4px 0 0; color: #065f46;">Vous pouvez maintenant commencer ou poursuivre votre travail en toute sérénité.</p>
            </div>

            <div class="details">
                <div class="detail-item">
                    <span class="detail-label">Montant garanti</span>
                    <span class="detail-value">{{ number_format($amountNet, 0, ',', ' ') }} XOF</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Statut du paiement</span>
                    <span class="detail-value" style="color: #059669;">Sécurisé en Escrow</span>
                </div>
            </div>

            <p style="margin-top: 32px;">Les fonds vous seront libérés automatiquement dès que le client aura validé la fin de la mission.</p>
            <div style="text-align: center;">
                <a href="{{ config('app.url') }}" class="btn">Voir ma mission</a>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Upply. Tous droits réservés.
        </div>
    </div>
</body>
</html>
