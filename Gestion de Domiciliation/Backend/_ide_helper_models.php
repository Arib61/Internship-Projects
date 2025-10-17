<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $action
 * @property string $entity_type
 * @property int $entity_id
 * @property array<array-key, mixed>|null $old_values
 * @property array<array-key, mixed>|null $new_values
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereNewValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereOldValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUserId($value)
 */
	class ActivityLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $nom_francais
 * @property string $nom_arabe
 * @property string $adresse_siege_social_francais
 * @property string $adresse_siege_social_arabe
 * @property string $ice
 * @property string|null $cin
 * @property string|null $certificat_negative
 * @property string $rc
 * @property string $identifiant_fiscal
 * @property string $tp
 * @property string $tribunal
 * @property string $type_tribunal
 * @property string $telephone
 * @property string $email
 * @property string $pays
 * @property string $ville
 * @property string|null $website
 * @property float $capital_social
 * @property string $type_entreprise
 * @property string $type_client
 * @property string $status
 * @property int|null $gerant_id
 * @property int|null $gestionnaire_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Gerant|null $gerant
 * @property-read \App\Models\Gestionnaire|null $gestionnaire
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereAdresseSiegeSocialArabe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereAdresseSiegeSocialFrancais($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCapitalSocial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCertificatNegative($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereGerantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereGestionnaireId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereIce($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereIdentifiantFiscal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereNomArabe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereNomFrancais($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client wherePays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereRc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereTp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereTribunal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereTypeClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereTypeEntreprise($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereTypeTribunal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereVille($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereWebsite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client withoutTrashed()
 */
	class Client extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $path
 * @property string $type
 * @property int $size
 * @property int $uploaded_by
 * @property string $entity_type
 * @property int $entity_id
 * @property \Illuminate\Support\Carbon $validity_start_date
 * @property \Illuminate\Support\Carbon $validity_end_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $uploader
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereEntityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereEntityType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUploadedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereValidityEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereValidityStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document withoutTrashed()
 */
	class Document extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $client_id
 * @property int $gestionnaire_id
 * @property int $created_by
 * @property \Illuminate\Support\Carbon $date_debut
 * @property \Illuminate\Support\Carbon $date_fin
 * @property int $renewal_count
 * @property float $montant
 * @property string $situation
 * @property bool $payement
 * @property string|null $reference_numero
 * @property string|null $notes
 * @property bool $is_deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Client $client
 * @property-read \App\Models\Gestionnaire $gestionnaire
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DomiciliationHistory> $histories
 * @property-read int|null $histories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Resiliation> $resiliations
 * @property-read int|null $resiliations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation whereDateDebut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation whereDateFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation whereGestionnaireId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation whereIsDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation whereMontant($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation wherePayement($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation whereReferenceNumero($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation whereRenewalCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation whereSituation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Domiciliation withoutTrashed()
 */
	class Domiciliation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $domiciliation_id
 * @property int $modified_by
 * @property \Illuminate\Support\Carbon $modification_date
 * @property array<array-key, mixed> $old_values
 * @property array<array-key, mixed> $new_values
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Domiciliation $domiciliation
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DomiciliationHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DomiciliationHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DomiciliationHistory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DomiciliationHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DomiciliationHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DomiciliationHistory whereDomiciliationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DomiciliationHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DomiciliationHistory whereModificationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DomiciliationHistory whereModifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DomiciliationHistory whereNewValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DomiciliationHistory whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DomiciliationHistory whereOldValues($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DomiciliationHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DomiciliationHistory withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DomiciliationHistory withoutTrashed()
 */
	class DomiciliationHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $nom
 * @property string $cin
 * @property string|null $address
 * @property string|null $date_naissance
 * @property string|null $telephone
 * @property string|null $email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Client> $clients
 * @property-read int|null $clients_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gerant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gerant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gerant onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gerant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gerant whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gerant whereCin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gerant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gerant whereDateNaissance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gerant whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gerant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gerant whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gerant whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gerant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gerant withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gerant withoutTrashed()
 */
	class Gerant extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $nom
 * @property string $prenom
 * @property string $email
 * @property string|null $telephone
 * @property string|null $adresse
 * @property string|null $ville
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gestionnaire newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gestionnaire newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gestionnaire onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gestionnaire query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gestionnaire whereAdresse($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gestionnaire whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gestionnaire whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gestionnaire whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gestionnaire whereNom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gestionnaire wherePrenom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gestionnaire whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gestionnaire whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gestionnaire whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gestionnaire whereVille($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gestionnaire withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gestionnaire withoutTrashed()
 */
	class Gestionnaire extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $message
 * @property string $type
 * @property bool $read
 * @property array<array-key, mixed>|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereRead($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Notification whereUserId($value)
 */
	class Notification extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $reference_type
 * @property int $reference_id
 * @property float $montant
 * @property string $date_payment
 * @property string $mode_payment
 * @property string $reference_payment
 * @property int $received_by
 * @property string|null $invoice_number
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereDatePayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereModePayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereMontant($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereReceivedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereReferencePayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUpdatedAt($value)
 */
	class Payment extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $domiciliation_id
 * @property string $date_resiliation
 * @property string|null $date_fin
 * @property string $raison
 * @property int $created_by
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Domiciliation $domiciliation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resiliation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resiliation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resiliation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resiliation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resiliation whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resiliation whereDateFin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resiliation whereDateResiliation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resiliation whereDomiciliationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resiliation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resiliation whereRaison($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resiliation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resiliation whereUpdatedAt($value)
 */
	class Resiliation extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $nom_francais
 * @property string $nom_arabe
 * @property string $adresse_siege_social_francais
 * @property string $adresse_siege_social_arabe
 * @property string $ice
 * @property string $rc
 * @property string $identifiant_fiscal
 * @property string $tp
 * @property string $telephone
 * @property string $email
 * @property string|null $logo
 * @property string|null $icon
 * @property string|null $website
 * @property float $capital_social
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe whereAdresseSiegeSocialArabe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe whereAdresseSiegeSocialFrancais($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe whereCapitalSocial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe whereIce($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe whereIdentifiantFiscal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe whereNomArabe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe whereNomFrancais($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe whereRc($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe whereTp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Societe whereWebsite($value)
 */
	class Societe extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $content
 * @property array<array-key, mixed>|null $variables
 * @property bool $is_active
 * @property string|null $version
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $creator
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereVariables($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Template whereVersion($value)
 */
	class Template extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property int $is_admin
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DomiciliationHistory> $modificationHistories
 * @property-read int|null $modification_histories_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

