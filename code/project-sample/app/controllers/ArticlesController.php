<?php
//namespace Application\Controllers;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Logger\Adapter\Stream as StreamAdapter;

class ArticlesController extends ControllerBase
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
     * Searches for articles
     */
    public function searchAction()
    {
        $this->loggerStdout->log('ArticlesController: Search for Article');

        $numberPage = 1;
        if ($this->request->isPost()) {
            //check sql
            parent::checkRequest($this->request);

            $query = Criteria::fromInput($this->di, '\Articles', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }

        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "id";

        $articles = Articles::find($parameters);
        if (count($articles) == 0) {
            $this->loggerStdout->log('Did not find any Article');
            $this->flash->notice("The search did not find any articles");

            $this->dispatcher->forward([
                "controller" => "articles",
                "action" => "index"
            ]);

            return;
        }

        $paginator = new Paginator([
            'data' => $articles,
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
        $this->loggerStdout->log('ArticlesController: Create Article');
    }

    /**
     * Edits a article
     *
     * @param string $id
     */
    public function editAction($id)
    {
        $this->loggerStdout->log('ArticlesController: Edit Article (id='.$id.')');

        if (!$this->request->isPost()) {
            //check sql
            parent::checkRequest($this->request);

            $article = Articles::findFirstByid($id);
            if (!$article) {

                $this->flash->error("article was not found");

                $this->dispatcher->forward([
                    'controller' => "articles",
                    'action' => 'index'
                ]);

                return;
            }

            $this->view->id = $article->id;

            $this->tag->setDefault("id", $article->id);
            $this->tag->setDefault("titre", $article->titre);
            $this->tag->setDefault("contenu", $article->contenu);
            $this->tag->setDefault("date_publication", $article->date_publication);
            $this->tag->setDefault("tagId", $article->tagId);
            $this->tag->setDefault("userId", $article->userId);
            
        }
    }

    /**
     * Creates a new article
     */
    public function createAction()
    {
        $this->loggerStdout->log('ArticlesController: Create Article');

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "articles",
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

        $article = new Articles();
        $article->titre = $this->request->getPost("titre");
        $article->contenu = $this->request->getPost("contenu");
        $article->datePublication = $this->request->getPost("date_publication");
        $article->tagId = $this->request->getPost("tagId");
        $article->userId = $this->request->getPost("userId");
        

        if (!$article->save()) {
            $this->loggerStderr->error('Could not create Article');

            foreach ($article->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "articles",
                'action' => 'new'
            ]);

            return;
        }

        $this->flash->success("article was created successfully");
        $this->loggerStdout->log('Article was created successfully');

        $this->dispatcher->forward([
            'controller' => "articles",
            'action' => 'index'
        ]);
    }

    /**
     * Saves a article edited
     *
     */
    public function saveAction()
    {

        $this->loggerStdout->log('ArticleController: Save and edited article');

        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "articles",
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
        $article = Articles::findFirstByid($id);

        if (!$article) {
            $this->flash->error("article does not exist " . $id);
            $this->loggerStderr->error('Article does not exists (id='.$id.')');

            $this->dispatcher->forward([
                'controller' => "articles",
                'action' => 'index'
            ]);

            return;
        }

        $article->id = $this->request->getPost("id");
        $article->titre = $this->request->getPost("titre");
        $article->contenu = $this->request->getPost("contenu");
        $article->datePublication = $this->request->getPost("date_publication");
        $article->tagId = $this->request->getPost("tagId");
        $article->userId = $this->request->getPost("userId");
        

        if (!$article->save()) {

            foreach ($article->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "articles",
                'action' => 'edit',
                'params' => [$article->id]
            ]);

            return;
        }

        $this->loggerStdout->log('article was updated successfully (id='.$id.')');
        $this->flash->success("article was updated successfully");

        $this->dispatcher->forward([
            'controller' => "articles",
            'action' => 'index'
        ]);
    }

    /**
     * Deletes a article
     *
     * @param string $id
     */
    public function deleteAction($id)
    {
        $this->loggerStdout->log('ArticleController: Delete Action (id='.$id.')');

        $article = Articles::findFirstByid($id);
        if (!$article) {
            $this->loggerStderr->error('article was not found');
            $this->flash->error("article was not found");

            $this->dispatcher->forward([
                'controller' => "articles",
                'action' => 'index'
            ]);

            return;
        }

        if (!$article->delete()) {

            $this->loggerStderr->error('could not delete article');
            foreach ($article->getMessages() as $message) {
                $this->flash->error($message);
            }

            $this->dispatcher->forward([
                'controller' => "articles",
                'action' => 'search'
            ]);

            return;
        }

        $this->loggerStdout->log('article was deleted successfully');
        $this->flash->success("article was deleted successfully");

        $this->dispatcher->forward([
            'controller' => "articles",
            'action' => "index"
        ]);
    }

}
