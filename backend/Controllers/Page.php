<?php
namespace Controllers;

# use Exception;
use Models\Model;
use Models\Web as WebModel;
use Services\Blade;

class Page
{
    function terms() : string
    {
        return (new Blade())->render('user.pages.terms');
    }
    function faqs() : string
    {
        return (new Blade())->render('user.pages.faqs');
    }

    function search($page = 1)
    {
        $q = input()->value("q") ?? input()->value("search") ?? input()->value("query") ?? input()->value("s") ?? '';
        $courses = WebModel::searchCourses($q);
        $courses_paginate = WebModel::getPaginate();
        $documents = WebModel::searchDocuments($q);
        $documents_paginate = WebModel::getPaginate();
        return (new Blade())->render('user.pages.search', [
            'page' => $page,
            'search' => $q,
            'courses' => $courses,
            'courses_paginate' => $courses_paginate,
            'documents' => $documents,
            'documents_paginate' => $documents_paginate,
        ]);
    }
    function courses($page = 1)
    {
        $data = WebModel::courses($page, 24);
        return (new Blade())->render('user.pages.courses', [
            'data' => $data,
            'page' => $page,
            'paginate' => WebModel::getPaginate(),
        ]);
    }
    function documents($page = 1)
    {
        $data = WebModel::documents($page, 12);
        return (new Blade())->render('user.pages.documents', [
            'data' => $data,
            'page' => $page,
            'paginate' => WebModel::getPaginate(),
        ]);
    }



    function document($id)
    {
        if (!is_numeric($id)) {
            response()->httpCode(404)->redirect('/');
        }
        $data = WebModel::documentData($id);
        if (!$data) {
            response()->httpCode(404)->redirect('/');
        }

        WebModel::updateStats('document');
        WebModel::updateViews('document', $id);
        return (new Blade())->render('user.pages.document', [
            'data' => $data
        ]);
    }
    function course($id)
    {
        if (!is_numeric($id)) {
            response()->httpCode(404)->redirect('/');
        }
        $data = WebModel::courseData($id);
        if (!$data) {
            response()->httpCode(404)->redirect('/');
        }

        WebModel::updateStats('course');
        WebModel::updateViews('course', $id);
        return (new Blade())->render('user.pages.course', [
            'data' => $data
        ]);
    }
    function lesson($id)
    {
        if (!is_numeric($id)) {
            response()->httpCode(404)->redirect('/');
        }
        $data = WebModel::lessonData($id);
        if (!$data) {
            response()->httpCode(404)->redirect('/');
        }

        WebModel::updateStats('lesson');
        WebModel::updateViews('lesson', $id);
        return (new Blade())->render('user.pages.lesson', [
            'data' => $data,
            'course' => WebModel::courseData($data->course),
            'next' => WebModel::nextLesson($data->course, $id),
            'prev' => WebModel::prevLesson($data->course, $id),
        ]);

    }
    function subject($id, $page = 1)
    {
        if (!is_numeric($id)) {
            response()->httpCode(404)->redirect('/');
        }
        $data = WebModel::subjectData($id);
        if (!$data) {
            response()->httpCode(404)->redirect('/');
        }

        $courses = WebModel::subjectCourses($id);
        $courses_paginate = WebModel::getPaginate();
        $documents = WebModel::subjectDocuments($id);
        $documents_paginate = WebModel::getPaginate();
        return (new Blade())->render('user.pages.list', [
            'data' => $data,
            'title' => str_replace("_name_", $data->name, __('common.page_subject')),
            "type" => "subject",
            'page' => $page,
            'courses' => $courses,
            'courses_paginate' => $courses_paginate,
            'documents' => $documents,
            'documents_paginate' => $documents_paginate,
        ]);
    }
    function class($id, $page = 1)
    {
        if (!is_numeric($id)) {
            response()->httpCode(404)->redirect('/');
        }
        $data = WebModel::classData($id);
        if (!$data) {
            response()->httpCode(404)->redirect('/');
        }

        $courses = WebModel::classCourses($id);
        $courses_paginate = WebModel::getPaginate();
        $documents = WebModel::classDocuments($id);
        $documents_paginate = WebModel::getPaginate();
        return (new Blade())->render('user.pages.list', [
            'data' => $data,
            'title' => str_replace("_name_", $data->name, __('common.page_class')),
            "type" => "class",
            'page' => $page,
            'courses' => $courses,
            'courses_paginate' => $courses_paginate,
            'documents' => $documents,
            'documents_paginate' => $documents_paginate,
        ]);
    }
    function type($id, $page = 1)
    {

        if (!is_numeric($id)) {
            response()->httpCode(404)->redirect('/');
        }
        $data = WebModel::typeData($id);
        if (!$data) {
            response()->httpCode(404)->redirect('/');
        }

        $courses = WebModel::typeCourses($id);
        $courses_paginate = WebModel::getPaginate();
        $documents = WebModel::typeDocuments($id);
        $documents_paginate = WebModel::getPaginate();
        return (new Blade())->render('user.pages.list', [
            'data' => $data,
            'title' => str_replace("_name_", $data->name, __('common.page_exam')),
            "type" => "type",
            'page' => $page,
            'courses' => $courses,
            'courses_paginate' => $courses_paginate,
            'documents' => $documents,
            'documents_paginate' => $documents_paginate,
        ]);
    }

    function changeLanguage($code)
    {
        if (\Models\Language::getByCode($code)) {
            $_SESSION['locale'] = $code;
        }
        
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        response()->redirect($referer);
    }
}