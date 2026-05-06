<?php
namespace Controllers;
use \Google\Client as Google_Client;
use Google\Model;
use \Models\User as UserModel;

use Services\Blade;

class Api
{
    function online() : string
    {
        $ip = get_ip_address() ?? "::1";


        $user = UserModel::getDB()->objectBuilder()->where('ip', $ip)->getOne("online");
        if ($user) {
            if ($user->online == 0) {
                UserModel::getDB()->where('ip', $ip)->update("online", [
                    'online' => 1,
                    'first_seen' => date('Y-m-d H:i:s')
                ]);
            } else {
                UserModel::getDB()->where('ip', $ip)->update("online", ['last_seen' => date('Y-m-d H:i:s')]);
            }
        } else {
            UserModel::getDB()->objectBuilder()->insert("online", ['ip' => $ip,]);
        }


        return UserModel::getDB()->where('online', 1)->getValue("online", "COUNT(*)");
    }

    function addCommentApi()
    {
        if (!is_login()) {
            response()->json(['status' => false, 'message' => 'Bạn cần đăng nhập để bình luận!']);
            return;
        }

        $type = input()->value('type');
        $targetId = (int)input()->value('target_id');
        $content = trim(input()->value('content'));
        $parentId = input()->value('parent_id') ? (int)input()->value('parent_id') : null;

        if (empty($content)) {
            response()->json(['status' => false, 'message' => 'Nội dung bình luận không được để trống!']);
            return;
        }

        if (!in_array($type, ['exam', 'course', 'document'])) {
            response()->json(['status' => false, 'message' => 'Loại bình luận không hợp lệ!']);
            return;
        }

        $res = \Models\Comment::addComment(userget()->id, $type, $targetId, $content, $parentId);
        
        if ($res) {
            response()->json(['status' => true, 'message' => 'Đã đăng bình luận!']);
        } else {
            response()->json(['status' => false, 'message' => 'Lỗi không xác định!']);
        }
    }

    function listCommentsApi()
    {
        $type = input()->value('type');
        $targetId = (int)input()->value('target_id');
        $page = (int)input()->value('page', 1);

        $data = \Models\Comment::getCommentsPaginated($type, $targetId, $page);
        
        $html = (new Blade())->render('user.components.comment-items', [
            'comments' => $data['items'],
            'type' => $type,
            'target_id' => $targetId
        ]);
        response()->json([
            'html' => $html,
            'paginate' => $data['paginate']
        ]);
    }

    function paymentCallback()
    {
        // Placeholder for callback from payment gateway
        // amount, fee, transaction_id, user_id, vip_level, months, status...
        $status = input()->value('status');
        if ($status === 'success') {
            // Update user VIP
        }
        response()->json(['status' => true]);
    }
}