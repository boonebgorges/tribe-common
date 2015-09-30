<?php

class Tribe__Capabilities {
	private $cap_aliases = array(
		'editor' => array( // full permissions to a post type
			'read',
			'read_private_posts',
			'edit_posts',
			'edit_others_posts',
			'edit_private_posts',
			'edit_published_posts',
			'delete_posts',
			'delete_others_posts',
			'delete_private_posts',
			'delete_published_posts',
			'publish_posts',
		),
		'author' => array( // full permissions for content the user created
			'read',
			'edit_posts',
			'edit_published_posts',
			'delete_posts',
			'delete_published_posts',
			'publish_posts',
		),
		'contributor' => array( // create, but not publish
			'read',
			'edit_posts',
			'delete_posts',
		),
		'subscriber' => array( // read only
			'read',
		),
	);

	/**
	 * Grant caps for the given post type to the given role
	 *
	 * @param string $post_type The post type to grant caps for
	 * @param string $role_id The role receiving the caps
	 * @param string $level The capability level to grant (see the list of caps above)
	 *
	 * @return bool false if the action failed for some reason, otherwise true
	 */
	public function register_post_type_caps( $post_type, $role_id, $level = '' ) {
		if ( empty( $level ) ) {
			$level = $role_id;
		}
		if ( $level == 'administrator' ) {
			$level = 'editor';
		}
		if ( ! isset( $this->cap_aliases[ $level ] ) ) {
			return false;
		}
		$role = get_role( $role_id );
		if ( ! $role ) {
			return false;
		}
		$pto = get_post_type_object( $post_type );
		if ( empty( $pto ) ) {
			return false;
		}

		foreach ( $this->cap_aliases[ $level ] as $alias ) {
			if ( isset( $pto->cap->$alias ) ) {
				$role->add_cap( $pto->cap->$alias );
			}
		}
		return true;
	}

	/**
	 * Remove all caps for the given post type from the given role
	 *
	 * @param string $post_type The post type to remove caps for
	 * @param string $role_id The role which is losing caps
	 *
	 * @return bool false if the action failed for some reason, otherwise true
	 */
	public function remove_post_type_caps( $post_type, $role_id ) {
		$role = get_role( $role_id );
		if ( ! $role ) {
			return false;
		}
		foreach ( $role->capabilities as $cap => $has ) {
			if ( strpos( $cap, $post_type ) !== false ) {
				$role->remove_cap( $cap );
			}
		}

		return true;
	}

	/**
	 * Set the initial capabilities for events and related post types on default roles
	 *
	 * @return void
	 */
	public function set_initial_caps() {
		foreach ( array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' ) as $role ) {
			foreach ( Tribe__Main::get_post_types() as $post_type ) {
				$this->register_post_type_caps( $post_type, $role );
			}
		}
	}

	/**
	 * Remove capabilities for events and related post types from default roles
	 *
	 * @return void
	 */
	public function remove_all_caps() {
		foreach ( array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' ) as $role ) {
			foreach ( Tribe__Main::get_post_types() as $post_type ) {
				$this->remove_post_type_caps( $post_type, $role );
			}
		}
	}
}