<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Romaji implements Rule
{
    public function passes($attribute, $value)
    {
        if ($value && !preg_match('/^[a-zA-Z0-9\s.,]+$/', $value)) {
            return false;
        }

        return true;
    }

    public function message()
    {
        return 'Only romaji characters are allowed';
    }
}
