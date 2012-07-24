<?php

namespace Gumdrop;

/**
 * Page object representing a page of the website
 */
class Page
{
    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */

    private $markdownContent;
    /**
     * @var string
     */
    private $htmlContent;

    /**
     * @var \Gumdrop\Application
     */
    private $app;

    /**
     * @param \Gumdrop\Application $app
     */
    public function __construct(\Gumdrop\Application $app)
    {
        $this->app = $app;
    }

    public function convertMarkdownToHtml()
    {
        $this->setHtmlContent($this->app->getMarkdownParser()->transformMarkdown($this->getMarkdownContent()));
    }

    public function applyTwigLayout()
    {
        if ($this->app->getFileHandler()->findPageTwigFile())
        {
            $this->setHtmlContent($this->app->getTwigEnvironment()->render(
                'page.twig',
                array('content' => $this->getHtmlContent())
            ));
        }
    }

    public function writeHtmFiles($destination)
    {
        $pathinfo = pathinfo($this->getLocation());
        if (!file_exists($destination . '/' . $pathinfo['dirname']))
        {
            mkdir($destination . '/' . $pathinfo['dirname'], 0777, true);
        }
        $destination_file = $destination . '/' . $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.htm';
        file_put_contents($destination_file, $this->getHtmlContent());
    }

    /**
     * TODO: test if header exists - header is delimited by "***"
     * TODO: test if it contains valid JSON
     */
//    public function convertConfigurationHeader()
//    {
//        ;
//    }

    /**
     * @param string $htmlContent
     */
    public
    function setHtmlContent($htmlContent)
    {
        $this->htmlContent = $htmlContent;
    }

    /**
     * @return string
     */
    public
    function getHtmlContent()
    {
        return $this->htmlContent;
    }

    /**
     * @param string $location
     */
    public
    function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public
    function getLocation()
    {
        return $this->location;
    }

    /**
     * @param $markdownContent
     */
    public
    function setMarkdownContent($markdownContent)
    {
        $this->markdownContent = $markdownContent;
    }

    /**
     * @return string
     */
    public
    function getMarkdownContent()
    {
        return $this->markdownContent;
    }
}