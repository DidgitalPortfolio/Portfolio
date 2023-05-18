<?

namespace Design\Controllers;

use MiladRahimi\PhpRouter\View\View;
use RedBeanPHP\R as R;
use RedBeanPHP\Finder as Finder;

class IndexController extends Controller
{

    public function handle(View $view)
    {
        return $view->make('auth');
    }

    public function profile(View $view)
    {
        $userId = $_SESSION['user_id'];

        $user = R::load('users', $userId);

        $userProjects = R::find('projects', 'user_id = ? ORDER BY id DESC', [$user->id]);

        foreach ($userProjects as $userProject) {
            $previewImg = R::findOne('images', 'project_id = ?', [$userProject->id]);
            $userProject->img = $previewImg->filename;
        }

        if ($user->avatar == NULL)
            $user->avatar = '/img/account-new.jpg';
        else
            $user->avatar = '/uploads/avatars/' . $user->avatar;


        return $view->make('profile', ['user' => $user, 'projects' => $userProjects, 'own' => true, 'liked' => false]);
    }

    public function profileUser(View $view, int $id)
    {
        $userId = $_SESSION['user_id'];

        $profileUser = R::load('users', $id);

        if ($profileUser->id == NULL) {
            return 'Пользователя нет';
        }

        ($userId == $profileUser->id) ? $own = true : $own = false;

        $userProjects = R::find('projects', 'user_id = ? ORDER BY id DESC', [$profileUser->id]);

        foreach ($userProjects as $userProject) {
            $previewImg = R::findOne('images', 'project_id = ?', [$userProject->id]);
            $userProject->img = $previewImg->filename;
        }

        if ($profileUser->avatar == NULL)
            $profileUser->avatar = '/img/account-new.jpg';
        else
            $profileUser->avatar = '/uploads/avatars/' . $profileUser->avatar;

        return $view->make('profile', ['user' => $profileUser, 'projects' => $userProjects, 'own' => $own, 'liked' => false]);
    }

    public function main(View $view)
    {
        $allProjects = R::findAll('projects', ' ORDER BY id DESC LIMIT 9');

        foreach ($allProjects as $allProject) {
            $previewImg = R::findOne('images', 'project_id = ?', [$allProject->id]);
            $allProject->img = $previewImg->filename;
            $allProject->user = R::findOne('users', 'id = ?', [$allProject->user_id]);
            if ($allProject->user->avatar == NULL)
                $allProject->user->avatar_min = '/img/account-min.jpg';
            else
                $allProject->user->avatar_min = '/uploads/avatars/' . $allProject->user->avatar;
        }

        return $view->make('main', ['projects' => $allProjects]);
    }

    public function liked(View $view)
    {
        $userId = $_SESSION['user_id'];

        $user = R::load('users', $userId);

        $query = 'SELECT project_id FROM likes WHERE user_id = ?';

        $projectIds = array_map(function ($x) {
            return intval($x['project_id']);
        }, R::getAll($query, [$user->id]));

        if (empty($projectIds)) {
            return $view->make('profile', ['user' => $user, 'projects' => [], 'own' => true, 'liked' => true]);
        }

        $likeProjects = R::find(
            'projects',
            ' id IN (' . R::genSlots($projectIds) . ') ORDER BY id DESC',
            $projectIds
        );

        foreach ($likeProjects as $likeProject) {
            $previewImg = R::findOne('images', 'project_id = ?', [$likeProject->id]);
            $likeProject->img = $previewImg->filename;
        }

        if ($user->avatar == NULL)
            $user->avatar = '/img/account-new.jpg';
        else
            $user->avatar = '/uploads/avatars/' . $user->avatar;

        return $view->make('profile', ['user' => $user, 'projects' => $likeProjects, 'own' => true, 'liked' => true]);
    }

    public function code404()
    {
        return 'Страницы нет';
    }
}
