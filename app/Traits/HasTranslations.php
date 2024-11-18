<?php

namespace App\Traits;

use App\Models\Translation;

trait HasTranslations
{
    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function translate($field, $language = null)
    {
        $language = $language ?: app()->getLocale();

        $translation = $this->translations()
            ->where('field', $field)
            ->where('language', $language)
            ->first();

        return $translation ? $translation->value : $this->getAttribute($field);
    }

    public function setTranslation($field, $language, $value)
    {
        $value = $value ?: '';

        return $this->translations()->updateOrCreate(
            [
                'field' => $field,
                'language' => $language
            ],
            ['value' => $value]
        );
    }

    public function getTranslations($field)
    {
        return $this->translations()
            ->where('field', $field)
            ->get()
            ->pluck('value', 'language');
    }

    public function getAllTranslations()
    {
        return $this->translations()
            ->get()
            ->groupBy('language')
            ->map(function ($items) {
                return $items->mapWithKeys(function ($item) {
                    return [$item->field => $item->value];
                });
            });
    }
}
