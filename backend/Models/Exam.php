<?php

namespace Models;

use Exception;
use MysqliDb;
use stdClass;

class Exam extends Model
{
    /**
     * Hệ số điểm lũy tiến cho câu Đúng/Sai
     */
    const TF_PROGRESSIVE = [0, 0.1, 0.25, 0.5, 1.0];

    // ========================
    // CRUD Đề thi
    // ========================

    static function getExam($id)
    {
        return Model::getDB()->objectBuilder()->where('id', $id)->getOne('exams');
    }

    static function listExams($type = null, $status = 1, $page = 1, $limit = 10)
    {
        $db = Model::getDB()->objectBuilder();
        if ($type) {
            $db->where('exam_type', $type);
        }
        if ($status !== null) {
            $db->where('status', $status);
        }
        if ($limit === null) {
            return $db->orderBy('updated_at', 'DESC')->get('exams');
        }
        $db->pageLimit = $limit;
        return $db->orderBy('updated_at', 'DESC')->paginate('exams', $page);
    }

    static function countExams($type = null)
    {
        $db = Model::getDB()->objectBuilder();
        if ($type) {
            $db->where('exam_type', $type);
        }
        $result = $db->getOne('exams', 'COUNT(id) as total');
        return $result->total ?? 0;
    }

    static function createExam($data)
    {
        return Model::getDB()->insert('exams', $data);
    }

    static function updateExam($id, $data)
    {
        return Model::getDB()->where('id', $id)->update('exams', $data);
    }

    static function deleteExam($id)
    {
        // Delete related questions and their TF items
        $questions = Model::getDB()->objectBuilder()->where('exam_id', $id)->get('questions');
        if ($questions) {
            foreach ($questions as $q) {
                Model::getDB()->where('question_id', $q->id)->delete('question_tf_items');
            }
        }
        Model::getDB()->where('exam_id', $id)->delete('questions');
        Model::getDB()->where('exam_id', $id)->delete('passage_groups');
        return Model::getDB()->where('id', $id)->delete('exams');
    }

    static function getExamPaginate()
    {
        return (object) [
            "total" => Model::getDB()->totalCount,
            "limit" => Model::getDB()->pageLimit,
            "total_page" => Model::getDB()->totalPages,
        ];
    }

    // ========================
    // Logic chấm điểm
    // ========================

    /**
     * Tính điểm cho một phiên thi
     */
    static function calculateScore($session_id)
    {
        $session = ExamSession::getSession($session_id);
        if (!$session) return false;

        $exam = self::getExam($session->exam_id);
        if (!$exam) return false;

        $answers = json_decode($session->answers, true) ?? [];

        if ($exam->exam_type === 'thpt') {
            return self::calculateThptScore($session, $exam, $answers);
        } else {
            return self::calculateHsaScore($session, $exam, $answers);
        }
    }

    /**
     * Tính điểm THPT
     */
    private static function calculateThptScore($session, $exam, $answers)
    {
        // Xác định môn Toán
        $isMath = false;
        if ($exam->subject_id) {
            $subject = Model::getDB()->objectBuilder()->where('id', $exam->subject_id)->getOne('subject');
            if ($subject && (stripos($subject->name, 'Toán') !== false || stripos($subject->name, 'toan') !== false)) {
                $isMath = true;
            }
        }

        // Lấy câu hỏi theo phần
        $questions = Question::getExamQuestions($exam->id);
        $scoreP1 = 0;
        $scoreP2 = 0;
        $scoreP3 = 0;

        foreach ($questions as $q) {
            $qId = (string)$q->id;
            if (!isset($answers[$qId])) continue;

            $userAnswer = $answers[$qId];

            switch ($q->part) {
                case 1: // Trắc nghiệm / Nhiều lựa chọn
                    if (($q->question_type === 'mc' || $q->question_type === 'ms') && strtoupper(trim($userAnswer)) === strtoupper(trim($q->correct_answer))) {
                        $scoreP1 += 0.25;
                    }
                    break;

                case 2: // Đúng/Sai lũy tiến / Ghép cột
                    if ($q->question_type === 'tf') {
                        $scoreP2 += self::calculateTfScore($q->id, $userAnswer);
                    } elseif ($q->question_type === 'matching') {
                        if (self::checkMatchingCorrect($q->options, $userAnswer)) {
                            $scoreP2 += 0.25; // Hoặc hệ số khác tùy quy định
                        }
                    }
                    break;

                case 3: // Trả lời ngắn / Điền ô trống
                    if ($q->question_type === 'short' || $q->question_type === 'mblanks') {
                        $correctAnswer = trim(mb_strtolower($q->correct_answer));
                        $userAnswerClean = trim(mb_strtolower($userAnswer));
                        if ($userAnswerClean === $correctAnswer) {
                            $scoreP3 += $isMath ? 0.5 : 0.25;
                        }
                    }
                    break;
            }
        }

        $total = round($scoreP1 + $scoreP2 + $scoreP3, 2);

        return [
            'score_p1' => round($scoreP1, 2),
            'score_p2' => round($scoreP2, 2),
            'score_p3' => round($scoreP3, 2),
            'total_score' => $total
        ];
    }

    /**
     * Tính điểm một câu Đúng/Sai lũy tiến
     */
    private static function calculateTfScore($questionId, $userAnswer)
    {
        $tfItems = Question::getTfItems($questionId);
        if (!$tfItems || !is_array($userAnswer)) return 0;

        $correctCount = 0;
        foreach ($tfItems as $item) {
            $key = (string)$item->order;
            if (isset($userAnswer[$key])) {
                $userVal = (bool)$userAnswer[$key];
                $correctVal = (bool)$item->is_correct;
                if ($userVal === $correctVal) {
                    $correctCount++;
                }
            }
        }

        return self::TF_PROGRESSIVE[$correctCount] ?? 0;
    }

    /**
     * Tính điểm HSA
     */
    private static function calculateHsaScore($session, $exam, $answers)
    {
        // Lấy câu hỏi (từ snapshot nếu đề random)
        if ($exam->is_random && $session->snapshot) {
            $questionIds = json_decode($session->snapshot, true);
            $questions = Question::getQuestionsByIds($questionIds);
        } else {
            $questions = Question::getExamQuestions($exam->id);
        }

        $scoreP1 = 0;
        $scoreP2 = 0;
        $scoreP3 = 0;

        foreach ($questions as $q) {
            $qId = (string)$q->id;
            if (!isset($answers[$qId])) continue;

            $userAnswer = $answers[$qId];
            $correct = false;

            if ($q->question_type === 'mc' || $q->question_type === 'ms') {
                $correct = strtoupper(trim($userAnswer)) === strtoupper(trim($q->correct_answer));
            } elseif ($q->question_type === 'short' || $q->question_type === 'mblanks') {
                $correct = trim(mb_strtolower($userAnswer)) === trim(mb_strtolower($q->correct_answer));
            } elseif ($q->question_type === 'matching') {
                $correct = self::checkMatchingCorrect($q->options, $userAnswer);
            } elseif ($q->question_type === 'tf') {
                $correct = self::calculateTfScore($q->id, $userAnswer) >= 1.0;
            }

            if ($correct) {
                switch ($q->part) {
                    case 1: $scoreP1 += 1.0; break;
                    case 2: $scoreP2 += 1.0; break;
                    case 3: $scoreP3 += 1.0; break;
                }
            }
        }

        $total = round($scoreP1 + $scoreP2 + $scoreP3, 2);

        return [
            'score_p1' => round($scoreP1, 2),
            'score_p2' => round($scoreP2, 2),
            'score_p3' => round($scoreP3, 2),
            'total_score' => $total
        ];
    }

    /**
     * Tính điểm một phần cụ thể (HSA)
     */
    static function calculatePartScore($session_id, $part)
    {
        $session = ExamSession::getSession($session_id);
        if (!$session) return 0;

        $exam = self::getExam($session->exam_id);
        $answers = json_decode($session->answers, true) ?? [];

        if ($exam->is_random && $session->snapshot) {
            $questionIds = json_decode($session->snapshot, true);
            $questions = Question::getQuestionsByIds($questionIds);
        } else {
            $questions = Question::getExamQuestions($exam->id, $part);
        }

        $score = 0;
        foreach ($questions as $q) {
            if ($q->part != $part) continue;
            $qId = (string)$q->id;
            if (!isset($answers[$qId])) continue;

            $userAnswer = $answers[$qId];

            if ($exam->exam_type === 'thpt') {
                switch ($q->part) {
                    case 1:
                        if (($q->question_type === 'mc' || $q->question_type === 'ms') && strtoupper(trim($userAnswer)) === strtoupper(trim($q->correct_answer))) {
                            $score += 0.25;
                        }
                        break;
                    case 2:
                        if ($q->question_type === 'tf') {
                            $score += self::calculateTfScore($q->id, $userAnswer);
                        } elseif ($q->question_type === 'matching') {
                            if (self::checkMatchingCorrect($q->options, $userAnswer)) $score += 0.25;
                        }
                        break;
                    case 3:
                        $isMath = false;
                        if ($exam->subject_id) {
                            $subject = Model::getDB()->objectBuilder()->where('id', $exam->subject_id)->getOne('subject');
                            if ($subject && stripos($subject->name, 'Toán') !== false) {
                                $isMath = true;
                            }
                        }
                        if (($q->question_type === 'short' || $q->question_type === 'mblanks') && trim(mb_strtolower($userAnswer)) === trim(mb_strtolower($q->correct_answer))) {
                            $score += $isMath ? 0.5 : 0.25;
                        }
                        break;
                }
            } else {
                // HSA: 1.0đ/câu đúng
                if (($q->question_type === 'mc' || $q->question_type === 'ms') && strtoupper(trim($userAnswer)) === strtoupper(trim($q->correct_answer))) {
                    $score += 1.0;
                } elseif (($q->question_type === 'short' || $q->question_type === 'mblanks') && trim(mb_strtolower($userAnswer)) === trim(mb_strtolower($q->correct_answer))) {
                    $score += 1.0;
                } elseif ($q->question_type === 'matching' && self::checkMatchingCorrect($q->options, $userAnswer)) {
                    $score += 1.0;
                } elseif ($q->question_type === 'tf' && self::calculateTfScore($q->id, $userAnswer) >= 1.0) {
                    $score += 1.0;
                }
            }
        }

        return round($score, 2);
    }

    /**
     * Kiểm tra ghép cột
     */
    private static function checkMatchingCorrect($optionsJson, $userAnswer)
    {
        $pairs = json_decode($optionsJson, true);
        if (!$pairs || !is_array($userAnswer)) return false;
        
        // userAnswer format: ["left_val": "right_val", ...]
        foreach ($pairs as $p) {
            $left = $p['left'];
            $right = $p['right'];
            if (!isset($userAnswer[$left]) || $userAnswer[$left] !== $right) {
                return false;
            }
        }
        return true;
    }

    // ========================
    // Leaderboard
    // ========================

    static function getLeaderboard($exam_id, $limit = 10)
    {
        return Model::getDB()->objectBuilder()
            ->join('users u', 'u.id = leaderboard.user_id', 'LEFT')
            ->where('exam_id', $exam_id)
            ->orderBy('total_score', 'DESC')
            ->orderBy('time_spent', 'ASC')
            ->get('leaderboard', $limit, 'leaderboard.*, u.avatar');
    }

    static function updateLeaderboard($session_id)
    {
        $session = ExamSession::getSession($session_id);
        if (!$session) return false;

        $user = User::userById($session->user_id);
        $displayName = $user['display_name'] ?? $user['name'] ?? $user['username'] ?? 'Unknown';

        // Check if entry exists
        $existing = Model::getDB()->objectBuilder()
            ->where('exam_id', $session->exam_id)
            ->where('user_id', $session->user_id)
            ->getOne('leaderboard');

        $data = [
            'exam_id' => $session->exam_id,
            'user_id' => $session->user_id,
            'session_id' => $session_id,
            'display_name' => $displayName,
            'total_score' => $session->total_score,
            'time_spent' => $session->time_spent,
        ];

        if ($existing) {
            // Chỉ cập nhật nếu điểm cao hơn
            if ($session->total_score > $existing->total_score ||
                ($session->total_score == $existing->total_score && $session->time_spent < $existing->time_spent)) {
                return Model::getDB()->where('id', $existing->id)->update('leaderboard', $data);
            }
            return true;
        } else {
            return Model::getDB()->insert('leaderboard', $data);
        }
    }
}
