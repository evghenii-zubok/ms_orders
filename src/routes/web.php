<?php

use Illuminate\Support\Facades\Route;

Route::fallback(function () {
    abort(403, 'Web access is not allowed.');
});
