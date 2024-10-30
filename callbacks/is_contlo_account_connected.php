<?php
function is_contlo_account_connected()
{
    return get_option('contlo_api_key', null) !== null;
}

?>