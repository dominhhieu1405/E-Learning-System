<?php

namespace Models;

use Exception;

class ExamSession extends Model
{
    /**
     * Bắt đầu phiên thi (hoặc resume nếu đã có)
     */
    static function startSession($user_id, $exam_id)
    {
        // Check active session chưa completed
        $active = self::getActiveSession($user_id, $exam_id);
        if ($active) {
            return $active; // Anti-F5: trả session cũ
        }

        $exam = Exam::getExam($exam_id);
        if (!$exam || !$exam->status) return false;

        // Check số lần làm bài
        if ($exam->attempt_limit > 0) {
            $count = count(Model::getDB()->where('user_id', $user_id)
                ->where('exam_id', $exam_id)
                ->where('status', 'completed')
                ->get('exam_sessions'));
            if ($count >= $exam->attempt_limit) {
                throw new Exception("Bạn đã đạt giới hạn số lần làm đề này ({$exam->attempt_limit} lần).");
            }
        }

        $sessionKey = self::generateSessionKey();
        $snapshot = null;

        // Nếu đề random HSA → bốc câu từ ngân hàng
        if ($exam->is_random && $exam->random_config) {
            $config = json_decode($exam->random_config, true);
            $questionIds = [];

            if (isset($config['p1']) && $config['p1'] > 0) {
                $p1Questions = Question::getRandomBankQuestions('dinh_luong', (int)$config['p1']);
                foreach ($p1Questions as $q) {
                    $questionIds[] = $q->id;
                }
            }
            if (isset($config['p2']) && $config['p2'] > 0) {
                $p2Questions = Question::getRandomBankQuestions('dinh_tinh', (int)$config['p2']);
                foreach ($p2Questions as $q) {
                    $questionIds[] = $q->id;
                }
            }
            if (isset($config['p3']) && $config['p3'] > 0) {
                $p3Questions = Question::getRandomBankQuestions('tu_chon', (int)$config['p3']);
                foreach ($p3Questions as $q) {
                    $questionIds[] = $q->id;
                }
            }

            $snapshot = json_encode($questionIds);
        } else if ($exam->shuffle_questions) {
            // Đề cố định nhưng yêu cầu đảo câu hỏi
            $questions = \Models\Question::getExamQuestions($exam->id);
            $grouped = [];
            foreach ($questions as $q) {
                $grouped[$q->part][] = $q->id;
            }
            $shuffledIds = [];
            foreach ($grouped as $part => $ids) {
                shuffle($ids);
                $shuffledIds = array_merge($shuffledIds, $ids);
            }
            $snapshot = json_encode($shuffledIds);
        }

        $data = [
            'user_id' => $user_id,
            'exam_id' => $exam_id,
            'session_key' => $sessionKey,
            'snapshot' => $snapshot,
            'current_part' => 1,
            'status' => 'in_progress',
            'started_at' => date('Y-m-d H:i:s'),
        ];

        $inserted = Model::getDB()->insert('exam_sessions', $data);
        if ($inserted) {
            return self::getSession(Model::getDB()->getInsertId());
        }
        return false;
    }

    /**
     * Lấy session đang active (chưa completed)
     */
    static function getActiveSession($user_id, $exam_id)
    {
        return Model::getDB()->objectBuilder()
            ->where('user_id', $user_id)
            ->where('exam_id', $exam_id)
            ->where('status', 'completed', '!=')
            ->getOne('exam_sessions');
    }

    /**
     * Lấy session theo ID
     */
    static function getSession($session_id)
    {
        return Model::getDB()->objectBuilder()
            ->where('id', $session_id)
            ->getOne('exam_sessions');
    }

    /**
     * Lấy session theo session_key
     */
    static function getSessionByKey($session_key)
    {
        return Model::getDB()->objectBuilder()
            ->where('session_key', $session_key)
            ->getOne('exam_sessions');
    }

    /**
     * Lưu bài làm (auto-save)
     */
    static function saveAnswers($session_id, $answers)
    {
        return Model::getDB()->where('id', $session_id)->update('exam_sessions', [
            'answers' => json_encode($answers, JSON_UNESCAPED_UNICODE)
        ]);
    }

    /**
     * HSA: Khóa phần thi + tính điểm phần đó
     */
    static function lockPart($session_id, $currentPart)
    {
        $session = self::getSession($session_id);
        if (!$session) return false;

        // Tính điểm phần hiện tại
        $partScore = Exam::calculatePartScore($session_id, $currentPart);

        $nextPart = $currentPart + 1;
        $update = [
            'current_part' => $nextPart,
        ];

        switch ($currentPart) {
            case 1:
                $update['score_p1'] = $partScore;
                $update['status'] = 'part1_done';
                $update['part2_started_at'] = date('Y-m-d H:i:s');
                break;
            case 2:
                $update['score_p2'] = $partScore;
                $update['status'] = 'part2_done';
                $update['part3_started_at'] = date('Y-m-d H:i:s');
                break;
        }

        Model::getDB()->where('id', $session_id)->update('exam_sessions', $update);

        return [
            'part_score' => $partScore,
            'next_part' => $nextPart,
        ];
    }

    /**
     * Nộp bài hoàn tất
     */
    static function submitExam($session_id)
    {
        $session = self::getSession($session_id);
        if (!$session || $session->status === 'completed') return false;

        // Tính điểm toàn bộ
        $scores = Exam::calculateScore($session_id);
        if (!$scores) return false;

        // Tính thời gian
        $startedAt = strtotime($session->started_at);
        $timeSpent = time() - $startedAt;

        $update = [
            'score_p1' => $scores['score_p1'],
            'score_p2' => $scores['score_p2'],
            'score_p3' => $scores['score_p3'],
            'total_score' => $scores['total_score'],
            'status' => 'completed',
            'submitted_at' => date('Y-m-d H:i:s'),
            'time_spent' => $timeSpent,
        ];

        Model::getDB()->where('id', $session_id)->update('exam_sessions', $update);

        // Cập nhật leaderboard
        Exam::updateLeaderboard($session_id);

        return $scores;
    }

    /**
     * Lấy danh sách session theo exam (admin view)
     */
    static function getExamSessions($exam_id = null, $page = 1, $limit = 20)
    {
        $db = Model::getDB()->objectBuilder();
        if ($exam_id) {
            $db->where('exam_sessions.exam_id', $exam_id);
        }
        $db->join('users u', 'u.id = exam_sessions.user_id', 'LEFT');
        $db->join('exams e', 'e.id = exam_sessions.exam_id', 'LEFT');
        $db->pageLimit = $limit;
        return $db->orderBy('exam_sessions.started_at', 'DESC')
            ->paginate('exam_sessions', $page, [
                'exam_sessions.*',
                'u.username',
                'u.display_name as user_display_name',
                'e.title as exam_title'
            ]) ?: [];
    }

    static function getSessionsPaginate()
    {
        return (object) [
            "total" => Model::getDB()->totalCount,
            "limit" => Model::getDB()->pageLimit,
            "total_page" => Model::getDB()->totalPages,
        ];
    }

    /**
     * Lấy lịch sử thi của user
     */
    static function getUserSessions($user_id, $limit = 20)
    {
        return Model::getDB()->objectBuilder()
            ->where('user_id', $user_id)
            ->join('exams e', 'e.id = exam_sessions.exam_id', 'LEFT')
            ->orderBy('exam_sessions.started_at', 'DESC')
            ->get('exam_sessions', $limit, [
                'exam_sessions.*',
                'e.title as exam_title',
                'e.exam_type'
            ]) ?: [];
    }

    /**
     * Lấy câu hỏi cho phiên thi (xử lý cả đề cố định & random)
     */
    static function getSessionQuestions($session, $part = null)
    {
        $exam = Exam::getExam($session->exam_id);

        if ($session->snapshot) {
            $questionIds = json_decode($session->snapshot, true);
            $questions = \Models\Question::getQuestionsByIds($questionIds);
        } else {
            $questions = \Models\Question::getExamQuestions($exam->id);
        }

        // Apply HSA Part 3 Filtering
        if ($exam->exam_type === 'hsa' && !empty($session->part3_choice)) {
            $choice = json_decode($session->part3_choice, true);
            $questions = array_filter($questions, function ($q) use ($choice) {
                if ($q->part != 3) return true;
                
                if ($choice['type'] === 'english') {
                    return strtolower(trim($q->branch)) === 'anh' || strtolower(trim($q->branch)) === 'tiếng anh' || $q->subject_id == 3;
                } else {
                    // Science
                    $selectedSubjectIds = $choice['subject_ids'] ?? [];
                    return in_array($q->subject_id, $selectedSubjectIds);
                }
            });
            $questions = array_values($questions);
        }

        if ($part !== null) {
            $questions = array_filter($questions, function ($q) use ($part) {
                return $q->part == $part;
            });
            $questions = array_values($questions);
        }

        return $questions;
    }

    /**
     * Tính thời gian còn lại (server-side, anti-cheat)
     */
    static function getRemainingTime($session)
    {
        $exam = Exam::getExam($session->exam_id);

        if ($exam->exam_type === 'thpt') {
            // THPT: tổng thời gian
            $startedAt = strtotime($session->started_at);
            $endTime = $startedAt + ($exam->duration * 60);
            return $endTime - time();
        } else {
            // HSA: thời gian theo phần
            $currentPart = $session->current_part;
            $durationField = "duration_p{$currentPart}";
            $duration = $exam->$durationField ?? 0;

            // Xác định thời điểm bắt đầu phần hiện tại
            switch ($currentPart) {
                case 1:
                    $partStart = strtotime($session->started_at);
                    break;
                case 2:
                    $partStart = $session->part2_started_at ? strtotime($session->part2_started_at) : time();
                    break;
                case 3:
                    $partStart = $session->part3_started_at ? strtotime($session->part3_started_at) : time();
                    break;
                default:
                    return 0;
            }

            $endTime = $partStart + ($duration * 60);
            return $endTime - time();
        }
    }

    /**
     * Làm sạch dữ liệu câu hỏi trước khi gửi về Client (ẩn đáp án, giải thích...)
     */
    static function sanitizeQuestions($questions)
    {
        if (empty($questions)) return [];
        
        $isSingle = !is_array($questions);
        if ($isSingle) $questions = [$questions];

        foreach ($questions as &$q) {
            // Xóa các trường nhạy cảm
            unset($q->correct_answer);
            unset($q->explanation);
            unset($q->ai_notes);
            unset($q->created_at);

            // Làm sạch TF items nếu có
            if (isset($q->tf_items) && is_array($q->tf_items)) {
                foreach ($q->tf_items as &$tf) {
                    unset($tf->is_correct);
                }
            }
        }

        return $isSingle ? $questions[0] : $questions;
    }

    /**
     * Generate a unique session key
     */
    private static function generateSessionKey()
    {
        return bin2hex(random_bytes(32));
    }
}
