<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\SoftDeleteTrait;

class Payment extends Model
{
    use SoftDeleteTrait;
    protected $table = 'eryx_payments';
    protected $fillable = [
        'reference_type',
        'reference_id',
        'montant',
        'date_payment',
        'mode_payment',
        'reference_payment',
        'received_by',
        'invoice_number',
        'notes',
    ];

    protected $dates = ['date_payment'];

    public function getDeletedAtColumn()
    {
        // Désactive proprement l’usage de deleted_at dans ce modèle
        return null;
    }
}
