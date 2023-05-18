<?

namespace Design\Controllers;

use MiladRahimi\PhpRouter\View\View;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use RedBeanPHP\R as R;

class AuthController extends Controller
{

    public function auth(ServerRequest $request, View $view)
    {
        $bodyContents = $request->getParsedBody();

        $login = trim($bodyContents['login']);
        $password = trim($bodyContents['password']);

        $checkUser = R::findOne('users', 'email = ? or username = ?', [$login, $login]);

        if ($checkUser == null)
            return $view->make('auth', ['text' => 'Пользователь с таким email, логином или паролем не найден!']);

        $passwordHash = $checkUser->password;

        $auth = password_verify($password, $passwordHash);

        if ($auth == false)
            return $view->make('auth', ['text' => 'Пользователь с таким email, логином или паролем не найден!']);

        $_SESSION['user_id'] = $checkUser->id;

        return new RedirectResponse('/');
    }

    public function register(ServerRequest $request)
    {
        $bodyContents = $request->getParsedBody();

        $name = trim($bodyContents['name']);
        $username = trim($bodyContents['username']);
        $email = trim($bodyContents['email']);
        $password = trim($bodyContents['password']);


        if (!$this->checkName($name))
            return new JsonResponse(['status' => 'fail', 'text' => 'Неверный формат имени!'], 200);
        if (!$this->checkUsername($username))
            return new JsonResponse(['status' => 'fail', 'text' => 'Неверный формат логина!'], 200);
        if (!$this->checkEmail($email))
            return new JsonResponse(['status' => 'fail', 'text' => 'Неверный формат email!'], 200);
        if (!$this->checkPassword($password))
            return new JsonResponse(['status' => 'fail', 'text' => 'Пароль должен быть больше 7 символов!'], 200);

        $checkUser = R::findOne('users', 'email = ? or username = ?', [$email, $username]);

        if ($checkUser != null)
            return new JsonResponse(['status' => 'fail', 'text' => 'Пользователь с таким email или логином уже есть!'], 200);

        $user = R::dispense('users');
        $user->name = $name;
        $user->username = $username;
        $user->email = $email;
        $user->password = password_hash($password, PASSWORD_DEFAULT);
        $user->created = date('Y-m-d H:i:s');
        R::store($user);

        return new JsonResponse(['status' => 'ok'], 200);
    }

    public function logout()
    {
        $_SESSION['user_id'] = null;
        return new RedirectResponse('/');
    }

    private function checkName($name)
    {
        $length = mb_strlen($name, 'UTF-8');
        if ($length < 2 || $length > 25)
            return false;
        if (!preg_match('/^([а-яА-ЯЁёa-zA-Z_ ]+)$/u', $name))
            return false;
        return true;
    }

    private function checkUsername($name)
    {
        $length = mb_strlen($name, 'UTF-8');
        if ($length < 2 || $length > 25)
            return false;
        if (!preg_match('/^([0-9а-яА-ЯЁёa-zA-Z_ ]+)$/u', $name))
            return false;
        return true;
    }

    private function checkEmail($email)
    {
        if (empty($email))
            return false;
        if (filter_var($email, FILTER_VALIDATE_EMAIL) != $email)
            return false;
        return true;
    }

    private function checkPassword($password)
    {
        $length = mb_strlen($password, 'UTF-8');
        if ($length < 8 || $length > 30)
            return false;
        return true;
    }
}
