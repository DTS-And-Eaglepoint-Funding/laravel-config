<?php

namespace TarfinLabs\LaravelConfig\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
use TarfinLabs\LaravelConfig\Enums\ConfigDataType;

class ConfigValueCast implements CastsAttributes
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, mixed $value, array $attributes)
    {
        $type = $attributes['type'] ?? 'string';
        if ($type instanceof ConfigDataType) {
            $type = $type->value;
        }
        // Check for custom caster
        $parts = explode(':', $type);
        $casterClass = array_shift($parts);

        if (class_exists($casterClass)) {
            $caster = match (true) {
                is_subclass_of($casterClass, CastsAttributes::class),
                is_subclass_of($casterClass, CastsInboundAttributes::class) => new $casterClass(...$parts),
                is_subclass_of($casterClass, Castable::class) => $casterClass::castUsing($parts),
                default => null
            };

            if ($caster !== null) {
                return $caster->get(model: $model, key: $key, value: $value, attributes: $attributes);
            }
        }

        // Fallback to simple switch logic
        switch ($type) {
            case ConfigDataType::BOOLEAN->value:
                return (bool) $value;
            case ConfigDataType::INTEGER->value:
                return (int) $value;
            case ConfigDataType::DATE->value:
                return Carbon::createFromFormat('Y-m-d', $value);
            case ConfigDataType::DATE_TIME->value:
                return Carbon::createFromFormat('Y-m-d H:i', $value);
            case ConfigDataType::JSON->value:
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, string $key, mixed $value, array $attributes)
    {
        $type = $attributes['type'] ?? 'string';
        if ($type instanceof ConfigDataType) {
            $type = $type->value;
        }
        // Check for custom caster
        $parts = explode(':', $type);
        $casterClass = array_shift($parts);

        if (class_exists($casterClass)) {
            $caster = match (true) {
                is_subclass_of($casterClass, CastsAttributes::class),
                is_subclass_of($casterClass, CastsInboundAttributes::class) => new $casterClass(...$parts),
                is_subclass_of($casterClass, Castable::class) => $casterClass::castUsing($parts),
                default => null
            };

            if ($caster !== null) {
                return $caster->set(model: $model, key: $key, value: $value, attributes: $attributes);
            }
        }

        // Fallback to simple switch logic
        switch ($type) {
            case ConfigDataType::DATE->value:
                return Carbon::parse($value)->format('Y-m-d');
            case ConfigDataType::DATE_TIME->value:
                return Carbon::parse($value)->format('Y-m-d H:i');
            case ConfigDataType::JSON->value:
                return is_string($value) ? $value : json_encode($value);
            default:
                return $value;
        }
    }
}
