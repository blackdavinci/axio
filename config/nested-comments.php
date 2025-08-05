<?php

use Illuminate\Database\Eloquent\Model;

return [
    'tables' => [
        'comments' => 'comments',
        'reactions' => 'reactions',
        'users' => 'users', // The table that will be used to get the authenticated user
    ],

    'models' => [
        'comment' => \Coolsam\NestedComments\Models\Comment::class,
        'reaction' => \Coolsam\NestedComments\Models\Reaction::class,
        'user' => env('AUTH_MODEL', 'App\Models\User'), // The model that will be used to get the authenticated user
    ],

    'policies' => [
        'comment' => \App\Policies\CommentPolicy::class,
        'reaction' => null,
    ],
    'allowed-reactions' => [
        '👍' => 'thumbs up', // thumbs up
        '👎' => 'thumbs down', // thumbs down
        '❤️' => 'heart', // heart
        '😂' => 'laughing', // laughing
        '😮' => 'surprised', // surprised
        '😢' => 'crying', // crying
        '💯' => 'hundred points', // angry
        '🔥' => 'fire', // fire
        '🎉' => 'party popper', // party popper
        '🚀' => 'rocket', // rocket
    ],
    'allow-all-reactions' => env('ALLOW_ALL_REACTIONS', false), // Allow any other emoji apart from the ones listed above
    'allow-multiple-reactions' => env('ALLOW_MULTIPLE_REACTIONS', false), // Allow multiple reactions from the same user
    'allow-guest-reactions' => env('ALLOW_GUEST_REACTIONS', false), // Allow guest users to react
    'allow-guest-comments' => env('ALLOW_GUEST_COMMENTS', false), // Allow guest users to comment
    'mentions' => [
        'items-placeholder' => 'Search users by name or email address',
        'empty-items-message' => 'No users found',
    ],
];
