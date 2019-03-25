<?php
//namespace Application\Controllers;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Logger\Adapter\Stream as StreamAdapter;

class CommentsController extends ControllerBase
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

    /**
     * Searches for comments
     */
    public function searchAction()
    {
        $this->loggerStdout->log('CommentsController: Search for a Comment');
        $numberPage = 1;
        if ($this->request->isPost()) {
            //check sql
            parent::checkRequest($this->request);

            $query = Criteria::fromInput($this->di, '\Comments', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "id";

        $comments = Comments::find($parameters);
        if (count($comments) == 0) {
            $this->loggerStdout->log('did not find any comments');
            $this->flash->notice("The search did not find any comments");

            $this->dispatcher->forward([
                "controller" => "comments",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $comments,
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
        $this->loggerStdout->log('CommentsController: Show the creation form');
    }

    /**
     * Edits a comment
     *
     * @param string $id
     */
    public function editAction($id)
    {
        $this->loggerStdout->log('CommentsController: Edit a comment');
        if (!$this->request->isPost()) {
            //check sql
            parent::checkRequest($this->request);


            $comment = Comments::findFirstByid($id);
            if (!$comment) {
                $this->loggerStderr->error('could not find comment (id='.$id.')');
                $this->flash->error("comment was not found");

                $this->dispatcher->forward([
                    'controller' => "comments",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->id = $comment->id;

            $this->tag->setDefault("id", $comment->id);
            $this->tag->setDefault("content", $comment->content);
            $this->tag->setDefault("date_publication", $comment->date_publication);
            $this->tag->setDefault("articleId", $comment->articleId);
            $this->tag->setDefault("userId", $comment->userId);
            
        }
    }

    /**
     * Creates a new comment
     */
    public function createAction()
    {
        $this->loggerStdout->log('CommentsController: Create a comment');

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "comments",
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

        $comment = new Comments();
        $comment->content = $this->request->getPost("content");
        $comment->datePublication = $this->request->getPost("date_publication");
        $comment->articleId = $this->request->getPost("articleId");
        $comment->userId = $this->request->getPost("userId");
        

        if (!$comment->save()) {
            $this->loggerStdout->log('CommentsController: Could not save comment');

            foreach ($comment->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "comments",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("comment was created successfully");
        $this->loggerStdout->log('comment was created successfully');


        $this->dispatcher->forward([
            'controller' => "comments",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a comment edited
     *
     */
    public function saveAction()
    {

        $this->loggerStdout->log('CommentsController: Save edited comment');

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "comments",
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

        $id = $this->request->getPost("id");
        $comment = Comments::findFirstByid($id);

        if (!$comment) {
            $this->loggerStderr->error("comment does not exist (id=" . $id . ")");
            $this->flash->error("comment does not exist " . $id);

            $this->dispatcher->forward([
                'controller' => "comments",
                'action' => 'index'
            ]);

            return;
        }

        $comment->id = $this->request->getPost("id");
        $comment->content = $this->request->getPost("content");
        $comment->datePublication = $this->request->getPost("date_publication");
        $comment->articleId = $this->request->getPost("articleId");
        $comment->userId = $this->request->getPost("userId");
        

        if (!$comment->save()) {
            $this->loggerStderr->error("could not save comment (id=" . $id . ")");

            foreach ($comment->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "comments",
                'action' => 'edit',
                'params' => [$comment->id]
            ]);

            return;
        }

        $this->loggerStdout->log('comment was updated successfully');
        $this->flash->success("comment was updated successfully");
        
        $this->dispatcher->forward([
            'controller' => "comments",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a comment
     *
     * @param string $id
     */
    public function deleteAction($id)
    {
        $this->loggerStdout->log('CommentsController: Delete a comment (id='.$id.')');

        $comment = Comments::findFirstByid($id);
        if (!$comment) {
            $this->loggerStderr->error('comment was not found (id='.$id.')');
            $this->flash->error("comment was not found");

            $this->dispatcher->forward([
                'controller' => "comments",
                'action' => 'index'
            ]);

            return;
        }

        if (!$comment->delete()) {

            foreach ($comment->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "comments",
                'action' => 'search'
            ]);

            return;
        }

        $this->loggerStdout->log('comment was deleted successfully');
        $this->flash->success("comment was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "comments",
            'action' => "index"
        ]);
    }

}
