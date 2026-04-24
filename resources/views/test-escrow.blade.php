<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Escrow Upply</title>
    <script src="https://cdn.fedapay.com/checkout.js?v=1.1.7"></script>
    <style>
        body { font-family: sans-serif; padding: 20px; line-height: 1.6; background: #f4f7f6; }
        .card { background: #fff; padding: 15px; margin-bottom: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn { background: #007bff; color: #fff; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .badge { padding: 5px 10px; border-radius: 4px; font-size: 0.8em; }
        .badge-opened { background: #e3f2fd; color: #0d47a1; }
    </style>
</head>
<body>
    <h1>Test du Système d'Escrow</h1>
    <p>Connecté en tant que : <strong>{{ Auth::user()->name }} (Client)</strong></p>

    <div id="tasks">
        @foreach($tasks as $task)
            <div class="card">
                <h3>{{ $task->title }}</h3>
                <p>{{ $task->description }}</p>
                <p>Budget: <strong>{{ $task->budget }} XOF</strong></p>
                
                <h4>Candidatures Acceptées :</h4>
                @foreach($task->applications->where('status', 'ACCEPTEE') as $app)
                    <div style="border-left: 3px solid #28a745; padding-left: 10px;">
                        <p>Prestataire: <strong>{{ $app->prestataire->name }}</strong></p>
                        <button class="btn pay-btn" 
                                data-task-id="{{ $task->id }}" 
                                data-prestataire-id="{{ $app->prestataire->id }}" 
                                data-amount="{{ $task->budget }}" 
                                data-title="{{ $task->title }}">
                            Valider le Prestataire (Payer)
                        </button>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <script>
        document.querySelectorAll('.pay-btn').forEach(btn => {
            FedaPay.init(btn, {
                public_key: '{{ config("fedapay.public_key") }}',
                transaction: {
                    amount: parseInt(btn.dataset.amount),
                    description: 'Paiement pour : ' + btn.dataset.title,
                    custom_metadata: {
                        prestataire_id: btn.dataset.prestataireId,
                        task_id: btn.dataset.taskId
                    }
                },
                onComplete: function(response) {
                    console.log('FedaPay response:', response);
                    if (response.transaction && response.transaction.status === 'approved') {
                        btn.innerText = 'Vérification...';
                        btn.disabled = true;

                        fetch('/api/transactions/' + response.transaction.id + '/verify', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            console.log('Verification data:', data);
                            if (data.success) {
                                // Redirect to payout page
                                window.location.href = '/test-payout/' + response.transaction.id;
                            } else {
                                alert('Erreur de vérification : ' + (data.message || data.data));
                                if(btn) btn.innerText = 'Réessayer la vérification';
                            }
                        })
                        .catch(err => {
                            console.error('Fetch error:', err);
                            alert('Erreur lors de la communication avec le serveur.');
                        });
                    }
                }
            });
        });
    </script>

</body>
</html>
