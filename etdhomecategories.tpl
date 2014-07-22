<!--[ETDHOOK:HOME3]-->
<div class="mod-etdhomecategories light">
    <div class="etd-block">
        {if $showtitle}
            <div class="title">
                <h1>{$title}</h1>
            </div>
        {/if}
        <div class="content">
            <ul class="thumbnails">
                {foreach $categories as $category}
                    <li class="">
                        <a href="{$link->getCategoryLink($category['id_category'], $category['link_rewrite'])}" title="{$category['name']}">
                            <h3><span>{$category['name']}</span></h3>
                            <img src="{$link->getCatImageLink($category['link_rewrite'], $category['id_category'], 'home_btt')}" alt="{$category['name']}">
                        </a>
                    </li>
                {/foreach}
            </ul>
        </div>
    </div>
</div>
<!--[/ETDHOOK:HOME3]-->