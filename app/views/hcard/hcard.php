<!doctype html>
<html>
    <head>
        <title><?= $profile->forename ?> <?= $profile->surname ?></title>
    </head>
    <body>
        <div class='entity_profile vcard author'>
            <h2>User profile</h2>
            <dl class='entity_nickname'>
                <dt>Nickname</dt>
                <dd>
                    <a class='nickname url uid' 
                       href='<?= site_url("u/{$profile->guid}") ?>' 
                       rel="me"><?= $profile->forename ?> <?= $profile->surname ?></a>
                <dd>
            </dl>
            <dl class='entity_family_name'>
                <dt>Forename</dt>
                <dd><span class='given_name'><?= $profile->forename ?></span></dd>
            </dl>
            <dl  class='entity_fn'>
                <dt>Surname</dt>
                <dd><span class='family_name'><?= $profile->surname ?></span></dd>
            </dl>
            <dl class='entity_photo'>
                <dt>Photo</dt>
                <dd>
                    <img class='photo avatar' 
                         height='100px' 
                         width='100px' 
                         src='/static/images/<?= empty($profile->avatar) ? 'anon.png' : $profile->avatar ?>'>
                </dd>
            </dl>
            <dl class='entity_searchable'>
                <dt>Searchable</dt>
                <dd><span class='searchable'><?= $profile->searchable ? 'true' : 'false' ?></span></dd>
            </dl>
        </div>
    </body>
</html>
