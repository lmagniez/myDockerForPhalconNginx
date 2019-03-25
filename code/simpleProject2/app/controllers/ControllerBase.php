<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Logger\Adapter\Stream as StreamAdapter;

class ControllerBase extends Controller
{

    public $loggerStdout;
    public $loggerStderr;

    public function initialize() 
    {
        $this->loggerStderr = new StreamAdapter('php://stderr');
        $this->loggerStdout = new StreamAdapter('php://stdout');
    }

    /**
     * Execute before the router so we can determine if this is a private controller, and must be authenticated, or a
     * public controller that is open to all.
     *
     * @param Dispatcher $dispatcher
     * @return boolean
     */
    public function beforeExecuteRoute(Dispatcher $dispatcher)
    {

        $actionName = $dispatcher->getActionName();
        $controllerName = $dispatcher->getControllerName();
        
        
        if($controllerName != 'users') {
            if (!$this->session->has('auth'))
            {
                $this->flash->notice('You must be connected to access this module!');

                $dispatcher->forward([
                    'controller' => 'users',
                    'action' => 'index'
                ]);
                return false;
            }
        }

    }

    public function checkRequest($request) {
        foreach ($request->getPost() as $v) {
            if($this->detect_SQLInjection($v)) {
                $this->generateLogForSQLInjection($this->dispatcher->getControllerName().":".$this->dispatcher->getActionName(), $v);
            }
        }
    }

    /**
     * Simple detection of SQL keywords
     * @param Dispatcher $dispatcher
     * @return boolean
     */
    public function detect_SQLInjection($strToCheck) {

        $sql_keyword = ["insert", "delete", "drop", "update", "into", "'", "=", ";", "\"", "where", ";", "union", "or", "and", "--", "all", "select"];
        $strToCheck = strtolower($strToCheck);

        // Particular case
        if ((strpos($strToCheck, "select") !== false) && (strpos($strToCheck, "from") !== false)) {
            return true;
        }
        if ((strpos($strToCheck, "delete") !== false) && (strpos($strToCheck, "from") !== false)) {
            return true;
        }
        if ((strpos($strToCheck, "insert") !== false) && (strpos($strToCheck, "into") !== false)) {
            return true;
        }
        if ((strpos($strToCheck, "update") !== false) && (strpos($strToCheck, "set") !== false)) {
            return true;
        }
        if ((strpos($strToCheck, "create") !== false) && (strpos($strToCheck, "table") !== false)) {
            return true;
        }

        foreach($sql_keyword as $keyword) {
            if (strpos($strToCheck, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    public function generateLogForSQLInjection($controllerAction, $keyword) {

        $this->loggerStdout->log("[".$controllerAction."] SQL Injection Attempt Detected! ".$keyword." detected.");

    }



}
