<?php

namespace App\Jobs;

use App;
use App\Models\BillingHistory;
use App\Models\Organisation;
use DateInterval;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class BillOrganizationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $fee = 1000; // 1000NGN
    protected $currency = "NGN";


    /**
     * Create a new job instance.
     */
    public function __construct(public Organisation $org)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        //
        if (!$this->org->hasActiveSubscription()) {
            return; // Skip billing if no active subscription
        }


        $now = new DateTime();

        // Go to the beginning of the current month
        $now->modify('first day of this month');

        // Subtract one month from the beginning of the current month
        $previousMonth = clone $now; // Clone to avoid modifying the original object
        $previousMonth->sub(new DateInterval('P1M'));

        // Format the previous month date (optional)
        $previousMonthFormatted = $previousMonth->format('Y-m');


        $orgCount = $this->org->employees->count();
        $paymentMethod = $this->org->paymentMethods->first();

        $secretKey = config('paystack.secretKey'); // Assuming SECRET_KEY is stored in config/paystack.php
        $paystackUrl = config('paystack.paymentUrl');
        $baseUrl = $paystackUrl . '/transaction/charge_authorization';

        $client = new Client();

        $headers = [
            'Authorization' => 'Bearer ' . $secretKey,
            'Content-Type' => 'application/json',
        ];

        $data = [
            'authorization_code' => $paymentMethod->auth_code,
            'email' => $paymentMethod->email_address, // Get customer email from request
            'amount' => $orgCount * ($this->fee * 100), // Get amount in kobo from request,
            'currency' => $this->currency,
            'metadata' => [
                "type" => "SUBSCRIPTION",
                "description" => "Subscription for {$previousMonthFormatted}",
                "organisation_id" => $this->org->id,
            ]
        ];

        try {
            $response = $client->post($baseUrl, [
                'headers' => $headers,
                'json' => $data,
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();

            // You don't typically get a response body for charge authorization
            if ($statusCode >= 200 && $statusCode < 300) {
                $bh = new \App\Http\Controllers\BillingController();
                $event = json_decode($responseBody);
                $bh->store(array(
                    "transaction_ref" => $event['data']['reference'],
                    "currency" => $event['data']['currency'],
                    "amount" => $event['data']['amount'] / 100, // this is because paystack stores in kobo
                    "description" => $event['data']['metadata']['description'] ?? "Subscription",
                    "provider" => "PAYSTACK",
                    "organisation_id" => $event['data']['metadata']['organisation_id'],
                ));

                // Successful authorization (no response body, but 2xx status code)
                // return response()->json(['message' => 'Authorization successful'], $statusCode);
                return;
            } else {
                // Handle error response
                // return response()->json(json_decode($responseBody), $statusCode);
                return;
            }
        } catch (Throwable $e) {
            // Handle exceptions during the request
            // return response()->json([
            //     'message' => 'An error occurred during the authorization request.',
            //     'exception' => $e->getMessage(),
            // ], 500);

            // TODO:Alert billing failed;
            return;
        }
    }
}
