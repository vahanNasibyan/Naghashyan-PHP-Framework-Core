<?php
/**
 * NGS predefined templater class
 * handle smarty and json responses
 *
 * @author Levon Naghashyan <levon@naghashyan.com>
 * @site http://naghashyan.com
 * @package ngs.framework.templater
 * @version 3.1.0
 * @year 2010-2016
 *
 * This file is part of the NGS package.
 *
 * @copyright Naghashyan Solutions LLC
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ngs\templater {

  use ngs\templater\AbstractTemplater;
  use ngs\templater\NgsSmartyTemplater;

  class NgsTemplater extends AbstractTemplater {

    /**
     * constructor
     * reading Smarty config and setting up smarty environment accordingly
     */
    private $smarty = null;
    private $template = null;
    private $params = array();
    private $permalink = null;
    private $smartyParams = array();
    private $httpStatusCode = 200;
    private $type = "json";

    public function __construct() {
    }

    public function setType($type) {
      $this->type = $type;
    }

    public function getType() {
      return $this->type;
    }

    public function getSmartyTemplater() {
      return new NgsSmartyTemplater();
    }


    /**
     * assign single smarty parameter
     *
     * @access public
     *
     * @param String $name
     * @param mixed $value
     *
     * @return void
     */
    public function assign($key, $value) {
      $this->smartyParams[$key] = $value;
    }

    /**
     * assign single json parameter
     *
     * @access public
     *
     * @param String $name
     * @param mixed $value
     *
     * @return void
     */
    public function assignJson($key, $value) {
      $this->params[$key] = $value;
    }

    /**
     * add multiple json parameters
     *
     * @access public
     * @param array $paramsArr
     *
     * @return void
     */
    public function assignJsonParams($paramsArr) {
      if (!is_array($paramsArr)){
        $paramsArr = [$paramsArr];
      }
      $this->params = array_merge($this->params, $paramsArr);
    }

    /**
     * set template
     *
     * @param String $template
     *
     */
    public function setTemplate($template) {
      $this->template = $template;
    }

    /**
     * Return a template
     *
     * @return String $template
     */
    public function getTemplate() {
      return $this->template;
    }

    /**
     * set template
     *
     * @param String $template
     *
     */
    public function setPermalink($permalink) {
      $this->permalink = $permalink;
    }

    /**
     * Return a template
     *
     * @return String $template|null
     */
    public function getPermalink() {
      return $this->permalink;
    }

    /**
     * set response http status code
     *
     * @param integer $httpStatusCode
     *
     */
    public function setHttpStatusCode($httpStatusCode) {
      $this->httpStatusCode = $httpStatusCode;
    }

    /**
     * get response http status code
     *
     * @param integer $httpStatusCode
     *
     */
    public function getHttpStatusCode() {
      return $this->httpStatusCode;
    }

    /**
     * display response
     * @access public
     *
     */
    public function display() {

      http_response_code($this->getHttpStatusCode());
      if (!$this->getTemplate()){
        $this->displayJson($this->params);
      }
      $this->smarty = new NgsSmartyTemplater();
      foreach ($this->smartyParams as $key => $value){
        $this->smarty->assign($key, $value);
      }
      if ($this->getType() == "json"){
        $this->displayJson();
        return;
      }

      $ext = pathinfo($this->getTemplate(), PATHINFO_EXTENSION);
      if ($ext != "json" && (NGS()->isJsFrameworkEnable() && !NGS()->getHttpUtils()->isAjaxRequest())){
        $this->smarty->setCustomHeader($this->getCustomHeader());
        $this->displaySmarty($this->getTemplate());
        return;
      }
      if (NGS()->isJsFrameworkEnable() && NGS()->getHttpUtils()->isAjaxRequest()){
        $params = [];
        $params["html"] = $this->smarty->fetch($this->getTemplate());
        $params["nl"] = NGS()->getLoadMapper()->getNestedLoads();
        $params["pl"] = $this->getPermalink();
        $params["params"] = $this->params;
        $this->displayJson($params);
        return;
      }
      $this->displayJson();
    }


    private function displayJson($params = null) {
      header('Content-Type: application/json; charset=utf-8');
      if ($params != null){
        echo json_encode($params);
        exit;
      }
      foreach ($this->params as $key => $value){
        $this->smarty->assign($key, $value);
      }
      echo($this->smarty->fetch($this->getTemplate()));
      exit;
    }

    private function displaySmarty() {
      echo $this->fetchSmartyTemplate($this->getTemplate());
    }


    public function fetchSmartyTemplate($templatePath) {
      return $this->smarty->fetch($templatePath);
    }


    protected function getCustomJsParams() {
      return array();
    }

    protected function getCustomHeader() {
      return "";
    }

  }

}
