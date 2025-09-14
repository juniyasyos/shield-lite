<?php

namespace juniyasyos\ShieldLite;

use Illuminate\Support\Str;

trait HasShieldLite
{

    public function defineGates(): array
    {
        // Provide sensible defaults for Filament Resources if not overridden
        $gates = [];

        // If the class using this trait is a Filament Resource, generate default actions
        if (is_subclass_of(static::class, \Filament\Resources\Resource::class)) {
            $label = method_exists(static::class, 'getModelLabel') ? static::getModelLabel() : \Illuminate\Support\Str::of(collect(explode('\\\\', static::class))->last())->headline();
            $slug = \Illuminate\Support\Str::slug($label, '.');

            $gates = [
                "{$slug}.view" => __('View :label', ['label' => $label]),
                "{$slug}.create" => __('Create :label', ['label' => $label]),
                "{$slug}.update" => __('Update :label', ['label' => $label]),
                "{$slug}.delete" => __('Delete :label', ['label' => $label]),
            ];
        }

        return $gates;
    }

    public function roleName()
    {
        return method_exists(__CLASS__, 'getModelLabel') ? static::getModelLabel() : Str::of(collect(explode('\\', get_class()))->last())->headline();
    }
    
    public function gateIndexs()
    {
        return $this->gateIndexes();
    }

    // Preferred naming
    public function gateIndexes(): array
    {
        return array_keys($this->defineGates());
    }
}
