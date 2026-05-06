<?php
namespace Models;

class Language extends Model
{
    protected static $table = 'languages';

    public static function allActive()
    {
        return self::getDB()->where('status', 1)->get(self::$table);
    }

    public static function getByCode($code)
    {
        return self::getDB()->where('code', $code)->getOne(self::$table);
    }

    public static function getAll()
    {
        return self::getDB()->get(self::$table);
    }

    public static function add($data)
    {
        return self::getDB()->insert(self::$table, $data);
    }

    public static function edit($id, $data)
    {
        return self::getDB()->where('id', $id)->update(self::$table, $data);
    }

    public static function remove($id)
    {
        return self::getDB()->where('id', $id)->delete(self::$table);
    }
}
