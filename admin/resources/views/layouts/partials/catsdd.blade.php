<div  id="catsDD">
<select name='cats_id' class="catsDD">
    <option value="0">Filter By Category</option>
    @php

        foreach($catsArr as $catsId => $cat) {
            echo '<option value="' . $catsId . '" ';
            if ($catsId == $searchCatsId) {
                echo "selected";
            }
            echo '>' . $cat . ' </option>';
        }

    @endphp
</select>
</div>