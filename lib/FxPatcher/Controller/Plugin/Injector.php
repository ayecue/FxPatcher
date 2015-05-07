<?php

namespace FxPatcher\Controller\Plugin;

use Pimcore\Tool as PimcoreTool;
use FxPatcher\Script;
use FxPatcher\Resolver;

class Injector extends \Zend_Controller_Plugin_Abstract {
    const PATCHER_SCRIPT = "/plugins/FxPatcher/static/js/patcher.js";

    private $script;
    private $resolver;
    private $configuration;

    function __construct($configuration) {
        $this->configuration = $configuration;
        $this->script = new Script();
        $this->resolver = new Resolver();
        $this->resolver->add("/website/" . $configuration->fxPatcherPath);
        include_once("simple_html_dom.php");
    }

    public function getState(){
        $resolveState = -1;
        $request = $this->getRequest();

        if (isset($_COOKIE["pimcore_admin_sid"])) {
            $resolveState = Resolver::STATE_ADMIN;
        }

        if ($request->getParam('pimcore_editmode') && $request->getParam('module') == 'website') {
            $resolveState = Resolver::STATE_DOCUMENT;
        }

        return $resolveState;
    }

    public function inject($html,$path,$state){
        if ($state === Resolver::STATE_ADMIN) {
            $this->script->addJavascript($html,$path,"body");
        } else {
            $this->script->addJavascript($html,$path);
        }
    }

    public function run($state){
        $body = $this->getResponse()->getBody();
        $html = str_get_html($body);
        $patcher = $this->resolver->resolve($state);
        $this->inject($html,self::PATCHER_SCRIPT,$state);

        foreach ($patcher as $path) {
            $this->inject($html,$path,$state);
        }

        $body = $html->save();

        $html->clear();
        unset($html);

        $this->getResponse()->setBody($body);
    }

    public function dispatchLoopShutdown() {
        if(!PimcoreTool::isHtmlResponse($this->getResponse())) {
            return;
        }

        $resolveState = $this->getState();

        if ($resolveState !== Resolver::STATE_DOCUMENT) {
            return;
        }

        $this->run($resolveState);
    }

    public function postDispatch(){
        if(!PimcoreTool::isHtmlResponse($this->getResponse())) {
            return;
        }

        $resolveState = $this->getState();

        if ($resolveState !== Resolver::STATE_ADMIN) {
            return;
        }

        $this->run($resolveState);
    }
}

