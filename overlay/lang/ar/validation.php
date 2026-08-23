<?php

/*
 |--------------------------------------------------------------------------
 | رسائل التحقق بالعربية
 |--------------------------------------------------------------------------
 | اللوحة كلها بالعربية، فرسائل الأخطاء يجب أن تكون بالعربية كذلك.
 | أسماء الحقول (:attribute) تأتي تلقائيًا من labels الخاصة بالـ schema
 | في ResourceController، فلا حاجة إلى تكرارها هنا.
 */

return [
    'accepted' => 'يجب الموافقة على :attribute.',
    'after' => 'حقل :attribute يجب أن يكون بعد :date.',
    'alpha' => 'حقل :attribute يجب أن يحتوي على حروف فقط.',
    'alpha_dash' => 'حقل :attribute يجب أن يحتوي على حروف وأرقام وشرطات فقط.',
    'alpha_num' => 'حقل :attribute يجب أن يحتوي على حروف وأرقام فقط.',
    'array' => 'حقل :attribute يجب أن يكون قائمة.',
    'before' => 'حقل :attribute يجب أن يكون قبل :date.',
    'between' => [
        'array' => 'حقل :attribute يجب أن يحتوي على عدد بين :min و :max عنصر.',
        'file' => 'حقل :attribute يجب أن يكون بين :min و :max كيلوبايت.',
        'numeric' => 'حقل :attribute يجب أن يكون بين :min و :max.',
        'string' => 'حقل :attribute يجب أن يكون بين :min و :max حرف.',
    ],
    'boolean' => 'حقل :attribute يجب أن يكون نعم أو لا.',
    'confirmed' => 'تأكيد :attribute غير مطابق.',
    'date' => 'حقل :attribute يجب أن يكون تاريخًا صحيحًا.',
    'date_format' => ':attribute غير متطابق مع الصيغة :format.',
    'different' => ':attribute و :other يجب أن يكونا مختلفين.',
    'digits' => 'حقل :attribute يجب أن يكون :digits رقم.',
    'digits_between' => 'حقل :attribute يجب أن يكون بين :min و :max رقم.',
    'email' => 'حقل :attribute يجب أن يكون بريدًا إلكترونيًا صحيحًا.',
    'exists' => 'قيمة :attribute المختارة غير موجودة.',
    'file' => 'حقل :attribute يجب أن يكون ملفًا.',
    'filled' => 'حقل :attribute مطلوب.',
    'gt' => [
        'numeric' => 'حقل :attribute يجب أن يكون أكبر من :value.',
        'string' => 'حقل :attribute يجب أن يكون أطول من :value حرف.',
    ],
    'gte' => [
        'numeric' => 'حقل :attribute يجب أن يكون :value أو أكبر.',
        'string' => 'حقل :attribute يجب أن يكون :value حرف أو أكثر.',
    ],
    'image' => 'حقل :attribute يجب أن يكون صورة.',
    'in' => 'قيمة :attribute المختارة غير صحيحة.',
    'integer' => 'حقل :attribute يجب أن يكون رقمًا صحيحًا.',
    'lt' => [
        'numeric' => 'حقل :attribute يجب أن يكون أقل من :value.',
        'string' => 'حقل :attribute يجب أن يكون أقصر من :value حرف.',
    ],
    'lte' => [
        'numeric' => 'حقل :attribute يجب أن يكون :value أو أقل.',
        'string' => 'حقل :attribute يجب أن يكون :value حرف أو أقل.',
    ],
    'max' => [
        'array' => 'حقل :attribute يجب ألا يزيد عن :max عنصر.',
        'file' => 'حقل :attribute يجب ألا يزيد عن :max كيلوبايت.',
        'numeric' => 'حقل :attribute يجب ألا يزيد عن :max.',
        'string' => 'حقل :attribute يجب ألا يزيد عن :max حرف.',
    ],
    'mimes' => 'حقل :attribute يجب أن يكون ملفًا من نوع: :values.',
    'min' => [
        'array' => 'حقل :attribute يجب أن يحتوي على :min عنصر على الأقل.',
        'file' => 'حقل :attribute يجب أن يكون :min كيلوبايت على الأقل.',
        'numeric' => 'حقل :attribute يجب أن يكون :min على الأقل.',
        'string' => 'حقل :attribute يجب أن يكون :min حروف على الأقل.',
    ],
    'not_in' => 'قيمة :attribute المختارة غير صحيحة.',
    'numeric' => 'حقل :attribute يجب أن يكون رقمًا.',
    'present' => 'حقل :attribute يجب أن يكون موجودًا.',
    'regex' => 'صيغة :attribute غير صحيحة.',
    'required' => 'حقل :attribute مطلوب.',
    'required_if' => 'حقل :attribute مطلوب عندما يكون :other هو :value.',
    'required_with' => 'حقل :attribute مطلوب مع :values.',
    'same' => ':attribute و :other يجب أن يكونا متطابقين.',
    'size' => [
        'array' => 'حقل :attribute يجب أن يحتوي على :size عنصر.',
        'file' => 'حقل :attribute يجب أن يكون :size كيلوبايت.',
        'numeric' => 'حقل :attribute يجب أن يكون :size.',
        'string' => 'حقل :attribute يجب أن يكون :size حرف.',
    ],
    'string' => 'حقل :attribute يجب أن يكون نصًا.',
    'unique' => 'حقل :attribute مستخدم من قبل.',
    'url' => 'حقل :attribute يجب أن يكون رابطًا صحيحًا.',

    'custom' => [],

    'attributes' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'phone' => 'رقم الهاتف',
        'message' => 'الرسالة',
        'area' => 'المنطقة',
        'budget' => 'الميزانية',
        'title' => 'العنوان',
        'slug' => 'الرابط',
        'role' => 'الدور',
    ],
];
