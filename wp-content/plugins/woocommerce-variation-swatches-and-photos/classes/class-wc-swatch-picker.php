<?php
class WC_Swatch_Picker {
	private $size;
	private $attributes;
	private $selected_attributes;
	private $swatch_type_options;

	public function __construct($product_id, $attributes, $selected_attributes) {
		$this->swatch_type_options = maybe_unserialize(get_post_meta($product_id, '_swatch_type_options', true));
		
		if (!$this->swatch_type_options) {
			$this->swatch_type_options = array();
		}

		$product_configured_size = get_post_meta($product_id, '_swatch_size', true);
		if (!$product_configured_size) {
			$this->size = 'swatches_image_size';
		} else {
			$this->size = $product_configured_size;
		}

		$this->attributes = $attributes;
		$this->selected_attributes = $selected_attributes;
	}

	public function picker() {
		?>

		<table class="variations-table" cellspacing="0">
			<tbody>
				<?php
				$loop = 0;
				foreach ($this->attributes as $name => $options) : $loop++;
					$st_name = sanitize_title($name);
					?>
					<tr>
						<td><label for="<?php echo $st_name; ?>"><?php echo WC_Swatches_Compatibility::wc_attribute_label($name); ?></label></td>
						<td>
							<?php
							if (isset($this->swatch_type_options[$st_name])) {
								$picker_type = $this->swatch_type_options[$st_name]['type'];
								if ($picker_type == 'default') {
									$this->render_default($st_name, $options);
								} else {
									$this->render_picker($st_name, $options, $name);
								}
							} else {
								$this->render_default($st_name, $options);
							}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	public function render_picker($name, $options, $real_name = '') {
		$st_name = sanitize_title($name);
		$taxonomy_lookup_name = taxonomy_exists($st_name) ? $st_name : (taxonomy_exists($real_name) ? $real_name : $st_name);

		$selected_value = (isset($this->selected_attributes[$st_name])) ? $this->selected_attributes[$st_name] : '';
		?>

		<?php $layout = apply_filters('wc_swatches_and_photos_label_get_layout', (isset($this->swatch_type_options[$st_name]['layout']) ? $this->swatch_type_options[$st_name]['layout'] : 'default'), $name, $options, $this); ?>

		<?php if ($layout == 'label_above') : ?>
			<?php $this->render_picker_label_layout($layout, $name, $options); ?>
		<?php endif; ?>

		<?php do_action('wc_swatches_and_photos_label_before', $layout, $name, $options, $this); ?>

		<div 
			data-attribute-name="<?php echo 'attribute_' . $st_name; ?>"
			data-value="<?php echo $selected_value; ?>"
			id="<?php echo esc_attr($st_name); ?>" 
			class="select attribute_<?php echo $st_name; ?>_picker">

			<input type="hidden" name="<?php echo 'attribute_' . $st_name; ?>" id="<?php echo 'attribute_' . $st_name; ?>" value="<?php echo $selected_value; ?>" />

			<?php if (is_array($options)) : ?>
				<?php
				// Get terms if this is a taxonomy - ordered
				if (taxonomy_exists($taxonomy_lookup_name)) :
					$args = array('menu_order' => 'ASC');
					$terms = get_terms($taxonomy_lookup_name, $args);

					foreach ($terms as $term) :

						if (!in_array($term->slug, $options)) {
							continue;
						}


						if ($this->swatch_type_options[$name]['type'] == 'term_options') {
							$size = apply_filters('woocommerce_swatches_size_for_product', $this->size, get_the_ID(), $st_name);
							$swatch_term = new WC_Swatch_Term('swatches_id', $term->term_id, $taxonomy_lookup_name, $selected_value == $term->slug, $size);
						} elseif ($this->swatch_type_options[$name]['type'] == 'product_custom') {
							$size = apply_filters('woocommerce_swatches_size_for_product', $this->swatch_type_options[$st_name]['size'], get_the_ID(), $st_name);
							$swatch_term = new WC_Product_Swatch_Term($this->swatch_type_options[$name], $term->term_id, $taxonomy_lookup_name, $selected_value == $term->slug, $size);
						}


						do_action('woocommerce_swatches_before_picker_item', $swatch_term);
						echo $swatch_term->get_output();
						do_action('woocommerce_swatches_after_picker_item', $swatch_term);

					endforeach;
				else :
					foreach ($options as $option) :


						$size = apply_filters('woocommerce_swatches_size_for_product', $this->swatch_type_options[$st_name]['size'], get_the_ID(), $st_name);
						$swatch_term = new WC_Product_Swatch_Term($this->swatch_type_options[$st_name], $option, $name, $selected_value == sanitize_title($option), $size);

						do_action('woocommerce_swatches_before_picker_item', $swatch_term);
						echo $swatch_term->get_output();
						do_action('woocommerce_swatches_after_picker_item', $swatch_term);
					endforeach;
				endif;
				?>
			<?php endif; ?>
		</div>
		<?php
	}

	public function render_default($name, $options) {
		$st_name = sanitize_title($name);
		?>
		<select 
			data-attribute-name="<?php echo 'attribute_' . $st_name; ?>"
			id="<?php echo esc_attr($st_name); ?>" 
			name="attribute_<?php echo $st_name; ?>">
			<option value=""><?php echo __('Choose an option', 'woocommerce') ?>&hellip;</option>
			<?php if (is_array($options)) : ?>
				<?php
				$selected_value = (isset($this->selected_attributes[$st_name])) ? $this->selected_attributes[$st_name] : '';
				// Get terms if this is a taxonomy - ordered
				if (taxonomy_exists($st_name)) :
					$args = array('menu_order' => 'ASC');
					$terms = get_terms($st_name, $args);

					foreach ($terms as $term) :
						if (!in_array($term->slug, $options))
							continue;
						echo '<option value="' . esc_attr($term->slug) . '" ' . selected($selected_value, $term->slug) . '>' . $term->name . '</option>';
					endforeach;
				else :
					foreach ($options as $option) :
						echo '<option value="' . esc_attr(sanitize_title($option)) . '" ' . selected($selected_value, sanitize_title($option)) . '>' . $option . '</option>';
					endforeach;
				endif;
				?>
			<?php endif; ?>
		</select>
		<?php
	}

	public function render_picker_label_layout($layout, $name, $options) {
		$st_name = sanitize_title($name);
		?>

		<div 
			id="<?php echo esc_attr($st_name); ?>_label" 
			class="attribute_<?php echo $st_name; ?>_picker_label">
			&nbsp;
		</div>

		<?php
	}

}