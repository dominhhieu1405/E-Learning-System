<?php

use Services\Router;

// === Trang chủ ===
Router::get('/', 'User@index', ['as' => 'home']);

// === Auth (không cần đăng nhập) ===
Router::get('/login', 'Auth@loginPage', ['as' => 'login']);
Router::post('/login', 'Auth@loginApi');
Router::get('/register', 'Auth@registerPage', ['as' => 'register']);
Router::post('/register', 'Auth@registerApi');
Router::get('/auth/google', 'Auth@googleRedirect', ['as' => 'auth.google']);
Router::get('/auth/google/callback', 'Auth@googleCallback');
Router::get('/logout', 'Auth@logout', ['as' => 'logout']);

// === Courses & Documents (public) ===
Router::get('/documents/{page?}', 'Page@documents', ['as' => 'documents']);
Router::get('/courses/{page?}', 'Page@courses', ['as' => 'courses']);
Router::get('/document/{id}/', 'Page@document', ['as' => 'document'])->where(['id', '([0-9]+)']);
Router::get('/course/{id}/', 'Page@course', ['as' => 'course'])->where(['id', '([0-9]+)']);
Router::get('/lesson/{id}/', 'Page@lesson', ['as' => 'lesson'])->where(['id', '([0-9]+)']);
Router::get('/class/{id}/{page?}', 'Page@class', ['as' => 'class'])->where(['id', '([0-9]+)']);
Router::get('/subject/{id}/{page?}', 'Page@subject', ['as' => 'subject'])->where(['id', '([0-9]+)']);
Router::get('/type/{id}/{page?}', 'Page@type', ['as' => 'type'])->where(['id', '([0-9]+)']);
Router::get('/search/{page?}', 'Page@search', ['as' => 'search']);
Router::get('/change-language/{code}', 'Page@changeLanguage', ['as' => 'change.language']);

// === API (public) ===
Router::group(['prefix' => '/api'], function () {
    Router::all('/online', 'Api@online', ['as' => 'api.online']);
    Router::get('/comment/list', 'Api@listCommentsApi');
});

// === Yêu cầu đăng nhập ===
Router::group(['middleware' => Middleware\Auth::class], function () {

    // Đề thi
    Router::get('/api/exam/questions', 'Exam@getQuestionsApi', ['as' => 'api.exam.questions']);
    Router::post('/api/exam/lock-part', 'Exam@lockPartApi');

    Router::post('/api/exam/set-part3-branch', 'Exam@setPart3BranchApi');
    Router::post('/api/exam/submit', 'Exam@submitApi');
    Router::get('/exams', 'Exam@list', ['as' => 'exams']);
    Router::get('/exam/{id}', 'Exam@detail', ['as' => 'exam.detail'])->where(['id', '([0-9]+)']);
    Router::get('/exam/{id}/start', 'Exam@start', ['as' => 'exam.start'])->where(['id', '([0-9]+)']);
    Router::get('/result/{id}', 'Exam@result', ['as' => 'exam.result'])->where(['id', '([0-9]+)']);
    Router::get('/leaderboard/{id}', 'Exam@leaderboard', ['as' => 'exam.leaderboard'])->where(['id', '([0-9]+)']);

    // Profile
    Router::get('/terms', 'Page@terms', ['as' => 'terms']);
    Router::get('/faqs', 'Page@faqs', ['as' => 'faqs']);

    Router::group(['prefix' => '/user'], function () {
        Router::get('/', 'User@profile', ['as' => 'profile']);
    });



    Router::group(['prefix' => '/api'], function () {
        Router::post('/comment/add', 'Api@addCommentApi');
        Router::post('/payment/callback', 'Api@paymentCallback', ['as' => 'api.payment.callback']);
        Router::group(['prefix' => '/user'], function () {
            Router::post('/update-profile', 'User@updateProfileApi');
            Router::post('/upload-avatar', 'User@uploadAvatarApi');
            Router::post('/change-password', 'User@changePasswordApi');
        });
    });
});

// === 404 ===
Router::get('/404', function () {
    response()->httpCode(404)->redirect('/');
});
