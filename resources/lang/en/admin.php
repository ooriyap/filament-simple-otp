<?php

return [
    'navigation_label' => 'Admin Management',
    'model_label' => 'Admin',
    'plural_model_label' => 'Admins',
    'sections' => [
        'account' => 'Account Information',
        'security' => 'Password & Access Permissions',
    ],
    'fields' => [
        'avatar' => 'Avatar Image',
        'name' => 'Full Name',
        'mobile' => 'Mobile Number',
        'email' => 'Email Address',
        'password' => 'Password',
        'is_active' => 'Active Account',
        'can_manage_admins' => 'Superadmin Access (Manage Admins)',
    ],
    'columns' => [
        'avatar' => 'Avatar',
        'name' => 'Name',
        'mobile' => 'Mobile',
        'email' => 'Email',
        'can_manage_admins' => 'Superadmin',
        'is_active' => 'Status',
        'created_at' => 'Registered At',
    ],
    'filters' => [
        'can_manage_admins' => 'Superadmin',
        'is_active' => 'Active',
    ],
];
