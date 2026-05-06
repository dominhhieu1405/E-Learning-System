<?php

namespace Models;

use Exception;
use MysqliDb;
use stdClass;

class Question extends Model
{
    // ========================
    // CRUD Câu hỏi
    // ========================

    static function getQuestion($id)
    {
        return Model::getDB()->objectBuilder()->where('id', $id)->getOne('questions');
    }

    static function getExamQuestions($exam_id, $part = null)
    {
        $db = Model::getDB()->objectBuilder()->where('exam_id', $exam_id);
        if ($part !== null) {
            $db->where('part', $part);
        }
        return $db->orderBy('part', 'ASC')->orderBy('`order`', 'ASC')->get('questions') ?: [];
    }

    static function getQuestionsByIds($ids)
    {
        if (empty($ids)) return [];
        $questions = Model::getDB()->objectBuilder()
            ->where('id', $ids, 'IN')
            ->get('questions') ?: [];
            
        // Map questions by ID for fast lookup
        $map = [];
        foreach ($questions as $q) {
            $map[$q->id] = $q;
        }
        
        // Sort based on the order in $ids
        $sorted = [];
        foreach ($ids as $id) {
            if (isset($map[$id])) {
                $sorted[] = $map[$id];
            }
        }
        return $sorted;
    }

    static function countExamQuestions($exam_id, $part = null)
    {
        $db = Model::getDB()->objectBuilder()->where('exam_id', $exam_id);
        if ($part !== null) {
            $db->where('part', $part);
        }
        $result = $db->getOne('questions', 'COUNT(id) as total');
        return $result->total ?? 0;
    }

    static function addQuestion($data)
    {
        $id = Model::getDB()->insert('questions', $data);
        return $id ? Model::getDB()->getInsertId() : false;
    }

    static function updateQuestion($id, $data)
    {
        return Model::getDB()->where('id', $id)->update('questions', $data);
    }

    static function deleteQuestion($id)
    {
        Model::getDB()->where('question_id', $id)->delete('question_tf_items');
        return Model::getDB()->where('id', $id)->delete('questions');
    }

    // ========================
    // Ngân hàng câu hỏi
    // ========================

    static function getBankQuestions($subject_id = null, $question_type = null, $page = 1, $limit = 20)
    {
        $db = Model::getDB()->objectBuilder();
        $db->join('subject s', 'q.subject_id = s.id', 'LEFT');
        if ($subject_id) {
            $db->where('q.subject_id', $subject_id);
        }
        if ($question_type) {
            $db->where('q.question_type', $question_type);
        }
        $db->orderBy('q.created_at', 'DESC');
        if ($limit === null) {
            return $db->get('questions q', null, 'q.*, s.name as subject_name') ?: [];
        }
        $db->pageLimit = $limit;
        return $db->paginate('questions q', $page, 'q.*, s.name as subject_name') ?: [];
    }

    static function countBankQuestions($subject_id = null, $question_type = null)
    {
        $db = Model::getDB()->objectBuilder();
        if ($subject_id) {
            $db->where('subject_id', $subject_id);
        }
        if ($question_type) {
            $db->where('question_type', $question_type);
        }
        $result = $db->getOne('questions', 'COUNT(id) as total');
        return $result->total ?? 0;
    }

    /**
     * Bốc ngẫu nhiên câu hỏi từ ngân hàng theo category
     */
    static function getRandomBankQuestions($category, $limit)
    {
        return Model::getDB()->objectBuilder()
            ->where('exam_id', null, 'IS')
            ->where('bank_category', $category)
            ->orderBy('RAND()')
            ->get('questions', $limit) ?: [];
    }

    static function getBankPaginate()
    {
        return (object) [
            "total" => Model::getDB()->totalCount,
            "limit" => Model::getDB()->pageLimit,
            "total_page" => Model::getDB()->totalPages,
        ];
    }

    // ========================
    // TF Items (ý con Đúng/Sai)
    // ========================

    static function getTfItems($question_id)
    {
        return Model::getDB()->objectBuilder()
            ->where('question_id', $question_id)
            ->orderBy('`order`', 'ASC')
            ->get('question_tf_items') ?: [];
    }

    /**
     * Lưu 4 ý con TF (xóa cũ + insert mới)
     * @param int $question_id
     * @param array $items [['content'=>'...','is_correct'=>1], ...]
     */
    static function saveTfItems($question_id, $items)
    {
        Model::getDB()->where('question_id', $question_id)->delete('question_tf_items');
        foreach ($items as $i => $item) {
            Model::getDB()->insert('question_tf_items', [
                'question_id' => $question_id,
                'order' => $i + 1,
                'content' => $item['content'],
                'is_correct' => (int)$item['is_correct']
            ]);
        }
        return true;
    }

    // ========================
    // Passage Groups
    // ========================

    static function getPassageGroup($id)
    {
        return Model::getDB()->objectBuilder()->where('id', $id)->getOne('passage_groups');
    }

    static function getExamPassageGroups($exam_id)
    {
        return Model::getDB()->objectBuilder()
            ->where('exam_id', $exam_id)
            ->orderBy('part', 'ASC')
            ->orderBy('`order`', 'ASC')
            ->get('passage_groups') ?: [];
    }

    static function savePassageGroup($data)
    {
        if (isset($data['id']) && $data['id']) {
            $id = $data['id'];
            unset($data['id']);
            return Model::getDB()->where('id', $id)->update('passage_groups', $data);
        }
        Model::getDB()->insert('passage_groups', $data);
        return Model::getDB()->getInsertId();
    }

    static function deletePassageGroup($id)
    {
        // Unlink questions from this passage group
        Model::getDB()->where('passage_group_id', $id)->update('questions', ['passage_group_id' => null]);
        return Model::getDB()->where('id', $id)->delete('passage_groups');
    }
}
