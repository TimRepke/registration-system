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
        return date('d.m.Y', strtotime($date));
    }

    protected function transformMail($mail) {
        return str_replace(array("@","."),array("&Oslash;", "&middot;"), $mail);
    }

    protected function echoImpressum() {
        echo '<a href="'.$this->environment->sysconf['impressum'].'">Impressum</a>';
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
                <div id="footerbox">&copy;<?php echo date("Y"); ?>
                    Fachschaftsinitiative Informatik der Humboldt Universität zu Berlin. <?php $this->echoImpressum() ?>
                </div>
                <img id="nyan" alt="O" src="<?php echo $this->resolvePath('graphics/studityp_5.gif') ?>"
                     style="position: fixed;bottom: 5px;left:0"/>
            </div>
        </body>
        </html>
    <?php
    }
}
