<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\SoftDeleteTrait;
class Template extends Model
{
     use SoftDeleteTrait;
    use HasFactory;

    /**
     * Les attributs qui peuvent être assignés en masse.
     *
     * @var array<int, string>
     */

    protected $table = 'eryx_templates';
    public $fillable = [
        'name',
        'description',
        'content',
        'path',
        'type',
        'document_type',
        'variables',
        'version',
        'created_by',
        'is_active'
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Les types de documents disponibles.
     *
     * @var array<int, string>
     */
    const DOCUMENT_TYPES = [
        'CONTRAT_DOMICILIATION',
        'ATTESTATION_DOMICILIATION',
        'FACTURE',
        'RECU_PAIEMENT',
        'CERTIFICAT',
        'AUTRE'
    ];

    /**
     * Les types de templates disponibles.
     *
     * @var array<int, string>
     */
    const TYPES = [
        'blade',
        'word',
        'pdf',
        'html'
    ];

    /**
     * Obtenir l'utilisateur qui a créé ce template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
