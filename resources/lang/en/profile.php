<?php

return [
    'title' => 'Edit Profile',
    'sections' => [
        'info' => [
            'title' => 'Profile Information',
            'description' => 'Manage your personal information, avatar, and email address.',
        ],
        'password' => [
            'title' => 'Change Password',
            'description' => 'Enter your current password and choose a new password to update account security.',
        ],
    ],
    'fields' => [
        'avatar' => 'Avatar Image',
        'name' => 'Full Name',
        'mobile' => 'Mobile Number',
        'email' => 'Email Address',
        'current_password' => 'Current Password',
        'new_password' => 'New Password',
        'new_password_confirmation' => 'Confirm New Password',
    ],
    'buttons' => [
        'save_profile' => 'Save Profile Info',
        'saving' => 'Saving...',
        'change_password' => 'Change Password',
        'updating_password' => 'Updating Password...',
    ],
    'notifications' => [
        'too_many_attempts_title' => 'Too Many Requests',
        'too_many_attempts_body' => 'Please try again in :seconds seconds.',
        'profile_updated' => 'Profile information updated successfully.',
        'password_updated' => 'Password changed successfully.',
    ],
    'validation' => [
        'current_password_incorrect' => 'The current password entered is incorrect.',
    ],
];
