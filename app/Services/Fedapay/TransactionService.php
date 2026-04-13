<?php
    namespace App\Services\FedapayTransactionService;

use App\Models\TransactionLogs;
use App\Models\Transaction;
use App\Models\User;
use App\Services\FedapayServices\FedapayService;
use Illuminate\Support\Facades\Auth;

    class TransactionService{
        //Fonction pour enregistrer une transaction dans les tables Transaction et TransactionLog
        public function handleTransaction($transactionId, $prestataireId){
            $fedapayService = new FedapayService();
            $transaction = $fedapayService->verifyCollect($transactionId);
            $clientId = Auth::user()->id;

                try{
                    //Verification si la transaction est valide si oui enregistrer dans les tables concernées
                    if($transaction['success'] === false){
                        //Enregistrer dans la table de log

                        $transactionLog = TransactionLogs::create([
                            'transaction_id' => $transaction['transaction_id'],
                            'client_id' => $clientId,
                            'prestataire_id' => $prestataireId,
                            'description' => $transaction['data']['status'],
                            'status' => $transaction['status'],
                            'metadata' => $transaction['metadata']
                        ]);

                        //Enregistrement de la transaction
                       $createTransaction = Transaction::updateOrCreate([
                            'user_id' => $clientId,
                            'prestataire_id' => $prestataireId,
                            'amount' => $transaction['amount'],
                            'currency' => $transaction[''],
                            'mode_payment' => $transaction['mode'],
                            'description' => $transaction['description'],
                            'status' => 'escrow_lock'
                        ]);

                        if($transactionLog && $createTransaction){
                            return response()->json([
                                'success' => true,
                                'message' => 'Transaction created successfully',
                            ], 200);
                        }
                    }
                }catch(\Exception $e){
                    return response()->json([
                        'success' => false,
                        'error' => $e->getMessage(),
                    ], 500);
                }

            }

        //Fonction qui permet maintenant de faire le versemnt directe sur le numero de l'utilisateur
        public function release($clientId, $prestataireId,$amount, $currency,$description){
            $fedapayService = new FedapayService();

            try{
                if(!$clientId && !$prestataireId){
                    return response()->json([
                        'success' => false,
                        'message' => 'clientid and prestataireId required'
                    ]);
                }

                //Recherche de la transaction où le client et le prestataire corresponde
                $transaction = Transaction::where('client_id', $clientId and 'prestataire_id', $prestataireId);

                if(!$transaction){
                    return response()->json([
                        'success' => false,
                        'message' => 'Transaction not found'
                    ], 404);
                }

                //Recherche des infos du prestataire
                $prestataireInfo = User::where('user_id', $prestataireId);

                $data = [
                    'amount' => $amount,
                    'currency' => ['iso'=>$currency],
                    'description' => $description,
                    'customer' => [
                        'firstname' => $prestataireInfo['firstname'],
                        'lastname' => $prestataireInfo['lastname'],
                        'email' => $prestataireInfo['email'],
                        'phone_number' => [
                            'number' => $prestataireInfo['phone'],
                            'country' => $prestataireInfo['country']
                        ]
                    ]
                ];

                //Lancer maintenant le payout
                $payout = $fedapayService->payout($data);

                //Verification si le payout s'est bien passé
                if($payout['success'] === true){
                    $payout['data'].sendNow();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Payout successfully',
                ], 200);
            }catch(\Exception $e){
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 500);
            }
        }
    }
