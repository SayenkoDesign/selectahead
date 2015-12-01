<?php
/**
 * @version 2.1.1
 * @package Perfect Easy & Powerful Contact Form
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Piotr Moćko
 */

// No direct access
function_exists('add_action') or die;


add_action( 'widgets_init', 'pwebcontact_widgets_init' );

function pwebcontact_widgets_init() {
    
    register_widget( 'PWebContact_Widget' );
}

class PWebContact_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	public function __construct($id_base = 'pwebcontact-widget', $name = null, $widget_options = array(), $control_options = array()) {
        
        $name = ($name ? $name : __('Perfect Contact Form', 'pwebcontact'));
        
		/* Widget settings. */
		$widget_options = array_merge(array( 'classname' => 'pwebcontact', 'description' => __('Ajax Popup Contact Form.', 'pwebcontact') ), $widget_options);

		/* Widget control settings. */
		$control_options = array_merge(array( 'width' => 300, 'height' => 500, 'id_base' => 'pwebcontact-widget' ), $control_options);

		/* Create the widget. */
		parent::__construct($id_base, $name, $widget_options, $control_options);
        
        add_action('admin_head', array($this, 'admin_head'));
	}

	/**
	 * How to display the widget on the screen.
	 */
	public function widget( $args, $instance ) {
        
		extract( $args );
        
        if ( $instance['form_id'] AND PWebContact::initForm($instance['form_id'], 'widget') !== false AND PWebContact::isFormReady($instance['form_id'])) {

            /* Variables from the widget settings. */
            $title = apply_filters('widget_title', $instance['title'] );

            /* Before widget (defined by themes). */
            echo $before_widget;

            /* Display the widget title if one was input (before and after defined by themes). */
            if ( $title )
                echo $before_title . $title . $after_title;

            /* Display contact from. */
            PWebContact::displayForm( $instance['form_id'] );

            /* After widget (defined by themes). */
            echo $after_widget;
        }
	}

	/**
	 * Update the widget settings.
	 */
	public function update( $new_instance, $old_instance ) {
        
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['form_id'] = intval( $new_instance['form_id'] );

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	public function form( $instance ) {
        
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php if (isset($instance['title'])) echo $instance['title']; ?>" class="widefat" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'form_id' ); ?>"><?php _e('Contact form:', 'pwebcontact'); ?></label>
			<select id="<?php echo $this->get_field_id( 'form_id' ); ?>" name="<?php echo $this->get_field_name( 'form_id' ); ?>" class="widefat">
                <option value=""><?php _e('- Select form -', 'pwebcontact'); ?></option>
            <?php 
            $forms = $this->_get_pwebcontact_forms();
            foreach ($forms as $form) {
                echo '<option value="'.$form->id.'"'. selected($form->id, $instance['form_id'], false) . (!$form->publish ? ' class="disabled"' : '') .'>'.
                        $form->title . (!$form->publish ? __( ' [Disabled]', 'pwebcontact' ) : '') .'</option>';
            }
            ?>
            </select>
		</p>
        <?php
	}
    
    
    public function admin_head() {
        
        global $pagenow;
        
        if ($pagenow != 'widgets.php') return;
        
        ?><style type="text/css">option.disabled { color: rgba(51, 51, 51, .5); }</style><?php
    }
    
    
    protected function _get_pwebcontact_forms() {
        
        global $wpdb;
        
        $sql = $wpdb->prepare(
				'SELECT `id`, `title`, `publish` '.
				'FROM `'.$wpdb->prefix.'pwebcontact_forms` '.
                'WHERE `position` = "%s" '.
                'ORDER BY `title` ASC', 'widget');
        $data = $wpdb->get_results($sql);
        
        return is_array($data) ? $data : array();
    }
}

