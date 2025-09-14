<?php

namespace juniyasyos\ShieldLite\Models;

use App\Models\Team;
use Filament\Facades\Filament;
use juniyasyos\ShieldLite\Helpers\UuidGenerator;
use Illuminate\Database\Eloquent\Model;

class ShieldRole extends Model
{

    use UuidGenerator;

    protected $table = 'shield_roles';

    protected $fillable = [
        'name',
        'created_by_name',
        'access',
        'team_id',
        'guard',
    ];

    protected $casts = [
        'access' => 'array'
    ];

    public function team()
    {
        return $this->belongsTo(Filament::getTenantModel());
    }
}
