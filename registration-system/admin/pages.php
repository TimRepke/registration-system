<?php

function page_stuff()
{
    global $text;
    $text .= "Übersichtsseite";
}

function page_list()
{
    global $text;
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

function page_404()
{
    global $text;
    $text .= "404 Seite nicht gefunden...";
}

?>