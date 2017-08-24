<?php

namespace Helper;

use Exception\FileNotExist;
use helpers\Random;
use PDO;

/**
 * Class BeardQuery
 * Query Builder managing Collection Objects
 * @package Helper
 */
class Response
{
    const TEMPLATE_ENGINE = '.html.twig';
    private $status = 200;
    private $viewFile;
    private $params;

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param array $params
     * @return Response
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    public function getViewFile()
    {
        return $this->viewFile;
    }

    public function setViewFile($viewFile)
    {
        $this->viewFile = $viewFile;
    }

    public function buildViewFile($viewFile)
    {
        $viewFile = $viewFile . self::TEMPLATE_ENGINE;
        if (!file_exists(APP_VIEWS_DIR . $viewFile))
        {
            throw new FileNotExist('The view you are looking for does not exist');
        }
        $this->setViewFile($viewFile);
        return $this;
    }

    private function initTwig()
    {
        $loader = new \Twig_Loader_Filesystem(APP_VIEWS_DIR);
        return new \Twig_Environment($loader, array(
            'cache' => false,
        ));
    }

    public function output()
    {
        $twig = $this->initTwig();
        echo $twig->render($this->getViewFile(), $this->getParams());
    }
}
