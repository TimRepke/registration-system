<?php


class AdminCostPage extends AdminPage {

    public function getHeaders() {
        return '
             <script type="text/javascript" src="../view/js/jquery-1.11.1.min.js"></script>
             <script type="text/javascript" src="../view/js/angular.min.js"></script>
             <script type="text/javascript" src="../view/js/xeditable.js"></script>
             <script type="text/javascript" src="../view/js/toastr.min.js"></script>
             <script type="text/javascript" src="../view/js/angular-strap.min.js"></script>
             <script type="text/javascript" src="../view/js/angular-strap.tpl.js"></script>
             <script type="text/javascript" src="pages_cost/pages_cost.js"></script>
             <link   type="text/css" rel="stylesheet" href="pages_cost/pages_cost.css" />
             <link   type="text/css" rel="stylesheet" href="../view/css/toastr.css" />';
    }

    public function getHeader() {
        return '';
    }

    public function getFooter() {
        return '';
    }

    public function getText() {
        return '
            <div ng-app="pages-cost">
                <h1>Kostenaufstellung</h1>

                <div ng-controller="TablePriceController as table">
                    <h2>Kosten pro Person</h2>
                    <table-price></table-price>
                </div>

                <div ng-controller="TableShoppingController as table">
                    <h2>Einkaufen</h2>
                    <table-shopping></table-shopping>
                </div>

                <div ng-controller="TableReceiptController as table">
                    <h2>Herbergsrechnung</h2>
                    <table-receipt></table-receipt>
                </div>

                <div ng-controller="TableMoneyioController as table">
                    <h2>Money In/Out</h2>
                    <table-moneyio></table-moneyio>
                </div>
            </div>


            <div class="cost-anmerkung">
                Hinweise:<br />
                 <ul>
                    <li>Zurückgezogene Registrierungen werden hier nicht beachtet! Wenn Bezahlung erhalten unbedingt seperat auf dem Notizzettel verwalten!</li>
                    <li>Eingesammelter Betrag wird nicht individuell gespeichert. Wird er geändert sind ggf. unterschiedliche Beträge nicht erfasst.</li>
                    <li>Für falsche Berechnungen wird keine Haftung übernommen. Wenn unsicher -> selbst rechnen und nachprüfen!</li>
                    <li>Die <strong>effektive</strong> Förderung muss in der "Kosten pro Person"-Tabelle angepasst werden</li>
                    <li>In der "Kosten pro Person"-Tabelle müssen die <strong>effektiven</strong> Kosten angegeben werden.</li>
                    <li>Es erfolgt keine Validierung eingegeber Typen. Dezimaltrennzeichen ist ".", nicht ",".</li>
                    <li>Berechnungen erfolgen mit eingegebener Präzision (ggf. abweichend von der auf zwei Stellen gerundeten Anzeige)</li>
                    <li>Manche Variablen updaten sich nicht automatisch. Seite neu laden hilft in dem Fall.</li>
                    <li>Die CSS-Klasse debug blendet debugdaten aus. Zum "Export" können die Werte kopiert werden, wenn man sich die Elemente einblenden lässt.</li>
                 </ul>
            </div>';
    }

    public function getAjax() {
        // some shorthands
        $fid = $this->fahrt->getID();
        $db = $this->environment->database;
        $data = '';
        // read payload
        $task = $_REQUEST['ajax'];
        if (isset($_REQUEST['data'])) {
            $data = $_REQUEST['data'];
        } elseif (strpos($task, 'set') !== false && strpos($task, 'json') !== false) {
            $data = file_get_contents('php://input');
        }

        // if no data is there yet, add it!
        if (!$db->has('cost', ['fahrt_id' => $fid])) {
            $db->insert('cost',
                ['fahrt_id' => $fid, 'tab1' => '', 'tab2' => '', 'tab3' => '', 'moneyIO' => '', 'collected' => 60]);
        }

        switch ($task) {

            // == GETTERS ==
            case 'get-price-json':
                header('Content-Type: application/json');
                return $db->get('cost', 'tab1', ['fahrt_id' => $fid]);

            case 'get-shopping-json':
                header('Content-Type: application/json');
                return $db->get('cost', 'tab2', ['fahrt_id' => $fid]);

            case 'get-receipt-json':
                header('Content-Type: application/json');
                return $db->get('cost', 'tab3', ['fahrt_id' => $fid]);

            case 'get-moneyio-json':
                header('Content-Type: application/json');
                return $db->get('cost', 'moneyIO', ['fahrt_id' => $fid]);

            case 'get-other-json':
                header('Content-Type: application/json');
                $notwaiting = ['on_waitlist' => 0,
                    'AND' => [
                        'transferred[!]' => null,
                        'on_waitlist' => 1
                    ]];
                $baseW = ['fahrt_id' => $fid, 'OR' => $notwaiting, 'backstepped' => null];

                $ret['remain'] = $db->select('bachelor', ['forname', 'sirname', 'bachelor_id', 'anday(von)', 'abday(bis)', 'antyp', 'abtyp'],
                    ['AND' => array_merge($baseW, ['repaid' => null])]);
                $ret['back'] = $db->select('bachelor', ['forname', 'sirname', 'bachelor_id', 'anday(von)', 'abday(bis)', 'antyp', 'abtyp'],
                    ['AND' => array_merge($baseW, ['repaid[!]' => null])]);
                $ret['bezahlt'] = $db->count('bachelor',
                    ['AND' => array_merge($baseW, ['paid[!]' => null])]);
                $ret['count'] = $db->count('bachelor',
                    ['AND' => $baseW]);
                $ret['cnt']['all'] = $ret['count'];
                $ret['cnt']['geman'] = $db->count('bachelor',
                    ['AND' => array_merge($baseW, ['antyp' => 'BUSBAHN'])]);
                $ret['cnt']['gemab'] = $db->count('bachelor',
                    ['AND' => array_merge($baseW, ['antyp' => 'BUSBAHN'])]);
                $ret['amount'] = $db->get('cost', 'collected', ['fahrt_id' => $fid]);
                $ret['fahrt'] = $db->get('fahrten', ['von', 'bis'], ['fahrt_id' => $fid]);
                $ret['arten'] = $this->environment->oconfig['reisearten'];

                if (!$ret['remain'])
                    $ret['remain'] = [];
                if (!$ret['back'])
                    $ret['back'] = [];
                return json_encode($ret);

            // == SETTER ==
            case 'set-price-json':
                $db->update('cost', ['tab1' => $data], ['fahrt_id' => $fid]);
                break;

            case 'set-shopping-json':
                $db->update('cost', ['tab2' => $data], ['fahrt_id' => $fid]);
                break;

            case 'set-receipt-json':
                $db->update('cost', ['tab3' => $data], ['fahrt_id' => $fid]);
                break;

            case 'set-moneyio-json':
                $db->update('cost', ['moneyIO' => $data], ['fahrt_id' => $fid]);
                break;

            case 'set-amount':
                $db->update('cost', ['collected' => $data], ['fahrt_id' => $fid]);
                break;

            // == DEFAULT ==
            default:
                break;
        }
    }
}
