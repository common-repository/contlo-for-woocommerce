<?php
function show_contlo_menu()
{
    if (get_option('contlo_api_key', null) === null) {
        displayContloConnect();
        return;
    } else {
        displayContloConnected();
        return;
    }
}

?>