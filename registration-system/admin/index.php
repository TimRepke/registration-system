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

    protected $message_succ;
    protected $message_err;

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

    protected function getMessage() {
        $ret = '';
        if (!empty($this->message_succ)) {
            $ret .= $this->getMessageBox($this->message_succ, 'success');
        }
        if (!empty($this->message_err)) {
            $ret .= $this->getMessageBox($this->message_err, 'error');
        }
        return $ret;
    }

    public function __construct($base) {
        $this->environment = Environment::getEnv(true);
        $this->ajaxMode = isset($_REQUEST['ajax']);
        $this->base = $base;
        $this->fahrt = $this->environment->getTrip(true);
        $this->message_err = null;
        $this->message_succ = null;
    }

    /**
     * Puts out Label and Selection box
     *
     * @param $name
     * @param $id
     * @param $values
     * @param $selected
     * @param $subtext
     * @return string
     */
    protected function getFormSel($name, $id, $values, $selected, $subtext) {
        $r = '<label>' . $name . '
        <span class="small">' . $subtext . '</span>
        </label>
        <select name="' . $id . '" id="' . $id . '">';
        foreach ($values as $val) {
            $r .= '<option value="' . $val . '"';
            if ($val == $selected) $r .= ' selected';
            $r .= '>' . $val . '</option>';
        }
        $r .= '</select>';

        return $r;
    }

    /**
     * Puts out Label and two selection boxes side by side right below
     *
     * @param $name
     * @param $id
     * @param $values
     * @param $selected
     * @param $id2
     * @param $values2
     * @param $selected2
     * @param $subtext
     * @return string
     */
    protected function getFormSel2($name, $id, $values, $selected, $id2, $values2, $selected2, $subtext) {
        $r = '<label style="text-align:left">' . $name . '
        <span class="small">' . $subtext . '</span>
        </label><table><tr><td>
        <select name="' . $id . '" id="' . $id . '" style="width:110px; text-align: center">';
        foreach ($values as $val) {
            $r .= '<option value="' . $val . '"';
            if ($val == $selected) $r .= ' selected';
            $r .= '>' . $val . '</option>';
        }
        $r .= '</select></td><td><select name="' . $id2 . '" id="' . $id2 . '">';
        foreach ($values2 as $val) {
            $r .= '<option value="' . $val . '"';
            if ($val == $selected2) $r .= ' selected';
            $r .= '>' . $val . '</option>';
        }
        $r .= '</select></td></tr></table>';
        return $r;
    }

    protected function getFormInput($name, $id, $value, $subtext) {
        $r = '<label>' . $name .
            '<span class="small">' . $subtext . '</span>
        </label>
        <input type="text" name="' . $id . '" id="' . $id . '" value="' . $value . '" />';
        return $r;
    }

    public function mysql2german($date) {
        return $this->base->mysql2german($date);
    }

    public function resolvePath($path) {
        return $this->base->resolvePath($path);
    }

}

(new AdminBase())->exec();