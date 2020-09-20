@php
echo '<form method="get">';
echo '<div class="sectionForm">';
echo "<input style='float:left;margin-right:4px;' type='text' name='search' size='20' placeholder='Search' value='" . $search . "'>";
echo " <input type='submit' value='Search' class='btn btn-primary'>";
echo " <a href='?' class='btn btn-info'>Reset</a>";
@endphp

@if (!empty($includeCats))
    @include('layouts.partials.catsdd', ['catsArr' => $catsArr, 'searchCatsId' => $searchCatsId])
@endif

@php
echo "</div>";
echo "</form>";

$ascActive = '';
$descActive = '';
$newActive = '';
$oldActive = '';
if ($sort == 'asc') {
    $ascActive = 'activeLink';
} elseif ($sort == 'old') {
    $oldActive = 'activeLink';
} elseif ($sort == 'desc') {
    $descActive = 'activeLink';
} else {
    $newActive = 'activeLink';
}
$viewSortStr = '';
if (!empty($viewSort)) {
    $viewSortStr = "&view=" . $viewSort;
}
$itemsIdStr = '';
if (!empty($itemsId)) {
    $itemsIdStr = '&items_id=' . $itemsId;
}
$searchQStr = '';
if (!empty($search)) {
    $searchQStr = "&search=" . urlencode($search);
}

echo '<ul style="padding-left:20px;padding-top:15px;float:left;" class="nav nav-pills">';

echo '<li class="nav-item" style="margin-left:10px;margin-top:10px;font-weight:bold;">Sort:</li>';

echo '<li class="nav-item">';
echo '<a style="padding-right:5px;padding-left:5px;" class="nav-link ' . $descActive . '" href="?sort=desc&cats_id=' . $searchCatsId . $searchQStr . $viewSortStr
 . $itemsIdStr . '">Desc</a>';
echo '</li>';

echo '<li class="nav-item">';
echo '<a style="padding-right:5px;padding-left:5px;" class="nav-link ' . $ascActive . '" href="?sort=asc&cats_id=' . $searchCatsId . $searchQStr . $viewSortStr .  $itemsIdStr . '">Asc</a>';
echo '</li>';

echo '<li class="nav-item">';
echo '<a style="padding-right:5px;padding-left:5px;" class="nav-link ' . $newActive . '" href="?sort=new&cats_id=' . $searchCatsId . $searchQStr . $viewSortStr .  $itemsIdStr . '">Newest</a>';
echo '</li>';

echo '<li class="nav-item">';
echo '<a style="padding-right:5px;padding-left:5px;" class="nav-link ' . $oldActive . '" href="?sort=old&cats_id=' . $searchCatsId . $searchQStr . $viewSortStr .  $itemsIdStr . '">Oldest</a>';
echo '</li>';



echo '</ul>';

@endphp


