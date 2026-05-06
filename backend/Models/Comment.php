<?php
namespace Models;

class Comment extends Model
{
    static function addComment($user_id, $type, $target_id, $content, $parent_id = null)
    {
        return Model::getDB()->insert('comments', [
            'user_id' => $user_id,
            'type' => $type,
            'target_id' => $target_id,
            'content' => $content,
            'parent_id' => $parent_id,
        ]);
    }

    static function getCommentsPaginated($type, $target_id, $page = 1, $limit = 10)
    {
        $db = Model::getDB()->objectBuilder();
        $db->where('type', $type)
            ->where('target_id', $target_id)
            ->where('parent_id', null, 'IS')
            ->join('users u', 'u.id = comments.user_id', 'LEFT')
            ->orderBy('comments.created_at', 'DESC');
        
        $db->pageLimit = $limit;
        $items = $db->paginate('comments', $page, [
            'comments.*',
            'u.display_name',
            'u.username',
            'u.avatar'
        ]);

        return [
            'items' => $items,
            'paginate' => [
                'total' => $db->totalCount,
                'limit' => $db->pageLimit,
                'total_page' => $db->totalPages,
                'current_page' => $page
            ]
        ];
    }

    static function getComments($type, $target_id, $limit = 50)
    {
        return Model::getDB()->objectBuilder()
            ->where('type', $type)
            ->where('target_id', $target_id)
            ->where('parent_id', null, 'IS')
            ->join('users u', 'u.id = comments.user_id', 'LEFT')
            ->orderBy('comments.created_at', 'DESC')
            ->get('comments', $limit, [
                'comments.*',
                'u.display_name',
                'u.username',
                'u.avatar'
            ]);
    }

    static function getReplies($parent_id)
    {
        return Model::getDB()->objectBuilder()
            ->where('parent_id', $parent_id)
            ->join('users u', 'u.id = comments.user_id', 'LEFT')
            ->orderBy('comments.created_at', 'ASC')
            ->get('comments', null, [
                'comments.*',
                'u.display_name',
                'u.username',
                'u.avatar'
            ]);
    }

    static function countComments($type, $target_id)
    {
        $res = Model::getDB()->where('type', $type)
            ->where('target_id', $target_id)
            ->getOne('comments', 'COUNT(id) as total');
        return $res->total ?? 0;
    }
}
