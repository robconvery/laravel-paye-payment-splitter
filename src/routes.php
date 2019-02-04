<?php

Route::get('/laravel-paye-payment-splitter', function () {
    return view('PaymentSplitter::split');
})
    ->name('payment-splitter');

