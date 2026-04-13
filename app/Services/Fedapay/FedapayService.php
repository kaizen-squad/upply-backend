<?php
    namespace App\Services\FedapayServices;

use FedaPay\Payout as FedapayPayout;
use FedaPay\Transaction as FedaPayTransaction ;

    class FedapayService{

        //Fonction pour la verification de la transaction (collecte) vu que la collecte est fait au niveau du front
        public function verifyCollect($transactionId){
            //Configuration des SDKs de Fedapay pour l'authentification
            \FedaPay\FedaPay::setApiKey(config('fedapay.secret_key'));
            \FedaPay\FedaPay::setEnvironment(config('fedapay.environment'));

            try{
                //Verifier le statut de la transaction dont il est question
                $transaction = FedaPayTransaction::retrieve($transactionId);

                //Verification de la status de la transaction
                if($transaction->status !== 'approved'){
                    //Si la transaction a echoué envoyer une erreur avec les données de la transaction
                    return response()->json([
                        'success' => false,
                        'message' => 'Transaction denied',
                        'data' => $transaction
                    ], 403);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Transaction successfully',
                    'data' => $transaction
                ], 200);

            }catch(\Exception $e){
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 500);
            }
        }


        //Fonction pour la gestion du payout (versement de l'argent vers le compte de la personne du)
        public function payout($transactionId, $data){
            try{
                $createPayout = FedapayPayout::create($data);
                
            }catch(\Exception $e){
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 500);
            }
        }
    }
