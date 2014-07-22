<?php
/**
 * @package     etdhomecategories
 *
 * @version     1.0
 * @copyright   Copyright (C) 2014 Jean-Baptiste Alleaume. Tous droits réservés.
 * @license     http://alleau.me/LICENSE
 * @author      Jean-Baptiste Alleaume http://alleau.me
 */

if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

class EtdHomeCategories extends Module {

    public function __construct() {

        $this->name    = 'etdhomecategories';
        $this->tab     = 'front_office_features';
        $this->version = '1.0';
        $this->author  = 'ETD Solutions';

        parent::__construct();

        $this->displayName = $this->l('ETD Home Categories');
        $this->description = $this->l('Add a grid with home categories on the home page.');

    }

    public function install() {

        return parent::install() && $this->registerHook('displayHome');

    }

    public function hookDisplayHome() {

        $cacheId = $this->getCacheId();
        if (!$this->isCached('etdhomecategories.tpl', $cacheId)) {
            $this->smarty->assign(array(
                'showtitle'  => 1,
                'title'      => $this->l('Notre sélection de vanille du monde'),
                'categories' => $this->getHomeCategories()
            ));
        }

        return $this->display(__FILE__, 'etdhomecategories.tpl', $cacheId);

    }

    protected function getHomeCategories() {

        $order = Configuration::get('ETD_HOMECATS_ORDER');
        if ($order) {
            $order = json_decode($order);
        } else {
            $order = array();
        }

        $query = '
			SELECT c.`id_category`, cl.`name`, cl.`link_rewrite`, category_shop.`id_shop`
			FROM `' . _DB_PREFIX_ . 'category` c
			LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (c.`id_category` = cl.`id_category`' . Shop::addSqlRestrictionOnLang('cl') . ')
			' . Shop::addSqlAssociation('category', 'c') . '
			WHERE `id_lang` = ' . (int)$this->context->language->id . '
			AND c.`id_parent` > 1
			AND `active` = 1
			AND c.`is_root_category` = 1
			GROUP BY c.`id_category`';

        if (empty($order)) {
            $query .= 'ORDER BY category_shop.`position` ASC';
        } else {
            $query .= 'ORDER BY FIELD (category_shop.`id_category`,' . implode(',', $order) . ') ASC';
        }

        $categories = Db::getInstance(_PS_USE_SQL_SLAVE_)
                        ->executeS($query);

        return $categories;

    }

    public function getContent() {

        $output = '<h2>' . $this->displayName . '</h2>';
        if (Tools::isSubmit('submitOrder')) {
            $order = Tools::getValue('order');
            Configuration::updateValue('ETD_HOMECATS_ORDER', $order);
            $output .= '<div class="conf confirm">' . $this->l('Settings updated') . '</div>';
        }

        return $output . $this->displayForm();
    }

    public function displayForm() {

        $cats  = $this->getHomeCategories();
        $order = array();

        $this->context->controller->addCSS($this->_path . 'assets/css/etdhomecategories.css', 'all');
        $this->context->controller->addJqueryUI('ui.sortable');

        $html = '
		<form action="' . Tools::safeOutput($_SERVER['REQUEST_URI']) . '" method="post">
			<fieldset>
				<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" />' . $this->l('Settings') . '</legend>
				<label>' . $this->l('Ordre des catégories') . '</label>
				<div class="margin-form">
                    <ul id="sortable">';
        foreach ($cats as $category) {
            $order[] = $category['id_category'];
            $html .= '<li class="ui-state-default" data-id="' . $category['id_category'] . '"><img src="' . $this->context->link->getCatImageLink($category['link_rewrite'], $category['id_category'], 'home_default') . '"><br><span>' . $category['name'] . '</span></li>';
        }
        $html .= '
                    </ul>
                    <input type="hidden" name="order" value="' . str_replace('"', "'", json_encode($order)) . '">
                    <div style="clear:both"></div>
                </div>
                <div class="margin-form">
				    <input type="submit" name="submitOrder" value="' . $this->l('Save') . '" class="button" />
				</div>
			</fieldset>
		</form>
		<script>
            $(function() {
                $( "#sortable" ).sortable();
                $( "#sortable" ).disableSelection();
                $( \'input[name="submitOrder"]\' ).on("click", function(e) {
                    var order = "[";
                    $("#sortable li").each(function(i,e) {
                        order += $(e).data("id") + ",";
                    });
                    order = order.substr(0,order.length-1);
                    order += "]";
                    $("input[name=\"order\"]").val(order);
                });
            });
        </script>
		';

        return $html;
    }

}