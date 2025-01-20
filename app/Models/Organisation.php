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
        return $this->hasMany(OrganisationUser::class, 'organisation_id');
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


    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'organisation_id');
    }


    public function activeSubscription()
    {
        $date = now()->addHours(22);
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('next_billing_date', '>', $date)
            ->first();
    }


    public function courses()
    {
        return $this->hasMany(Course::class, 'organisation_id');
    }
}
