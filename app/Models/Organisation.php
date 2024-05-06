<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function employees()
    {
        return $this->hasMany(User::class, 'organisation_id');
    }

    public function invites()
    {
        return $this->hasMany(OrganisationInvitation::class, 'organisation_id');
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class, 'organisation_id');
    }

    public function billingHistories()
    {
        return $this->hasMany(BillingHistory::class, 'organisation_id');
    }


    public function hasActiveSubscription()
    {
        // $isFirstMonth = $this->created_at->gt(now()->subDays(30));
        $isNewUser = $this->created_at->startOfMonth()->gte(now()->startOfMonth());

        // config('app.env') === 'staging'
        if (config('app.env') === 'local'  || $isNewUser) {
            return true;
        }

        $latestPayment = BillingHistory::where('organisation_id', $this->id)
            // ->where('status', 'success')
            ->where('description', 'LIKE', '%subscription%')
            ->latest()
            ->first();

        if ($latestPayment) {
            // Check if the payment is within the subscription period (e.g., last 30 days)
            return $latestPayment->created_at->gte(now()->subDays(30));
        }

        return false;
    }

    // public function courses()
    // {
    //     return $this->hasMany(Course::class, 'organisation_id');
    // }
}
