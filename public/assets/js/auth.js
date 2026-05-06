$(document).ready(function() {
    // Login form logic
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnLogin');
        var $error = $('#loginError');
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');
        $error.addClass('d-none');

        $.ajax({
            url: '/login',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    window.location.href = res.redirect || '/';
                } else {
                    $error.removeClass('d-none').text(res.message || 'Sai tài khoản hoặc mật khẩu');
                    $btn.prop('disabled', false).html('<i class="fas fa-sign-in-alt"></i> Đăng nhập');
                }
            },
            error: function() {
                $error.removeClass('d-none').text('Lỗi hệ thống, vui lòng thử lại sau!');
                $btn.prop('disabled', false).html('<i class="fas fa-sign-in-alt"></i> Đăng nhập');
            }
        });
    });

    // Register form logic
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#btnRegister');
        var $error = $('#regError');
        var $success = $('#regSuccess');
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');
        $error.addClass('d-none');
        $success.addClass('d-none');

        $.ajax({
            url: '/register',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    $success.removeClass('d-none').text(res.message || 'Đăng ký thành công!');
                    setTimeout(() => window.location.href = '/login', 1500);
                } else {
                    $error.removeClass('d-none').text(res.message || 'Đăng ký thất bại');
                    $btn.prop('disabled', false).html('<i class="fas fa-user-plus"></i> Đăng ký');
                }
            },
            error: function() {
                $error.removeClass('d-none').text('Lỗi hệ thống, vui lòng thử lại sau!');
                $btn.prop('disabled', false).html('<i class="fas fa-user-plus"></i> Đăng ký');
            }
        });
    });
});
