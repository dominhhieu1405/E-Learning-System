<?php
namespace Controllers;

use Models\Exam as ExamModel;
use Models\Question as QuestionModel;
use Models\ExamSession;
use Models\Web as WebModel;
use Services\Blade;

class Exam
{
    /**
     * Chi tiết đề thi
     */
    function detail($id): string
    {
        $exam = ExamModel::getExam($id);
        if (!$exam || !$exam->status) {
            response()->redirect(url('exams'));
            exit;
        }

        $question_count = QuestionModel::countExamQuestions($id);
        $attempts_count = count(WebModel::getDB()->where('exam_id', $id)->where('status', 'completed')->get('exam_sessions'));
        $leaderboard = ExamModel::getLeaderboard($id, 5);

        return (new Blade())->render('user.pages.exam-detail', [
            'title' => 'Chi tiết - ' . $exam->title,
            'exam' => $exam,
            'question_count' => $question_count,
            'attempts_count' => $attempts_count,
            'leaderboard' => $leaderboard,
        ]);
    }

    /**
     * Danh sách đề thi (user)
     */
    function list(): string
    {
        $type = input()->value('type', null, 'get');
        $page = max(1, (int) input()->value('page', 1, 'get'));

        $exams = ExamModel::listExams($type, 1, $page, 12);
        $paginate = ExamModel::getExamPaginate();

        return (new Blade())->render('user.pages.exams', [
            'title' => 'Danh sách đề thi',
            'exams' => $exams,
            'paginate' => $paginate,
            'type' => $type,
        ]);
    }

    /**
     * Bắt đầu / Resume phiên thi
     */
    function start($id): string
    {
        $exam = ExamModel::getExam($id);
        if (!$exam || !$exam->status) {
            response()->redirect(url('exams'));
            exit;
        }

        try {
            $session = ExamSession::startSession(userget()->id, $id);
        } catch (\Exception $e) {
            response()->redirect(url('exam.detail', ['id' => $id]) . '?error=' . urlencode($e->getMessage()));
            exit;
        }
        if (!$session) {
            response()->redirect(url('exams'));
            exit;
        }

        // Lấy câu hỏi theo phần hiện tại
        $currentPart = $session->current_part;

        if ($exam->exam_type === 'hsa') {
            // HSA: chỉ hiển thị câu hỏi phần hiện tại
            $questions = ExamSession::getSessionQuestions($session, $currentPart);
        } else {
            // THPT: tất cả các phần
            $questions = ExamSession::getSessionQuestions($session);
        }

        // Load TF items cho câu Đúng/Sai
        $tfItems = [];
        foreach ($questions as $q) {
            if ($q->question_type === 'tf') {
                $tfItems[$q->id] = QuestionModel::getTfItems($q->id);
            }
        }

        // Passage groups
        $passageGroups = [];
        foreach ($questions as $q) {
            if ($q->passage_group_id) {
                if (!isset($passageGroups[$q->passage_group_id])) {
                    $passageGroup = QuestionModel::getPassageGroup($q->passage_group_id);
                    $passageGroups[$q->passage_group_id] = $passageGroup;
                }
                // Merge passage group content into question object for JS ease of use
                $q->passage_group = $passageGroups[$q->passage_group_id];
            }
        }

        // Thời gian còn lại (server-side)
        $remainingTime = ExamSession::getRemainingTime($session);

        // Bài làm đã lưu
        $savedAnswers = json_decode($session->answers, true) ?? [];

        return (new Blade())->render('user.pages.exam', [
            'title' => $exam->title,
            'exam' => $exam,
            'session' => $session,
            'questions' => ExamSession::sanitizeQuestions($questions),
            'tfItems' => $tfItems,
            'passageGroups' => $passageGroups,
            'remainingTime' => $remainingTime,
            'savedAnswers' => $savedAnswers,
            'currentPart' => $currentPart,
        ]);
    }

    /**
     * API: Lấy câu hỏi theo session (Ajax)
     */
    function getQuestionsApi(): void
    {
        $sessionKey = input()->value('session_key', null, 'get');
        if (!$sessionKey) {
            response()->json(['status' => false, 'message' => 'Thiếu session_key']);
            return;
        }

        $session = ExamSession::getSessionByKey($sessionKey);
        if (!$session || $session->user_id != userget()->id) {
            response()->json(['status' => false, 'message' => 'Session không hợp lệ']);
            return;
        }

        $exam = ExamModel::getExam($session->exam_id);
        $currentPart = $session->current_part;

        if ($exam->exam_type === 'hsa') {
            $questions = ExamSession::getSessionQuestions($session, $currentPart);
        } else {
            $questions = ExamSession::getSessionQuestions($session);
        }

        // TF items
        $tfItems = [];
        foreach ($questions as $q) {
            if ($q->question_type === 'tf') {
                $tfItems[$q->id] = QuestionModel::getTfItems($q->id);
            }
        }

        // Passage groups
        foreach ($questions as $q) {
            if ($q->passage_group_id) {
                $q->passage_group = QuestionModel::getPassageGroup($q->passage_group_id);
            }
        }

        response()->json([
            'status'    => true,
            'questions' => ExamSession::sanitizeQuestions($questions),
            'tf_items'  => $tfItems,
        ]);
    }

    /**
     * API: Start session (Ajax)
     */
    function startApi(): void
    {
        $examId = (int) input()->value('exam_id');
        $session = ExamSession::startSession(userget()->id, $examId);

        if ($session) {
            response()->json([
                'status' => true,
                'session_key' => $session->session_key,
                'redirect' => url('exam.start', ['id' => $examId])
            ]);
        } else {
            response()->json([
                'status' => false,
                'message' => 'Không thể bắt đầu phiên thi!'
            ]);
        }
    }

    /**
     * API: Auto-save bài làm
     */
    function saveAnswersApi(): void
    {
        $sessionKey = input()->value('session_key');
        $answers = json_decode(input()->value('answers'), true);

        $session = ExamSession::getSessionByKey($sessionKey);
        if (!$session || $session->user_id != userget()->id || $session->status === 'completed') {
            response()->json(['status' => false, 'message' => 'Session không hợp lệ']);
            return;
        }

        $remainingTime = ExamSession::getRemainingTime($session);
        if ($remainingTime < -15) {
            response()->json(['status' => false, 'message' => 'Hết thời gian làm bài, không thể lưu đáp án']);
            return;
        }

        ExamSession::saveAnswers($session->id, $answers);
        response()->json(['status' => true]);
    }

    /**
     * API: HSA lock part
     */
    function lockPartApi(): void
    {
        $sessionKey = input()->value('session_key');
        $currentPart = (int) input()->value('current_part');

        $session = ExamSession::getSessionByKey($sessionKey);
        if (!$session || $session->user_id != userget()->id || $session->status === 'completed') {
            response()->json(['status' => false, 'message' => 'Session không hợp lệ']);
            return;
        }

        $remainingTime = ExamSession::getRemainingTime($session);

        // Lưu answers trước khi lock
        $answers = json_decode(input()->value('answers'), true);
        if ($answers && $remainingTime >= -15) {
            ExamSession::saveAnswers($session->id, $answers);
        }

        $result = ExamSession::lockPart($session->id, $currentPart);

        if ($result) {
            // Lấy câu hỏi phần tiếp theo
            $session = ExamSession::getSession($session->id); // refresh
            $nextPart = $result['next_part'];
            $exam = ExamModel::getExam($session->exam_id);
            
            $data = [
                'status' => true,
                'next_part' => $nextPart,
            ];

            // Nếu HSA: không hiện điểm từng phần
            if ($exam->exam_type !== 'hsa') {
                $data['part_score'] = $result['part_score'];
            }

            // Nếu chuẩn bị sang Phần 3 HSA: cần chọn nhánh
            if ($exam->exam_type === 'hsa' && $nextPart == 3) {
                $data['require_branch'] = true;
                
                // Parse random_config to get branch options
                $config = json_decode($exam->random_config, true);
                $p3Config = $config['p3'] ?? null;
                
                if ($p3Config) {
                    $data['subject_count'] = $p3Config['science']['min_select'] ?? 3;
                    $data['science_subjects'] = $p3Config['science']['subjects'] ?? [];
                } else {
                    $data['subject_count'] = 3;
                    $data['science_subjects'] = [
                        ['label' => 'Vật lý', 'subject_id' => 4],
                        ['label' => 'Hóa học', 'subject_id' => 5],
                        ['label' => 'Sinh học', 'subject_id' => 6],
                        ['label' => 'Lịch sử', 'subject_id' => 7],
                        ['label' => 'Địa lý', 'subject_id' => 8]
                    ];
                }
            } else {
                $nextQuestions = ExamSession::getSessionQuestions($session, $nextPart);
                
                // TF items and Passage groups cho phần mới
                $tfItems = [];
                foreach ($nextQuestions as $q) {
                    if ($q->question_type === 'tf') {
                        $tfItems[$q->id] = QuestionModel::getTfItems($q->id);
                    }
                    if ($q->passage_group_id) {
                        $q->passage_group = QuestionModel::getPassageGroup($q->passage_group_id);
                    }
                }

                $durationField = "duration_p{$nextPart}";
                $nextDuration = $exam->$durationField ?? 0;
                
                $data['next_duration'] = $nextDuration * 60;
                $data['next_questions'] = ExamSession::sanitizeQuestions($nextQuestions);
                $data['tf_items'] = $tfItems;
            }

            response()->json($data);
        } else {
            response()->json(['status' => false, 'message' => 'Không thể khóa phần']);
        }
    }

    /**
     * API: HSA chọn nhánh phần 3
     */
    function setPart3BranchApi(): void
    {
        $sessionKey = input()->value('session_key');
        $choice = input()->value('choice'); // JSON string

        $session = ExamSession::getSessionByKey($sessionKey);
        if (!$session || $session->user_id != userget()->id) {
            response()->json(['status' => false, 'message' => 'Session không hợp lệ']);
            return;
        }

        if (!empty($session->part3_choice)) {
            response()->json(['status' => false, 'message' => 'Bạn đã lựa chọn hướng thi cho Phần 3 rồi.']);
            return;
        }

        // Lưu lựa chọn
        ExamSession::getDB()->where('id', $session->id)->update('exam_sessions', [
            'part3_choice' => $choice
        ]);

        // Trả về câu hỏi sau khi đã chọn
        $session = ExamSession::getSession($session->id); // refresh
        $questions = ExamSession::getSessionQuestions($session, 3);
        $exam = ExamModel::getExam($session->exam_id);

        $tfItems = [];
        foreach ($questions as $q) {
            if ($q->question_type === 'tf') {
                $tfItems[$q->id] = QuestionModel::getTfItems($q->id);
            }
            if ($q->passage_group_id) {
                $q->passage_group = QuestionModel::getPassageGroup($q->passage_group_id);
            }
        }

        response()->json([
            'status' => true,
            'next_questions' => ExamSession::sanitizeQuestions($questions),
            'tf_items' => $tfItems,
            'next_duration' => ($exam->duration_p3 ?? 0) * 60
        ]);
    }

    /**
     * API: Nộp bài hoàn tất
     */
    function submitApi(): void
    {
        $sessionKey = input()->value('session_key');

        $session = ExamSession::getSessionByKey($sessionKey);
        if (!$session || $session->user_id != userget()->id) {
            response()->json(['status' => false, 'message' => 'Session không hợp lệ']);
            return;
        }

        if ($session->status === 'completed') {
            response()->json([
                'status' => true,
                'redirect' => url('exam.result', ['id' => $session->id])
            ]);
            return;
        }

        $remainingTime = ExamSession::getRemainingTime($session);

        // Lưu answers cuối cùng
        $answers = json_decode(input()->value('answers'), true);
        if ($answers && $remainingTime >= -15) {
            ExamSession::saveAnswers($session->id, $answers);
        }

        $scores = ExamSession::submitExam($session->id);

        if ($scores) {
            response()->json([
                'status' => true,
                'scores' => $scores,
                'redirect' => url('exam.result', ['id' => $session->id])
            ]);
        } else {
            response()->json(['status' => false, 'message' => 'Nộp bài thất bại!']);
        }
    }

    /**
     * Xem kết quả
     */
    function result($id): string
    {
        $session = ExamSession::getSession($id);
        if (!$session || $session->user_id != userget()->id) {
            response()->redirect(url('exams'));
            exit;
        }

        $exam = ExamModel::getExam($session->exam_id);
        $questions = ExamSession::getSessionQuestions($session);
        $answers = json_decode($session->answers, true) ?? [];

        // TF items
        $tfItems = [];
        foreach ($questions as $q) {
            if ($q->question_type === 'tf') {
                $tfItems[$q->id] = QuestionModel::getTfItems($q->id);
            }
        }

        $leaderboard = ExamModel::getLeaderboard($session->exam_id, 10);

        return (new Blade())->render('user.pages.result', [
            'title' => 'Kết quả - ' . $exam->title,
            'exam' => $exam,
            'session' => $session,
            'questions' => $exam->show_answers ? $questions : [], // Ẩn câu hỏi nếu admin yêu cầu
            'answers' => $answers,
            'tfItems' => $tfItems,
            'leaderboard' => $leaderboard,
        ]);
    }

    /**
     * Bảng xếp hạng
     */
    function leaderboard($id): string
    {
        $exam = ExamModel::getExam($id);
        if (!$exam) {
            response()->redirect(url('exams'));
            exit;
        }

        $leaderboard = ExamModel::getLeaderboard($id, 100);

        return (new Blade())->render('user.pages.leaderboard', [
            'title' => 'Bảng xếp hạng - ' . $exam->title,
            'exam' => $exam,
            'leaderboard' => $leaderboard,
        ]);
    }
}
