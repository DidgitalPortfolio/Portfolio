<?
namespace Design;

use RedBeanPHP\R as R;

class Db
{
    public static $CONNECTED = false;

    public static function Setup()
    {
        if(!self::$CONNECTED){
            R::setup('mysql:host=localhost;dbname=kzt345e5_design', 'kzt345e5_design', 'PbBUY&U8');
            if (!R::testConnection()) die('Нет подключения к бд');
            R::debug( FALSE );
            self::$CONNECTED = true;
        }
    }

    public static function Close()
    {
        R::Close();
        self::$CONNECTED = false;
    }
}