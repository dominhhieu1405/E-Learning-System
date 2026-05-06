<?php


namespace Models;


use Exception;
use MysqliDb;
use stdClass;

class Web extends Model
{
    static function updateStats($name = "access") : bool
    {
        $allows = ['access', 'view_course', 'view_lesson', 'view_document'];
        if (in_array("view_" . $name, $allows)) {
            $name = "view_" . $name;
        }
        if (!in_array($name, $allows)) {
            return false;
        }
        $date = date("Y-m-d");
        $check = Model::getDB()->objectBuilder()->where('date', $date)->getOne('statistic');
        if (!$check) {
            Model::getDB()->insert('statistic', ['date' => $date]);
        }
        Model::getDB()->rawQuery("UPDATE statistic SET $name = $name + 1 WHERE `date` = '$date'");
        return true;
    }
    static function updateViews($type, $id) : bool
    {
        if (!in_array($type, ['course', 'lesson', 'document'])) {
            return false;
        }
        Model::getDB()->rawQuery("UPDATE $type SET views = views + 1, views_day = views_day + 1, views_week = views_week + 1, views_month = views_month + 1 WHERE id = $id");
        return true;
    }

    static function searchCourses(string $search, int $page = 1, int $limit = 8) : MysqliDb|array|string|null
    {
        $search = str_replace(' ', '%', $search);
        Model::getDB()->pageLimit = $limit;
        return Model::getDB()->objectBuilder()->where('name', "%$search%", 'LIKE')->orWhere("description", "%$search%", 'LIKE')->paginate('course', $page);
    }
    static function searchDocuments(string $search, int $page = 1, int $limit = 8) : MysqliDb|array|string|null
    {
        $search = str_replace(' ', '%', $search);
        Model::getDB()->pageLimit = $limit;
        return Model::getDB()->objectBuilder()->where('name', "%$search%", 'LIKE')->orWhere("description", "%$search%", 'LIKE')->paginate('document', $page);
    }
    static function subjectCourses(int $subject, int $page = 1, int $limit = 8) : MysqliDb|array|string|null
    {
        Model::getDB()->pageLimit = $limit;
        return Model::getDB()->objectBuilder()->where('subject', $subject)->paginate('course', $page);
    }
    static function subjectDocuments(int $subject, int $page = 1, int $limit = 8) : MysqliDb|array|string|null
    {
        Model::getDB()->pageLimit = $limit;
        return Model::getDB()->objectBuilder()->where('subject', $subject)->paginate('document', $page);
    }
    static function typeCourses(int $type, int $page = 1, int $limit = 8) : MysqliDb|array|string|null
    {
        Model::getDB()->pageLimit = $limit;
        return Model::getDB()->objectBuilder()->where('type', $type)->paginate('course', $page);
    }
    static function typeDocuments(int $type, int $page = 1, int $limit = 8) : MysqliDb|array|string|null
    {
        Model::getDB()->pageLimit = $limit;
        return Model::getDB()->objectBuilder()->where('type', $type)->paginate('document', $page);
    }
    static function classCourses(int $class, int $page = 1, int $limit = 8) : MysqliDb|array|string|null
    {
        Model::getDB()->pageLimit = $limit;
        return Model::getDB()->objectBuilder()->where('class', $class)->paginate('course', $page);
    }
    static function classDocuments(int $class, int $page = 1, int $limit = 8) : MysqliDb|array|string|null
    {
        Model::getDB()->pageLimit = $limit;
        return Model::getDB()->objectBuilder()->where('class', $class)->paginate('document', $page);
    }

    static function getPaginate() : object
    {
        return (object) [
            "total" => Model::getDB()->totalCount,
            "limit" => Model::getDB()->pageLimit,
            "total_page" => Model::getDB()->totalPages,
        ];
    }

    static function count($table)
    {
        if (!in_array($table, ['type', 'subject', 'document', 'course', 'lesson', 'class', 'unit', 'exams', 'questions', 'exam_sessions'])) {
            return 0;
        }
        return Model::getDB()->objectBuilder()->getOne($table, 'COUNT(id) as total')->total ?? 0;
    }
    static function countViews($table, $type)
    {
        if (!in_array($table, ['course', 'lesson', 'document'])) {
            return 0;
        }
        if (!in_array($type, ['views', 'views_day', 'views_week', 'views_month'])) {
            return 0;
        }
        return Model::getDB()->objectBuilder()->getOne($table, "SUM($type) as total")->total ?? 0;
    }
    /**
     * @throws Exception
     */
    static function typeData($id) : string|array|MysqliDb|null|stdClass
    {
        return Model::getDB()->objectBuilder()->where('id', $id)->getOne('type');
    }

    /**
     * @throws Exception
     */
    static function subjectData($id) : MysqliDb|array|string|null|stdClass
    {
        return Model::getDB()->objectBuilder()->where('id', $id)->getOne('subject');
    }
    /**
     * @throws Exception
     */
    static function courseData($id) : MysqliDb|array|string|null|stdClass
    {
        return Model::getDB()->objectBuilder()
            ->join('class c', 'c.id = course.class', 'LEFT')
            ->join('subject s', 's.id = course.subject', 'LEFT')
            ->where('course.id', $id)
            ->getOne('course', 'course.*, c.name as class_name, s.name as subject_name');
    }
    /**
     * @throws Exception
     */
    static function lessonData($id) : MysqliDb|array|string|null|stdClass
    {
        return Model::getDB()->objectBuilder()->where('id', $id)->getOne('lesson');
    }
    static function courseUnit($id) : MysqliDb|array|string|null
    {
        return Model::getDB()->objectBuilder()->where('course', $id)->get('unit');
    }
    static function courseUnitLesson($id) : MysqliDb|array|string|null
    {
        return Model::getDB()->objectBuilder()->where('unit', $id)->get('lesson');
    }
    static function nextLesson(int $courseId, int $lessonId)
    {
        return Model::getDB()->objectBuilder()
            ->where('course', $courseId)
            ->where('id', $lessonId, '>')
            ->orderBy('id', 'ASC')
            ->getOne('lesson');
    }
    static function prevLesson(int $courseId, int $lessonId)
    {
        return Model::getDB()->objectBuilder()
            ->where('course', $courseId)
            ->where('id', $lessonId, '<')
            ->orderBy('id', 'DESC')
            ->getOne('lesson');
    }
    /**
     * @throws Exception
     */
    static function classData($id) : MysqliDb|array|string|null|stdClass
    {
        return Model::getDB()->objectBuilder()->where('id', $id)->getOne('class');
    }



    /**
     * @throws Exception
     */
    static function allType() : MysqliDb|array|string|null
    {
        return Model::getDB()->objectBuilder()->get('type');
    }

    /**
     * @throws Exception
     */
    static function allClass() : MysqliDb|array|string|null
    {
        return Model::getDB()->objectBuilder()->get('class');
    }

    /**
     * @throws Exception
     */
    static function allSubject() : MysqliDb|array|string|null
    {
        return Model::getDB()->objectBuilder()->get('subject');
    }



    /**
     * @throws Exception
     */
    static function subject(int $type = 0) : MysqliDb|array|string|null
    {
        return Model::getDB()->objectBuilder()->where('id', $type)->get('subject');
    }

    /**
     * @throws Exception
     */
    static function courses(int $page = 1, int|null $limit = 8, string $order = "time_update") : MysqliDb|array|string|null
    {
        if ($limit === null) {
            return Model::getDB()->orderBy($order)->objectBuilder()->get('course');
        }
        Model::getDB()->pageLimit = $limit;
        return Model::getDB()->orderBy($order)->objectBuilder()->paginate('course', $page);
    }
    static function courseAdd(array $data) : bool
    {
        return Model::getDB()->insert('course', $data);
    }
    static function courseUpdate(int $id, array $data)
    {
        return Model::getDB()->where('id', $id)->update('course', $data);
    }
    static function courseDelete($id)
    {
        Model::getDB()->where('course', $id)->delete('lesson');
        Model::getDB()->where('course', $id)->delete('unit');
        return Model::getDB()->where('id', $id)->delete('course');
    }

    static function unitAdd(int $course, array $data) : int
    {
        $data['course'] = $course;
        Model::getDB()->insert('unit', $data);
        return Model::getDB()->getInsertId();
    }
    static function unitUpdate(int $id, array $data) : bool
    {
        return Model::getDB()->where('id', $id)->update('unit', $data);
    }
    static function unitDelete($id)
    {
        Model::getDB()->where('unit', $id)->delete('lesson');
        return Model::getDB()->where('id', $id)->delete('unit');
    }
    static function lessonAdd(int $course, $data)
    {
        $data['course'] = $course;
        return Model::getDB()->insert('lesson', $data);
    }
    static function lessonUpdate(int $id, array $data) : bool
    {
        return Model::getDB()->where('id', $id)->update('lesson', $data);
    }
    static function lessonDelete($id)
    {
        return Model::getDB()->where('id', $id)->delete('lesson');
    }

    /* Documents */

    /**
     * @throws Exception
     */
    static function documents(int $page = 1, int|null $limit = 10, string $order = "time_update") : MysqliDb|array|string|null
    {
        if ($limit === null) {
            return Model::getDB()->orderBy($order)->objectBuilder()->get('document');
        }
        Model::getDB()->pageLimit = $limit;
        return Model::getDB()->orderBy($order)->objectBuilder()->paginate('document', $page);
    }
    /**
     * @throws Exception
     */
    static function documentData($id) : MysqliDb|array|string|null|stdClass
    {
        return Model::getDB()->objectBuilder()->where('id', $id)->getOne('document');
    }
    /**
     * @throws Exception
     */
    static function documentAdd(array $data) : bool
    {
        return Model::getDB()->insert('document', $data);
    }
    static function documentUpdate(int $id, array $data) : bool
    {
        return Model::getDB()->where('id', $id)->update('document', $data);
    }
    static function documentDelete($id)
    {
        return Model::getDB()->where('id', $id)->delete('document');
    }

    public static function stats(int $int)
    {
        return Model::getDB()->objectBuilder()->orderBy('id')->get('statistic', $int);
    }

    static function getLatestComments($limit = 50)
    {
        return Model::getDB()->join('users u', 'c.user_id = u.id', 'LEFT')
            ->objectBuilder()
            ->orderBy('c.created_at', 'DESC')
            ->get('comments c', $limit, 'c.*, u.display_name, u.avatar');
    }
}