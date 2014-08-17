<?php

function page_stuff()
{
    global $text;
    $text .= "Übersichtsseite";
}

function page_list()
{
    global $text, $headers;
    $headers =<<<END
    <link rel="stylesheet" type="text/css" href="../view/css/DataTables/css/jquery.dataTables.min.css" />
    <script type="text/javascript" src="../view/js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="../view/js/jquery.dataTables.min.js"></script>
END;
    $text .= "Meldeliste";

    $text .=<<<END
    <table id="mlist">
        <thead>
            <tr>
                <th>Column 1</th>
                <th>Column 2</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Row 1 Data 1</td>
                <td>Row 1 Data 2</td>
            </tr>
            <tr>
                <td>Row 2 Data 1</td>
                <td>Row 2 Data 2</td>
            </tr>
        </tbody>
    </table>
    <script type='text/javascript'>
        $(document).ready(function(){
            $('#mlist').dataTable({});
        });
    </script>
END;

}

function page_404($pag)
{
    global $text;
    $text .='
        <div style="background-color:black; color:antiquewhite; font-family: \'Courier New\', Courier, monospace;height: 100%; width: 100%;position:fixed; top:0; padding-top:40px;">
            $ get-page '.$pag.'<br />
            404 - page not found ('.$pag.')<br />
            $ <blink>&#9611;</blink>
        </div>';

}

function page_notes(){
    require_page("pages_notes.php");
}

function page_mail(){
    require_page("pages_mail.php");
}

function page_cost(){
    require_page("pages_cost.php");
}

function require_page($page){
    if(!@file_exists($page) ) {
        page_404($page);
    } else {
        require_once $page;
    }
}

?>