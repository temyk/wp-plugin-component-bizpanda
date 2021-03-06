<?php
	/**
	 * A class for the page providing the subscription settings.
	 *
	 * @author Paul Kashtanoff <paul@byonepress.com>
	 * @copyright (c) 2014, OnePress Ltd
	 *
	 * @package core
	 * @since 1.0.0
	 */

	/**
	 * The Subscription Settings
	 *
	 * @since 1.0.0
	 */
	class OPanda_SubscriptionSettings extends OPanda_Settings {

		public $id = 'subscription';

		public function init()
		{

			if( isset($_GET['opanda_aweber_disconnected']) ) {
				$this->success = __('Your Aweber Account has been successfully disconnected.', 'bizpanda');
			}
		}

		/**
		 * Shows the header html of the settings screen.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function header()
		{
			?>
			<p><?php _e('Set up here how you would like to save emails of your subscribers.', 'bizpanda') ?></p>
		<?php
		}

		/**
		 * Returns subscription options.
		 *
		 * @since 1.0.0
		 * @return mixed[]
		 */
		public function getOptions()
		{

			$options = array();

			$options[] = array(
				'type' => 'separator'
			);

			require_once OPANDA_BIZPANDA_DIR . '/admin/includes/subscriptions.php';
			$serviceList = OPanda_SubscriptionServices::getSerivcesList();

			// fix
			$service = get_option('opanda_subscription_service', 'database');
			if( $service == 'none' ) {
				update_option('opanda_subscription_service', 'database');
			}

			$listItems = array();

			foreach($serviceList as $serviceName => $serviceInfo) {

				$listItems[] = array(
					'value' => $serviceName,
					'title' => $serviceInfo['title'],
					'hint' => isset($serviceInfo['description'])
						? $serviceInfo['description']
						: null,
					'image' => isset($serviceInfo['image'])
						? $serviceInfo['image']
						: null,
					'hover' => isset($serviceInfo['hover'])
						? $serviceInfo['hover']
						: null
				);
			}

			$options[] = array(
				'type' => 'dropdown',
				'name' => 'subscription_service',
				'way' => 'ddslick',
				'width' => 450,
				'data' => $listItems,
				'default' => 'none',
				'title' => __('Mailing Service', 'bizpanda')
			);

			//todo: Фильтр opanda_subscription_services_options устарел
			$options = factory_000_apply_filters_deprecated("opanda_subscription_services_options", array(
				$options,
				$this
			), '1.2.4', "bizpanda_subscription_services_options");

			$options = apply_filters('bizpanda_subscription_services_options', $options, $this);

			$options[] = array(
				'type' => 'separator'
			);

			$options[] = array('type' => 'html', 'html' => array($this, 'showConfirmationMessageHeader'));

			$options[] = array(
				'type' => 'textbox',
				'name' => 'sender_email',
				'title' => __('Sender Email', 'bizpanda'),
				'hint' => __('Optional. A sender for confirmation emails.', 'bizpanda'),
				'default' => get_bloginfo('admin_email')
			);

			$options[] = array(
				'type' => 'textbox',
				'name' => 'sender_name',
				'title' => __('Sender Name', 'bizpanda'),
				'hint' => __('Optional. A sender name for confirmation emails.', 'bizpanda'),
				'default' => get_bloginfo('name')
			);

			$options[] = array(
				'type' => 'separator'
			);

			return $options;
		}

		public function showConfirmationMessageHeader()
		{
			?>
			<div class="form-group">
				<label class="col-sm-2 control-label"></label>

				<div class="control-group controls col-sm-10">
					<?php _e('If you are going to use Double Opt-In and send confirmation emails through Wordpress, fill the sender information below.', 'bizpanda') ?>
				</div>
			</div>
		<?php
		}

		/**
		 * Calls before saving the settings.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function onSaving()
		{
			//todo: хук является устаревшим opanda_on_saving_subscription_settings
			factory_000_do_action_deprecated("opanda_on_saving_subscription_settings", array($this), '1.2.4', "bizpanda_on_saving_subscription_settings");

			do_action('bizpanda_on_saving_subscription_settings', $this);
		}

		public function disconnectAweberAction()
		{

			delete_option('opanda_aweber_consumer_key');
			delete_option('opanda_aweber_consumer_secret');
			delete_option('opanda_aweber_access_key');
			delete_option('opanda_aweber_access_secret');
			delete_option('opanda_aweber_auth_code');
			delete_option('opanda_aweber_account_id');

			return $this->redirectToAction('index', array('opanda_aweber_disconnected' => true));
		}
	}
/*@mix:place*/