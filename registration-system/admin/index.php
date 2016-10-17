<?php
session_start();

require_once __DIR__ . '/../view/default_admin.php';

class AdminBase extends DefaultAdmin {

    const STATE_200 = 0;
    const STATE_403 = 1;
    const STATE_404 = 2;
    const STATE_500 = 3;

    protected $isAdmin;
    protected $isSudo;
    protected $requerestedPage;
    /** @var AdminPage */
    protected $page;
    protected $pageStatus = null;

    private static $PAGES = [
        'front' => 'Anmeldung',
        'overview' => 'Übersicht',
        'list' => 'Meldeliste',
        'wl' => 'Warteliste',
        'cost' => 'Kosten',
        'mail' => 'Rundmail',
        'notes' => 'Notizen',
        'export' => 'Listenexport',
        'infos' => 'Infos',
        'admin' => 'SA*'
    ];
    private static $DEFAULT_PAGE = 'overview';
    private static $SUPERADMIN_PAGES = ['admin'];

    public function __construct() {
        parent::__construct();

        $this->isAdmin = $this->environment->isAdmin();
        $this->isSudo = $this->environment->isSuperAdmin();

        $this->requerestedPage = isset($_GET['page']) ? $_GET['page'] : AdminBase::$DEFAULT_PAGE;
        $this->page = $this->getRequestedPage();
    }

    private function isLoggedIn() {
        return $this->isAdmin or $this->isSudo;
    }

    private function getRequestedPage() {
        if ($this->isLoggedIn()) {
            $pagefile = __DIR__ . '/pages_' . $this->requerestedPage . '.php';
            if (!isset(AdminBase::$PAGES[$this->requerestedPage]) or
                (in_array($this->requerestedPage, AdminBase::$SUPERADMIN_PAGES) and !$this->isSudo) or
                !@file_exists($pagefile)
            ) {
                $this->pageStatus = AdminBase::STATE_404;
                return null;
            } else {
                try {
                    require_once $pagefile;
                    $classname = 'Admin' . ucfirst($this->requerestedPage) . 'Page';
                    $this->pageStatus = AdminBase::STATE_200;
                    return new $classname($this);
                } catch (Exception $e) {
                    $this->pageStatus = AdminBase::STATE_500;
                    $this->exceptionMessage = $e->getMessage();
                    return null;
                }
            }
        } else {
            $this->pageStatus = AdminBase::STATE_403;
            return null;
        }

    }

    protected function echoHeaders() {
        if (!empty($this->page))
            echo $this->page->getHeaders();
    }

    protected function echoContent() {
        if ($this->pageStatus === AdminBase::STATE_200) {
            echo $this->page->getText();
        } elseif ($this->pageStatus === AdminBase::STATE_403) {
            $this->echoLoginForm();
        } elseif ($this->requerestedPage == 'front') {
            $this->echoRegistrationPage();
        } else {
            $this->echoPage404();
        }
    }

    protected function echoHeader() {
        if (!empty($this->page))
            echo $this->page->getHeader();
    }

    protected function echoTitle() {
        echo 'FSFahrt - Admin Panel';
    }

    protected function echoFooter() {
        if (!empty($this->page))
            echo $this->page->getFooter();
    }

    protected function echoNavigation() {
        if ($this->isLoggedIn()) {
            echo $this->makeNavigationItems(array_flip(AdminBase::$PAGES));
        }
    }

    private function echoPage404() {
        echo '
        <div style="background-color:black; color:antiquewhite; font-family: \'Courier New\', Courier, monospace;height: 100%; width: 100%;position:fixed; top:0; padding-top:40px;">
            $ get-page ' . $this->requerestedPage . '<br />';
        if ($this->pageStatus == AdminBase::STATE_500) {
            echo '500 - internal error (' . $this->exceptionMessage . ')<br />';
        } else {
            echo '404 - page not found (' . $this->requerestedPage . ')<br />';
        }
        echo 'access level admin: ' . ($this->isAdmin ? 'yes' : 'no') .
            '<br /> access level sudo: ' . ($this->isAdmin ? 'yes' : 'no') .
            '<br />$ <blink>&#9611;</blink>
        </div>';
    }

    private function echoRegistrationPage() {
        $baseurl = $this->environment->sysconf['baseURL'];
        $fid = $this->environment->getCurrentTripId();
        echo '<style>#admin-content{padding:0}</style>
            <a href="' . $baseurl . '?fid=' . $fid . '">' . $baseurl . '?fid=' . $fid . '</a><br />
            <iframe src="' . $baseurl . '?fid=' . $fid . '"
                    style="height:90vh; width:100%; position: absolute; border:0;"></iframe>';
    }

    public function exec() {
        if ($this->pageStatus === AdminBase::STATE_200 and $this->page->ajaxMode) {
            echo $this->page->getAjax();
        } else {
            $this->render();
        }
    }
}

abstract class AdminPage {
    public $printMode = false;
    public $ajaxMode = false;
    /** @var  AdminBase */
    protected $base;
    /** @var  Fahrt */
    protected $fahrt;

    abstract public function getHeaders();

    abstract public function getHeader();

    abstract public function getFooter();

    abstract public function getText();

    abstract public function getAjax();

    /**
     * @param $content
     * @param $mode string (info, success, warning, error)
     * @return string
     */
    protected function getMessageBox($content, $mode) {
        return '<div class="' . $mode . '">' . $content . '</div>';
    }

    public function __construct($base) {
        $this->environment = Environment::getEnv(true);
        $this->ajaxMode = isset($_REQUEST['ajax']);
        $this->base = $base;
        $this->fahrt = $this->environment->getTrip(true);
    }

}

(new AdminBase())->exec();