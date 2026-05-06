<?php
namespace Services;

use Jenssegers\Blade\Blade as Template;

class Blade extends Template {

    protected $viewPath = ROOT_PATH. "/resources/views";
    protected $cachePath = ROOT_PATH. "/resources/cache/blade";

    public function __construct(){

        parent::__construct($this->viewPath, $this->cachePath);
    }

    public static function timeAgo($timestamp) {
        if (!$timestamp) return "N/A";
        $time = is_numeric($timestamp) ? $timestamp : strtotime($timestamp);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) return "Vừa xong";
        if ($diff < 3600) return round($diff / 60) . " phút trước";
        if ($diff < 86400) return round($diff / 3600) . " giờ trước";
        if ($diff < 604800) return round($diff / 86400) . " ngày trước";
        return date("d/m/Y", $time);
    }
}

