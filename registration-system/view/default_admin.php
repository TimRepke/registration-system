<?php

require_once __DIR__ . '/../frameworks/Environment.php';

abstract class DefaultAdmin {

    protected $environment;

    protected function __construct() {
        $this->environment = Environment::getEnv(true);
    }

    abstract protected function echoHeaders();

    abstract protected function echoContent();

    abstract protected function echoNavigation();

    abstract protected function echoHeader();

    abstract protected function echoTitle();

    abstract protected function echoFooter();

    public function resolvePath($resource) {
        return $this->environment->sysconf['baseURL'] . 'view/' . $resource;
    }

    public static function mysql2german($date) {
        if (preg_match('/\d{4}-\d{2}-\d{2}/', $date))
            return date('d.m.Y', DateTime::createFromFormat('Y-m-d', $date)->getTimestamp());
        if (preg_match('/\d{9}/', $date))
            return date('d.m.Y', $date);
        return date('d.m.Y', strtotime($date));
    }

    protected function translateOption($opt, $val) {
        $conf = $this->environment->oconfig[$opt];
        if (isset($conf[$val]))
            return $conf[$val];
        return $val;
    }

    protected function echoLoginForm() {
        echo '<form method="post">
            <input name="user" type="text" />
            <input name="password" type="password" />
            <input type="submit" value="anmelden" />
        </form>';
    }

    protected function makeNavigationItems($menu) {
        $text = '';
        foreach ($menu as $name => $page) {
            $text .= '<a href="?page=' . $page . '">' . $name . '</a>';
        }
        return $text;
    }

    public function render() {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title><?php $this->echoTitle() ?></title>
            <meta charset="UTF-8"/>
            <link rel="stylesheet" href="<?php echo $this->resolvePath('admin_style.css') ?>"/>
            <?php $this->echoHeaders(); ?>
        </head>
        <body>
        <div id="admin-content">
            <?php $this->echoContent(); ?>
        </div>
        <div id="linkbar">
            <?php $this->echoNavigation(); ?>
            <a href="?logout" id="logout">.</a>
        </div>
        </body>
        </html>
        <?php
    }

    public function renderPrint() { ?>
        <!DOCTYPE html>
        <html moznomarginboxes mozdisallowselectionprint>
        <head>
            <title><?php $this->echoTitle() ?></title>
            <?php $this->echoHeaders() ?>
            <link href="../view/print_style.css" rel="stylesheet"/>
        </head>
        <body>

        <!-- Jaaaa... Tabellenfoo! Aber anders geht es einfach nicht! -->
        <table width="100%">
            <thead>
            <tr>
                <th class="header"><?php $this->echoHeader() ?></th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td width="100%">
                    <table width="100%">
                        <tr>
                            <td><br>&nbsp;</td>
                        </tr>
                    </table>
            </tfoot>
            <tbody>
            <tr>
                <td width="100%" style="padding-top: 10pt">
                    <?php $this->echoContent() ?>
                </td>
            </tr>
            </tbody>
        </table>

        <table id="footer" width="100%">
            <tr>
                <td width="100%">
                    <?php $this->echoFooter() ?>
                </td>
            </tr>
        </table>
        </body>
        </html>
        <?php
    }

    public function renderPrintNoHeaders() { ?>
        <!DOCTYPE html>
        <html moznomarginboxes mozdisallowselectionprint>
        <head>
            <title><?php $this->echoTitle() ?></title>
            {headers}
            <link href="<?php $this->resolvePath('print_style.css') ?>" rel="stylesheet"/>
        </head>
        <body>
        <?php $this->echoContent() ?>
        </body>
        </html>

        <?php
    }
}
