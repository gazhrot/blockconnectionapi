<?php

/* 
** 2016 - Axel Bruneaux
**
** @author : Axel Bruneaux <axel.bruneaux@epitech.eu>
** @copyright 2016 - Axel Bruneaux
*/

require_once __DIR__ . '/vendor/autoload.php';

if (!defined('_PS_VERSION_'))
	exit;

class BlockConnectionApi extends Module
{
	public function __construct()
	{
		$this->name = 'blockconnectionapi';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'Axel Bruneaux';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.7');
		$this->dependencies = array('blockcart');

		parent::__construct();

		$this->displayName = $this->l('Connection API');
		$this->description = $this->l('Connection API est un module qui permet de gerer la connection avec Facebook et Twitter.');

		$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

		if (!Configuration::get('CONNECTION_API'))      
			$this->warning = $this->l('No name provided');
	}

	public function getContent()
	{
		$output = null;
		
		if (Tools::isSubmit('submit'.$this->name))
		{
			$fb_app_id = strval(Tools::getValue('fb-app-id'));
			$fb_app_secret = strval(Tools::getValue('fb-app-secret'));
			$twitter_api_key = strval(Tools::getValue('twitter-api-key'));
			$twitter_api_secret = strval(Tools::getValue('twitter-api-secret'));

			if (!$fb_app_id  || empty($fb_app_id) || !Validate::isGenericName($fb_app_id))
				$output .= $this->displayError( $this->l('Invalid Configuration value') );
			elseif (!$fb_app_secret  || empty($fb_app_secret) || !Validate::isGenericName($fb_app_secret))
				$output .= $this->displayError( $this->l('Invalid Configuration value') );
			elseif (!$twitter_api_key  || empty($twitter_api_key) || !Validate::isGenericName($twitter_api_key))
				$output .= $this->displayError( $this->l('Invalid Configuration value') );
			elseif (!$twitter_api_secret  || empty($fb_app_id) || !Validate::isGenericName($fb_app_id))
				$output .= $this->displayError( $this->l('Invalid Configuration value') );
			else
			{
				Configuration::updateValue('fb-app-id', $fb_app_id);
				Configuration::updateValue('fb-app-secret', $fb_app_secret);
				Configuration::updateValue('twitter-api-key', $twitter_api_key);
				Configuration::updateValue('twitter-api-secret', $twitter_api_secret);
				$output .= $this->displayConfirmation($this->l('Settings updated'));
			}
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
    // Get default Language
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

    // Init Fields form array
		$fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Configuration API'),
				),
			'input' => array(
				array(
					'type' => 'text',
					'label' => $this->l('APP-ID Facebook'),
					'name' => 'fb-app-id',
					'size' => 20,
					'required' => true
					),
				array(
					'type' => 'text',
					'label' => $this->l('APP-SECRET Facebook'),
					'name' => 'fb-app-secret',
					'size' => 20,
					'required' => true
					),
				array(
					'type' => 'text',
					'label' => $this->l('Consumer Key (API Key) Twitter'),
					'name' => 'twitter-api-key',
					'size' => 20,
					'required' => true
					),
				array(
					'type' => 'text',
					'label' => $this->l('Consumer Secret (API Secret) Twitter'),
					'name' => 'twitter-api-secret',
					'size' => 20,
					'required' => true
					)
				),
			'submit' => array(
				'title' => $this->l('Save'),
				'class' => 'button'
				)
			);

		$helper = new HelperForm();

    // Module, token and currentIndex
		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

    // Language
		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

    // Title and toolbar
		$helper->title = $this->displayName;
	    $helper->show_toolbar = true;        // false -> remove toolbar
	    $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
	    $helper->submit_action = 'submit'.$this->name;
	    $helper->toolbar_btn = array(
	    	'save' =>
	    	array(
	    		'desc' => $this->l('Save'),
	    		'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
	    		'&token='.Tools::getAdminTokenLite('AdminModules'),
	    		),
	    	'back' => array(
	    		'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
	    		'desc' => $this->l('Back to list')
	    		)
	    	);

	    // Load current value
	    $helper->fields_value['fb-app-id'] 		 	= Configuration::get('fb-app-id');
	    $helper->fields_value['fb-app-secret'] 	 	= Configuration::get('fb-app-secret');
	    $helper->fields_value['twitter-api-key'] 	= Configuration::get('twitter-api-key');
	    $helper->fields_value['twitter-api-secret'] = Configuration::get('twitter-api-secret');

	    return $helper->generateForm($fields_form);
	}

	public function install()
	{
		if (Shop::isFeatureActive())
			Shop::setContext(Shop::CONTEXT_ALL);

		return parent::install() &&
		$this->registerHook('leftColumn') &&
		$this->registerHook('header') &&
		Configuration::updateValue('CONNECTION_API', 'axel');
		Configuration::updateValue('fb-app-id', 'Your fb app id.');
		Configuration::updateValue('fb-app-secret', 'Your fb app secret.');
		Configuration::updateValue('twitter-api-key', 'Your twitter api key.');
		Configuration::updateValue('twitter-api-secret', 'Your twitter api secret.');
	}

	public function uninstall()
	{
		return parent::uninstall() && Configuration::deleteByName('CONNECTION_API')
								   && Configuration::deleteByName('fb-app_id')
								   && Configuration::deleteByName('fb_app-secret')
								   && Configuration::deleteByName('twitter-api-key')
								   && Configuration::deleteByName('twitter-api-secret');
	}
}