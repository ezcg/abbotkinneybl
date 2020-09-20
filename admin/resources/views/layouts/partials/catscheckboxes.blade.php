@php

foreach($catsArr as $catsId => $title) {
    echo $title . ":<input type='checkbox' name='catsArr[]' value='$catsId'";
    if (!empty($itemsCatsArr[$itemsId][$catsId])) {
        echo " checked";
    }
    echo "> |<br> ";
    // include subcategories if category has them
}

@endphp