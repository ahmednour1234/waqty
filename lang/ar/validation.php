<?php

return [
    'required' => 'حقل :attribute مطلوب.',
    'email' => 'يجب أن يكون حقل :attribute عنوان بريد إلكتروني صالح.',
    'string' => 'يجب أن يكون حقل :attribute نصاً.',
    'max' => [
        'numeric' => 'يجب ألا يزيد حقل :attribute عن :max.',
        'string' => 'يجب ألا يزيد حقل :attribute عن :max حرف.',
        'file' => 'يجب ألا يزيد حجم ملف :attribute عن :max كيلوبايت.',
        'array' => 'يجب ألا يحتوي حقل :attribute على أكثر من :max عناصر.',
    ],
    'min' => [
        'numeric' => 'يجب ألا يقل حقل :attribute عن :min.',
        'string' => 'يجب ألا يقل حقل :attribute عن :min حرف.',
        'array' => 'يجب أن يحتوي حقل :attribute على :min عناصر على الأقل.',
        'file' => 'يجب ألا يقل حجم ملف :attribute عن :min كيلوبايت.',
    ],
    'boolean' => 'يجب أن يكون حقل :attribute true أو false.',
    'integer' => 'يجب أن يكون حقل :attribute رقماً صحيحاً.',
    'array' => 'يجب أن يكون حقل :attribute مصفوفة.',
    'exists' => 'القيمة المحددة لحقل :attribute غير صالحة.',
    'unique' => 'تم استخدام :attribute من قبل.',
    'confirmed' => 'تأكيد حقل :attribute غير متطابق.',
    'size' => [
        'string' => 'يجب أن يكون حقل :attribute :size حرف.',
    ],
];
