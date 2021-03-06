<?php
	if( !defined('OPANDA_PROXY') ) {
		exit;
	}

	/**
	 * The class to proxy the request to the Twitter API.
	 */
	class OPanda_SignupHandler extends OPanda_Handler {

		/**
		 * Handles the proxy request.
		 */
		public function handleRequest()
		{

			// - context data

			$contextData = isset($_POST['opandaContextData'])
				? $_POST['opandaContextData']
				: array();
			$contextData = $this->normilizeValues($contextData);

			// - identity data

			$identityData = isset($_POST['opandaIdentityData'])
				? $_POST['opandaIdentityData']
				: array();
			$identityData = $this->normilizeValues($identityData);

			// prepares data received from custom fields to be transferred to the mailing service

			$identityData = $this->prepareDataToSave(null, null, $identityData);

			require_once OPANDA_BIZPANDA_DIR . '/admin/includes/leads.php';
			OPanda_Leads::add($identityData, $contextData);

			if( is_user_logged_in() ) {
				return false;
			}

			$email = $identityData['email'];
			if( empty($email) ) {
				return;
			}

			if( !email_exists($email) ) {

				$username = $this->generateUsername($email);
				$random_password = wp_generate_password($length = 12, false);

				$userId = wp_create_user($username, $random_password, $email);
				$userUrl = $identityData[$identityData['source'] . 'Url'];

				wp_update_user(array(
					'ID' => $userId,
					'first_name' => isset($identityData['name'])
						? $identityData['name']
						: null,
					'last_name' => isset($identityData['family'])
						? $identityData['family']
						: null,
					'display_name' => $identityData['displayName']
						? $identityData['displayName']
						: null,
					'user_url' => $userUrl
				));

				wp_new_user_notification($userId, $random_password);

				//todo: хук является устаревшим opanda_registered
				factory_000_do_action_deprecated('opanda_registered', array(
					$identityData,
					$contextData
				), '1.2.4', 'bizpanda_user_registered');

				do_action('bizpanda_user_registered', $identityData, $contextData);
			} else {
				$user = get_user_by('email', $email);
				$userId = $user->ID;
			}

			/*
			 * Unsafe code, should be re-written
			 */
			/*
			if ( !is_user_logged_in() ) {

				$mode = $this->options['mode'];

				if ( in_array( $mode, array('hidden', 'obvious')) ) {
					wp_set_auth_cookie( $userId, true );
				}
			}*/
		}

		protected function generateUsername($email)
		{

			$parts = explode('@', $email);
			if( count($parts) < 2 ) {
				return false;
			}

			$username = $parts[0];
			if( !username_exists($username) ) {
				return $username;
			}

			$index = 0;

			while( true ) {
				$index++;
				$username = $parts[0] . $index;

				if( !username_exists($username) ) {
					return $username;
				}
			}
		}
	}


