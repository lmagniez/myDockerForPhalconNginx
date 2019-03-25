<?php
//namespace Application\Controllers;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Logger\Adapter\Stream as StreamAdapter;

class TagsController extends ControllerBase
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
        $this->loggerStdout->log('Accessed in Index of Tags Controller');
        $this->persistent->parameters = null;
    }

    /**
     * Searches for tags
     */
    public function searchAction()
    {
        $this->loggerStdout->log('Tag searchAction: Search for a tag');
        
        $numberPage = 1;
        if ($this->request->isPost()) {
            //check sql
            parent::checkRequest($this->request);

            $query = Criteria::fromInput($this->di, '\Tags', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "id";

        $tags = Tags::find($parameters);
        if (count($tags) == 0) {
            $this->flash->notice("The search did not find any tags");
            $this->loggerStdout->log('Did not found any Tag');

            $this->dispatcher->forward([
                "controller" => "tags",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $tags,
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
        $this->loggerStdout->log('Accessed New Action in Tags Controller');
    }

    /**
     * Edits a tag
     *
     * @param string $id
     */
    public function editAction($id)
    {
        $this->loggerStdout->log('Tag editAction: Edit a Tag (id='.$id.')');

        if (!$this->request->isPost()) {
            //check sql
            parent::checkRequest($this->request);

            $tag = Tags::findFirstByid($id);
            if (!$tag) {
                $this->flash->error("tag was not found");
                $this->loggerStderr->error('Could not find Tag');

                $this->dispatcher->forward([
                    'controller' => "tags",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->id = $tag->id;

            $this->tag->setDefault("id", $tag->id);
            $this->tag->setDefault("libelle", $tag->libelle);
            
        }
    }

    /**
     * Creates a new tag
     */
    public function createAction()
    {
        $this->loggerStdout->log('Tag createAction: Create a new Tag');

        if (!$this->request->isPost()) {
            $this->loggerStdout->log('Not post request: To indexAction');

            $this->dispatcher->forward([
                'controller' => "tags",
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

        $tag = new Tags();
        $tag->libelle = $this->request->getPost("libelle");

        if (!$tag->save()) {
            $this->loggerStderr->error('Could not save Tag');

            foreach ($tag->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "tags",
                'action' => 'new'
            ]);

            return;
        }

        $this->loggerStdout->log('Tag was created successfully');
        $this->flash->success("tag was created successfully");

        $this->dispatcher->forward([
            'controller' => "tags",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a tag edited
     *
     */
    public function saveAction()
    {

        $this->loggerStdout->log('Tag saveAction: Attempt to save an edited Tag');
        
        if (!$this->request->isPost()) {
            $this->loggerStdout->log('Request is not POST, forward to IndexAction');

            $this->dispatcher->forward([
                'controller' => "tags",
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
        $tag = Tags::findFirstByid($id);

        if (!$tag) {
            $this->flash->error("tag does not exist (id=" . $id . ")");
            $this->loggerStderr->error('Tag does not exist '.$id);

            $this->dispatcher->forward([
                'controller' => "tags",
                'action' => 'index'
            ]);

            return;
        }

        $tag->id = $this->request->getPost("id");
        $tag->libelle = $this->request->getPost("libelle");

        if (!$tag->save()) {
            $this->loggerStderr->error('Could not save Tag');

            foreach ($tag->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "tags",
                'action' => 'edit',
                'params' => [$tag->id]
            ]);

            return;
        }

        $this->loggerStdout->log('Tag was updated successfully');
        $this->flash->success("tag was updated successfully");

        $this->dispatcher->forward([
            'controller' => "tags",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a tag
     *
     * @param string $id
     */
    public function deleteAction($id)
    {

        $this->loggerStdout->log('Tag deleteAction: Delete a Tag (id='.$id.')');

        $tag = Tags::findFirstByid($id);
        if (!$tag) {
            $this->flash->error("tag was not found");

            $this->dispatcher->forward([
                'controller' => "tags",
                'action' => 'index'
            ]);

            return;
        }

        if (!$tag->delete()) {

            foreach ($tag->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "tags",
                'action' => 'search'
            ]);

            return;
        }

        $this->flash->success("Tag was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "tags",
            'action' => "index"
        ]);
    }

}
