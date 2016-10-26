<?php

require_once __DIR__.'/../frameworks/Environment.php';

abstract class DefaultIndex {

    protected $environment;

    protected function __construct() {
        $this->environment = Environment::getEnv();
    }

    abstract protected function echoHeaders();
    abstract protected function echoContent();

    protected function resolvePath($resource) {
        return $this->environment->sysconf['baseURL'] . 'view/' . $resource;
    }

    protected function mysql2german($date) {
        if (preg_match('/\d{4}-\d{2}-\d{2}/', $date))
            return date('d.m.Y', DateTime::createFromFormat('Y-m-d', $date)->getTimestamp());
        if (preg_match('/\d{9}/', $date))
            return date('d.m.Y', $date);
        return date('d.m.Y', strtotime($date));
    }

    protected function transformMail($mail) {
        return str_replace(array("@","."),array("&Oslash;", "&middot;"), $mail);
    }

    protected function translateOption($opt, $val) {
        $conf = $this->environment->oconfig[$opt];
        if (isset($conf[$val]))
            return $conf[$val];
        return $val;
    }

    public function render() {
        ?>
        <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
        <head>

            <!-- ------------------------------------------------------- -->
            <!--               Programmiert von                          -->
            <!--                                                         -->
            <!--               ~ Manu Herrmann ~                         -->
            <!--                     und                                 -->
            <!--                ~ Tim Repke ~                            -->
            <!--                                                         -->
            <!-- Erste Version in 2014.                                  -->
            <!-- Beide haben 2015 noch einmal eine Schippe draufgesetzt! -->
            <!-- Irgendwie 2016 noch einmal!                             -->
            <!-- ------------------------------------------------------- -->

            <!--
                 Die Entwickler sind der Meinung, dass im Quelltext rumschnüffeln unethisch ist.
                 Da wir sehr schlau sind, haben wir deinen Rechner infiziert. Eine Anmeldung zur
                 Fachschaftsfahrt ist daher nicht mehr möglich.
                 Selbst schuld!

                 PS: Solltest du es doch geschafft haben, gib' uns bitte Bescheid, damit wir dich wieder löschen.
            -->

            <!--
                Current Version (git), check out the details on https://github.com/TimRepke/registration-system

                   > <?php echo implode("\n                   > ", $this->environment->sysconf['gitSummary']); ?>

            -->

            <title>Fachschaftsfahrt</title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <?php $this->echoHeaders(); ?>
        </head>
        <body>
            <div class="shadowbox">
                <div id="headerbox">
                    <div class="headerboxshade"><h1>Fachschaftsfahrt</h1></div>
                    <div class="headerboxshade"><h2>Informatik</h2></div>
                    <p></p>
                </div>
                <div id="menubox">
                    <?php $this->echoContent(); ?>
                </div>
                <div id="footerbox">&nbsp;
                    <span style="float:left; margin-left:10px">
                        &copy; <?php echo date("Y"); ?> Fachschaftsinitiative Informatik der Humboldt Universität zu Berlin.
                    </span>
                    <a href="<?php echo $this->environment->sysconf['impressum']; ?>" style="color:white;float:right; margin-right: 10px" target="_blank">Impressum</a>
                </div>
                <img id="nyan" alt="O" src="<?php echo $this->resolvePath('graphics/studityp_5.gif') ?>"
                     style="position: fixed;bottom: 5px;left:0"/>
                <a style="position: fixed;top:5px; right:5px;" target="_blank" href="https://github.com/TimRepke/registration-system">
                    <img alt="GitHub" title="Auf GitHub gabeln" src="<?php echo $this->resolvePath('graphics/GitHub-Mark-32px.png') ?>"/></a>
            </div>
        </body>
        </html>
    <?php


    }
}
