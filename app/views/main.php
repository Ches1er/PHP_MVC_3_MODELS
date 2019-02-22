<h1><?=$hh?></h1>
<table>
    <tr>
        <th>id</th>
        <th>name</th>
        <th>year</th>
        <th>user_id</th>
    </tr>
<?php if (!empty($films)):;
foreach ($films as $film):?>
    <tr>
        <td><?=$film->id?></td>
        <td><?=$film->name?></td>
        <td><?=$film->year?></td>
        <td><?=$film->user()->login?></td>
        <td><?php $film->genres()?></td>
    </tr>
<?php endforeach;
endif?>
</table>
<h2>-!!!!!!!!!!!!!!!!!!-</h2>
<h1><?=$hh?></h1>
<table>
    <tr>
        <th>login</th>
        <th>film_name</th>
        <th>genre</th>
        <th>year</th>
    </tr>
    <?php foreach ($users_films as $u):?>
        <tr>
            <td><?=$u->name?></td>
            <td><?=$u->year?></td>
            <td><?=$u->user()->login?></td>
        </tr>
    <?php endforeach;?>
</table>