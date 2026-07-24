<?php

return [
    'navigation_label' => 'مدیریت ادمین‌ها',
    'model_label' => 'ادمین',
    'plural_model_label' => 'ادمین‌ها',
    'sections' => [
        'account' => 'اطلاعات حساب',
        'security' => 'رمز عبور و دسترسی‌ها',
    ],
    'fields' => [
        'avatar' => 'تصویر آواتار',
        'name' => 'نام و نام خانوادگی',
        'mobile' => 'شماره همراه',
        'email' => 'پست الکترونیکی',
        'password' => 'رمز عبور',
        'is_active' => 'حساب فعال',
        'can_manage_admins' => 'سازنده مدیر',
    ],
    'columns' => [
        'avatar' => 'آواتار',
        'name' => 'نام',
        'mobile' => 'موبایل',
        'email' => 'ایمیل',
        'can_manage_admins' => 'مدیر کل',
        'is_active' => 'وضعیت',
        'created_at' => 'تاریخ ثبت',
    ],
    'filters' => [
        'can_manage_admins' => 'مدیر کل',
        'is_active' => 'فعال',
    ],
];
