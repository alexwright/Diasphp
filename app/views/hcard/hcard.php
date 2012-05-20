<div id='content'>
    <h1><?= $profile->forename ?> <?= $profile->surname ?></h1>
    <div id='content_inner'>
        <div class='entity_profile vcard author' id='i'>
            <h2>User profile</h2>
            <dl class='entity_nickname'>
                <dt>Nickname</dt>
                <dd>
                    <a class='nickname url uid' 
                       href='<?= site_url() ?>' 
                       rel="me"><?= $profile->forename ?> <?= $profile->surname ?></a>
                <dd>
            </dl>
            <dl class='entity_given_name'>
                <dt>Forename</dt>
                <dd><span class='given_name'><?= $profile->forename ?></span></dd>
            </dl>
            <dl class='entity_family_name'>
                <dt>Surname</dt>
                <dd><span class='family_name'><?= $profile->surname ?></span></dd>
            </dl>
            <dl class='entity_url'>
                <dt>URL</dt>
                <dd>
                    <a class='url' 
                       href='<?= site_url() ?>' 
                       id='pod_location' 
                       rel='me'><?= site_url() ?></a>
                </dd>
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
            <dl class='entity_photo_medium'>
                <dt>Photo</dt>
                <dd>
                    <img class='photo avatar' 
                         height='100px' 
                         width='100px' 
                         src='/static/images/<?= empty($profile->avatar) ? 'anon.png' : $profile->avatar ?>'>
                </dd>
            </dl>
            <dl class='entity_photo_small'>
                <dt>Photo</dt>
                <dd>
                    <img class='photo avatar' 
                         height='50px' 
                         width='50px' 
                         src='/static/images/<?= empty($profile->avatar) ? 'anon.png' : $profile->avatar ?>'>
                </dd>
            </dl>
            <dl class='entity_searchable'>
                <dt>Searchable</dt>
                <dd><span class='searchable'><?= $profile->searchable ? 'true' : 'false' ?></span></dd>
            </dl>
        </div>
    </div>
</div>
