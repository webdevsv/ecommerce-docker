<?php

namespace App\Services;

use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

class StripeService
{
    protected StripeClient $client;

    public function __construct()
    {
        $this->client = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Create a PaymentIntent for the given amount (in the smallest currency unit).
     *
     * @throws ApiErrorException
     */
    public function createPaymentIntent(float $amount, string $currency, array $metadata = []): PaymentIntent
    {
        return $this->client->paymentIntents->create([
            'amount' => (int) round($amount * 100),
            'currency' => $currency,
            'metadata' => $metadata,
            'automatic_payment_methods' => ['enabled' => true],
        ]);
    }

    /**
     * Retrieve a PaymentIntent by its ID.
     *
     * @throws ApiErrorException
     */
    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return $this->client->paymentIntents->retrieve($paymentIntentId);
    }

    /**
     * Confirm a PaymentIntent (used for server-side confirmation flows/testing).
     *
     * @throws ApiErrorException
     */
    public function confirmPaymentIntent(string $paymentIntentId, string $paymentMethod): PaymentIntent
    {
        return $this->client->paymentIntents->confirm($paymentIntentId, [
            'payment_method' => $paymentMethod,
        ]);
    }
}