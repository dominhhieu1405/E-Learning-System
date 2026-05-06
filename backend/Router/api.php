<?php

use Services\Router;

Router::group(['prefix' => '/api'], function () {
    // Google login (legacy routes)
    Router::get('/login/google', 'Auth@googleRedirect', ['as' => 'api.login.google']);
    Router::get('/login/google/callback', 'Auth@googleCallback', ['as' => 'api.login.google.callback']);

    // Authenticated API
    Router::group(['middleware' => Middleware\Auth::class], function () {
        // Online tracker
        Router::all('/online', 'Api@online', ['as' => 'api.online']);

        // Exam APIs
        Router::post('/exam/start', 'Exam@startApi', ['as' => 'api.exam.start']);
        Router::post('/exam/save', 'Exam@saveAnswersApi', ['as' => 'api.exam.save']);
        Router::post('/exam/lock-part', 'Exam@lockPartApi', ['as' => 'api.exam.lock-part']);
        Router::post('/exam/submit', 'Exam@submitApi', ['as' => 'api.exam.submit']);
    });
});
