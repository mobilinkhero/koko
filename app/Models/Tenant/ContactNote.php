<?php

namespace App\Models\Tenant;

use App\Models\BaseModel;
use App\Models\Tenant;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;

/**
 * Class ContactNote
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $contact_id
 * @property string $notes_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Contact $contact
 * @property Tenant $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactNote forTenant($tenant)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactNote query()
 *
 * @mixin \Eloquent
 */
class ContactNote extends BaseModel
{
    use BelongsToTenant;

    protected $casts = [
        'tenant_id' => 'int',
        'contact_id' => 'int',
    ];

    /**
     * Create a new instance of the model with the correct table name
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if ($subdomain = tenant_subdomain()) {
            $this->setTable($subdomain.'_contact_notes');
        }
    }

    protected $fillable = [
        'tenant_id',
        'contact_id',
        'notes_description',
    ];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function fromTenant(string $subdomain)
    {
        return (new static)->setTable($subdomain.'_contact_notes');
    }
}
