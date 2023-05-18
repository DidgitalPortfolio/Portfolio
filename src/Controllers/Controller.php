<?
namespace Design\Controllers;

use Design\Db;

abstract class Controller
{
    public function __construct()
    {
        Db::Setup();
    }

    public function __destruct() {
        Db::Close();
    }
}