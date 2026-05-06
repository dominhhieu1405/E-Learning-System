/**
 * ExamApp — Core exam logic
 * Timer, auto-save, navigator, lock-part, submit
 */
const ExamApp = {
    sessionKey: '',
    examType: '',      // 'thpt' | 'hsa'
    currentPart: 1,
    currentQ: 0,
    questions: [],
    answers: {},
    totalQuestions: 0,
    autoSaveInterval: null,

    init(config) {
        this.sessionKey = config.sessionKey;
        this.examType = config.examType;
        this.currentPart = config.currentPart || 1;
        this.questions = config.questions || [];
        this.totalQuestions = this.questions.length;
        this.answers = config.savedAnswers || {};

        // Restore from localStorage
        this.loadFromLocal();

        // Render all questions
        this.renderAllQuestions();

        // Start timer
        this.timer.init(config.remainingTime);
        this.timer.start();

        // Auto-save
        this.autoSaveInterval = setInterval(() => {
            this.saveToLocal();
            this.syncToServer();
        }, 30000);

        this.renderNavigator();
        this.bindEvents();
        this.bindAllAnswerEvents();
        this.initScrollDetection();

        this.updatePartUI(this.currentPart);

        // Scroll to last active question if any
        if (this.currentQ > 0) {
            setTimeout(() => this.goTo(this.currentQ), 500);
        }
    },

    goTo(index) {
        if (index < 0 || index >= this.totalQuestions) return;
        const el = document.querySelector(`.question-item-wrap[data-index="${index}"]`);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        this.currentQ = index;
        this.updateNavigator();
    },

    prev() { this.goTo(this.currentQ - 1); },
    next() { this.goTo(this.currentQ + 1); },

    renderAllQuestions() {
        const container = document.getElementById('questionContent');
        if (!container) return;

        let html = '';
        let lastPassageId = null;

        this.questions.forEach((q, index) => {
            const qId = q.id.toString();
            
            // Check for Passage Change
            const currentPassageId = q.passage_group_id || (q.passage ? 'q_' + q.id : null);
            if (currentPassageId && currentPassageId !== lastPassageId) {
                const passageText = q.passage || (q.passage_group ? q.passage_group.content : '');
                if (passageText) {
                    html += `<div class="passage-inline">${passageText}</div>`;
                }
                lastPassageId = currentPassageId;
            } else if (!currentPassageId) {
                lastPassageId = null;
            }

            html += `
            <div class="question-item-wrap mb-5" data-id="${qId}" data-index="${index}" data-part="${q.part}">
                <div class="question-number">Câu ${index + 1}/${this.totalQuestions}</div>
                <div class="question-content">${q.content}</div>
                <div class="options-container" id="options_${qId}">
                    ${this.renderQuestionOptions(q, qId)}
                </div>
            </div>`;
        });

        container.innerHTML = html;

        // Render LaTeX for all
        if (window.MathJax && window.MathJax.typesetPromise) {
            window.MathJax.typesetPromise([container]).catch(err => console.warn('MathJax error:', err));
        }
    },

    renderQuestionOptions(q, qId) {
        if (q.question_type === 'mc') return this.renderMC(q, qId);
        if (q.question_type === 'ms') return this.renderMS(q, qId);
        if (q.question_type === 'tf') return this.renderTF(q, qId);
        if (q.question_type === 'short') return this.renderShort(q, qId);
        if (q.question_type === 'mblanks') return this.renderMBlanks(q, qId);
        if (q.question_type === 'matching') return this.renderMatching(q, qId);
        return '';
    },

    updatePartUI(part) {
        const partLabel = document.getElementById('partLabel');
        if (partLabel) partLabel.textContent = `Phần ${part}/3`;

        const btnLock = document.getElementById('btnLockPart');
        if (this.examType === 'hsa' && btnLock) {
            btnLock.style.display = (part >= 3) ? 'none' : '';
            btnLock.innerHTML = `<i class="fas fa-lock"></i> Khóa phần ${part}`;
        }
        this.currentPart = part;
    },

    renderMC(q, qId) {
        let options = q.options;

        // parse nếu là string
        if (typeof options === 'string') {
            try {
                options = JSON.parse(options);
            } catch (e) {
                options = {};
            }
        }

        const labels = ['A', 'B', 'C', 'D'];
        let html = '';

        labels.forEach((label, index) => {
            const opt = options[index] ?? '';

            const selected =
                this.answers[qId] === label ? 'selected' : '';

            html += `
        <div class="option-item ${selected}"
             data-qid="${qId}"
             data-value="${label}">
            <div class="option-radio"></div>
            <span class="option-label">${label}.</span>
            <span class="option-text">${opt}</span>
        </div>`;
        });

        return html;
    },

    renderTF(q, qId) {
        const tfItems = q.tf_items || [];
        const userAnswer = this.answers[qId] || {};
        const labels = ['a', 'b', 'c', 'd'];
        let html = '<div class="tf-container">';
        tfItems.forEach((item, i) => {
            const key = (item.order || (i + 1)).toString();
            const val = userAnswer[key];
            html += `
            <div class="tf-row">
                <div class="tf-content">
                    <strong>${labels[i]})</strong> ${item.content}
                </div>
                <div class="tf-actions">
                    <div class="tf-btn ${val === true ? 'selected-true' : ''}" data-qid="${qId}" data-key="${key}" data-value="true">Đ</div>
                    <div class="tf-btn ${val === false ? 'selected-false' : ''}" data-qid="${qId}" data-key="${key}" data-value="false">S</div>
                </div>
            </div>`;
        });
        html += '</div>';
        return html;
    },

    renderShort(q, qId) {
        const val = this.answers[qId] || '';
        return `<div class="mb-3">
            <input type="text" class="short-input" data-qid="${qId}" value="${val}" placeholder="Nhập đáp án...">
        </div>`;
    },

    renderMS(q, qId) {
        const options = typeof q.options === 'string' ? JSON.parse(q.options) : (q.options || []);
        const labels = ['A', 'B', 'C', 'D'];
        const selectedArr = (this.answers[qId] || '').split(',').filter(x => x);
        let html = '';
        options.forEach((opt, i) => {
            const isSelected = selectedArr.includes(labels[i]);
            html += `
            <div class="option-item ms-item ${isSelected ? 'selected' : ''}" data-qid="${qId}" data-value="${labels[i]}">
                <div class="option-checkbox"><i class="fas ${isSelected ? 'fa-check-square' : 'fa-square'}"></i></div>
                <span class="option-label">${labels[i]}.</span>
                <span class="option-text">${opt}</span>
            </div>`;
        });
        return html;
    },

    renderMBlanks(q, qId) {
        let content = q.content;
        const savedAnswer = this.answers[qId] || '';
        const savedArr = savedAnswer.split('|');
        let count = 0;
        
        // Replace [blank] with input
        const html = content.replace(/\[blank\]/g, function() {
            const val = savedArr[count] || '';
            const input = `<input type="text" class="mblank-input" data-qid="${qId}" data-index="${count}" value="${val}" style="width: 120px; display: inline-block; margin: 0 5px; border-bottom: 2px solid #6366f1 !important; border:none; outline:none; background: transparent; text-align:center;">`;
            count++;
            return input;
        });
        
        // We actually want the content in the .question-content div, but renderQuestionOptions returns what's inside .options-container.
        // So for mblanks, we'll clear the .question-content and put it here, or just wrap it.
        // Special case: we append a hidden script to update the parent content
        return `<div class="mblanks-container" data-qid="${qId}">${html}</div>
                <style>#questionContent .question-item-wrap[data-id="${qId}"] .question-content { display: none; }</style>`;
    },

    renderMatching(q, qId) {
        const pairs = typeof q.options === 'string' ? JSON.parse(q.options) : (q.options || []);
        const userAnswer = this.answers[qId] || {}; // { left_val: right_val }
        
        // Prepare Right side (shuffled once for the UI)
        if (!q._shuffledMatching) {
            q._shuffledMatching = pairs.map(p => p.right).sort(() => Math.random() - 0.5);
        }
        
        let html = '<div class="matching-container d-flex flex-column gap-3">';
        pairs.forEach((p, i) => {
            const leftVal = p.left;
            const currentSelectedRight = userAnswer[leftVal] || null;
            
            html += `
            <div class="matching-row d-flex align-items-center gap-3">
                <div class="matching-left p-2 border rounded bg-light" style="flex: 1;">${p.left}</div>
                <div class="matching-icon"><i class="fas fa-link text-muted"></i></div>
                <div class="matching-right" style="flex: 1;">
                    <select class="form-select form-select-sm matching-select" data-qid="${qId}" data-left="${leftVal}">
                        <option value="">-- Chọn ghép --</option>
                        ${q._shuffledMatching.map(rv => `
                            <option value="${rv}" ${currentSelectedRight === rv ? 'selected' : ''}>${rv}</option>
                        `).join('')}
                    </select>
                </div>
            </div>`;
        });
        html += '</div>';
        return html;
    },

    bindAllAnswerEvents() {
        const self = this;
        // MC
        $(document).on('click', '.option-item', function() {
            const qId = $(this).data('qid');
            const val = $(this).data('value');
            const isAlreadySelected = self.answers[qId] === val;

            $(`.option-item[data-qid="${qId}"]`).removeClass('selected');
            
            if (isAlreadySelected) {
                delete self.answers[qId];
            } else {
                $(this).addClass('selected');
                self.answers[qId] = val;
            }
            
            self.saveToLocal();
            self.updateNavigator();
        });

        // TF
        $(document).on('click', '.tf-btn', function() {
            const qId = $(this).data('qid');
            const key = $(this).data('key').toString();
            const val = $(this).data('value') === true;
            
            if (!self.answers[qId] || typeof self.answers[qId] !== 'object') self.answers[qId] = {};
            
            const isAlreadySelected = self.answers[qId][key] === val;
            
            // Clear current row selection
            $(this).closest('.tf-row').find('.tf-btn').removeClass('selected-true selected-false');

            if (isAlreadySelected) {
                // Deselect
                delete self.answers[qId][key];
                if (Object.keys(self.answers[qId]).length === 0) delete self.answers[qId];
            } else {
                // Select
                self.answers[qId][key] = val;
                $(this).addClass(val ? 'selected-true' : 'selected-false');
            }
            
            self.saveToLocal();
            self.updateNavigator();
        });

        // Multiple Select (MS)
        $(document).on('click', '.ms-item', function() {
            const qId = $(this).data('qid');
            const val = $(this).data('value');
            let selectedArr = (self.answers[qId] || '').split(',').filter(x => x);
            
            if (selectedArr.includes(val)) {
                selectedArr = selectedArr.filter(x => x !== val);
                $(this).removeClass('selected').find('.fas').removeClass('fa-check-square').addClass('fa-square');
            } else {
                selectedArr.push(val);
                $(this).addClass('selected').find('.fas').removeClass('fa-square').addClass('fa-check-square');
            }
            
            self.answers[qId] = selectedArr.sort().join(',');
            if (!self.answers[qId]) delete self.answers[qId];
            
            self.saveToLocal();
            self.updateNavigator();
        });

        // Multiple Blanks (mblanks)
        $(document).on('input', '.mblank-input', function() {
            const qId = $(this).data('qid');
            const container = $(this).closest('.mblanks-container');
            const inputs = container.find('.mblank-input');
            const vals = [];
            inputs.each(function() { vals.push($(this).val().trim()); });
            
            self.answers[qId] = vals.join('|');
            if (vals.every(v => v === '')) delete self.answers[qId];
            
            self.saveToLocal();
            self.updateNavigator();
        });

        // Matching
        $(document).on('change', '.matching-select', function() {
            const qId = $(this).data('qid');
            const left = $(this).data('left');
            const right = $(this).val();
            
            if (!self.answers[qId] || typeof self.answers[qId] !== 'object') self.answers[qId] = {};
            
            if (!right) delete self.answers[qId][left];
            else self.answers[qId][left] = right;
            
            if (Object.keys(self.answers[qId]).length === 0) delete self.answers[qId];
            
            self.saveToLocal();
            self.updateNavigator();
        });

        // Short
        $(document).on('input', '.short-input', function() {
            const qId = $(this).data('qid');
            self.answers[qId] = $(this).val().trim();
            if (!self.answers[qId]) delete self.answers[qId];
            self.saveToLocal();
            self.updateNavigator();
        });
    },

    initScrollDetection() {
        const container = document.getElementById('questionContent');
        if (!container) return;

        const self = this;
        let isScrolling;
        
        container.addEventListener('scroll', () => {
            window.clearTimeout(isScrolling);
            isScrolling = setTimeout(() => {
                const items = document.querySelectorAll('.question-item-wrap');
                const containerRect = container.getBoundingClientRect();
                
                // Check if we are at the bottom
                const isBottom = Math.abs(container.scrollHeight - container.scrollTop - container.clientHeight) < 50;
                
                let activeIndex = 0;
                if (isBottom) {
                    activeIndex = items.length - 1;
                } else {
                    // Find the question closest to the top 20% of the viewport
                    const targetTop = containerRect.top + (containerRect.height * 0.2);
                    let minDiff = Infinity;
                    
                    items.forEach((item, i) => {
                        const rect = item.getBoundingClientRect();
                        const diff = Math.abs(rect.top - targetTop);
                        if (diff < minDiff) {
                            minDiff = diff;
                            activeIndex = i;
                        }
                    });
                }
                
                if (activeIndex !== self.currentQ && activeIndex >= 0) {
                    self.currentQ = activeIndex;
                    self.updateNavigator();
                    const item = items[activeIndex];
                    if (item && item.dataset.part) {
                        self.updatePartUI(parseInt(item.dataset.part));
                    }
                }
            }, 50);
        }, { passive: true });
    },

    bindEvents() {
        const self = this;
        document.getElementById('btnPrev')?.addEventListener('click', () => self.prev());
        document.getElementById('btnNext')?.addEventListener('click', () => self.next());
        document.querySelectorAll('#btnSubmit, #btnSubmitPC, #btnSubmitMobile').forEach(btn => {
            btn.addEventListener('click', () => self.submit());
        });
        document.querySelectorAll('#btnLockPart, #btnLockPartPC, #btnLockPartMobile').forEach(btn => {
            btn.addEventListener('click', () => self.lockPart());
        });

        document.getElementById('btnNavToggle')?.addEventListener('click', () => {
            new bootstrap.Offcanvas(document.getElementById('navOffcanvas')).show();
        });
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowUp') self.prev();
            if (e.key === 'ArrowDown') self.next();
        });
    },

    renderNavigator() {
        const containers = document.querySelectorAll('.nav-grid');
        containers.forEach(container => {
            let html = '';
            this.questions.forEach((q, i) => {
                const answered = this.isAnswered(q.id.toString()) ? 'answered' : '';
                const current = i === this.currentQ ? 'current' : '';
                html += `<div class="nav-btn ${answered} ${current}" data-index="${i}">${i + 1}</div>`;
            });
            container.innerHTML = html;
            container.querySelectorAll('.nav-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    this.goTo(parseInt(btn.dataset.index));
                    const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('navOffcanvas'));
                    if (offcanvas) offcanvas.hide();
                });
            });
        });
    },

    updateNavigator() {
        document.querySelectorAll('.nav-btn').forEach(btn => {
            const idx = parseInt(btn.dataset.index);
            const q = this.questions[idx];
            if (!q) return;
            btn.classList.remove('current', 'answered');
            if (idx === this.currentQ) btn.classList.add('current');
            else if (this.isAnswered(q.id.toString())) btn.classList.add('answered');
        });
    },

    isAnswered(qId) {
        const a = this.answers[qId];
        if (a === undefined || a === null || a === '') return false;
        if (typeof a === 'object') return Object.keys(a).length > 0;
        return true;
    },

    timer: {
        remaining: 0,
        interval: null,
        el: null,
        init(seconds) {
            this.remaining = Math.max(0, seconds);
            this.el = document.getElementById('timerDisplay');
        },
        start() {
            this.update();
            this.interval = setInterval(() => {
                this.remaining--;
                this.update();
                if (this.remaining <= 0) {
                    this.stop();
                    this.onExpire();
                }
            }, 1000);
        },
        stop() { clearInterval(this.interval); },
        update() {
            if (!this.el) return;
            const h = Math.floor(this.remaining / 3600);
            const m = Math.floor((this.remaining % 3600) / 60);
            const s = this.remaining % 60;
            this.el.textContent = (h > 0 ? h + ':' : '') + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            const timerWrap = this.el.closest('.exam-timer');
            if (this.remaining < 300 && timerWrap) timerWrap.classList.add('warning');
        },
        onExpire() {
            if (ExamApp.examType === 'hsa') ExamApp.lockPart();
            else ExamApp.submit();
        }
    },

    updatePartUI(part) {
        // Update label
        const partLabel = document.getElementById('partLabel');
        if (partLabel) partLabel.textContent = `Phần ${part}/3`;

        // Update current part number in buttons
        document.querySelectorAll('.current-part-num').forEach(el => el.textContent = part);

        // Logic ẩn hiện nút Khóa/Nộp
        const isLastPart = (this.examType === 'hsa' && part >= 3) || (this.examType === 'thpt');
        
        const lockBtns = document.querySelectorAll('#btnLockPartPC, #btnLockPartMobile');
        const submitBtns = document.querySelectorAll('#btnSubmitPC, #btnSubmitMobile');

        if (isLastPart) {
            lockBtns.forEach(b => b.style.display = 'none');
            submitBtns.forEach(b => b.style.display = 'block');
        } else {
            lockBtns.forEach(b => b.style.display = 'block');
            submitBtns.forEach(b => b.style.display = 'none');
        }
    },

    saveToLocal() {
        localStorage.setItem('exam_' + this.sessionKey, JSON.stringify({
            answers: this.answers,
            currentQ: this.currentQ,
            currentPart: this.currentPart,
            lastSaved: Date.now()
        }));
    },

    loadFromLocal() {
        const data = localStorage.getItem('exam_' + this.sessionKey);
        if (data) {
            try {
                const parsed = JSON.parse(data);
                if (parsed.answers) {
                    Object.keys(parsed.answers).forEach(qId => {
                        if (!this.answers[qId]) this.answers[qId] = parsed.answers[qId];
                    });
                }
                if (parsed.currentQ !== undefined) this.currentQ = parsed.currentQ;
            } catch (e) {}
        }
    },

    syncToServer() {
        $.ajax({
            url: '/api/exam/save', method: 'POST',
            data: { session_key: this.sessionKey, answers: JSON.stringify(this.answers) },
            dataType: 'json'
        });
    },

    lockPart() {
        if (this.currentPart >= 3) { this.submit(); return; }
        
        if (this.timer.remaining > 0) {
            this.showConfirm(
                'Khóa phần thi',
                `Bạn muốn kết thúc Phần ${this.currentPart} và chuyển sang phần tiếp theo?\nHành động này không thể quay lại!`,
                () => this.doLockPart()
            );
        } else {
            this.doLockPart();
        }
    },

    doLockPart() {
        this.timer.stop();
        this.saveToLocal();
        $.ajax({
            url: '/api/exam/lock-part', method: 'POST',
            data: { session_key: this.sessionKey, current_part: this.currentPart, answers: JSON.stringify(this.answers) },
            dataType: 'json',
            success: (res) => {
                if (res.status) {
                    if (res.part_score !== undefined) {
                        toastr.success(`Phần ${this.currentPart} hoàn thành! Điểm: ${res.part_score}`);
                    } else {
                        toastr.info(`Phần ${this.currentPart} hoàn thành! Chuyển sang phần tiếp theo.`);
                    }

                    this.currentPart = res.next_part;
                    this.updatePartUI(this.currentPart);

                    if (res.require_branch) {
                        this.showBranchChoice(res);
                    } else if (res.next_questions) {
                        this.processNextQuestions(res);
                    }
                } else toastr.error(res.message || 'Lỗi!');
            }
        });
    },

    showBranchChoice(config) {
        console.log(config);
        const modalId = 'branchChoiceModal';
        const subjectCount = config.subject_count || 3;
        const scienceSubjects = config.science_subjects || [];

        let modalEl = document.getElementById(modalId);
        if (modalEl) modalEl.remove(); // Re-create to refresh subjects

        const html = `
        <div class="modal fade" id="${modalId}" data-bs-backdrop="static" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-ot-primary text-white">
                        <h5 class="modal-title fw-bold">Lựa chọn Phần 3</h5>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-muted mb-4">Vui lòng chọn hướng thi cho Phần 3:</p>
                        <div class="d-grid gap-3">
                            <button class="btn btn-outline-primary p-3 text-start choice-btn" data-type="english">
                                <div class="fw-bold">1. Tiếng Anh</div>
                                <small>Làm 50 câu hỏi Ngoại ngữ (Tiếng Anh)</small>
                            </button>
                            <button class="btn btn-outline-primary p-3 text-start choice-btn" data-type="science">
                                <div class="fw-bold">2. Khoa học</div>
                                <small>Chọn ${subjectCount} môn trong danh mục khoa học</small>
                            </button>
                        </div>

                        <div id="scienceSubjectPick" class="mt-4" style="display:none;">
                            <hr>
                            <p class="fw-bold small mb-2">Chọn ${subjectCount} môn học:</p>
                            <div class="row g-2">
                                ${scienceSubjects.map(s => `
                                    <div class="col-6">
                                        <div class="form-check p-2 border rounded">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" value="${s.subject_id}" id="subj_${s.subject_id}">
                                            <label class="form-check-label" for="subj_${s.subject_id}">${s.label}</label>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                            <div class="mt-3 text-end">
                                <button class="btn btn-ot-primary px-4" id="btnConfirmScience" disabled>Bắt đầu làm bài</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', html);
        modalEl = document.getElementById(modalId);

        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        const self = this;
        const $modal = $(modalEl);
        const $sciencePick = $modal.find('#scienceSubjectPick');
        const $confirmBtn = $modal.find('#btnConfirmScience');

        $modal.find('.choice-btn').on('click', function() {
            const type = $(this).data('type');
            if (type === 'english') {
                self.setBranch({ type: 'english' }, modal);
            } else {
                $sciencePick.slideDown();
                $modal.find('.choice-btn').removeClass('active');
                $(this).addClass('active');
            }
        });

        $modal.find('.form-check-input').on('change', function() {
            const checkedCount = $modal.find('.form-check-input:checked').length;
            $confirmBtn.prop('disabled', checkedCount !== subjectCount);
        });

        $confirmBtn.on('click', function() {
            const selectedIds = [];
            $modal.find('.form-check-input:checked').each(function() { selectedIds.push(parseInt($(this).val())); });
            self.setBranch({ type: 'science', subject_ids: selectedIds }, modal);
        });
    },

    setBranch(choice, modal) {
        $.ajax({
            url: '/api/exam/set-part3-branch', method: 'POST',
            data: { session_key: this.sessionKey, choice: JSON.stringify(choice) },
            dataType: 'json',
            success: (res) => {
                if (res.status) {
                    if (modal) modal.hide();
                    toastr.success('Cập nhật hướng thi thành công!');
                    this.processNextQuestions(res);
                } else toastr.error(res.message || 'Lỗi!');
            }
        });
    },

    processNextQuestions(res) {
        if (res.tf_items) {
            res.next_questions.forEach(q => { 
                if (q.question_type === 'tf' && res.tf_items[q.id]) q.tf_items = res.tf_items[q.id]; 
            });
        }
        this.questions = res.next_questions;
        this.totalQuestions = this.questions.length;
        this.currentQ = 0;
        this.renderAllQuestions();
        this.renderNavigator();
        document.getElementById('questionContent').scrollTop = 0;
        this.timer.init(res.next_duration);
        this.timer.start();
        this.saveToLocal();
    },

    submit() {
        if (this.timer.remaining > 0) {
            this.showConfirm(
                'Nộp bài thi',
                'Bạn chắc chắn muốn kết thúc bài thi và nộp bài?',
                () => this.doSubmit()
            );
        } else {
            this.doSubmit();
        }
    },

    doSubmit() {
        this.timer.stop();
        clearInterval(this.autoSaveInterval);
        this.saveToLocal();
        $.ajax({
            url: '/api/exam/submit', method: 'POST',
            data: { session_key: this.sessionKey, answers: JSON.stringify(this.answers) },
            dataType: 'json',
            success: (res) => {
                if (res.status) {
                    localStorage.removeItem('exam_' + this.sessionKey);
                    window.location.href = res.redirect;
                } else toastr.error(res.message || 'Nộp bài thất bại!');
            }
        });
    },

    showConfirm(title, message, onConfirm) {
        const modalEl = document.getElementById('confirmModal');
        if (!modalEl) return;
        
        document.getElementById('confirmTitle').textContent = title;
        document.getElementById('confirmMessage').textContent = message;
        
        const modal = new bootstrap.Modal(modalEl);
        const confirmBtn = document.getElementById('btnConfirmAction');
        
        const handleConfirm = () => {
            modal.hide();
            onConfirm();
            confirmBtn.removeEventListener('click', handleConfirm);
        };
        
        // Use {once: true} or manual removal to prevent multiple listeners
        confirmBtn.onclick = handleConfirm;
        
        modal.show();
    }
};
