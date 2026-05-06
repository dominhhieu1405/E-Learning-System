<?php

namespace Services;

use Config\Settings;
use setasign\Fpdi\Fpdi;

/**
 * AiService — Gửi file PDF đến AI (Gemini / OpenAI) và parse câu hỏi trả về
 */
class AiService
{
    // =============================================
    // Config
    // =============================================

    public static function getConfig(): array
    {
        return [
            'provider' => Settings::get('ai_provider', 'gemini'),
            'endpoint' => Settings::get('ai_endpoint', ''),
            'model' => Settings::get('ai_model', 'gemini-1.5-pro'),
            'key' => Settings::get('ai_key', ''),
        ];
    }

    // =============================================
    // Main entry point
    // =============================================

    /**
     * Upload file PDF, gửi lên AI và trả về mảng dữ liệu đã parse.
     *
     * @param string $filePath  Đường dẫn tuyệt đối đến file PDF
     * @param string $examType  'thpt' | 'hsa' | 'tsa'
     * @return array  ['exam_title', 'passage_groups', 'questions', 'warnings']
     * @throws \Exception khi AI trả về lỗi
     */
    public static function importFromPdf(string $filePath, string $examType): array
    {
        $cfg = self::getConfig();

        if (empty($cfg['key'])) {
            throw new \Exception('Chưa cấu hình API Key AI. Vào Admin → Cài đặt → Cấu hình AI để nhập key.');
        }

        // --- Step 1: Đếm số trang PDF bằng FPDI ---
        $pdfReader = new Fpdi();
        $totalPages = $pdfReader->setSourceFile($filePath);

        // --- Step 2: Chia batch (ví dụ 5 trang/batch, gối đầu 1 trang) ---
        $batchSize = 5;
        $step = 5;
        $allQuestions = [];
        $allPassageGroups = [];
        $allWarnings = [];
        $examTitle = 'Đề thi import từ AI';
        $currentSubjectId = 1; // Khởi đầu mặc định cho HSA là Toán (id=1)
        $currentPart = 1;      // Khởi đầu mặc định là Phần 1

        if (PHP_SAPI === 'cli') {
            echo "--- Bắt đầu xử lý PDF ($totalPages trang) bằng FPDI ---\n";
        }

        for ($start = 1; $start <= $totalPages; $start += $step) {
            $end = min($start + $batchSize - 1, $totalPages);

            // Gối đầu 1 trang nếu có thể để giữ context
            $extractStart = max(1, $start - 1);

            if (PHP_SAPI === 'cli') {
                echo "⏳ Trích xuất và xử lý Batch: Trang $start đến $end (Context từ trang $extractStart)... ";
            }

            // Tạo file PDF tạm cho batch này
            $batchFilePath = self::extractPages($filePath, $extractStart, $end);

            try {
                $batchPrompt = self::buildSystemPrompt($examType, $start, $end, $totalPages, $currentSubjectId, $currentPart);

                if ($cfg['provider'] === 'gemini') {
                    $raw = self::callGemini($batchFilePath, $batchPrompt, $cfg);
                } else {
                    $raw = self::callOpenAI($batchFilePath, $batchPrompt, $cfg);
                }

                $batchResult = self::parseResponse($raw);

                // Cập nhật part và subject_id cho batch kế tiếp dựa trên câu cuối cùng của batch này
                if (!empty($batchResult['questions'])) {
                    $lastQ = end($batchResult['questions']);
                    if (!empty($lastQ['subject_id'])) {
                        $currentSubjectId = $lastQ['subject_id'];
                    }
                    if (!empty($lastQ['part'])) {
                        $currentPart = $lastQ['part'];
                    }
                }

                if (PHP_SAPI === 'cli') {
                    echo "Done! (Tìm thấy " . count($batchResult['questions'] ?? []) . " câu)\n";
                }

                if ($start === 1) {
                    $examTitle = $batchResult['exam_title'];
                }

                $passageGroups = $batchResult['passage_groups'] ?? [];
                $pgg = [];
                foreach ($passageGroups as $pg) {
                    var_dump($pg);
                    // Thêm tiền tố batch vào group_key để tránh trùng lặp tạm thời
                    $pgg[$pg['group_key']] = $pg['content'];
                }
                var_dump($pgg);
                $batchResult['questions'] = array_map(function ($e) use ($pgg){
                    if (!empty($e['passage_group_key']) && isset($pgg[$e['passage_group_key']])) {
                        $e['passage'] = $pgg[$e['passage_group_key']];
                    }
                    $e['passage_group_key'] = null;
                    return $e;
                }, $batchResult['questions'] ?? []);

                $allQuestions = array_merge($allQuestions, $batchResult['questions']);
//                $allPassageGroups = array_merge($allPassageGroups, $batchResult['passage_groups']);
                $allWarnings = array_merge($allWarnings, $batchResult['warnings']);

            } finally {
                // Xóa file tạm của batch
                if (file_exists($batchFilePath)) {
                    @unlink($batchFilePath);
                }
            }

            if ($end >= $totalPages)
                break;
        }

        // --- Step 3: Lọc trùng và chuẩn hóa ---
        $result = self::mergeAndFinalize($examTitle, $allQuestions, $allPassageGroups, $allWarnings);

        if (empty($result['questions'])) {
            throw new \Exception('AI không trích xuất được câu hỏi nào từ file PDF này. Vui lòng kiểm tra lại định dạng file.');
        }

        return $result;
    }

    /**
     * Trích xuất các trang từ PDF gốc ra một file PDF tạm mới.
     */
    private static function extractPages(string $sourceFile, int $startPage, int $endPage): string
    {
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($sourceFile);

        for ($i = $startPage; $i <= $endPage; $i++) {
            if ($i < 1 || $i > $pageCount)
                continue;
            $tplIdx = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tplIdx);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplIdx);
        }

        $tmpDir = ROOT_PATH . '/public/uploads/temp/';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }
        $tempPath = $tmpDir . 'batch_' . time() . '_' . uniqid() . '.pdf';
        $pdf->Output('F', $tempPath);
        return $tempPath;
    }

    // =============================================
    // Gemini (Google)
    // =============================================

    private static function callGemini(string $filePath, string $prompt, array $cfg): string
    {
        $model = $cfg['model'] ?: 'gemini-1.5-pro';
        $apiKey = $cfg['key'];
        $endpoint = $cfg['endpoint'] ?: "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        // --- Step 1: Upload file lên Gemini Files API ---
        $uploadUrl = "https://generativelanguage.googleapis.com/upload/v1beta/files?key={$apiKey}";
        $fileSize = filesize($filePath);
        $mimeType = 'application/pdf';
        $fileName = basename($filePath);

        // Initiate resumable upload
        $ch = curl_init($uploadUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'X-Goog-Upload-Protocol: resumable',
                'X-Goog-Upload-Command: start',
                "X-Goog-Upload-Header-Content-Length: {$fileSize}",
                "X-Goog-Upload-Header-Content-Type: {$mimeType}",
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode(['file' => ['display_name' => $fileName]]),
            CURLOPT_HEADER => true,
        ]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception("Gemini Files API: Không thể khởi tạo upload (HTTP {$httpCode}).");
        }

        // Extract upload URL from response headers
        preg_match('/x-goog-upload-url: (https?:\/\/[^\r\n]+)/i', substr($resp, 0, $headerSize), $m);
        if (empty($m[1])) {
            throw new \Exception('Gemini Files API: Không tìm được upload URL.');
        }
        $resumableUrl = trim($m[1]);

        // --- Step 2: Upload file data ---
        $fileData = file_get_contents($filePath);
        $ch = curl_init($resumableUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: {$mimeType}",
                "Content-Length: {$fileSize}",
                'X-Goog-Upload-Command: upload, finalize',
                "X-Goog-Upload-Offset: 0",
            ],
            CURLOPT_POSTFIELDS => $fileData,
        ]);
        $uploadResp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception("Gemini Files API: Upload thất bại (HTTP {$httpCode}): {$uploadResp}");
        }

        $uploadData = json_decode($uploadResp, true);
        $fileUri = $uploadData['file']['uri'] ?? null;
        if (!$fileUri) {
            throw new \Exception('Gemini Files API: Không lấy được file URI sau khi upload.');
        }

        // --- Step 3: Gọi Gemini generateContent với file đã upload ---
        $body = json_encode([
            'contents' => [
                [
                    'parts' => [
                        [
                            'file_data' => [
                                'mime_type' => $mimeType,
                                'file_uri' => $fileUri,
                            ]
                        ],
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'maxOutputTokens' => 65536,
                'responseMimeType' => 'application/json',
            ],
        ], JSON_UNESCAPED_UNICODE);

        $url = $endpoint . "?key={$apiKey}";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => 300,
        ]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr)
            throw new \Exception("cURL error: {$curlErr}");
        if ($httpCode !== 200) {
            $errData = json_decode($resp, true);
            $msg = $errData['error']['message'] ?? $resp;
            throw new \Exception("Gemini API lỗi (HTTP {$httpCode}): {$msg}");
        }

        $data = json_decode($resp, true);
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!$text) {
            throw new \Exception('Gemini trả về phản hồi rỗng hoặc bị chặn (finishReason: ' . ($data['candidates'][0]['finishReason'] ?? 'unknown') . ').');
        }

        return $text;
    }

    // =============================================
    // OpenAI / Compatible
    // =============================================

    private static function callOpenAI(string $filePath, string $prompt, array $cfg): string
    {
        $model = $cfg['model'] ?: 'gpt-4o';
        $apiKey = $cfg['key'];
        $endpoint = $cfg['endpoint'] ?: 'https://api.openai.com/v1/chat/completions';

        $fileData = file_get_contents($filePath);
        $base64Pdf = base64_encode($fileData);

        $body = json_encode([
            'model' => $model,
            'temperature' => 0.1,
            'max_completion_tokens' => 99999,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $prompt,
                        ],
                        [
                            'type' => 'file',
                            'file' => [
                                'filename' => basename($filePath),
                                'file_data' => "data:application/pdf;base64,{$base64Pdf}",
                            ],
                        ],
                    ],
                ]
            ],
            'response_format' => ['type' => 'json_object'],
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: Bearer {$apiKey}",
            ],
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => 300,
        ]);
        $resp = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr)
            throw new \Exception("cURL error: {$curlErr}");
        if ($httpCode !== 200) {
            $errData = json_decode($resp, true);
            $msg = $errData['error']['message'] ?? $resp;
            throw new \Exception("OpenAI API lỗi (HTTP {$httpCode}): {$msg}");
        }

        $data = json_decode($resp, true);
        $text = $data['choices'][0]['message']['content'] ?? null;
        if (!$text) {
            throw new \Exception('OpenAI trả về phản hồi rỗng.');
        }

        return $text;
    }

    // =============================================
    // System Prompt
    // =============================================
    public static function buildSystemPrompt(string $examType, int $startPage = 1, int $endPage = 0, int $totalPages = 0, int $currentSubjectId = 1, int $currentPart = 1): string
    {
        $typeLabel = match ($examType) {
            'thpt' => 'THPT Quốc gia (2025)',
            'hsa' => 'HSA - Đánh giá năng lực (ĐHQG)',
            'tsa' => 'TSA - Tư duy (ĐHBK)',
            default => strtoupper($examType),
        };

        $rangeNote = "";
        if ($totalPages > 0) {
            $isFirstBatch = ($startPage === 1);
            $contextPage = max(1, $startPage - 1);
            $rangeNote = "\n**QUAN TRỌNG - Batch trang $startPage đến $endPage / $totalPages trang tổng:**\n";
            if (!$isFirstBatch) {
                $rangeNote .= "- Trang $contextPage là trang **CONTEXT** (gối đầu). Nếu có câu hỏi bắt đầu trang $contextPage nhưng kết thúc trang $startPage trở đi (câu nằm vắt), hãy trích xuất câu đó TRONG BATCH NÀY với đầy đủ đề BÀI và ĐÁP ÁN.\n";
                $rangeNote .= "- Câu hỏi nào **bắt đầu và kết thúc hoàn toàn ở trang $contextPage** thì **BỎ QUA**, đừng trích xuất lại.\n";
            }
            $rangeNote .= "- CHỈ trích xuất câu hỏi bắt đầu từ trang $startPage đến $endPage.\n";
            $rangeNote .= "- Nếu câu hỏi bắt đầu trong khoảng này nhưng đáp án nằm ở trang tiếp theo > $endPage, hãy để `correct_answer` là \"PENDING\".";
        }

        $typeRules = match ($examType) {
            'thpt' => <<<RULES
- Phần 1 (part=1): Câu trắc nghiệm nhiều lựa chọn (type="mc"), 4 đáp án A/B/C/D.
- Phần 2 (part=2): Câu trắc nghiệm đúng/sai (type="tf"), mỗi câu có đúng 4 ý con (a, b, c, d).
- Phần 3 (part=3): Câu trả lời ngắn / điền số (type="short"), đáp án là một con số hoặc giá trị ngắn.
RULES,
            'hsa' => <<<RULES
**PHẦN 1 – Tư duy Định lượng (Toán học)** → part=1, subject_id=1 (75 phút)
- Gồm câu trắc nghiệm (type="mc") và điền số (type="short").

**PHẦN 2 – Tư duy Định tính (Ngữ văn / Tiếng Việt)** → part=2, subject_id=2 (60 phút)
- Gồm câu trắc nghiệm (type="mc") dựa trên bài đọc hiểu.

**PHẦN 3 – KHOA HỌC HOẶC TIẾNG ANH** → part=3 (60 phút)
- AI phải xác định MÔN dựa trên TIÊU ĐỀ hoặc CHÚ THÍCH môn học trong PDF:

**1. TIẾNG ANH (ENGLISH)**
- Gán `subject_id = 3`.

**2. KHOA HỌC / TỰ CHỌN**
- AI phải cập nhật `subject_id` khi thấy tiêu đề môn tương ứng:
  * "VẬT LÍ" / "VẬT LÝ" → subject_id=4
  * "HÓA HỌC" → subject_id=5
  * "SINH HỌC" → subject_id=6
  * "LỊCH SỬ" → subject_id=7
  * "ĐỊA LÍ" / "ĐỊA LÝ" → subject_id=8

Lưu ý: Tiêu đề không bắt buộc phải khớp 100% nhưng phải có từ khóa nhận diện rõ ràng. Nếu không thấy tiêu đề môn mới, hãy mặc định các câu tiếp theo vẫn thuộc môn hiện tại.
Vd: "Phần thứ ba. KHOA HỌC Chủ đề Lịch sử" => Lịch sử (subject_id 7)
Vd2: "Phần thi thứ hai: Ngôn ngữ - Văn học" => Văn học (subject_id 2)
Vd3: "PHẦN 3: NGOẠI NGỮ (TIẾNG ANH)" => Tiếng Anh (subject_id 3)


**CONTEXT HIỆN TẠI:**
- Bạn đang tiếp nối từ batch trước. Nếu trong text của batch này không xuất hiện tiêu đề phần/môn mới, BẮT BUỘC gán mặc định cho tất cả các câu hỏi: **Part = {$currentPart}** và **Subject ID = {$currentSubjectId}**.
RULES,
            'tsa' => <<<RULES
- Phần 1 (part=1): Tư duy toán học.
- Phần 2 (part=2): Tư duy đọc hiểu.
- Phần 3 (part=3): Tư duy giải quyết vấn đề.
RULES,
            default => '- Câu trắc nghiệm (type="mc"), 4 đáp án A/B/C/D.',
        };

        return <<<PROMPT
Bạn là AI chuyên trích xuất câu hỏi từ đề thi Việt Nam. Hãy phân tích file PDF đề thi loại **{$typeLabel}** và trả về JSON theo cấu trúc sau.
{$rangeNote}

## Quy tắc phân loại câu hỏi theo loại đề:

{$typeRules}

## Quy tắc xử lý đặc biệt:

**LaTeX:** Dùng `\$...\$` cho công thức inline, `\$\$...\$\$` cho công thức block. Ví dụ: `\$f(x) = x^2\$`, `\$\$\\int_0^1 x\\,dx = \\frac{1}{2}\$\$`.

**Hình ảnh/Biểu đồ:** Nếu câu hỏi có hình ảnh, biểu đồ, bảng số liệu ở dạng ảnh:
- Thêm `[Hình ảnh: <mô tả ngắn>]` vào trường `content` tại vị trí hình ảnh.
- Điền `ai_notes` bằng mô tả chi tiết hình ảnh để admin thêm thủ công.

**Đoạn văn chung (Passage Group):** Nếu nhiều câu hỏi cùng dựa vào một đoạn văn/bảng số liệu chung:
- Đưa đoạn văn vào `passage_groups` với `group_key` dạng `"pg1"`, `"pg2"`, ...
- Các câu hỏi thuộc nhóm đó set `passage_group_key` bằng `group_key` tương ứng.
- **Không lặp lại** nội dung đoạn văn trong từng câu hỏi.

**Câu tf (đúng/sai):** Mỗi câu PHẢI có đúng 4 ý con trong mảng `tf_items`, mỗi ý có `content` và `is_correct` (true/false).

**Câu short:** `correct_answer` là giá trị chính xác, có thể là số, phân số (vd: "1/3"), hoặc chuỗi ngắn. Không bỏ đơn vị vào correct_answer.

## Cấu trúc JSON trả về (BẮT BUỘC):

```json
{
  "exam_title": "Tên đề thi trích từ file",
  "passage_groups": [
    {
      "group_key": "pg1",
      "content": "Nội dung đoạn văn/bảng số liệu chung...",
      "part": 1
    }
  ],
  "questions": [
    {
      "type": "mc",
      "part": 1,
      "order": 1,
      "content": "Nội dung câu hỏi, có thể chứa LaTeX \$f(x)\$",
      "passage_group_key": null,
      "options": {"A": "...", "B": "...", "C": "...", "D": "..."},
      "correct_answer": "A",
      "explanation": "Giải thích (nếu có)",
      "ai_notes": null
    },
    {
      "type": "tf",
      "part": 2,
      "order": 1,
      "content": "Câu hỏi đúng/sai...",
      "passage_group_key": null,
      "options": null,
      "tf_items": [
        {"content": "Ý a: ...", "is_correct": true},
        {"content": "Ý b: ...", "is_correct": false},
        {"content": "Ý c: ...", "is_correct": true},
        {"content": "Ý d: ...", "is_correct": false}
      ],
      "correct_answer": "a:1,b:0,c:1,d:0",
      "explanation": "",
      "ai_notes": null
    },
    {
      "type": "short",
      "part": 3,
      "order": 1,
      "content": "Tính giá trị \$S = \\sum_{k=1}^{10} k^2\$.",
      "passage_group_key": null,
      "options": null,
      "correct_answer": "385",
      "explanation": "Dùng công thức \$\\frac{n(n+1)(2n+1)}{6}\$ với \$n=10\$.",
      "ai_notes": null
    },
    {
      "type": "mc",
      "part": 1,
      "order": 5,
      "content": "[Hình ảnh: biểu đồ cột thống kê] Năm nào có số liệu cao nhất?",
      "passage_group_key": null,
      "options": {"A": "2020", "B": "2021", "C": "2022", "D": "2023"},
      "correct_answer": "C",
      "explanation": "",
      "ai_notes": "Câu này có biểu đồ cột thống kê cần admin chèn ảnh thủ công."
    }
  ]
}
```

**Lưu ý quan trọng:**
- Trả về JSON thuần túy, không bọc trong markdown code block.
- Giữ nguyên thứ tự câu hỏi trong đề, `order` phải là số thứ tự câu trong TOÀN BỘ đề thi (không reset về 1 ở mỗi phần).
- Không bỏ sót bất kỳ câu hỏi nào có trong các trang của batch này.
- Nếu câu hỏi có kèm đáp án ở phần đáp án riêng (cuối đề hoặc trang sau), hãy điền vào `correct_answer`; nếu chưa thấy đáp án thì để `"PENDING"`.
- Đối với đề HSA part=3, mỗi câu PHẢI có trường `subject_id` (số nguyên theo mapping ở trên).
PROMPT;
    }

    // =============================================
    // Parse & Validate JSON response
    // =============================================

    public static function parseResponse(string $raw): array
    {
        // Strip markdown code fences nếu AI bọc JSON
        $raw = preg_replace('/^```(?:json)?\s*/i', '', trim($raw));
        $raw = preg_replace('/\s*```$/i', '', $raw);

        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Thử tìm JSON block trong chuỗi
            if (preg_match('/\{[\s\S]+\}/m', $raw, $m)) {
                $data = json_decode($m[0], true);
            }
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('AI trả về dữ liệu không hợp lệ (không phải JSON): ' . substr($raw, 0, 500));
            }
        }

        $questions = $data['questions'] ?? [];
        $passageGroups = $data['passage_groups'] ?? [];
        $examTitle = $data['exam_title'] ?? 'Đề thi import từ AI';

        // Validate + normalize từng câu
        $warnings = [];
        $normalized = [];
        foreach ($questions as $i => $q) {
            $idx = $i + 1;
            $type = $q['type'] ?? 'mc';
            if (!in_array($type, ['mc', 'tf', 'short'])) {
                $warnings[] = "Câu {$idx}: Loại câu hỏi \"{$type}\" không hợp lệ, bỏ qua.";
                continue;
            }
            if (empty($q['content'])) {
                $warnings[] = "Câu {$idx}: Không có nội dung, bỏ qua.";
                continue;
            }
            if ($type === 'mc' && empty($q['options'])) {
                $warnings[] = "Câu {$idx}: Câu mc không có đáp án, bỏ qua.";
                continue;
            }
            if ($type === 'tf' && (empty($q['tf_items']) || count($q['tf_items']) !== 4)) {
                $warnings[] = "Câu {$idx}: Câu tf phải có đúng 4 ý con, bỏ qua.";
                continue;
            }
            $normalized[] = [
                'type' => $type,
                'part' => (int) ($q['part'] ?? 1),
                'order' => (int) ($q['order'] ?? $idx),
                'content' => trim($q['content']),
                'passage_group_key' => $q['passage_group_key'] ?? null,
                'options' => isset($q['options']) ? self::normalizeOptions($q['options']) : null,
                'tf_items' => $q['tf_items'] ?? null,
                'correct_answer' => trim($q['correct_answer'] ?? ''),
                'explanation'    => trim($q['explanation'] ?? ''),
                'ai_notes'       => $q['ai_notes'] ? trim($q['ai_notes']) : null,
                'subject_id'     => isset($q['subject_id']) ? (int) $q['subject_id'] : null,
            ];
        }

        return [
            'exam_title' => $examTitle,
            'passage_groups' => $passageGroups,
            'questions' => $normalized,
            'warnings' => $warnings,
        ];
    }
    /**
     * Gộp kết quả từ các batch, lọc trùng câu hỏi dựa trên nội dung.
     */
    public static function mergeAndFinalize(string $title, array $questions, array $passageGroups, array $warnings): array
    {
        $finalQuestions = [];
        $seenByOrder = []; // "part_order" -> index in $finalQuestions (để ưu tiên bản có đáp án)
        $seenByContent = []; // content_hash -> true (tránh lặp câu hoàn toàn giống nhau)
        $uniquePassages = [];
        $finalPG = [];

//        // 1. Lọc trùng Passage Groups
//        foreach ($passageGroups as $pg) {
//            $pgContent = preg_replace('/\s+/', '', mb_strtolower($pg['content']));
//            $hash = md5($pgContent);
//            if (!isset($uniquePassages[$hash])) {
//                $newKey = "pg_" . (count($finalPG) + 1);
//                $uniquePassages[$hash] = $newKey;
//                $pg['group_key'] = $newKey;
//                $finalPG[] = $pg;
//            }
//        }

        // 2. Merge questions: ưu tiên bản có đáp án khi gặp trùng order
        foreach ($questions as $q) {
            $contentNorm = preg_replace('/\s+/', '', mb_strtolower($q['content']));
            $contentHash = md5($q['part'] . '_' . $contentNorm);
            $orderKey = $q['part'] . '_' . $q['order'];
            $answer = trim($q['correct_answer'] ?? '');
            $hasAnswer = ($answer !== '' && $answer !== 'PENDING');

//            // Remap passage_group_key
//            if (!empty($q['passage_group_key'])) {
//                $oldKey = $q['passage_group_key'];
//                foreach ($passageGroups as $pg) {
//                    if (($pg['group_key'] ?? '') === $oldKey) {
//                        $pgHash = md5(preg_replace('/\s+/', '', mb_strtolower($pg['content'])));
//                        if (isset($uniquePassages[$pgHash])) {
//                            $q['passage_group_key'] = $uniquePassages[$pgHash];
//                            break;
//                        }
//                    }
//                }
//            }

            // Case A: Nội dung hoàn toàn giống (cùng content hash) → chỉ giữ bản có đáp án
            if (isset($seenByContent[$contentHash])) {
                $existingIdx = $seenByContent[$contentHash];
                $existingAnswer = trim($finalQuestions[$existingIdx]['correct_answer'] ?? '');
                $existingHasAnswer = ($existingAnswer !== '' && $existingAnswer !== 'PENDING');
                if ($hasAnswer && !$existingHasAnswer) {
                    // Bản mới có đáp án, bản cũ thì không → cập nhật đáp án
                    $finalQuestions[$existingIdx]['correct_answer'] = $answer;
                    $finalQuestions[$existingIdx]['explanation'] = $q['explanation'] ?? '';
                }
                continue;
            }

            // Case B: Cùng order nhưng nội dung khác nhẹ (AI detect lại câu từ trang gối) → merge đáp án
            if (isset($seenByOrder[$orderKey])) {
                $existingIdx = $seenByOrder[$orderKey];
                $existingAnswer = trim($finalQuestions[$existingIdx]['correct_answer'] ?? '');
                $existingHasAnswer = ($existingAnswer !== '' && $existingAnswer !== 'PENDING');

                if ($hasAnswer && !$existingHasAnswer) {
                    $finalQuestions[$existingIdx]['correct_answer'] = $answer;
                    $finalQuestions[$existingIdx]['explanation'] = $q['explanation'] ?? '';
                }
                // Bỏ qua bản trùng order
                continue;
            }

            // Câu mới hoàn toàn
            $newIdx = count($finalQuestions);
            $seenByContent[$contentHash] = $newIdx;
            $seenByOrder[$orderKey] = $newIdx;
            $finalQuestions[] = $q;
        }

        // Sắp xếp lại theo part rồi order
        usort(
            $finalQuestions,
            fn($a, $b) =>
            $a['part'] !== $b['part']
            ? $a['part'] <=> $b['part']
            : $a['order'] <=> $b['order']
        );

        return [
            'exam_title' => $title,
            'passage_groups' => $finalPG,
            'questions' => $finalQuestions,
            'warnings' => array_unique($warnings),
        ];
    }

    /**
     * Chuẩn hóa options: chuyển object {"A":"...","B":"...","C":"...","D":"..."} → JSON array ["valA","valB","valC","valD"]
     * để nhất quán với cách lưu từ form thủ công (array indexed).
     */
    private static function normalizeOptions($options): string
    {
        if (is_string($options)) {
            $options = json_decode($options, true);
        }
        if (!is_array($options)) {
            return json_encode([], JSON_UNESCAPED_UNICODE);
        }
        // Nếu đã là array dạng [0=>"...", 1=>"..."] thì giữ nguyên
        if (array_values($options) === $options) {
            return json_encode(array_values($options), JSON_UNESCAPED_UNICODE);
        }
        // Nếu là object dạng {A:"...", B:"...", C:"...", D:"..."}
        $keys = ['A', 'B', 'C', 'D'];
        $arr = [];
        foreach ($keys as $k) {
            $arr[] = $options[$k] ?? $options[strtolower($k)] ?? '';
        }
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }
}
