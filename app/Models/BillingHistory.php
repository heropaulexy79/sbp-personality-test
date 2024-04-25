<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        "transaction_ref",
        "amount",
        "description",
        "provider",
        "organisation_id"
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}
