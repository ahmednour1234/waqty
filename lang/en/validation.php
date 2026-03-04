<?php

return [
    'required' => 'The :attribute field is required.',
    'email' => 'The :attribute field must be a valid email address.',
    'string' => 'The :attribute field must be a string.',
    'max' => [
        'string' => 'The :attribute field must not be greater than :max characters.',
        'file' => 'The :attribute field must not be greater than :max kilobytes.',
    ],
    'min' => [
        'string' => 'The :attribute field must be at least :min characters.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'integer' => 'The :attribute field must be an integer.',
    'array' => 'The :attribute field must be an array.',
    'exists' => 'The selected :attribute is invalid.',
    'unique' => 'The :attribute has already been taken.',
    'confirmed' => 'The :attribute field confirmation does not match.',
    'size' => [
        'string' => 'The :attribute field must be :size characters.',
    ],
];
