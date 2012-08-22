<?php
/**
 * Page object representing a page of the website
 * @package Gumdrop
 */

namespace Gumdrop;

/**
 * Page object representing a page of the website
 */
class Page
{
    /**
     * Dependency injector
     * @var \Gumdrop\Application
     */
    private $app;

    /**
     * Page location - relative to the root of the source files
     * @var string
     */
    private $location;

    /**
     * Set page location
     *
     * @param string string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * Get page location
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Page's Markdown content
     * @var string
     */
    private $markdownContent;

    /**
     * Set Page's Markdown content
     *
     * @param string $markdownContent
     */
    public function setMarkdownContent($markdownContent)
    {
        $this->markdownContent = $markdownContent;
    }

    /**
     * Get Page's Markdown content
     * @return string
     */
    public function getMarkdownContent()
    {
        return $this->markdownContent;
    }

    /**
     * Page's HTML content
     * @var string
     */
    private $htmlContent;

    /**
     * Set Page's HTML content
     *
     * @param string $htmlContent
     */
    public function setHtmlContent($htmlContent)
    {
        $this->htmlContent = $htmlContent;
    }

    /**
     * Get Page's HTML content
     * @return string
     */
    public function getHtmlContent()
    {
        return $this->htmlContent;
    }

    /**
     * Page's final content
     * @var string
     */
    private $pageContent;

    /**
     * Set Page's content
     *
     * @param string $pageContent
     */
    public function setPageContent($pageContent)
    {
        $this->pageContent = $pageContent;
    }

    /**
     * Get Page's content
     * @return string
     */
    public function getPageContent()
    {
        return $this->pageContent;
    }

    /**
     * Page's configuration
     * @var \Gumdrop\PageConfiguration
     */
    private $configuration;

    /**
     * Set Page's configuration
     *
     * @param \Gumdrop\PageConfiguration $configuration
     */
    public function setConfiguration(\Gumdrop\PageConfiguration $configuration)
    {
        $this->setMarkdownContent($configuration->extractHeader($this->getMarkdownContent()));
        $this->configuration = $configuration;
    }

    /**
     * Get Page's configuration
     * @return \Gumdrop\PageConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Page's layout Twig environment
     * @var \Twig_Environment
     */
    private $layoutTwigEnvironment;

    /**
     * Set Page's layout Twig environment
     *
     * @param \Twig_Environment $layoutTwigEnvironment
     */
    public function setLayoutTwigEnvironment(\Twig_Environment $layoutTwigEnvironment)
    {
        $this->layoutTwigEnvironment = $layoutTwigEnvironment;
    }

    /**
     * Get Page's layout Twig environment
     * @return \Twig_Environment
     */
    public function getLayoutTwigEnvironment()
    {
        return $this->layoutTwigEnvironment;
    }

    /**
     * Page's page Twig environment
     * @var \Twig_Environment
     */
    private $pageTwigEnvironment;

    /**
     * Set Page's page Twig environment
     *
     * @param \Twig_Environment $pageTwigEnvironment
     */
    public function setPageTwigEnvironment($pageTwigEnvironment)
    {
        $this->pageTwigEnvironment = $pageTwigEnvironment;
    }

    /**
     * Get Page's page Twig environment
     * @return \Twig_Environment
     */
    public function getPageTwigEnvironment()
    {
        return $this->pageTwigEnvironment;
    }

    /* METHODS */
    /**
     * Constructor
     *
     * @param \Gumdrop\Application $app
     */
    public function __construct(\Gumdrop\Application $app)
    {
        $this->app = $app;
    }

    /**
     * Converts the Markdown code to HTML
     */
    public function convertMarkdownToHtml()
    {
        $this->setHtmlContent($this->app->getMarkdownParser()->transformMarkdown($this->getMarkdownContent()));
    }

    /**
     * Renders the layout Twig environment of the page
     */
    public function renderLayoutTwigEnvironment()
    {
        $this->setPageContent($this->getHtmlContent());
        if (!is_null($this->getLayoutTwigEnvironment()))
        {
            $twig_layout = null;
            if (isset($this->configuration['layout']) && !is_null($this->configuration['layout']))
            {
                $twig_layout = $this->configuration['layout'];
            }
            elseif ($this->app->getFileHandler()->findPageTwigFile())
            {
                $twig_layout = 'page.twig';
            }
            if (!is_null($twig_layout))
            {
                $this->setPageContent($this->getLayoutTwigEnvironment()->render(
                    $twig_layout,
                    $this->generateTwigData()
                ));
            }
        }
    }

    /**
     * Renders the page Twig environment of the page
     */
    public function renderPageTwigEnvironment()
    {
        $this->setHtmlContent($this->getPageTwigEnvironment()->render(
            $this->getHtmlContent(),
            $this->generateTwigData()
        ));
    }

    /**
     * Writes the final HTML content to file
     *
     * @param string $destination
     */
    public function writeHtmFiles($destination)
    {
        $pathinfo = pathinfo($this->getLocation());
        if (!file_exists($destination . '/' . $pathinfo['dirname']))
        {
            mkdir($destination . '/' . $pathinfo['dirname'], 0777, true);
        }
        $conf = $this->getConfiguration();
        if (isset($conf['target_name']) && !empty($conf['target_name']))
        {
            $destination_file = $destination . '/' . $pathinfo['dirname'] . '/' . $conf['target_name'];
        }
        else
        {
            $destination_file = $destination . '/' . $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.htm';
        }
        file_put_contents($destination_file, $this->getPageContent());
    }

    /**
     * Return the Page's data for use in Twig rendering phase
     * @return array
     */
    public function exportForTwig()
    {
        $path_info = pathinfo($this->getLocation());
        return array_merge(
            $this->getConfiguration()->extract(),
            array(
                'location' => str_replace($path_info['basename'], $path_info['filename'] . '.htm', $this->getLocation()),
                'html' => $this->getHtmlContent(),
                'markdown' => $this->getMarkdownContent()
            ));
    }

    /**
     * Returns an array containing the data to pass to Twig renderer
     * @return array
     */
    private function generateTwigData()
    {
        return array(
            'content' => $this->getHtmlContent(),
            'page' => $this->exportForTwig(),
            'pages' => $this->app->getPageCollection()->exportForTwig()
        );
    }
}
