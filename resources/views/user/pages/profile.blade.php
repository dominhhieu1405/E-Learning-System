@extends('user.layout.layout')
@section('title', __('profile.title'))
@section('content')

<div class="container py-5">
    <div class="row g-4">
        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="ot-card p-4 text-center">
                <div class="mb-3 position-relative d-inline-block avatar-upload-container">
                    <img src="{{ $user->avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($user->display_name).'&background=6366f1&color=fff' }}" 
                         alt="Avatar" class="rounded-circle border" style="width:120px; height:120px; object-fit:cover;" id="profileAvatarPreview">
                    <label for="avatar_file" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 p-2 shadow-sm" style="cursor:pointer;">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="avatar_file" class="d-none" accept="image/*">
                </div>
                <h5 class="fw-bold mb-1" id="sidebarDisplayName">{{ $user->display_name }}</h5>
                <p class="text-muted small mb-4">@<span>{{ $user->username }}</span></p>
                
                <div class="list-group list-group-flush text-start border-top pt-3">
                    <a href="#tab-info" class="list-group-item list-group-item-action border-0 active rounded-3 mb-1" data-bs-toggle="pill">
                        <i class="fas fa-user-edit me-2"></i> {{ __('profile.info_tab') }}
                    </a>
                    <a href="#tab-history" class="list-group-item list-group-item-action border-0 rounded-3 mb-1" data-bs-toggle="pill">
                        <i class="fas fa-history me-2"></i> {{ __('profile.history_tab') }}
                    </a>
                    <a href="#tab-password" class="list-group-item list-group-item-action border-0 rounded-3 mb-1" data-bs-toggle="pill">
                        <i class="fas fa-key me-2"></i> {{ __('profile.password_tab') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="col-lg-8">
            <div class="tab-content">
                {{-- Tab: Information --}}
                <div class="tab-pane fade show active" id="tab-info">
                    <div class="ot-card p-4">
                        <h5 class="fw-bold mb-4">{{ __('profile.update_info') }}</h5>
                        <form id="formUpdateProfile">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">{{ __('profile.username') }}</label>
                                    <input type="text" class="form-control bg-light" value="{{ $user->username }}" readonly disabled>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">{{ __('profile.last_name') }}</label>
                                    <input type="text" name="last_name" class="form-control" value="{{ $user->last_name }}" placeholder="Ví dụ: Nguyễn">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold">{{ __('profile.middle_first_name') }}</label>
                                    <input type="text" name="middle_first_name" class="form-control" value="{{ $user->middle_first_name }}" placeholder="Ví dụ: Văn A" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">{{ __('profile.email') }}</label>
                                    <input type="email" name="email" class="form-control" value="{{ $user->email }}" placeholder="example@gmail.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">{{ __('profile.phone') }}</label>
                                    <input type="text" name="phone" class="form-control" value="{{ $user->phone }}" placeholder="09xxxx">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">{{ __('profile.birthday') }}</label>
                                    <input type="date" name="birthday" class="form-control" value="{{ $user->birthday }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">{{ __('profile.gender') }}</label>
                                    <select name="gender" class="form-select">
                                        <option value="0" {{ $user->gender == 0 ? 'selected' : '' }}>{{ __('profile.male') }}</option>
                                        <option value="1" {{ $user->gender == 1 ? 'selected' : '' }}>{{ __('profile.female') }}</option>
                                        <option value="2" {{ $user->gender == 2 ? 'selected' : '' }}>{{ __('profile.other') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">{{ __('profile.location') }}</label>
                                    <select name="province_id" class="form-select">
                                        <option value="">{{ __('profile.select_province') }}</option>
                                        @foreach($provinces as $id => $name)
                                            <option value="{{ $id }}" {{ $user->province_id == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 mt-4 text-end">
                                    <button type="submit" class="btn btn-ot-primary">
                                        <i class="fas fa-save me-1"></i> {{ __('profile.save_changes') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Tab: History --}}
                <div class="tab-pane fade" id="tab-history">
                    <div class="ot-card p-4">
                        <h5 class="fw-bold mb-4">{{ __('profile.history_title') }}</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('profile.exam') }}</th>
                                        <th>{{ __('profile.submit_time') }}</th>
                                        <th class="text-center">{{ __('profile.score') }}</th>
                                        <th class="text-center">{{ __('profile.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sessions as $s)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $s->exam_title }}</div>
                                            <small class="text-muted">{{ strtoupper($s->exam_type) }}</small>
                                        </td>
                                        <td>{{ date('H:i d/m/Y', strtotime($s->submitted_at)) }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-success py-2 px-3 fs-6">{{ $s->total_score }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="/exam/result/{{ $s->id }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                {{ __('profile.view_details') }}
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            {{ __('profile.empty_history') }}
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Tab: Password --}}
                <div class="tab-pane fade" id="tab-password">
                    <div class="ot-card p-4">
                        <h5 class="fw-bold mb-4">{{ __('profile.password_tab') }}</h5>
                        <form id="formChangePassword">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">{{ __('profile.current_password') }}</label>
                                <input type="password" name="old_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">{{ __('profile.new_password') }}</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">{{ __('profile.confirm_password') }}</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-ot-primary">
                                    <i class="fas fa-lock me-1"></i> {{ __('profile.change_password_btn') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
$(document).ready(function() {
    // Cập nhật thông tin
    $('#formUpdateProfile').on('submit', function(e) {
        e.preventDefault();
        const data = $(this).serialize();
        $.post('/api/user/update-profile', data, function(res) {
            if (res.status) {
                toastr.success(res.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                toastr.error(res.message);
            }
        }, 'json');
    });

    // Upload Avatar
    $('#avatar_file').on('change', function() {
        const file = this.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('avatar_file', file);
            
            $.ajax({
                url: '/api/user/upload-avatar',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    if (res.status) {
                        $('#profileAvatarPreview').attr('src', res.url);
                        toastr.success('{{ __('profile.avatar_success') }}');
                    } else {
                        toastr.error(res.message);
                    }
                }
            });
        }
    });

    // Đổi mật khẩu
    $('#formChangePassword').on('submit', function(e) {
        e.preventDefault();
        const data = $(this).serialize();
        $.post('/api/user/change-password', data, function(res) {
            if (res.status) {
                toastr.success(res.message);
                $('#formChangePassword')[0].reset();
            } else {
                toastr.error(res.message);
            }
        }, 'json');
    });
});
</script>
@endsection
