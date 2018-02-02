<?php

namespace app\commands;

use app\constants\Errors;
use app\constants\Roles;
use app\models\DevicePlatform;
use app\models\User;
use app\models\Token;
use Yii;
use yii\console\Controller;
use yii\db\Expression;
use yii\db\IntegrityException;
use yii\rbac\Role;
use yii\web\NotFoundHttpException;

class RbacController extends Controller
{

    /**
     * Init permissions
     */
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        // Create default user role

        $user = $auth->createRole(Roles::USER);
        $user->description = 'Free user';
        $auth->add($user);


        // Create free user role

        $free = $auth->createRole(Roles::FREE);
        $free->description = 'Free user';
        $auth->add($free);

        // Create herbs role

        $herbs = $auth->createRole(Roles::HERBS);
        $herbs->description = 'Herbs';
        $auth->add($herbs);

        // Create notes role

        $notes = $auth->createRole(Roles::NOTES);
        $notes->description = 'Notes';
        $auth->add($notes);

        // Create formulas role

        $formulas = $auth->createRole(Roles::FORMULAS);
        $formulas->description = 'Formulas';
        $auth->add($formulas);

        // Create acupuncture role

        $acupuncture  = $auth->createRole(Roles::ACUPUNCTURE);
        $acupuncture ->description = 'Acupuncture';
        $auth->add($acupuncture);

        // Create administrator

        $administrator = $auth->createRole(Roles::ADMINISTRATOR);
        $administrator->description = 'System administrator';
        $auth->add($administrator);

        $editor = $auth->createRole(Roles::EDITOR);
        $editor->description = 'Editor';
        $auth->add($editor);

        $moderator = $auth->createRole(Roles::MODERATOR);
        $moderator->description = 'Moderator';
        $auth->add($moderator);

        // Permissions

        // User

        // Login

        $login = $auth->createPermission('login');
        $login->description = 'Login to API';
        $auth->add($login);

        // Books

        $createBook = $auth->createPermission('createBook');
        $createBook->description = 'Create a book';
        $auth->add($createBook);

        $updateBook = $auth->createPermission('updateBook');
        $updateBook->description = 'Update book';
        $auth->add($updateBook);

        $deleteBook = $auth->createPermission('deleteBook');
        $deleteBook->description = 'Delete book';
        $auth->add($deleteBook);

        $getBooks = $auth->createPermission('getBooks');
        $getBooks->description = 'Get books';
        $auth->add($getBooks);

        $searchBooks = $auth->createPermission('searchBooks');
        $searchBooks->description = 'Search books';
        $auth->add($searchBooks);

        // Herbs

        $createHerb = $auth->createPermission('createHerb');
        $createHerb->description = 'Create a herb';
        $auth->add($createHerb);

        $updateHerb = $auth->createPermission('updateHerb');
        $updateHerb->description = 'Update herb';
        $auth->add($updateHerb);

        $deleteHerb = $auth->createPermission('deleteHerb');
        $deleteHerb->description = 'Delete herb';
        $auth->add($deleteHerb);

        $getHerbs = $auth->createPermission('getHerbs');
        $getHerbs->description = 'Get herbs';
        $auth->add($getHerbs);

        $searchHerbs = $auth->createPermission('searchHerbs');
        $searchHerbs->description = 'Search herbs';
        $auth->add($searchHerbs);

        // Formulas

        $createFormula = $auth->createPermission('createFormula');
        $createFormula->description = 'Create a formula';
        $auth->add($createFormula);

        $updateFormula = $auth->createPermission('updateFormula');
        $updateFormula->description = 'Update formula';
        $auth->add($updateFormula);

        $deleteFormula = $auth->createPermission('deleteFormula');
        $deleteFormula->description = 'Delete formula';
        $auth->add($deleteFormula);

        $getFormulas = $auth->createPermission('getFormulas');
        $getFormulas->description = 'Get formulas';
        $auth->add($getFormulas);

        $searchFormulas = $auth->createPermission('searchFormulas');
        $searchFormulas->description = 'Search formulas';
        $auth->add($searchFormulas);

        $auth->addChild($administrator, $createBook);
        $auth->addChild($administrator, $updateBook);
        $auth->addChild($administrator, $deleteBook);
        $auth->addChild($administrator, $searchBooks);

        $auth->addChild($administrator, $user);
    }

    /**
     * Clean up current permissions
     */
    public function actionClean()
    {
        \app\models\auth\Item::deleteAll();
    }

    /**
     * Load default rules
     */
    public function actionRules()
    {
        $auth = Yii::$app->authManager;


        $rule = new \app\rbac\BookAuthorRule;

        $auth->add($rule);

        $updateOwnBook = $auth->createPermission('updateOwnBook');
        $updateOwnBook->description = 'Update own book';
        $updateOwnBook->ruleName = $rule->name;
        $auth->add($updateOwnBook);

        $updateBook = $auth->getPermission('updateBook');

        $auth->addChild($updateOwnBook, $updateBook);

        $user = $auth->getRole(Roles::USER);

        $auth->addChild($user, $updateOwnBook);


        $rule = new \app\rbac\HerbAuthorRule;

        $auth->add($rule);

        $updateOwnHerb = $auth->createPermission('updateOwnHerb');
        $updateOwnHerb->description = 'Update own herb';
        $updateOwnHerb->ruleName = $rule->name;
        $auth->add($updateOwnHerb);

        $updateHerb = $auth->getPermission('updateHerb');

        $auth->addChild($updateOwnHerb, $updateHerb);

        $herbs = $auth->getRole(Roles::HERBS);

        $auth->addChild($herbs, $updateOwnHerb);


        $rule = new \app\rbac\FormulaAuthorRule;

        $auth->add($rule);

        $updateOwnFormula = $auth->createPermission('updateOwnFormula');
        $updateOwnFormula->description = 'Update own formula';
        $updateOwnFormula->ruleName = $rule->name;
        $auth->add($updateOwnFormula);

        $updateFormula = $auth->getPermission('updateFormula');

        $auth->addChild($updateOwnFormula, $updateFormula);

        $formulas = $auth->getRole(Roles::FORMULAS);

        $auth->addChild($formulas, $updateOwnFormula);
    }

    /**
     * Assign specified role to user
     *
     * @param integer|string $userId
     * @param string $roleName
     * @param string $token
     *
     * @throws IntegrityException
     * @throws NotFoundHttpException
     */
    public function actionAssignRole($userId, $roleName, $token = '')
    {
        $auth = Yii::$app->authManager;
        $user = null;

        if(is_numeric($userId))
            $user = User::findOne(['id' => $userId]);
        else
            $user = User::findOne(['email' => $userId]);

        if(!$user)
            throw new NotFoundHttpException('User not found', Errors::USER_NOT_FOUND);

        $role = $auth->getRole($roleName);

        if(!$role)
            throw new NotFoundHttpException('Role not found', Errors::USER_ROLE_NOT_FOUND);

        if($user->hasRole($roleName))
            throw new IntegrityException('Role already assigned', Errors::USER_ROLE_ALREADY_ASSIGNED);

        $auth->assign($role, $user->id);

        if($token != '') {

            $userToken = Token::findOne(['user_id' => $user->id, 'access_token' => $token]);

            if(!$userToken) {

                $userToken = new Token();

                $userToken->access_token = $token;
                $userToken->platform_id = DevicePlatform::PLATFORM_WEB;
                $userToken->created_by = $user->id;
                $userToken->user_id = $user->id;
                $userToken->created_time = new Expression('NOW()');
                $userToken->timeout = strtotime('+1 year');

                $userToken->save();

            }
        }

        echo sprintf('Role %s assigned to user %d', $roleName, $user->id), PHP_EOL;

    }

    /**
     * Remove specific role from user
     *
     * @param $userId
     * @param $roleName
     *
     * @throws IntegrityException
     * @throws NotFoundHttpException
     */
    public function actionRevokeRole($userId, $roleName)
    {
        $auth = Yii::$app->authManager;
        $user = null;

        if(is_numeric($userId))
            $user = User::findOne(['id' => $userId]);
        else
            $user = User::findOne(['email' => $userId]);

        if(!$user)
            throw new NotFoundHttpException('User not found', Errors::USER_NOT_FOUND);

        $role = $auth->getRole($roleName);

        if(!$role)
            throw new NotFoundHttpException('Role not found', Errors::USER_ROLE_NOT_FOUND);

        if(!$user->hasRole($roleName))
            throw new IntegrityException('Role not assigned', Errors::USER_ROLE_NOT_ASSIGNED);

        $auth->revoke($role, $user->id);

        echo sprintf('Role %s revoked from user %d', $roleName, $user->id), PHP_EOL;
    }

    /**
     * Remove all roles from user
     *
     * @param $userId
     *
     * @throws NotFoundHttpException
     */
    public function actionRevokeAll($userId)
    {
        $auth = Yii::$app->authManager;
        $user = null;

        if(is_numeric($userId))
            $user = User::findOne(['id' => $userId]);
        else
            $user = User::findOne(['email' => $userId]);

        if(!$user)
            throw new NotFoundHttpException('User not found', Errors::USER_NOT_FOUND);

        $auth->revokeAll($user->id);

        echo sprintf('All roles revoked from user %d', $user->id), PHP_EOL;
    }

    /**
     * Show all user roles
     *
     * @param $userId
     *
     * @throws NotFoundHttpException
     */
    public function actionListRoles($userId)
    {
        $auth = Yii::$app->authManager;
        $user = null;

        if(is_numeric($userId))
            $user = User::findOne(['id' => $userId]);
        else
            $user = User::findOne(['email' => $userId]);

        if(!$user)
            throw new NotFoundHttpException('User not found', Errors::USER_NOT_FOUND);

        $roles = $auth->getRolesByUser($user->id);

        echo 'List user roles:', PHP_EOL;

        print_r($roles);

        echo PHP_EOL;
    }
}
