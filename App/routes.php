<?php

return [
    '~^$~' => [App\Controllers\MainController::class, 'main'],
    '~^articles/(\d+)$~' => [App\Controllers\ArticlesController::class, 'view'],
    '~^articles/(\d+)/edit$~' => [App\Controllers\ArticlesController::class, 'edit']
];