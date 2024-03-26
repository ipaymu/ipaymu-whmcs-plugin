<?php 

class Helper {

    private $base;
    private $va;
    private $secret;


    public function __construct($base, $va, $secret) {
        $this->base = $base;
        $this->va = $va;
        $this->secret = $secret;
    }

    public function header($body)
    {
        // throw new Exception($secret);
        $method       = 'POST'; //method

        // *Don't change this
        $jsonBody     = json_encode($body, JSON_UNESCAPED_SLASHES);
        $requestBody  = strtolower(hash('sha256', $jsonBody));
        $stringToSign = strtoupper($method) . ':' . $this->va . ':' . $requestBody . ':' . $this->secret;
        $signature    = hash_hmac('sha256', $stringToSign, $this->secret);
        $timestamp    = Date('YmdHis');
        //End Generate Signature

        return [
            'signature' => $signature,
            'timestamp' => $timestamp,
            'body' => $body,
        ];
    }

    public function send($endPoint, $body){
        $header = $this->header($body);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->base.$endPoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($header['body']),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'signature: '.$header['signature'],
                'va: '.$this->va,
                'timestamp: '.$header['timestamp']
            ),
        ));

        $err = curl_error($curl);
        $response['res'] = json_decode(curl_exec($curl),true);
        $response['err'] = $err;
        curl_close($curl);
        
        // throw new Exception(json_encode($response));
        
        return $response;
    }

    public function create($product, $qty, $price, $name, $phone, $email, $returnurl, $callbackurl){

        $body['product']    = $product;
        $body['qty']        = $qty;
        $body['price']      = $price;

        $body['buyerName']  = $name;
        $body['buyerEmail'] = $email;
        $body['buyerPhone'] = $phone == null ? null : $phone;

        $body['returnUrl']  = $returnurl;
        $body['notifyUrl']  = $callbackurl;

        $body['feeDirection']  = 'BUYER';

        return $this->send('/api/v2/payment', $body);
    }

    public function check_transaction($transaction_id, $account = null)
    {
        $body['transactionId']    = $transaction_id;

        if ($account != null) {
            $body['account'] = $account;
        }

        return $this->send('/api/v2/transaction', $body);
    }
}