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

    // public function courses()
    // {
    //     return $this->hasMany(Course::class, 'organisation_id');
    // }
}
