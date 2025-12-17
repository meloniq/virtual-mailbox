<?php
/**
 * Virtual Mailbox Cron class.
 *
 * @package Meloniq\VirtualMailbox
 */

namespace Meloniq\VirtualMailbox;

use WP_Query;

/**
 * Cron class for scheduling tasks.
 */
class Cron {

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'schedule' ) );
		add_action( 'vmbx_purge', array( $this, 'purge' ) );
	}

	/**
	 * Schedule the purge task.
	 *
	 * @return void
	 */
	public function schedule() {
		$frequecy = apply_filters( 'vmbx_purge_frequency', 'daily' );
		// make sure we have a schedule for purging old logs.
		if ( ! wp_next_scheduled( 'vmbx_purge' ) ) {
			wp_schedule_event( time() + 10, $frequecy, 'vmbx_purge' );
		}
	}

	/**
	 * Execute purge of old email logs.
	 *
	 * @return void
	 */
	public function purge(): void {
		$limit_days = (int) get_option( 'vmbx_days_limit' );
		if ( empty( $limit_days ) ) {
			return;
		}

		$this->_purge( $limit_days );
	}

	/**
	 * Purge old email logs.
	 *
	 * @param int $limit_days Days limit.
	 *
	 * @return void
	 */
	protected function _purge( int $limit_days ): void {
		if ( $limit_days < 1 ) {
			return;
		}

		$cutoff = date_create( "-$limit_days days" );

		$args_query = array(
			'post_type'      => 'vmbx_email',
			'posts_per_page' => 100,
			'date_query'     => array(
				array(
					'before' => $cutoff->format( 'Y-m-d' ),
				),
			),
			'fields'         => 'ids',
		);
		$args_query = apply_filters( 'vmbx_purge_query_args', $args_query );

		$query = new WP_Query( $args_query );

		$posts = $query->posts;
		if ( ! $posts ) {
			return;
		}

		foreach ( $posts as $post_id ) {
			wp_delete_post( $post_id, true );
		}
	}
}
