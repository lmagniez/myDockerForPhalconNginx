<?php
//namespace Application\Controllers;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Security;

class UsersController extends ControllerBase
{

    public function initialize() 
    {
        parent::initialize(); // call parent initializer too
        
    }


    /**
     * Index action
     */
    public function indexAction()
    {
        $this->persistent->parameters = null;
    }

    public function loginAction() {  

        if ($this->request->isPost()) { 

            //check sql
            parent::checkRequest($this->request);

            if ($this->security->checkToken()) {
                // The token is OK
            
                $user = Users::findFirst(array( 
                    'pseudo = :pseudo:', 'bind' => array( 
                        'pseudo' => $this->request->getPost("pseudo") 
                    ) 
                ));  
                if ($user === false) { 
                    $this->flash->error("Incorrect credentials"); 
                    return $this->dispatcher->forward(array( 
                        'controller' => 'users', 'action' => 'index' 
                    )); 
                    // To protect against timing attacks. Regardless of whether a user
                    // exists or not, the script will take roughly the same amount as
                    // it will always be computing a hash.
                    $this->security->hash(rand());
                }

                $password = $this->request->getPost("password");
                if ($this->security->checkHash($password, $user->password)) {
                    // The password is valid
                    $this->session->set('auth', $user->id);  
                    $this->flash->success("You've been successfully logged in"); 
                    $this->loggerStdout->log('UsersController: user connected (id='.$user->id.')');
                }
                else {
                    $this->flash->error("Incorrect credentials"); 
                }
            }
            else {
                $this->flash->error("Token not OK"); 
            }
        }

        return $this->dispatcher->forward(array( 
           'controller' => 'users', 'action' => 'index' 
        )); 
     }


     public function logoutAction() { 
        $this->session->remove('auth'); 
        $this->loggerStdout->log('UsersController: user logged out');

        return $this->dispatcher->forward(array( 
           'controller' => 'articles', 'action' => 'index' 
        )); 
     } 


    /**
     * Searches for users
     */
    public function searchAction()
    {
        $this->loggerStdout->log('UsersControllers: Search a users');

        $numberPage = 1;
        if ($this->request->isPost()) {
            //check sql
            parent::checkRequest($this->request);
            $query = Criteria::fromInput($this->di, '\Users', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "id";

        $users = Users::find($parameters);
        if (count($users) == 0) {
            $this->loggerStderr->error('did not find any users');
            $this->flash->notice("The search did not find any users");

            $this->dispatcher->forward([
                "controller" => "users",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $users,
            'limit'=> 10,
            'page' => $numberPage
        ]);

        $this->view->page = $paginator->getPaginate();
    }

    /**
     * Displays the creation form
     */
    public function newAction()
    {
        $this->loggerStdout->log('UsersController: display the creation form');


    }

    /**
     * Edits a user
     *
     * @param string $id
     */
    public function editAction($id)
    {
        $this->loggerStdout->log('UsersController: edit a user (id='.$id.')');

        if (!$this->request->isPost()) {

            //check sql
            parent::checkRequest($this->request);

            $user = Users::findFirstByid($id);
            if (!$user) {
                $this->loggerStderr->log('UsersController: edit a user (id='.$id.')');
                $this->flash->error("user was not found");

                $this->dispatcher->forward([
                    'controller' => "users",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->id = $user->id;

            $this->tag->setDefault("id", $user->id);
            $this->tag->setDefault("pseudo", $user->pseudo);
            $this->tag->setDefault("email", $user->email);
            
        }
    }

    /**
     * Creates a new user
     */
    public function createAction()
    {
        $this->loggerStdout->log('UsersController: create a user');


        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "users",
                'action' => 'index'
            ]);

            return;
        }
        if (!$this->security->checkToken()) {
            $this->flash->error("Token not OK"); 
            return;
        }

        //check sql
        parent::checkRequest($this->request);

        $user = new Users();
        //$user->id = 1;
        $user->pseudo = $this->request->getPost("pseudo");
        $user->email = $this->request->getPost("email", "email");
        $password = $this->request->getPost("password");
        $user->password = $this->security->hash($password);
        

        if (!$user->save()) {
            $this->loggerStderr->log('could not create user');

            foreach ($user->getMessages() as $message) {
                $this->flash->error($message."!!");
            }

            $this->dispatcher->forward([
                'controller' => "users",
                'action' => 'new'
            ]);

            return;
        }

        $this->loggerStdout->log('user was created successfully');
        $this->flash->success("user was created successfully");

        $this->dispatcher->forward([
            'controller' => "users",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a user edited
     *
     */
    public function saveAction()
    {
        $this->loggerStdout->log('UsersController: save a user edited');

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "users",
                'action' => 'index'
            ]);

            return;
        }
        if ($this->security->checkToken()) {
            //check sql
            parent::checkRequest($this->request);

            // The token is OK
            $id = $this->request->getPost("id");
            $user = Users::findFirstByid($id);

            if (!$user) {
                $this->flash->error("user does not exist " . $id);
                $this->loggerStderr->error('user does not exist ' . $id);

                $this->dispatcher->forward([
                    'controller' => "users",
                    'action' => 'index'
                ]);

                return;
            }

            $user->id = $this->request->getPost("id");
            $user->pseudo = $this->request->getPost("pseudo");
            $user->email = $this->request->getPost("email", "email");
            
            if (!$user->save()) {

                foreach ($user->getMessages() as $message) {
                    $this->flash->error($message);
                }

                $this->dispatcher->forward([
                    'controller' => "users",
                    'action' => 'edit',
                    'params' => [$user->id]
                ]);

                return;
            }

            $this->loggerStdout->log('user was updated successfully');
            $this->flash->success("user was updated successfully");

            $this->dispatcher->forward([
                'controller' => "users",
                'action' => 'index'
            ]);
        }
        else {
            $this->flash->error("Token not OK"); 
        }
    }

    /**
     * Deletes a user
     *
     * @param string $id
     */
    public function deleteAction($id)
    {
        $this->loggerStdout->log('UserController: delete a user');

        $user = Users::findFirstByid($id);
        if (!$user) {
            $this->flash->error("user was not found");
            $this->loggerStderr->error('user was not found');

            $this->dispatcher->forward([
                'controller' => "users",
                'action' => 'index'
            ]);

            return;
        }

        if (!$user->delete()) {
            $this->loggerStderr->error('could not delete user');

            foreach ($user->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "users",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("user was deleted successfully");
        $this->loggerStdout->log('user was deleted successfully');

        $this->dispatcher->forward([
            'controller' => "users",
            'action' => "index"
        ]);
    }

}
