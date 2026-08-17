<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'term', 'reading_on', 'reading_kun', 'meaning', 'example_sentence',
    'example_translation', 'category', 'jlpt_level', 'tags',
])]
class DictionaryEntry extends Model
{
    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }
}
