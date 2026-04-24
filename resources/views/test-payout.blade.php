<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payout Escrow Upply</title>
    <style>
        body { font-family: sans-serif; padding: 20px; line-height: 1.6; background: #f4f7f6; }
        .card { background: #fff; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 500px; margin: auto; }
        .btn { background: #28a745; color: #fff; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; width: 100%; font-size: 1.1em; }
        .btn:hover { background: #218838; }
        .info { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Validation du Payout</h1>
        <div class="info">
            <p>Transaction FedaPay : <strong>{{ $transaction->fedapay_transaction_id }}</strong></p>
            <p>Montant Net à verser : <strong>{{ $transaction->amount_net }} XOF</strong></p>
            <p>Prestataire : <strong>{{ $transaction->prestataire->name }}</strong></p>
            <p>Statut Actuel : <span style="color: orange;">{{ $transaction->status }}</span></p>
        </div>

        <button class="btn" onclick="makePayout()">Valider le Payout (Finaliser)</button>
        
        <div id="result" style="margin-top: 20px;"></div>
    </div>

    <script>
        function makePayout() {
            const btn = document.querySelector('.btn');
            btn.disabled = true;
            btn.innerText = 'Traitement en cours...';

            fetch('/api/transactions/{{ $transaction->fedapay_transaction_id }}/payout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                const resDiv = document.getElementById('result');
                if (data.success) {
                    resDiv.innerHTML = '<p style="color: green;">✔ ' + data.message + '</p>';
                    btn.innerText = 'Payout Terminé';
                } else {
                    resDiv.innerHTML = '<p style="color: red;">✘ Erreur : ' + data.message + '</p>';
                    btn.disabled = false;
                    btn.innerText = 'Réessayer le Payout';
                }
            })
            .catch(err => {
                alert('Erreur réseau');
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>
