<?php

/**
 * Categories Page
 *
 * @package Metanotify\Admin
 */

/**
 * Metanotify_Categories_Page
 *
 * Displaying the Categories Page
 */
final class Metanotify_Categories_Page
{
	/**
	 * @var string
	 */
	const CATEGORIES_GROUP = 'metanotifyCategoriesGroup';

	/**
	 * @var array
	 */
	private $categories;

	/**
	 * Singleton
	 */
	public static function init()
	{
		static $self = null;

		if (null === $self) {
			$self = new self;
			add_action('admin_menu', array($self, 'add_menu_page'), 20);
			add_action('admin_enqueue_scripts', array($self, 'enqueueScripts'));

		}
	}

	/**
	 * Constructor
	 */
	private function __construct()
	{

	}

	/**
	 * Add page
	 *
	 * @see https://developer.wordpress.org/reference/hooks/admin_menu/
	 */
	public function add_menu_page()
	{
		$this->hook_name = add_submenu_page(
			'metanotify-settings',
			__('Categories', 'meta-notify'),
			__('Notifications', 'meta-notify'),
			'manage_options',
			'metanotify-categories',
			array($this, 'render')
		);
	}





	/**
	 * Render the categories page
	 *
	 * @internal  Callback.
	 */
	public function render()
	{
		// Add the Bootstrap CSS and JavaScript links
		echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">';

		$site_id = get_option('metanotify_site_id', "");

		$data = MetanotifyCategoryApi::getCategories($site_id);
		$metanotify_categories = $data->categories;
		$notification_data = MetanotifyCategoryApi::getNotifications($site_id);
		$notifications = $notification_data->notifications;
		$visitors_data = MetanotifyCategoryApi::getVisitors($site_id);
		$metanotify_visitors = $visitors_data->visitors;




		?>
		<div class="container">
			<ul class="nav nav-tabs">
				<li class="active"><a data-toggle="tab" href="#metanotify_tab_add_notifs">Add Notification</a></li>
				<li><a data-toggle="tab" href="#metanotify_tab_categories">Categories</a></li>
				<li><a data-toggle="tab" href="#metanotify_tab_visitors">Visitors</a></li>
				<li><a data-toggle="tab" href="#metanotify_tab_notifications">Notifications</a></li>
			</ul>
			<div class="tab-content" style="display:block;">
				<div id="metanotify_tab_add_notifs" class="tab-pane fade in active">
					<div class="wrap">
						<h3>Add Notifications</h3>
						<div class="col-md-6">
							<form class="w3-container">
								<div class="form-group">
									<label for="meta-notify-notification-title" data-toggle="tooltip" data-placement="top"
										title="Required">Notification Title <span class="required">*</span> :</label>
									<input class="form-control" placeholder=" TITLE" type="text"
										name="meta-notify-notification-title" id="meta-notify-notification-title" value=""
										required />
								</div>
								<div class="form-group">
									<label for="meta-notify-notification-body" data-toggle="tooltip" data-placement="top"
										title="Required">Notification Body <span class="required">*</span> : </label>
									<input class="form-control" placeholder=" BODY" type="text"
										name="meta-notify-notification-body" id="meta-notify-notification-body" value=""
										required />
								</div>
								<div class="form-group">
									<label for="meta-notify-notification-image" data-toggle="tooltip" data-placement="top"
										title="Enter image link here">Notification Image :</label>
									<input class="form-control" placeholder=" IMAGE LINK" type="text"
										name="meta-notify-notification-image" id="meta-notify-notification-image" value="" />
								</div>
								<div class="form-group">
									<label for="meta-notify-choosen-category" data-toggle="tooltip" data-placement="top"
										title="If no category is selected, notification will be pushed to all categories">Notification
										Category :</label>
									<select multiple class="form-control" name="meta-notify-choosen-category"
										id="meta-notify-choosen-category">
										<option value="">Select a Category</option>
										<?php

										if (isset($metanotify_categories)) {
											foreach ($metanotify_categories as $category) {
												echo '<option value="' . $category->id . '">' . $category->name . '</option>';
											}
										}
										?>
									</select>
								</div>
								<div class="form-group">
									<label for="meta-notify-choosen-visitor" data-toggle="tooltip" data-placement="top"
										title="If no visitor is selected,notification will be pushed to all visitors">Choose
										Visitors:</label>
									<select multiple class="form-control" name="meta-notify-choosen-visitors"
										id="meta-notify-choosen-visitors">
										<option value="">Select visitors</option>
										<?php

										if (isset($metanotify_visitors)) {
											foreach ($metanotify_visitors as $visitors) {
												echo '<option value="' . $visitors->visitorID . '">' . $visitors->visitorID . '</option>';
											}
										}
										?>
									</select>
								</div>
								<button type="button" id="meta-notify-notification-add-btn" class="button button-primary"
									data-plugin="meta-notify">
									<?php echo esc_html(__('Add Notification', 'meta-notify')); ?>
								</button>
							</form>
						</div>
						<div class="col-md-6">
						</div>
					</div>

					<table class="data-table table-bordered table-condensed">

						<thead>
							<tr>
								<th>ID</th>

								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<?php
							if (isset($notifications)) {
								foreach ($notifications as $notification) {
									echo "<tr>";
									echo "<td>" . esc_html($notification->notificationID) . "</td>";
									echo "<td>" . esc_html($notification->notificationStatus) . "</td>";
									echo "</tr>";
								}
							}
							?>



						</tbody>
					</table>

				</div>
				<div id="metanotify_tab_categories" class="tab-pane">
					<div class="wrap container">
						<h1>
							<?php echo esc_html(__('Categories', 'meta-notify')); ?>
						</h1>


						<table class="data-table table-bordered table-condensed">
							<thead>
								<tr>
									<th>ID</th>
									<th>Category Name</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<?php
								if (isset($metanotify_categories)) {
									foreach ($metanotify_categories as $metanotify_category) {

										echo "<tr>";
										echo '<td>' . esc_html($metanotify_category->id) . '</td>';
										echo '<td>' . esc_html($metanotify_category->name) . '</td>';

										echo "<td> <button type='button' class='meta-notify-category-delete-btn button button-primary'  data-category-id='" . $metanotify_category->id . "' data-plugin='meta-notify'>" . __('Delete Category', 'meta-notify') . "</button> </td>";
										echo "</tr>";
									}
								}
								?>




							</tbody>
						</table>
					</div>
				</div>
				<div id="metanotify_tab_visitors" class="tab-pane fade">
					<h3>Visitors</h3>
					<table class="data-table table-bordered table-condensed">
						<thead>
							<tr>
								<th>Visitor ID</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<?php
							if (isset($metanotify_visitors)) {
								foreach ($metanotify_visitors as $visitor) {
									echo "<tr>";
									echo '<td>' . esc_html($visitor->visitorID) . '</td>';
									echo '<td>' . esc_html($visitor->siteStatus) . '</td>';

									echo "</tr>";
								}
							}
							?>
						</tbody>
					</table>
				</div>
				<div id="metanotify_tab_notifications" class="tab-pane fade">
					<h3>Notifications</h3>
					<div id="table-container">
						<table class="data-table table-bordered table-condensed">
							<thead>
								<tr>
									<th> Id</th>
									<th> Title</th>
									<th> Body</th>
									<th> Image</th>
									<th> Status</th>

									<!-- add more <th> tags for additional columns -->
								</tr>
							</thead>
							<tbody>
								<?php
								global $wpdb;
								$table_name = 'metanotify_notifications';
								$results = $wpdb->get_results("SELECT * FROM $table_name");

								foreach ($results as $row) {
									echo '<tr>';
									echo '<td>' . esc_html($row->notification_id) . '</td>';
									echo '<td>' . esc_html($row->notification_title) . '</td>';
									echo '<td>' . esc_html($row->notification_body) . '</td>';
									echo '<td>' . esc_html($row->notification_image) . '</td>';
									echo '<td>' . esc_html($row->notification_status) . '</td>';
									echo '</tr>';
								}
								?>
							</tbody>
						</table>
					</div>



				</div>

			</div>
		</div>








		<?php

	}

	/**
	 * Enqueue assets
	 *
	 * @internal  Used as a callback.
	 */
	public function enqueueScripts($hook_name)
	{
		if ($hook_name !== $this->hook_name) {
			return;
		}

		wp_localize_script(
			'jquery-core',
			'metanotify',
			array(
				'nonce' => wp_create_nonce('m3t4n0t1fy'),
				'ajaxURL' => admin_url('admin-ajax.php'),
				'adminURL' => admin_url()
			)
		);
		wp_enqueue_script('metanotify_datatable', META_NOTIFY_URI . 'assets/js/datatable.min.js', [], META_NOTIFY_VER, true);
		// Enqueue DataTables JS
		wp_enqueue_script('metanotify_datatables', META_NOTIFY_URI . 'assets/js/jquery.dataTables.min.js', array('jquery'));
		wp_enqueue_script('metanotify_datatables-responsive', META_NOTIFY_URI . 'assets/js/dataTables.responsive.min.js', array('jquery'));
		// Enqueue DataTables CSS
		wp_enqueue_style('metanotify_datatables-css', META_NOTIFY_URI . 'assets/css/jquery.dataTables.min.css');
		wp_enqueue_style('metanotify_datatables-responsive-css', META_NOTIFY_URI . 'assets/css/responsive.dataTables.min.css');


		wp_enqueue_script('metanotify_category', META_NOTIFY_URI . 'assets/js/category.min.js', [], META_NOTIFY_VER, true);
		wp_enqueue_script('metanotify_notification', META_NOTIFY_URI . 'assets/js/notification.min.js', [], META_NOTIFY_VER, true);
		wp_enqueue_script('metanotify_visitor', META_NOTIFY_URI . 'assets/js/visitorlist.min.js', [], META_NOTIFY_VER, true);

	}




}


// Initialize the Singleton.
Metanotify_Categories_Page::init();