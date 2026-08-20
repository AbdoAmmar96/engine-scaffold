<?php

/*
 |--------------------------------------------------------------------------
 | رسائل التحقق بالعربي
 |--------------------------------------------------------------------------
 | اللوحة كلها عربي، فرسائل الأخطاء لازم تبقى عربي كمان.
 | أسماء الحقول (:attribute) بتيجي تلقائيًا من labels بتاعة الـ schema
 | في ResourceController، فمش محتاجين نكرّرها هنا.
 */

return [
    'accepted' => 'لازم توافق على :attribute.',
    'after' => 'حقل :attribute لازم يكون بعد :date.',
    'alpha' => 'حقل :attribute لازم يحتوي على حروف بس.',
    'alpha_dash' => 'حقل :attribute لازم يحتوي على حروف وأرقام وشرطات بس.',
    'alpha_num' => 'حقل :attribute لازم يحتوي على حروف وأرقام بس.',
    'array' => 'حقل :attribute لازم يكون قائمة.',
    'before' => 'حقل :attribute لازم يكون قبل :date.',
    'between' => [
        'array' => 'حقل :attribute لازم يحتوي على عدد بين :min و :max عنصر.',
        'file' => 'حقل :attribute لازم يكون بين :min و :max كيلوبايت.',
        'numeric' => 'حقل :attribute لازم يكون بين :min و :max.',
        'string' => 'حقل :attribute لازم يكون بين :min و :max حرف.',
    ],
    'boolean' => 'حقل :attribute لازم يكون نعم أو لا.',
    'confirmed' => 'تأكيد :attribute مش مطابق.',
    'date' => 'حقل :attribute لازم يكون تاريخ صحيح.',
    'date_format' => ':attribute مش متطابق مع الصيغة :format.',
    'different' => ':attribute و :other لازم يكونوا مختلفين.',
    'digits' => 'حقل :attribute لازم يكون :digits رقم.',
    'digits_between' => 'حقل :attribute لازم يكون بين :min و :max رقم.',
    'email' => 'حقل :attribute لازم يكون إيميل صحيح.',
    'exists' => 'قيمة :attribute المختارة غير موجودة.',
    'file' => 'حقل :attribute لازم يكون ملف.',
    'filled' => 'حقل :attribute مطلوب.',
    'gt' => [
        'numeric' => 'حقل :attribute لازم يكون أكبر من :value.',
        'string' => 'حقل :attribute لازم يكون أطول من :value حرف.',
    ],
    'gte' => [
        'numeric' => 'حقل :attribute لازم يكون :value أو أكبر.',
        'string' => 'حقل :attribute لازم يكون :value حرف أو أكتر.',
    ],
    'image' => 'حقل :attribute لازم يكون صورة.',
    'in' => 'قيمة :attribute المختارة غير صحيحة.',
    'integer' => 'حقل :attribute لازم يكون رقم صحيح.',
    'lt' => [
        'numeric' => 'حقل :attribute لازم يكون أقل من :value.',
        'string' => 'حقل :attribute لازم يكون أقصر من :value حرف.',
    ],
    'lte' => [
        'numeric' => 'حقل :attribute لازم يكون :value أو أقل.',
        'string' => 'حقل :attribute لازم يكون :value حرف أو أقل.',
    ],
    'max' => [
        'array' => 'حقل :attribute مينفعش يزيد عن :max عنصر.',
        'file' => 'حقل :attribute مينفعش يزيد عن :max كيلوبايت.',
        'numeric' => 'حقل :attribute مينفعش يزيد عن :max.',
        'string' => 'حقل :attribute مينفعش يزيد عن :max حرف.',
    ],
    'mimes' => 'حقل :attribute لازم يكون ملف من نوع: :values.',
    'min' => [
        'array' => 'حقل :attribute لازم يحتوي على :min عنصر على الأقل.',
        'file' => 'حقل :attribute لازم يكون :min كيلوبايت على الأقل.',
        'numeric' => 'حقل :attribute لازم يكون :min على الأقل.',
        'string' => 'حقل :attribute لازم يكون :min حروف على الأقل.',
    ],
    'not_in' => 'قيمة :attribute المختارة غير صحيحة.',
    'numeric' => 'حقل :attribute لازم يكون رقم.',
    'present' => 'حقل :attribute لازم يكون موجود.',
    'regex' => 'صيغة :attribute غير صحيحة.',
    'required' => 'حقل :attribute مطلوب.',
    'required_if' => 'حقل :attribute مطلوب لما :other يكون :value.',
    'required_with' => 'حقل :attribute مطلوب مع :values.',
    'same' => ':attribute و :other لازم يكونوا متطابقين.',
    'size' => [
        'array' => 'حقل :attribute لازم يحتوي على :size عنصر.',
        'file' => 'حقل :attribute لازم يكون :size كيلوبايت.',
        'numeric' => 'حقل :attribute لازم يكون :size.',
        'string' => 'حقل :attribute لازم يكون :size حرف.',
    ],
    'string' => 'حقل :attribute لازم يكون نص.',
    'unique' => 'حقل :attribute مستخدم قبل كده.',
    'url' => 'حقل :attribute لازم يكون رابط صحيح.',

    'custom' => [],

    'attributes' => [
        'name' => 'الاسم',
        'email' => 'الإيميل',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'phone' => 'الموبايل',
        'message' => 'الرسالة',
        'area' => 'المنطقة',
        'budget' => 'الميزانية',
        'title' => 'العنوان',
        'slug' => 'الرابط',
        'role' => 'الدور',
    ],
];
