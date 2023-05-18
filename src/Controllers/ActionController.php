<?

namespace Design\Controllers;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\UploadedFile;
use Laminas\Diactoros\Response\JsonResponse;
use MiladRahimi\PhpRouter\View\View;
use RedBeanPHP\R as R;

class ActionController extends Controller
{

    public function createProject(ServerRequest $request)
    {
        $bodyContents = $request->getParsedBody();

        $userId = $_SESSION['user_id'];
        $user = R::load('users', $userId);

        $name = trim($bodyContents['name']);
        $desc = trim($bodyContents['description']);

        $project = R::dispense('projects');
        $project->name = $name;
        $project->description = $desc;
        $project->user = $user;
        $project->created = date('Y-m-d H:i:s');
        $id = R::store($project);

        return new JsonResponse(['status' => 'ok', 'id' => $id], 200);
    }

    public function imageUpload(ServerRequest $request)
    {
        $bodyContents = $request->getParsedBody();
        $getUploadedFile = $request->getUploadedFiles();

        $directory = __DIR__ . '/../../public_html/uploads';

        $projectId = trim($bodyContents['project_id']);
        $uploadedFile = $getUploadedFile['file'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = $this->moveUploadedFile($directory, $uploadedFile);

            $checkProject = R::load('projects', $projectId);

            if ($checkProject != null) {
                $image = R::dispense('images');
                $image->filename = $filename;
                $image->project = $checkProject;
                $image->created = date('Y-m-d H:i:s');
                R::store($image);
            }

            return new JsonResponse(['status' => 'ok'], 200);
        }


        return new JsonResponse(['status' => 'fail'], 200);
    }

    public function avatarUpload(ServerRequest $request)
    {
        $getUploadedFile = $request->getUploadedFiles();

        $userId = $_SESSION['user_id'];

        $directory = __DIR__ . '/../../public_html/uploads/avatars';

        $uploadedFile = $getUploadedFile['file'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = $this->moveUploadedFile($directory, $uploadedFile);

            $checkUser = R::load('users', $userId);

            if ($checkUser->id != NULL) {
                if ($checkUser->avatar != NULL) {
                    @unlink($directory . '/' . $checkUser->avatar);
                }
                $checkUser->avatar = $filename;
                R::store($checkUser);
            }

            return new JsonResponse(['status' => 'ok', 'avatar' => $filename], 200);
        }


        return new JsonResponse(['status' => 'fail'], 200);
    }

    public function getProfileProjects(View $view)
    {
        $userId = $_SESSION['user_id'];

        $user = R::load('users', $userId);

        $userProjects = R::find('projects', 'user_id = ? ORDER BY id DESC', [$user->id]);

        foreach ($userProjects as $userProject) {
            $previewImg = R::findOne('images', 'project_id = ?', [$userProject->id]);
            $userProject->img = $previewImg->filename;
        }

        return $view->make('part.profile-block-img', ['projects' => $userProjects]);
    }

    public function getProject(View $view, int $id)
    {
        $userId = $_SESSION['user_id'];

        $project = R::load('projects', $id);

        if ($project->id == NULL) {
            return 'Такого проекта не существует!';
        }

        $user = R::load('users', $project->user_id);

        if ($user->avatar == NULL) {
            $user->avatar = '/img/account-normal.jpg';
        } else {
            $user->avatar = '/uploads/avatars/' . $user->avatar;
        }

        $myUser = R::load('users', $userId);

        if ($myUser->avatar == NULL)
            $myUser->avatar_min = '/img/account-min.jpg';
        else
            $myUser->avatar_min = '/uploads/avatars/' . $myUser->avatar;

        $myLike = R::findOne('likes', 'project_id = ? AND user_id = ?', [$project->id, $myUser->id]);

        if ($myLike->id == NULL) {
            $myUser->like = '/img/icon-like.png';
        } else {
            $myUser->like = '/img/icon-like-yes.png';
        }

        $images = R::findAll('images', 'project_id = ?', [$project->id]);

        foreach ($images as $image) {
            $project->img = $image->filename;
            break;
        }

        $comments = R::findAll('comments', 'project_id = ?', [$project->id]);

        foreach ($comments as $comment) {
            $userComment = R::load('users', $comment->user_id);
            $comment->name = $userComment->name;
            if ($userComment->avatar == NULL)
                $comment->avatar = '/img/account-min.jpg';
            else
                $comment->avatar = '/uploads/avatars/' . $userComment->avatar;
        }

        return $view->make('project', ['project' => $project, 'images' => $images, 'user' => $user, 'myuser' => $myUser, 'comments' => $comments]);
    }

    public function createComment(ServerRequest $request)
    {
        $bodyContents = $request->getParsedBody();

        $userId = $_SESSION['user_id'];
        $user = R::load('users', $userId);

        $commentText = trim($bodyContents['comment']);
        $projectId = trim($bodyContents['project_id']);

        $project = R::load('projects', $projectId);

        if ($project->id == NULL) {
            return new JsonResponse(['status' => 'fail'], 200);
        }

        if ($user->avatar == NULL)
            $avatar = '/img/account-min.jpg';
        else
            $avatar = '/uploads/avatars/' . $user->avatar;


        $comment = R::dispense('comments');
        $comment->text = $commentText;
        $comment->user = $user;
        $comment->project = $project;
        $comment->created = date('Y-m-d H:i:s');

        R::store($comment);

        return new JsonResponse(['status' => 'ok', 'author' => $user->name, 'comment' => $commentText, 'avatar' => $avatar], 200);
    }

    public function createLike(ServerRequest $request)
    {
        $bodyContents = $request->getParsedBody();

        $userId = $_SESSION['user_id'];
        $user = R::load('users', $userId);

        $projectId = trim($bodyContents['project_id']);

        $project = R::load('projects', $projectId);

        if ($project->id == NULL) {
            return new JsonResponse(['status' => 'fail'], 200);
        }

        $checkLike = R::findOne('likes', 'project_id = ? AND user_id = ?', [$project->id, $user->id]);

        if ($checkLike->id == NULL) {
            $like = R::dispense('likes');
            $like->user = $user;
            $like->project = $project;
            $like->created = date('Y-m-d H:i:s');
            R::store($like);
            return new JsonResponse(['status' => 'ok', 'like' => 1], 200);
        }

        R::trash($checkLike);
        return new JsonResponse(['status' => 'ok', 'like' => 0], 200);
    }

    public function updateSocials(ServerRequest $request)
    {
        $bodyContents = $request->getParsedBody();

        $userId = $_SESSION['user_id'];
        $user = R::load('users', $userId);

        $vk = trim($bodyContents['vk']);
        $tg = trim($bodyContents['tg']);

        if (!empty($vk))
            $user->vk = $vk;
        if (!empty($tg))
            $user->tg = $tg;

        R::store($user);

        return new JsonResponse(['status' => 'ok'], 200);
    }

    private function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8));
        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }
}
