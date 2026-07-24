<?php

return [
    'title' => 'ویرایش پروفایل',
    'sections' => [
        'info' => [
            'title' => 'اطلاعات کاربری',
            'description' => 'مدیریت اطلاعات شخصی، آواتار و پست الکترونیکی حساب کاربری.',
        ],
        'password' => [
            'title' => 'تغییر رمز عبور',
            'description' => 'جهت تغییر رمز عبور، رمز فعلی و رمز جدید خود را وارد نمایید.',
        ],
    ],
    'fields' => [
        'avatar' => 'تصویر آواتار',
        'name' => 'نام و نام خانوادگی',
        'mobile' => 'شماره همراه',
        'email' => 'پست الکترونیکی',
        'current_password' => 'رمز عبور فعلی',
        'new_password' => 'رمز عبور جدید',
        'new_password_confirmation' => 'تکرار رمز عبور جدید',
    ],
    'buttons' => [
        'save_profile' => 'ذخیره اطلاعات کاربری',
        'saving' => 'در حال ذخیره...',
        'change_password' => 'تغییر رمز عبور',
        'updating_password' => 'در حال به‌روزرسانی رمز...',
    ],
    'notifications' => [
        'too_many_attempts_title' => 'تعداد درخواست‌ها بیش از حد مجاز است',
        'too_many_attempts_body' => 'لطفاً :seconds ثانیه دیگر مجدداً تلاش کنید.',
        'profile_updated' => 'اطلاعات کاربری با موفقیت به‌روزرسانی شد.',
        'password_updated' => 'رمز عبور با موفقیت تغییر یافت.',
    ],
    'validation' => [
        'current_password_incorrect' => 'رمز عبور فعلی وارد شده نادرست است.',
    ],
];
